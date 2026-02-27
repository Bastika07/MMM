<?php
/* Datenbank-Konnektivität
 * (und mehr - das hier nicht unbedingt hinein gehört...)
 */

require_once('constants.php');


/* `mysql_pconnect()` versucht, bestehende DB-Links wieder zu verwenden.
 * Entsprechend sollte es ausreichen, nur hier einmalig einen
 * Verbindungsaufbau durchzuführen; in den Scripts, die dieses Script
 * einbinden, ist der Aufruf dann nicht mehr erforderlich.
 *
 * Weiterhin kann man davon ausgehen, dass bei Einbindung dieses Scripts
 * (man beachte dessen Namen) auch eine DB-Verbindung genutzt werden soll.
 * Sollte dies tatsächlich in Einzelfällen nicht so sein und auch vermieden
 * werden, ist das ein guter Zeitpunkt, den benutzten, nicht in dieses
 * Script passenden (weil DB-irrelevanten) Code auszulagern.
 *
 * -Y0Gi, 31-Aug-2007
 */
DB::connect();



function microtime_float() {
    return microtime(true);
}


/* Prüft lediglich auf Rechte für den entsprechenden Bereich
* unabhängig von Mandanten - dies geschieht in den Templates.
*/
function BenutzerHatRechtMandant($iRecht, $iMandant=null) {
  global $loginID;
  if ($iMandant) {
    $q = 'SELECT USERID FROM RECHTZUORDNUNG WHERE USERID = ? AND RECHTID = ? AND MANDANTID = ?';
    return (DB::getOne($q, (int)$loginID, $iRecht, (int)$iMandant) == $loginID);
  }
  $q = 'SELECT USERID FROM RECHTZUORDNUNG WHERE USERID = ? AND RECHTID = ?';
  return (DB::getOne($q, (int)$loginID, $iRecht) == $loginID);
}

/* Prüft lediglich auf Rechte für den entsprechenden Bereich
* unabhängig von Mandanten - dies geschieht in den Templates.
*/
function BenutzerHatRecht($iRecht) {
  return BenutzerHatRechtMandant($iRecht);
}

/* Liefert einen ungefährlichen String zurück, egal ob magic quotes an sind oder nicht */
function safe($value) {
  return DB::$link->real_escape_string($value);
}


/* PELAS:
 *
 *   fehler($str)
 *     Zeigt eine standardisierte Fehlermeldung an.
 *
 *   confirm($str)
 *     Zeigt eine standardisierte Bestätigungsmeldung an.
 *
 *   mandantArray(): Array
 *     Gibt ein Array der Mandanten zurück, wobei die ID als Schlüssel und die
 *     Beschreibung als Wert eingesetzt werden.
 *
 *   teammemberArray($mandant=-1): Array
 *     Gibt ein Array aller Teammember zurück.  Wenn `$mandant != -1`, dann nur
 *     die Teammember von `$mandant`.
 *
 *   mandantByID(): String
 *     Gibt die Beschreibung des Mandanten, angegeben durch `$ID`, zurück.
 */

class PELAS {

		/* PW-Hash ermitteln */
		function HashPassword ($iPassword, $userID) {
			$salt = base64_encode($userID);
			$pwHash = sha1($salt.$iPassword);
			return $pwHash;
		}

		/* Die PayPal-Gebühren ausrechnen. Gebührenformel ATM hart kodiert */
		function PayPalGebuehr($summe) {
			$gebuehren = $summe / 100 * 1.9 + 0.35;
			/* MwSt. muss abgeführt werden, deswegen +19% */
			$gebuehren = $gebuehren * 1.19;
			return round($gebuehren, 2);
		}

    /* Eine Fehlermeldung ausgeben. */
    function fehler($msg) {
        printf('<p class="fehler">%s</p>'."\n", $msg);
    }

    /* Eine Bestätigung ausgeben. */
    function confirm($msg) {
        printf('<p class="confirm">%s</p>'."\n", $msg);
    }

    /* Zeigt eine Landesflagge an. */
    function displayFlag($countryCode) {
        $countryCode = strtolower($countryCode);
        if ($countryCode == '') {
            $countryCode = 'de';
        }
        $imgLink = '<img src="'.PELASHOST.'gfx/flags/'.$countryCode.'.png" border="0">';
        return $imgLink;
    }

    /* Formatiert die Bestellnummer mit führenden Nullen. */
    function formatBestellNr($partyId, $bestNr) {
        $bestNr = sprintf('%05d', $bestNr);
        $partyId = sprintf('%02d', $partyId);
        return $partyId . $bestNr;
    }

    /* Wandelt die Bestellnummer in einfache Zahlen als PartyID um. */
    function getPartyIdFromBestNr($bestellNr) {
        return intval(substr($bestellNr, 0, 2));
    }

    /* Wandelt die Bestellnummer in einfache Zahlen als BestellID um. */
    function getBestellIdFromBestNr($bestellNr) {
        return intval(substr($bestellNr, 2, 6));
    }

    /* Formatiert die Ticketnummer mit führenden Nullen. */
    function formatTicketNr($ticketNr) {
        return sprintf('%05d', $ticketNr);
    }

    /* Log to database. */
    function logging($msg, $cat=NULL, $userID=NULL) {
        if ($userID != NULL) {
            $userID = intval($userID);
        }
        if (empty($cat)) {
            $cat = NULL;
        }
        $sql = 'INSERT INTO `logging` (`userID`, `msg`, `cat`) VALUES (?, ?, ?)';
        return DB::query($sql, $userID, $msg, $cat) ? true : false;
    }

    /* Gibt ein Array in der Form
     *   $retval[2] = 'MultiMadness'
     *   ...
     * zurück.
     * Bei $rightcheck == 1 werden nur die Mandanten
     * zurückgegeben, zu denen der eingeloggte User die Rechte hat.
     */
    function mandantArray($rightcheck=False) {
        $q = 'SELECT MANDANTID id, BESCHREIBUNG name
              FROM MANDANT';
	$authorized = array();
	foreach (DB::getRows($q) as $row) {
            if (! $rightcheck or User::hatRecht('TEAMMEMBER', -1, $row['id'])) {
                $authorized[$row['id']] = $row['name'];
            }
	}
	return $authorized;
    }

    /* Gibt ein Array aller Teammitglieder zurück.
     * Bei `$mandant != -1` nur die Teammitglieder dieses Mandanten.
     */
    function teammemberArray($mandant=-1) {
        if ($mandant == -1) {
            $q = "SELECT USERID
                  FROM RECHTZUORDNUNG
                  WHERE RECHTID = 'TEAMMEMBER'
                  GROUP BY USERID";
            $res = DB::query($q);
        } else {
            $q = "SELECT USERID
                  FROM RECHTZUORDNUNG
                  WHERE RECHTID = 'TEAMMEMBER'
                  AND MANDANTID = ?";
            $res = DB::query($q, (int)$mandant);
        }
        $retval = array();
        while ($row = $res->fetch_row()) {
            array_push($retval, $row[0]);
        }
        return $retval;
    }

    /* Liefert die Beschreibung des Mandanten. */
    function mandantByID($mandant_id) {
        if (isset($mandant_id)) {
            $q = 'SELECT BESCHREIBUNG FROM MANDANT WHERE MANDANTID = ?';
            $res = DB::query($q, (int)$mandant_id);
            $row = $res->fetch_row();
            return $row[0];
        }
    }

    /* Liefert die aktuell aktive PartyID des Mandaten. */
    public static function mandantAktuelleParty($mandant_id) {
        if (isset($mandant_id)) {
            $q = "SELECT partyId FROM party WHERE mandantId = ? AND aktiv = 'J'";
            $res = DB::query($q, (int)$mandant_id);
            $row = $res->fetch_row();
            return $row[0];
        }
    }

    /* Liefert die kommende PartyID des Mandaten. Oder False wenn es noch keine gibt */
    public static function mandantNextParty($mandant_id, $aktuelle_party_id) {
		$q = "SELECT partyId
                  FROM party
                  WHERE mandantId = ?
                    AND terminVon > (SELECT terminVon FROM party WHERE partyId = ?)
				  ORDER BY terminVon ASC
				  LIMIT 1";
            $res = DB::query($q, (int)$mandant_id, (int)$aktuelle_party_id);
            $row = $res->fetch_row();
			
            if (isset($row[0]) && $row[0] > 0)
				return $row[0];
			else
				return false;
    }

    /* Liefert die MandantID anhand der aktuellen PartyID. */
    function AktuellePartyMandant($party_id) {
        if (isset($party_id)) {
            $q = 'SELECT mandantId FROM party WHERE partyId = ?';
            $res = DB::query($q, (int)$party_id);
            $row = $res->fetch_row();
            return $row[0];
        }
    }

    function userLink($user_id) {
        return (is_numeric($user_id))
            ? 'benutzerdetails.php?nUserID=' . $user_id
            : 'ID fehlt';
    }

    /* Gibt Tage, Stunden, Minuten bis $timestamp zurück. */
    function countdown($timestamp) {
        $diff = $timestamp - time();
        $rc['days'] = floor($diff / (24 * 60 * 60));
        $diff = $diff - $rc['days'] * (24 * 60 * 60);
        $rc['hours'] = floor($diff / (60 * 60));
        $diff = $diff - $rc['hours'] * (60 * 60);
        $rc['minutes'] = floor($diff / (60));
        $diff = $diff - $rc['minutes'] * (60);
        $rc['seconds'] = $diff;
        return $rc;
    }

}

# ---------------------------------------------------------------- #

/* User:
 *
 *   loginID(): Integer
 *     Gibt die ID des eingeloggten Users zurück.
 *
 *   name($userID=-1): String
 *     Gibt den Namen des Users zurück.
 *
 *   email($userID=-1): String
 *     Gibt die Email des Users zurück.
 *
 *   hatRecht($recht, $userID=-1, $mandantID=-1): Boolean
 *     Überprüft, ob aktueller User das Recht `$recht` hat.  Ist `$userID`
 *     angegeben, so wird auf diesen User überprüft. Ist `$mandantID` angegeben,
 *     so wird auf das $recht in Verbindung mit `$mandant` geprüft.
 */

class User {

    # ---------------------------------------------------------------- #
    # Methoden, deren Verwendung die Instanziierung eines User-Objekts
    # voraussetzt:
    #
    #  # aktueller Benutzer
    #  $currentUser = new User();
    #
    #  # Benutzer mit der ID 123
    #  $user123 = new User(123);

    function User($id=null) {
        $id = (int) $id;
        if ($id) {
	    		$this->id = $id;
				} else {
	    	$this->id = User::loginID();
				}
			$this->name = User::name($this->id);
    }

    /* Rechte des Benutzers ermitteln. */
    function getRights() {
        return DB::getCol('
            SELECT DISTINCT RECHTID
            FROM RECHTZUORDNUNG
            WHERE USERID = ?
            ', $this->id);
						
    }

    /* Liefert ein Array vom Organisatoren-Team des angefragten Benutzers zurück
		Im Format string wird weiterhin "Teamleiter" zurückgegeben, wenn es sich hierbei um den Teamleiter handelt
     */
    function orgaTeam($user, $format = "string") {
        $q = 'SELECT uet.*
	      FROM user_ext_team uet,
		  		USER_EXT ue
              WHERE ue.USERID = ?
                AND uet.id = ue.TEILTEAMID';
		
		$row = DB::getRow($q, (int)$user);
		if ( $row ) {
			if ($row['leader_id'] == $user || $row['proxy_id'] == $user) {
				$ret = "Head of ";
			} else {
				$ret = "Team ";
			}
			$ret .= $row['description'];
			if ($format == "string") {
				return $ret;
			} else {
				return $row['id'];
			}
		} else {
	        return "";
		}
    }

    /* Prüfen, ob er Benutzer das angegebene Recht hat.
     *
     * Ist `mandantID` angegeben, wird geprüft, ob der Benutzer das
     * angegebene Recht für den angegebenen Mandanten hat.
     */
    function hasRight($right, $mandantID=null) {
        $q = 'SELECT COUNT(*) > 0
	      FROM RECHTZUORDNUNG
              WHERE USERID = ?
                AND RECHTID = ?';
	$mandantID = (int) $mandantID;
        if ($mandantID) {
            # Nur gegen den angegebenen Mandanten prüfen.
            $q .= ' AND MANDANTID = ?';
            return DB::getOne($q, (int)$this->id, $right, $mandantID);
        }
        return DB::getOne($q, (int)$this->id, $right);
    }

    /* Liefert alle Mandanten, auf die der Benutzer mit dem angegebenen Recht
     * (default: 'TEAMMEMBER') Zugriff hat.
     */
    function getMandanten($right='TEAMMEMBER') {
        $rows = DB::getRows('
	    SELECT m.MANDANTID id, m.BESCHREIBUNG name
            FROM MANDANT m
	      INNER JOIN RECHTZUORDNUNG r USING (MANDANTID)
	    WHERE r.USERID = ?
	      AND r.RECHTID = ?
	    ', $this->id, $right);

	$authorized = array();
	foreach ($rows as $row) {
            $authorized[$row['id']] = $row['name'];
	}
	return $authorized;
    }

    # ---------------------------------------------------------------- #
    # Statische Methoden, die über `User::methode()` verwendet werden.

    function loginID() {
				global $loginID;
        return $loginID;
    }

    function name($userID=-1) {
        if ($userID == -1) {
            $userID = User::loginID();
        }
        $q = 'SELECT LOGIN FROM USER WHERE USERID = ?';
	return DB::getOne($q, (int)$userID);
    }

    function email($userID=-1) {
        if ($userID == -1) {
            $userID = User::loginID();
        }
        $q = 'SELECT EMAIL FROM USER WHERE USERID = ?';
	return DB::getOne($q, (int)$userID);
    }

    /*
     * $recht: nach welchen Recht wird geguckt (String)
     * $userID: User für den das Recht nachgeguckt wird (Integer)
     * $mandantID: Mandant, für den das Recht gelten soll (Integer)
     */
    static function hatRecht($recht, $userID=-1, $mandantID=-1) {
	$userID = (int) $userID;
        if ($userID == -1) {
            # Kein User angegeben, aktuellen User nehmen.
            $userID = User::loginID();
        }
	$mandantID = (int) $mandantID;
        $q = "SELECT COUNT(*) > 0
	      FROM RECHTZUORDNUNG
              WHERE USERID = ?
                AND RECHTID = ?";
        if ($mandantID != -1) {
            # Nur nach dem angegebenen Mandanten schauen.
            $q .= ' AND MANDANTID = ?';
            return DB::getOne($q, $userID, $recht, $mandantID);
        }
        return DB::getOne($q, $userID, $recht);
    }

    function istAngemeldet($userID=-1, $mandantID=-1) {
        global $nPartyID, $nLoginID, $STATUS_ANGEMELDET;
	$userID = (int) $userID;
        if ($userID == -1) {
            # Kein User angegeben, aktuellen User nehmen.
            $userID = $nLoginID;
        }
	$mandantID = (int) $mandantID;
        if ($mandantID == -1) {
          # Keine Party angegeben, aktuelle Party nehmen.
          $mandantID = $nPartyID;
        }
        $q = 'SELECT COUNT(*)
              FROM ASTATUS
              WHERE USERID = ?
                AND MANDANTID = ?
                AND STATUS = ?';
        return (DB::getOne($q, (int)$userID, (int)$mandantID, (int)$STATUS_ANGEMELDET) == 1);
    }

    /*
     * $userID: User für den das Recht nachgeguckt wird (Integer)
     * $mandantID: Mandant, bei dem geprüft werden soll (Integer)
     *
     * Es wird sowohl das alte als auch das neue System geprüft.
     * WICHTIG: Wenn neues System genutzt wird, Benutzer beim alten
     * auf abgemeldet setzen.
     */
    function hatBezahlt($userID=-1, $mandantID=-1) {
        global $nPartyID,
            $nLoginID,
            $STATUS_BEZAHLT,
            $STATUS_BEZAHLT_LOGE,
            $STATUS_BEZAHLT_VIPLOGE,
            $STATUS_COMFORT_4PERS,
            $STATUS_COMFORT_6PERS,
            $STATUS_COMFORT_8PERS,
            $STATUS_PREMIUM_4PERS,
            $STATUS_PREMIUM_6PERS,
            $STATUS_ZUGEORDNET,
            $STATUS_VIP_2PERS,
            $STATUS_VIP_4PERS;

        if ($userID == -1) {
            # Kein User angegeben, aktuellen User nehmen.
            $userID = $nLoginID;
        }
        if ($userID < 1) {
            # Ungültige UserID, auf -1 setzen.
            $userID = -1;
        }
        if ($mandantID == -1) {
            # Keine Party angegeben, aktuelle Party nehmen.
            $mandantID = $nPartyID;
        }
        # Sonst nur nach dem angegebenen Mandanten schauen.
        $q = "SELECT COUNT(*)
              FROM ASTATUS
              WHERE USERID = ?
                AND MANDANTID = ?
                AND STATUS IN ($STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, $STATUS_BEZAHLT_VIPLOGE, $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS,
                           $STATUS_COMFORT_8PERS, $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS,
                           $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS, $STATUS_VIP_4PERS)";
        $rc = (DB::getOne($q, (int)$userID, (int)$mandantID) == 1);

        # neues System
        $q = "SELECT t.userId
              FROM acc_tickets t,
                party p
              WHERE t.userId = ?
	        AND t.partyId = p.partyId
	        AND p.aktiv = 'J'
	        AND t.statusId = ?
	        AND p.mandantId = ?
            ";
        $res = DB::query($q, (int)$userID, ACC_STATUS_BEZAHLT, (int)$mandantID);
        if ($res->num_rows) {
            $rc = true;
        };

        return $rc;
  }


    /*
     * Prüft oder der abgefragte Benutzer einen bezahlten Supporterpass für die Parta hat
		 * $userID: User für den das Recht nachgeguckt wird (Integer)
     * $realPartyID: Echte Party-ID (Integer)
     *
     */
    function isSupporter($userID=-1, $realPartyID=-1) {
        global $nPartyID, $nLoginID;

        if ($userID == -1) {
            # Kein User angegeben, aktuellen User nehmen.
            $userID = $nLoginID;
        }
        if ($userID < 1) {
            # Ungültige UserID, auf -1 setzen.
            $userID = -1;
        }
        if ($realPartyID == -1) {
            # Keine Party angegeben, aktuelle Party nehmen.
            $realPartyID = PELAS::mandantAktuelleParty($nPartyID);
        }

        # Supporterpass suchen
        $q = "SELECT s.ownerId
              FROM acc_supporterpass s,
                party p
              WHERE s.ownerId = ?
	        AND s.partyId = p.partyId
	        AND p.aktiv = 'J'
	        AND s.statusId = ?
	        AND s.partyId = ?
            ";
	      $res = DB::query($q, (int)$userID, ACC_STATUS_BEZAHLT, (int)$realPartyID);
        if ($res->num_rows) {
            $rc = true;
        };
       return $rc;
  }



}

# ---------------------------------------------------------------- #

/* CFG:
 *
 *   get($key): String
 *     Holt den Konfigurationswert zu `$key`.
 */

class CFG {

    static function get($key) {
        global $dbname, $dbhost, $dbuser, $dbpass;
        $vars = array(
            'mysql' => array(
                'host' => $dbhost,
                'usr' => $dbuser,
                'pwd' => $dbpass,
                'db' => $dbname
            )
        );
        return $vars[$key];
    }

    /* Gibt einen Config-Wert zurück. */
    function getMandantConfig($key, $mandant=-1) {
        global $nPartyID;
        if ($mandant == -1) {
            # Keine Party angegeben, aktuelle Party nehmen.
            $mandant = $nPartyID;
        }
        # Sonst nur nach dem angegebenen Mandanten schauen.
        $q = 'SELECT STRINGWERT FROM CONFIG WHERE PARAMETER = ? AND MANDANTID = ?';
				$value = DB::getOne($q, $key, (int)$mandant);
				if ($value == "") {
					return -1;
				} else {
					return $value;
			  }
		}

}

# ---------------------------------------------------------------- #

/* DB:
 *
 *   connect(): Resource
 *     Verbindet zur DB und gibt den Resourcehandler zurück.
 *
 *   query($q): Resource
 *     Führt query `$q` aus und gibt die Resource zurück.  Beim Auftreten eines
 *     Fehlers wird eine entsprechende Fehlermeldung ausgegeben.
 */

class DB {

    public $queries;
    static $link = null;

    static function connect() {
        $CFG = CFG::get('mysql');
        $link = mysqli_connect($CFG['host'], $CFG['usr'], $CFG['pwd'], $CFG['db']);
        if (!$link) {
            die('Verbindung zum Datenbankserver konnte nicht hergestellt werden! (Zeile: '
                . __LINE__ . ")\n<br/>Fehler: " . mysqli_connect_error());
        }
        mysqli_set_charset($link, 'utf8');
        DB::$link = $link;
        return $link;
    }

    /* Ausführen eines Queries.
     *
     * Optional können Parameter übergeben werden, die als echte Prepared-Statement-
     * Parameter (MySQLi prepare/bind_param) übergeben werden. Dabei muss die Anzahl
     * der Platzhalter ('?') mit der Anzahl der Parameter übereinstimmen.
     */
    static function query($sql) {
        # Use a real prepared statement when parameters are provided via ? placeholders.
        if (func_num_args() > 1) {
            $params = array_slice(func_get_args(), 1);
            if (DB_STATISTICS) {
                $start = microtime_float();
            }
            $stmt = DB::$link->prepare($sql);
            if (!$stmt) {
                echo 'Query prepare failed: ' . DB::$link->error . "\n";
                return false;
            }
            $types = '';
            foreach ($params as $param) {
                if (is_int($param) || is_bool($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            if (DB_STATISTICS) {
                $GLOBALS['queries'][] = array(
                    'query' => $sql,
                    'time' => microtime_float() - $start);
            }
            if ($stmt->errno) {
                echo 'Query failed: ' . $stmt->error . "\n";
                return false;
            }
            return $stmt->get_result();
        }

	# Execute (and, optionally, measure) query.
        if (DB_STATISTICS) {
            $start = microtime_float();
        }
        $res = DB::$link->query($sql);
        if (DB_STATISTICS) {
            $GLOBALS['queries'][] = array(
	        'query' => $sql,
		'time' => microtime_float() - $start);
        }

        if ($res) {
            return $res;
        } else {
            echo 'Query failed: ' . DB::$link->error . "\n";
        }
    }

    function outputQueryStatistic() {
        $sum = 0;
        foreach ($GLOBALS['queries'] as $key => $val) {
            $sum += $val['time'];
        }
        foreach ($GLOBALS['queries'] as $key => $val) {
            $GLOBALS[$key]['percent'] = round($val['time'] / $sum, 2);
        }
        echo count($GLOBALS['queries']) . " queries\n";
        echo 'Summe: ' . round($sum, 4) . "\n";
        print_r($GLOBALS['queries']);
    }

    # ---------------------------------------------------------------- #
    # Hilfsmethoden zur Verwendung von Platzhaltern mit Auto-Escaping
    # gegen SQL Injection in Queries
    #
    # hinzugefügt von Y0Gi, 04-Sep-2007
    # Quelle: Database Abstraction Layer <http://homework.nwsnet.de/products/67>

    /* Escape quotes (' and ") to prevent SQL injection.
     *
     * Strings will be surrounded by quotes if they don't contain numbers only.
     */
    function quote($str) {
	# Quote query string.
        $str = DB::$link->real_escape_string($str);

        # Quote string if not an integer.
        if (! is_numeric($str) or (intval($str) != $str)) {
            $str = "'" . $str . "'";
        }
        return $str;
    }

    /* Process ? placeholders in the query string. */
    function buildQuery($sql, $params=array()) {
        # Transform ? placeholders to %s for use with vsprintf().
        $sql_raw = strtr($sql, array('%' => '%%', '?' => '%s'));

        # Quote each parameter and build the argument list.
        $quoted = array_map(array('DB', 'quote'), $params);

        return vsprintf($sql_raw, $quoted);
    }

    # ---------------------------------------------------------------- #
    # Hilfsmethoden für den Zugriff auf SELECT-Ergebnisse
    #
    # hinzugefügt von Y0Gi, 10-Apr-2007
    # Quelle: Database Abstraction Layer <http://homework.nwsnet.de/products/67>

    /* Fetch and return the value of the first column of the first row. */
    static function getOne($sql) {
        $args = func_get_args();
        ##$result = DB::query($sql);
	$result = call_user_func_array(array('DB', 'query'), $args);
        $row = $result->fetch_array();
        return $row[0];
    }

    /* Fetch a single column and return it as array. */
    static function getCol($sql) {
        $args = func_get_args();
        $fields = array();
        for (
            ##$result = DB::query($sql);
	    $result = call_user_func_array(array('DB', 'query'), $args);
            $row = $result->fetch_array();
            $fields[] = $row[0]);
        return $fields;
    }

    /* Fetch the first row and return an associative array using the column
     * names as keys and the row's fields as their values.
     */
    static function getRow($sql) {
        $args = func_get_args();
        ##$result = DB::query($sql);
	$result = call_user_func_array(array('DB', 'query'), $args);
        return $result->fetch_assoc();
    }

    /* Fetch multiple rows and return an array of associative arrays that use
     * the column names as keys and the row's fields as their values.
     */
    static function getRows($sql) {
        $args = func_get_args();
        $rows = array();
        for (
            ##$result = DB::query($sql);
	    $result = call_user_func_array(array('DB', 'query'), $args);
            $row = $result->fetch_assoc();
            $rows[] = $row);
        return $rows;
    }
}

# ---------------------------------------------------------------- #
# Backward-compatible wrappers for legacy mysql_* calls.
# The mysql_* extension was removed in PHP 7. These wrappers provide
# the same interface using the mysqli connection stored in DB::$link.

if (!function_exists('mysql_query')) {
    function mysql_query($sql, $link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->query($sql);
    }
}
if (!function_exists('mysql_fetch_row')) {
    function mysql_fetch_row($result) {
        return $result->fetch_row();
    }
}
if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result) {
        return $result->fetch_assoc();
    }
}
if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result, $type = MYSQLI_BOTH) {
        return $result->fetch_array($type);
    }
}
if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows($result) {
        return $result->num_rows;
    }
}
if (!function_exists('mysql_affected_rows')) {
    function mysql_affected_rows($link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->affected_rows;
    }
}
if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id($link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->insert_id;
    }
}
if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($str, $link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->real_escape_string($str);
    }
}
if (!function_exists('mysql_escape_string')) {
    function mysql_escape_string($str) {
        return DB::$link->real_escape_string($str);
    }
}
if (!function_exists('mysql_error')) {
    function mysql_error($link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->error;
    }
}
if (!function_exists('mysql_errno')) {
    function mysql_errno($link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->errno;
    }
}
if (!function_exists('mysql_select_db')) {
    function mysql_select_db($dbname, $link = null) {
        $conn = ($link !== null) ? $link : DB::$link;
        return $conn->select_db($dbname);
    }
}
if (!function_exists('mysql_free_result')) {
    function mysql_free_result($result) {
        return $result->free();
    }
}
if (!function_exists('mysql_result')) {
    function mysql_result($result, $row, $field = 0) {
        $result->data_seek($row);
        $r = $result->fetch_array();
        if ($r === null) {
            return false;
        }
        if (is_string($field) && strpos($field, '.') !== false) {
            $field = substr($field, strrpos($field, '.') + 1);
        }
        return isset($r[$field]) ? $r[$field] : false;
    }
}
if (!function_exists('mysql_data_seek')) {
    function mysql_data_seek($result, $row) {
        return $result->data_seek($row);
    }
}
if (!function_exists('mysql_num_fields')) {
    function mysql_num_fields($result) {
        return $result->field_count;
    }
}
if (!function_exists('mysql_field_name')) {
    function mysql_field_name($result, $field_offset) {
        $field = $result->fetch_field_direct($field_offset);
        return ($field !== false) ? $field->name : false;
    }
}

# ---------------------------------------------------------------- #
# CSRF token helpers
#
# Usage:
#   In every state-changing form add:  <?= csrf_field(); ? >
#   At the top of the POST handler:    csrf_verify();
#
# The token is generated once per PHP session and stored in
# $_SESSION['MMMSESSION']['csrf_token'].

/**
 * Return the current CSRF token string, generating one if needed.
 */
function csrf_token() {
    if (empty($_SESSION['MMMSESSION']['csrf_token'])) {
        $_SESSION['MMMSESSION']['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['MMMSESSION']['csrf_token'];
}

/**
 * Return an HTML hidden-input field carrying the CSRF token.
 * Embed this inside every HTML <form> that performs a state change.
 */
function csrf_field() {
    return '<input type="hidden" name="_csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
        . '">';
}

/**
 * Verify the CSRF token submitted with the current POST request.
 * Terminates with HTTP 403 on failure.
 *
 * The function is a no-op for non-state-changing methods (GET, HEAD, OPTIONS).
 * Call it at the top of any request handler that modifies state.
 */
function csrf_verify() {
    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
    // CSRF is only relevant for state-changing methods.
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }
    $submitted = isset($_POST['_csrf_token']) ? $_POST['_csrf_token'] : '';
    $expected  = csrf_token();
    if (!hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('CSRF token mismatch. Request blocked.');
    }
}
?>

