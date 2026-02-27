<?php
/* Verwaltung der Gastserver.
 *
 * Komplett überarbeitet, in Controller-Action-Struktur überführt,
 * Templates erstellt, Exports hinzugefügt und Erweiterungen
 * von Y0Gi.
 */
require('controller.php');
require_once('dblib.php');
$iRecht = 'TECHNIKADMIN';
require_once('checkrights.php');
require_once('format.php');
require_once('util.php');
include('admin/vorspann.php');

$mandant_id = intval($_REQUEST['mandant']);


class Controller {

    /* Mandanten auflisten (samt Zahl der jeweiligen Gastserver). */
    function index() {
        global $loginID;
        $q = 'SELECT 
				  g.MANDANTID id, 
				  m.BESCHREIBUNG title,
                  COUNT(g.MANDANTID) server_count
              FROM 
				  MANDANT m
			  LEFT JOIN RECHTZUORDNUNG r USING (MANDANTID)
			  LEFT JOIN GASTSERVER g USING (MANDANTID)
              
			  WHERE 
				  r.MANDANTID > 0
				  AND r.RECHTID = "TECHNIKADMIN"
				  AND r.USERID = ' . $loginID . '
              GROUP 
				  BY g.MANDANTID, m.BESCHREIBUNG ';
        return array('mandanten' => DB::getRows($q));
    }

    /* Gastserver zu diesem Mandanten auflisten. */
    function list_servers() {
        global $mandant_id;
	if (! $mandant_id) {
	    exit('Mandant wurde nicht angegeben!');
	}
	$mandant = get_mandant($mandant_id);
        $servers = get_mandant_servers($mandant_id);
        foreach ($servers as $key => $srv) {
            $servers[$key]['ipaddr'] = CFG::getMandantConfig('GASTSERVER_IP', $mandant_id) . $srv['id'];
        }
        return array('mandant' => $mandant, 'servers' => $servers);
    }

    /* Einen Server hinzufügen. */
    function add() {
        global $mandant_id;
	if (! $mandant_id) {
	    exit('Mandant wurde nicht angegeben!');
	}
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return array('mandant_id' => $mandant_id);
	}

        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $reverse = $_POST['reverse'] ? 'J' : '';
        $description = $_POST['description'];

        $error = 0;
        if (! $user_id or ! $name) {
            $error = 'Bitte alle Felder ausfüllen!';
        } elseif (preg_match('/[^0-9a-zA-Z-]/', $name)) {
            # DNS im falschen Format.
            $error = 'Name/DNS-Eintrag darf keine Leer- und Sonderzeichen enthalten.';
        } elseif (check_name($mandant_id, $name)) {
	    $error = 'Dieser Name/DNS-Eintrag ist bereits vergeben.';
        } else {
            # Höchste laufende Nummer herausfinden.
            $new_serial = 1;
            $q = 'SELECT MAX(LFDNR)
                  FROM GASTSERVER
                  WHERE MANDANTID = '.$mandant_id;
            $new_serial = DB::getOne($q) + 1;
            if ($new_serial > 254) {
                # Das Subnetz ist voll.
                $error = 'Es ist kein freier Platz mehr für Deinen Gastserver vorhanden.';
            }
        }
	if ($error) {
	    return array('mandant_id' => $mandant_id, 'error' => $error);
	}

        # Ab in die Datenbank!
        $q = "INSERT INTO GASTSERVER
 	      (LFDNR, USERID, MANDANTID, NAMEDNS, BESCHREIBUNG, REVERSE, WANNANGELEGT)
              VALUES ('$new_serial', '$user_id', '$mandant_id', '$name', '$description', '$reverse', NOW())";
        DB::query($q);
        $ipaddr = CFG::getMandantConfig('GASTSERVER_IP', $mandant_id) . $new_serial;
	return array('mandant_id' => $mandant_id, 'success' => True, 'ipaddr' => $ipaddr);
    }

    /* Einen Server bearbeiten. */
    function edit() {
        global $mandant_id;
	if (! $mandant_id) {
	    exit('Mandant wurde nicht angegeben!');
	}
	$server_id = (int) $_REQUEST['server'];
	if (! $server_id) {
	    exit('Die Server-ID wurde nicht angegeben!');
	}
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    DB::query('
	        UPDATE GASTSERVER
		SET USERID = ?, NAMEDNS = ?, BESCHREIBUNG = ?, REVERSE = ?
		WHERE LFDNR = ?
		  AND MANDANTID = ?
		', (int) $_POST['user_id'], $_POST['name'], $_POST['description'],
		$_POST['reverse'], $server_id, $mandant_id);
	    return array(
	        'mandant_id' => $mandant_id,
		'server_id' => $server_id,
		'success' => True);
	} else {
	    $server = DB::getRow('
	        SELECT LFDNR server_id, MANDANTID mandant_id, USERID user_id,
		       NAMEDNS name, BESCHREIBUNG description, REVERSE reverse
		FROM GASTSERVER
		WHERE LFDNR = ?
		  AND MANDANTID = ?
		', $server_id, $mandant_id);
            return $server;
	}
    }

    /* Einen Server löschen. */
    function delete() {
        global $mandant_id;
	if (! $mandant_id) {
	    exit('Mandant wurde nicht angegeben!');
	}
	$server_id = intval($_GET['server']);
        if (! $_GET['confirmed']) {
	    return array('mandant_id' => $mandant_id, 'server' => $server_id);
	} else {
	    # Server löschen.
            $q = 'DELETE FROM GASTSERVER
                  WHERE LFDNR = '.$server_id.'
                    AND MANDANTID = '.$mandant_id;
            DB::query($q);
	    return array('mandant_id' => $mandant_id, 'confirmed' => True);
	}
    }

    /* Gastserver-Daten exportieren. */
    function export() {
        global $mandant_id;
	if (! $mandant_id) {
	    exit('Mandant wurde nicht angegeben!');
	}
        $servers = get_mandant_servers($mandant_id);
        foreach ($servers as $idx => $srv) {
            $servers[$idx]['ip_address'] = CFG::getMandantConfig('GASTSERVER_IP', $mandant_id) . $srv['id'];
            $servers[$idx]['reverse'] = ($srv['reverse'] == 'J') ? 1 : 0;
            $servers[$idx]['dnsname'] = strtolower($srv['dnsname']);
        }

        # Send headers.
        header('Content-Type: text/plain');
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');

        switch ($_GET['output']) {
            case 'json':
                # Daten als JSON-Strukturen ausgeben.
                export_json($servers);
                break;
            case 'sql':
                # SQL-INSERT-Statements generieren.
                $sql_forward = "INSERT INTO `rr` (zone, name, type, data, aux, ttl) VALUES (1, '%s', 'A', '%s', 1, 300);\n";
                $sql_reverse = "INSERT INTO `rr` (zone, name, type, data, aux, ttl) VALUES (3, '%s', 'PTR', '%s.lan.multimadness.de', 1, 300);\n";
                foreach ($servers as $srv) {		
                    printf($sql_forward, $srv['dnsname'], $srv['ip_address']);
                    if ($srv['reverse']) {
                        $octets = explode('.', $srv['ip_address']);
                        printf($sql_reverse, $octets[3] . '.' . $octets[2], $srv['dnsname']);
                    }
                }
                break;
            default:
                # CSV-Liste generieren.
	        foreach ($servers as $srv) {
                    $values = array($srv['ip_address'], $srv['reverse'], $srv['dnsname'], $srv['admin_name']);
                    echo join(',', $values)."\n";
                }
        }
        exit;
    }

}

exec_ctrl();


# ---------------------------------------------------------------- #
# helpers

/* Liefere ID, Name und URL des Mandanten. */
function get_mandant($mandant_id) {
    $q = 'SELECT MANDANTID id, BESCHREIBUNG name, REFERER url
          FROM MANDANT
	  WHERE MANDANTID = '.$mandant_id;
    return DB::getRow($q);
}

/* Liefere alle Gastserver-Einträge für diesen Mandanten. */
function get_mandant_servers($mandant_id) {
    $q = 'SELECT g.LFDNR id, g.NAMEDNS dnsname, g.REVERSE reverse,
                 g.BESCHREIBUNG description, g.WANNANGELEGT added_at,
                 u.USERID admin_id, u.LOGIN admin_name
          FROM GASTSERVER g, USER u
          WHERE g.USERID = u.USERID
            AND g.MANDANTID = '.$mandant_id.'
          ORDER BY g.LFDNR';
    return DB::getRows($q);
}

/* Prüfen, ob der DNS-Name schon vergeben ist. */
function check_name($mandant_id, $name) {
    $q = "SELECT LFDNR
          FROM GASTSERVER
          WHERE MANDANTID = $mandant_id
            AND LCASE(NAMEDNS)='".safe(strtolower($name))."'";
    return (DB::getOne($q) > 0);
}
include('admin/nachspann.php');
?>