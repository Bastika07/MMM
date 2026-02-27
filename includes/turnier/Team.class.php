<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once("turnier/t_constants.php");
require_once("turnier/TurnierAdmin.class.php");

/**
 * Team bezogene Funktionen
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/14 ore - initial version
 * @version 2004/07/16 ore - fehlercodes, rechtechecks, namelist, testing
 * @version 2004/07/17 ore - caching
 * @version 2004/07/18 ore - user-exists-check, keysort(), leaderuebergabe
 * @version 2004/07/19 ore - getTeamCount
 * @version 2004/07/23 ore - typsicherheit
 */
class Team {
	/**
	 * @var int Teamid (primary key)
	 */
	var $teamid = 0;
	/**
	 * @var int Turnierid
	 */
	var $turnierid = 0;
	/**
	 * @var string Name des Teams (uniqe mit Turnierid)
	 */
	var $name = '';
	/**
	 * @var string Ligaid des Teams (uniqe mit Turnierid)
	 */
	var $ligaid = '';
	/**
	 * @var int Flags
	 */
	var $flags = 0;
	/**
	 * @var int groesse des Teams
	 */
	var $size = 0;
	/**
	 * @ignore
	 */
	var $userlist = array();
	/**
	 * @ignore
	 */
	var $namelist = array();
	/**
	 * @var int Userid des Leaders
	 */
	var $leader = 0;


	/**
	 * checkt ob userid ein Member ist
	 * @param int $userid User der ueberprueft wird
	 * @return boolean TRUE | FALSE
	 */
	function isMember($userid) {
		return isset($this->userlist[$userid]) && ($this->userlist[$userid] & TEAM2USER_MEMBER);
	}


	/**
	 *  checkt ob userid der Leader ist
	 * @param int $userid User der ueberprueft wird
	 * @return boolean TRUE | FALSE
	 */
	function isLeader($userid) {
		return ($userid == $this->leader);
	}


	/**
	 * fuegt einen user in die aufnahmeliste des teams hinzu
	 * @param int $userid User der aufgenommen werden will
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function addQueue($userid) {
		/* ist bereits im team, oder will bereits joinen */
		if (isset($this->userlist[$userid]))
			return TS_SUCCESS;

		/* namen holen */
		$login = COMPAT::getLoginByID($userid);
		if (!isset($login))
			return TS_ERROR;

		$this->userlist[$userid] = (TEAM2USER_QUEUED | TEAM2USER_NEW);
		$this->namelist[$userid] = $login;

		/* nach ID bzw. Name sortieren */
		ksort($this->userlist);
		natcasesort($this->namelist);
		return TS_SUCCESS;
	}


	/**
	 * enfernt einen user aus der waitlist und fuegt ihn in die teamliste ein
	 * @param int $userid User der aufgenommen wird
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function acceptUser($userid) {
		/* ist nicht in unserem team, und will auch nicht joinen */
		if (!isset($this->userlist[$userid]))
			return TS_ERROR;

		/* ist bereits in unserem team */
		if ($this->isMember($userid))
			return TS_SUCCESS;

		$this->userlist[$userid] &= ~TEAM2USER_QUEUED;
		$this->userlist[$userid] |= (TEAM2USER_MEMBER | TEAM2USER_MODIFY | TEAM2USER_LIGAMAIL);
		$this->size++;

		/* nach ID bzw. Name sortieren */
		ksort($this->userlist);
		return TS_SUCCESS;
	}


	/**
	 * fuegt dem team einen user hinzu (ohne queue).
	 * erster User des Teams wird automatisch Leader
	 * @param int $userid User der hinzugefuegt wird
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function addUser($userid) {
		/* ist bereits in unserem team, oder will bereits joinen */
		if (isset($this->userlist[$userid]))
			return $this->acceptUser($userid);

		/* namen holen */
		$login = COMPAT::getLoginByID($userid);
		if (!isset($login))
			return TS_ERROR;

		/* wenn das Team leer ist, wird der erste User automatisch zum Leader */
		if(count($this->userlist) == 0) {
			$this->userlist[$userid] = (TEAM2USER_LEADER | TEAM2USER_MEMBER | TEAM2USER_NEW | TEAM2USER_LIGAMAIL);
			$this->leader = $userid;
		} else {
			$this->userlist[$userid] = (TEAM2USER_MEMBER | TEAM2USER_NEW | TEAM2USER_LIGAMAIL);
		}
		$this->namelist[$userid] = $login;
		$this->size++;

		/* nach ID bzw. Name sortieren */
		ksort($this->userlist);
		natcasesort($this->namelist);
		return TS_SUCCESS;
	}


	/**
	 * entfernt einen user aus dem team
	 * @param int userid User der entfernt werden soll
	 * @return TS_SUCCESS | TS_ERROR
	 */
	function delUser($userid) {
		/* ist nicht in unserem team, und will auch nicht joinen */
		if (!isset($this->userlist[$userid]))
			return TS_ERROR;

		$this->userlist[$userid] &= ~(TEAM2USER_QUEUED | TEAM2USER_MEMBER | TEAM2USER_LEADER);
		$this->userlist[$userid] |= TEAM2USER_DELETE;
		$this->size--;
		return TS_SUCCESS;
	}


	/**
	 * setzt einen neuen Team Leader
	 * @param int userid User der Leader werden soll
	 * @return TS_SUCCESS | TS_ERROR
	 */
	function setLeader($userid) {
		/* ist nicht im Team oder ist noch kein member */
		if (!$this->isMember($userid))
			return TS_ERROR;

		/* Leader ruecksetzen */
		foreach ($this->userlist as $user => $flags) {
			if ($this->userlist[$user] & TEAM2USER_LEADER) {
				$this->userlist[$user] &= ~TEAM2USER_LEADER;
				$this->userlist[$user] |= TEAM2USER_MODIFY;
			}
		}

		/* neuen Leader setzen */
		$this->userlist[$userid] |= (TEAM2USER_LEADER | TEAM2USER_MODIFY);
		$this->leader = $userid;
		return TS_SUCCESS;
	}


	/**
	 * gibt zurueck ob das Team Ergebnisuebermittlung mit Email adressen will
	 * @param int $userid User für den abgefragt werden soll, default: alle user
	 * @return mixed boolean oder array
	 */
	function getLigaMail($userid = -1) {
		if ($userid != -1) {
			if (!isset($this->userlist[$userid]))
				return false;
			return ($this->userlist[$userid] & TEAM2USER_LIGAMAIL) == TEAM2USER_LIGAMAIL;
		}

		$retval = array();
		foreach ($this->userlist as $userid => $flags)
			$retval[$userid] = (($flags & TEAM2USER_LIGAMAIL) == TEAM2USER_LIGAMAIL);

		return $retval;
	}

	/**
	 * setzt die liga email uebertragungsberechtigungsblub
	 * @param int $userid
	 * @param boolean $flag
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function setLigaMail($userid, $flag) {
		if (!isset($this->userlist[$userid]))
			return TS_ERROR;

		if ($flag) {
			$this->userlist[$userid] |= (TEAM2USER_LIGAMAIL | TEAM2USER_MODIFY);
		} else {
			$this->userlist[$userid] &= ~TEAM2USER_LIGAMAIL;
			$this->userlist[$userid] |= TEAM2USER_MODIFY;
		}
		return TS_SUCCESS;
	}


	/**
	 * laed ein Team-Objekt aus der Datenbank.
	 * das Objekt wird gecached.
	 * @param int $teamid Teamid
	 * @param int $turnierid Turnierid
	 * @param boolean $flush True um ein Objekt aus dem cache zu loeschen
	 * @return mixed Team | TS_ERROR
	 * @todo cache ist nicht eindeutig (bei mehreren turnieren)
	 * @static
	 */
	function load($turnierid, $teamid, $flush = FALSE) {
		static $cache = array();

		if (!isset($teamid) || ($teamid < 1) || !isset($turnierid) || ($turnierid < 1))
			return TS_ERROR;

		/* eintrag aus cache loeschen */
		if ($flush) {
			unset($cache[$turnierid][$teamid]);
			return TS_SUCCESS;
		}

		/* eintrag aus cache holen */
		if ((isset($cache[$turnierid])) && (isset($cache[$turnierid][$teamid]))) {
			return $cache[$turnierid][$teamid];
		}

		$retval = new Team();

		$sql = "SELECT * FROM t_team WHERE turnierid = '{$turnierid}' AND teamid = '{$teamid}'";
		$res = DB::query($sql);
		if (mysql_num_rows($res) < 1)
			return TS_ERROR;

		$row = mysql_fetch_assoc($res);

		$retval->teamid		= (int)$row['teamid'];
		$retval->turnierid	= (int)$row['turnierid'];
		$retval->name		= (string)$row['name'];
		$retval->ligaid		= (string)$row['ligaid'];
		$retval->flags		= (int)$row['flags'];

		$sql = "SELECT t2u.userid, t2u.flags, u.LOGIN as login
			FROM t_team2user t2u
			LEFT JOIN USER u ON t2u.userid = u.USERID
			WHERE t2u.teamid = '{$teamid}'
			AND t2u.turnierid = '{$turnierid}'";
		$res = DB::query($sql);

		while ($row = mysql_fetch_assoc($res)) {
			$retval->userlist[$row['userid']] = (int)$row['flags'];
			$retval->namelist[$row['userid']] = (string)$row['login'];

			if ($row['flags'] & TEAM2USER_MEMBER)
				$retval->size++;

			if ($row['flags'] & TEAM2USER_LEADER)
				$retval->leader = $row['userid'];
		}

		if (!isset($cache[$turnierid]))
			$cache[$turnierid] = array();
		
		$cache[$turnierid][$teamid] = $retval;

		/* nach ID bzw. Name sortieren */
		ksort($retval->userlist);
		natcasesort($retval->namelist);
		return $retval;
	}


	/**
	 * Sichert ein Team-Objekt in der Datenbank.
	 * flushed cache von load()
	 * @return int TS_SUCCESS
	 * @todo wann Fehler zurueckgeben?
	 */
	function save() {
		$sql = "UPDATE t_team SET
				name = '".mysql_escape_string($this->name)."',
				ligaid = '".mysql_escape_string($this->ligaid)."',
				flags = '{$this->flags}',
				wann_geaendert = '".time()."',
				wer_geaendert = '".COMPAT::currentID()."'
			WHERE	teamid = '{$this->teamid}'
			AND	turnierid = '{$this->turnierid}'";
		DB::query($sql);

		foreach ($this->userlist as $userid => $flags) {
			$dbflags = $flags & (TEAM2USER_QUEUED | TEAM2USER_MEMBER | TEAM2USER_LEADER | TEAM2USER_LIGAMAIL);

			// neuer user
			if (($flags & (TEAM2USER_NEW | TEAM2USER_DELETE)) == TEAM2USER_NEW) {
				$sql = "INSERT INTO t_team2user SET
						teamid = '{$this->teamid}',
						turnierid = '{$this->turnierid}',
						userid = '{$userid}',
						flags = '{$dbflags}',
						wann_angelegt = '".time()."',
						wer_angelegt = '".COMPAT::currentID()."'";
				DB::query($sql);

			// user entfernt
			} else if (($flags & (TEAM2USER_NEW | TEAM2USER_DELETE)) == TEAM2USER_DELETE) {
				$sql = "DELETE FROM t_team2user
					WHERE teamid = '{$this->teamid}'
					AND turnierid = '{$this->turnierid}'
					AND userid = '{$userid}'";
				DB::query($sql);

			// user geaendert
			} else if (($flags & (TEAM2USER_NEW | TEAM2USER_MODIFY | TEAM2USER_DELETE)) == TEAM2USER_MODIFY) {
				$sql = "UPDATE t_team2user SET
						flags = '{$dbflags}',
						wann_geaendert = '".time()."',
						wer_geaendert = '".COMPAT::currentID()."'
					WHERE teamid = '{$this->teamid}'
					AND turnierid = '{$this->turnierid}'
					AND userid = '{$userid}'";
				DB::query($sql);
			}

			$this->userlist[$userid] = $dbflags;
		}

		/* flush load() cache */
		Team::load($this->teamid, $this->turnierid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * Erzeugt ein neues Objektabbild in der Datenbank.
	 * flushed cache von load()
	 * @param boolean $force mit Teamid erzeugen (Coverage import)
	 * @return int TS_SUCCESS | TS_ERROR
	 * @todo wann Fehler zurueckgeben?
	 */
	function create($force = FALSE) {
		if (!$force) {
			if (!isset($this->turnierid) || !isset($this->name))
				return TS_ERROR;

			$sql = "SELECT MAX(teamid) as lastid FROM t_team WHERE turnierid = '{$this->turnierid}'";
			$res = DB::query($sql);
			$row = mysql_fetch_assoc($res);
			$this->teamid = (isset($row['lastid'])) ? $row['lastid'] +1 : 1;
		}

		$sql = "INSERT INTO t_team SET
				teamid = '{$this->teamid}',
				turnierid = '{$this->turnierid}',
				name = '".mysql_escape_string($this->name)."',
				ligaid = '".mysql_escape_string($this->ligaid)."',
				flags = '{$this->flags}',
				wann_angelegt = '".time()."',
				wer_angelegt = '".COMPAT::currentID()."'";
		DB::query($sql);

		foreach ($this->userlist as $userid => $flags) {
			$dbflags = $flags & (TEAM2USER_QUEUED | TEAM2USER_MEMBER | TEAM2USER_LEADER | TEAM2USER_LIGAMAIL);

			// neuer user
			if ($flags & TEAM2USER_NEW) {
				$sql = "INSERT INTO t_team2user SET
						teamid = '{$this->teamid}',
						turnierid = '{$this->turnierid}',
						userid = '{$userid}',
						flags = '{$dbflags}',
						wann_angelegt = '".time()."',
						wer_angelegt = '".COMPAT::currentID()."'";
				DB::query($sql);
			}
			$this->userlist[$userid] = $dbflags;
		}

		/* flush load() cache (eigentlich nicht noetig...) */
		Team::load($this->teamid, $this->turnierid, TRUE);

		return TS_SUCCESS;
	}


	/**
	 * Loescht Objekt aus der Datenbank.
	 * @param int $turnierid Turnier
	 * @param int $teamid Team (optional)
	 * @return int TS_SUCCESS
	 * @static
	 */
	function delete($turnierid, $teamid = -1) {
		$sql = "DELETE FROM t_team WHERE turnierid = '{$turnierid}'";
		if ($teamid != -1)
			$sql .= " AND teamid = '{$teamid}'";
		DB::query($sql);

		$sql = "DELETE FROM t_team2user WHERE turnierid = '{$turnierid}'";
		if ($teamid != -1)
			$sql .= " AND teamid = '{$teamid}'";
		DB::query($sql);

		return TS_SUCCESS;
	}


	/**
	 * holt eine Liste aller Teams dieses Turniers
	 * @param int $turnierid Turnierid
	 * @return mixed Array von Teams
	 * @static
	 */
	function getTeamNameList($turnierid) {
		$sql = "SELECT t.teamid, t.name, COUNT(t2u.teamid) as size
			FROM t_team as t
			LEFT JOIN t_team2user t2u ON t.teamid = t2u.teamid AND t.turnierid = t2u.turnierid
			AND (t2u.flags & '".TEAM2USER_MEMBER."') = '".TEAM2USER_MEMBER."'
			WHERE t.turnierid = '{$turnierid}'
			GROUP BY teamid
			ORDER BY t.name";

		$res = DB::query($sql);

		$retval = array();
		while ($row = mysql_fetch_assoc($res))
			$retval[$row['teamid']] =  $row;

		return $retval;
	}


	/**
	 * Gibt die Anzahl der angemeldeten Teams zurueck
	 * @param int $turnierid Turnierid
	 * @return int Anzahl der Teams
	 * @static
	 */
	function getTeamCount($turnierid) {
		$sql = "SELECT COUNT(teamid) as teams FROM t_team WHERE turnierid = '{$turnierid}'";
		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);
		return $row['teams'];
	}


	/**
	 * sucht eine userid in einem Turnier
	 * @param int $turnierid Turnierid
	 * @param int $userid Userid die gesucht wird
	 * @return int Teamid des Users oder 0 wenn nicht gefunden
	 * @static
	 */
	function findUser($turnierid, $userid) {
		$sql = "SELECT t.teamid
			FROM t_team t, t_team2user t2u
			WHERE t.turnierid = '{$turnierid}'
			AND t.turnierid = t2u.turnierid
			AND t.teamid = t2u.teamid
			AND t2u.userid = '{$userid}'";

		$res = DB::query($sql);
		if (mysql_num_rows($res) < 1)
			return 0;

		$row = mysql_fetch_assoc($res);
		return $row['teamid'];
	}


	/**
	 * sucht einen teamnamen in einem Turnier
	 * @param int $turnierid Turnierid
	 * @param string $teamname Teamname der gesucht wird
	 * @return int Teamid des Teams oder 0 wenn nicht gefunden
	 * @static
	 */
	function findTeamName($turnierid, $teamname) {
		$sql = "SELECT teamid FROM t_team WHERE turnierid = '{$turnierid}' AND name = '{$teamname}'";
		$res = DB::query($sql);
		if (mysql_num_rows($res) < 1)
			return 0;

		$row = mysql_fetch_assoc($res);
		return $row['teamid'];
	}


	/**
	 * sucht einen LigaID in einem Turnier
	 * @param int $turnierid Turnierid
	 * @param int $ligaid LigaID die gesucht wird
	 * @return int Teamid des Teams oder 0 wenn nicht gefunden
	 * @static
	 */
	function findLigaId($turnierid, $ligaid) {
		$sql = "SELECT teamid FROM t_team WHERE turnierid = '{$turnierid}' AND ligaid = '{$ligaid}'";
		$res = DB::query($sql);
		if (mysql_num_rows($res) < 1)
			return 0;

		$row = mysql_fetch_assoc($res);
		return $row['teamid'];
	}

	/**
	 * holt die aktuellste MatchID in einem Turnier für ein Team
	 * @param int $turnierId TurnierId
	 * @param int $teamId TeamId
	 * @return int aktuellste MatchID
	 * @static
	 * @throws muffidenktsichzulangemethodennamenaus
	 */
	function getLatestMatchIdForTeamInTourney($turnierId, $teamId) {
		$sql = "SELECT matchid
			FROM t_match
			WHERE turnierid = '{$turnierId}'
			AND (team1 = '{$teamId}' OR team2 = '{$teamId}')
			AND (flags & '".(MATCH_READY & ~MATCH_COMPLETE)."' OR flags = '0')";
		$res = DB::query($sql);
		if (mysql_num_rows($res) == 0)
			return null;
		$row = mysql_fetch_assoc($res);
		return $row['matchid'];
	}

	/**
	* Markiert alle Teammember als neu. Das ist zum Kopieren vom Teams wichtig, wie es
	* beim erzeugen von Vorrundenturnieren genutzt wird.
	* Team->create() legt nur User2Team neu an, wenn die Speiler als neu gekennzeichnet sind.
	 * @param int $turnierId TurnierId
	 * @param int $teamId TeamId
	 * @return int TS_SUCCESS
	**/
	function markMembersAsNew() {
		foreach ($this->userlist as $user => $flags) {
			$this->userlist[$user] |= TEAM2USER_NEW;
		}
	}

}
?>
