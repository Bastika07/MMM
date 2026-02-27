<?php
include_once "dblib.php";
include_once "format.php";

function view($userid, $list, $err = "") {
?>
<h1>Portfreischaltungen f&uuml;r Internetzugang</h1>
Es sind bis zu 2 Freischaltungen pro User m&ouml;glich.<br>
Die Eingaben werden vor dem eigentlichen Freischalten noch von einem Technikadmin &uuml;berpr&uuml;ft,<br>
und können ohne Angabe von Gründen abgelehnt werden.
<?php
if (!empty($err))
	PELAS::fehler($err);
?>
<table class="rahmen_allg" cellpadding="3" cellspacing="1" width="600">
<tr><td class="TNListe" width="50">Status</td>
<td class="TNListe" width="50">Ports</td>
<td class="TNListe" width="450">Begr&uuml;ndung</td>
<td class="TNListe" width="50"></td>
</tr>
<?php
$tdclass = 'hblau';
foreach ($list as $id => $row) {
	if ($row->flags & F_REMOVE)
		continue;
	if ($row->status != "wrong ip") {
		echo "<tr><td class=\"{$tdclass}\" align=\"center\" width=\"50\">{$row->status}</td>\n";

	} else {
		echo "<tr><td class=\"{$tdclass}\" align=\"center\" width=\"50\">";
		echo "<a href=\"{$_SERVER['PHP_SELF']}?action=update&userid={$userid}&id={$id}\">wrong ip</a></td>\n";
	}
	echo "<td class=\"{$tdclass}\" width=\"50\">".$row->portlo.(($row->porthi != 0) ? " - ".$row->porthi : "")."</td>\n";
	echo "<td class=\"{$tdclass}\" width=\"450\">".db2display($row->info)."</td>\n";
	echo "<td class=\"{$tdclass}\" width=\"50\"><a href=\"{$_SERVER['PHP_SELF']}?action=del&userid={$userid}&id={$id}\">l&ouml;schen</a></td></tr>\n";
	$tdclass = ($tdclass == 'hblau') ? 'dblau' : 'hblau';
}
echo "<form method=\"POST\" action=\"{$_SERVER['PHP_SELF']}?action=add\">";
?>
<tr><td class="dblau" width="50"></td>
<td class="dblau" width="50"><input type="text" size="6" maxlength="5" name="ports"></td>
<td class="dblau" width="450"><input type="text" size="20" maxlength="64" name="info"></td>
<td class="dblau" width="50"><input type="submit" value="add"></td>
</tr>
</form>
</table>

<?php
}

// gueltige Session?
function sessionIsValid() {
	global $nLoginID;
	return isset($nLoginID) && $nLoginID > 0;
}

// userid
function currentID() {
	global $nLoginID, $loginID;
	return (int) (isset($nLoginID) ? $nLoginID : $loginID);
}

// hat bezahlt
function userHasPayed($userid, $partyid) {
	return User::hatBezahlt($userid, $partyid);
}

// port parsen
function parsePort($string) {
	if ($string == '*') {
		return array(0, 65535);

	} else {
		if (strpos($string, ':'))
			$ports = explode(':', $string);
		else
			$ports = explode('-', $string);

		if (!isset($ports[0]) || !is_numeric($ports[0]) || $ports[0] < 0 || $ports[0] > 65535)
			return null;

		if (isset($ports[1])) {
			if (!is_numeric($ports[1]) || $ports[1] < 0 || $ports[1] > 65535)
				return null;

			if ($ports[0] >= $ports[1])
				return null;
		} else {
			$ports[1] = 0;
		}
		return $ports;
	}
}

define('F_NEW',		0x01);	// neue regel
define('F_ACCEPTED',	0x02);	// regel ist aktiv -> nicht mehr neu
define('F_DENIED',	0x04);	// regel wurde von admin abgelehnt
define('F_REMOVE',	0x08);	// regel kann geloescht werden (req von user)
define('F_DELETED',	0x10);	// regel wurde geloescht (kann aus db entfernt werden)
define('F_ACTIVE',	0x20);	// regel ist aktiv in der fw


/*
Neu anlegen:
============
madnix:
datensatz angelegen, flags |= F_NEW

sync (get vom gateway):
alle datensätze mit F_NEW -> an gateway übermitteln
=>
wenn datensatz dort schon vorhanden -> datensatz nicht schreiben
wenn datensatz dort nicht vorhanden -> datensatz abspeichern

gateway (admin eingabe):
alle datensätze anzeigen
wenn accept -> flags |= F_ACCEPTED
wenn denied -> flags |= F_DENIED

gateway (update der fw):
init:
datensätze mit F_ACCEPTED -> regel einfuegen, flags |= F_ACTIVE
update:
datensätze mit F_ACCEPTED, ohne F_ACTIVE -> regel einfuegen, flags |= F_ACTIVE

sync (push vom gateway):
alle datensätze mit F_NEW & (F_ACTIVE | F_DENIED) uebertragen
=>
wenn datensatz dort schon mit != F_NEW -> datensatz nicht schreiben
wenn datensatz dort schon mit == F_NEW -> flags == F_ACCEPTED / F_DENIED


Loeschen
========
madnix:
aendern, flags |= F_REMOVE

sync (get vom gw):
alle datensätze mit F_REMOVE -> an gateway uebermitteln
=>
wenn datensatz dort vorhanden -> speichern
wenn datensatz dort nicht vorhanden -> speichern mit flags |= F_DELETED

gateway (update der fw):
wenn datensatz F_ACTIVE -> regel loeschen, flags |= DELETED

sync (push vom gw):
alle datensätze mit F_DELETED uebertragen
=>
wenn datensatz dort vorhanden -> loeschen
wenn datensatz dort nicht vorhanden -> nix tun
=>
wenn erfolgreich uebertragen -> auf gw loeschen


Update:
=======
madnix:
alter datensatz: flags |= F_REMOVE
neuer datensatz: flags |= (F_NEW | F_ACCEPT)


IMPLEMENTIERUNG:

gayway cronjob:
push aller datensätze mit (F_NEW & (F_ACCEPT | F_DENIED)) | F_DELETED
get datensätze
- mit F_NEW & vorhanden -> nicht schreiben
- mit F_NEW & nicht vorhanden -> schreiben
- mit F_REMOVE & vorhanden -> schreiben
- mit F_REMOVE & nicht vorhanden -> speichern mit flags |= F_DELETED

*/


class Userport {
	var $userid = 0, $id = 0;
	var $flags = 0;
	var $ip = "", $proto = 0;
	var $portlo = 0, $porthi = 0;
	var $info = "";

	function load($userid, $id = -1) {
		$sql = "SELECT userid, id, flags, INET_NTOA(ip) AS ip, proto, portlo, porthi, info
			FROM gw_userports
			WHERE userid = '{$userid}'";

		$sql .= ($id != -1) ? " AND id = '{$id}'" : " ORDER BY portlo, porthi";

		$res = DB::query($sql);
		$retval = array();
		while ($row = $res->fetch_assoc()) {
			$retval[$row['id']] = new Userport();
			$retval[$row['id']]->userid = 	(int) $row['userid'];
			$retval[$row['id']]->id = 	(int) $row['id'];
			$retval[$row['id']]->flags = 	(int) $row['flags'];
			$retval[$row['id']]->ip = 	(string) $row['ip'];
			$retval[$row['id']]->proto = 	(int) $row['proto'];
			$retval[$row['id']]->portlo = 	(int) $row['portlo'];
			$retval[$row['id']]->porthi = 	(int) $row['porthi'];
			$retval[$row['id']]->info = 	(string) $row['info'];
		}

		return ($id == -1) ? $retval : $retval[$id];
	}

	function save() {
		$sql = "UPDATE gw_userports SET
			flags = '{$this->flags}',
			ip = INET_ATON('{$this->ip}'),
			proto = '{$this->proto}',
			portlo = '{$this->portlo}',
			porthi = '{$this->porthi}',
			info = '{$this->info}'
			WHERE userid = '{$this->userid}'
			AND id = '{$this->id}'";
		DB::query($sql);
	}

	function create() {
		$sql = "SELECT MAX(id) as max FROM gw_userports WHERE userid = '{$this->userid}'";
		$res = DB::query($sql);

		if ($res->num_rows != 1) {
			$this->id = 1;

		} else {
			$row = $res->fetch_assoc();
			$this->id = $row['max'] +1;
		}

		$sql = "INSERT INTO gw_userports SET
			userid = '{$this->userid}',
			id = '{$this->id}',
			flags = '{$this->flags}',
			ip = INET_ATON('{$this->ip}'),
			proto = '{$this->proto}',
			portlo = '{$this->portlo}',
			porthi = '{$this->porthi}',
			info = '{$this->info}'";
		DB::query($sql);
	}

	function delete($userid, $id = -1) {
		$sql = "DELETE FROM gw_userports WHERE userid = '{$userid}'";
		if ($id != -1)
			$sql .= " AND id = '{$id}'";

		DB::query($sql);
	}

	function getUserIP($userid) {
		$sql = "SELECT IP FROM USER2IP WHERE userid = '{$userid}' LIMIT 1";
		$res = DB::query($sql);
		$row = $res->fetch_assoc();

		return $row['IP'];
	}
}

DB::connect();

function check_() {
	if (!sessionIsValid()) {
		echo "Du musst eingeloggt sein, um dir Ports freischalten zu lassen.";
		return;
	}

	if (!userHasPayed(currentID(), MANDANTID)) {
		echo "Du musst bezahlt haben, um dir Ports freischalten zu lassen.";
		return;
	}
}

function add() {
	check_();

	if (empty($_POST['ports']) || empty($_POST['info'])) {
		show("Bitte beide Felder ausfüllen.");
		return;
	}

	$ports = parsePort($_POST['ports']);
	if ($ports == null) {
		show("Ungültige Portangabe");
		return;
	}

	$userid = currentID();

	$tmp = new Userport();
	$tmp->userid = $userid;
	$tmp->flags = F_NEW;
	$tmp->ip = Userport::getUserIP($userid);
	$tmp->proto = 6;
	$tmp->portlo = $ports[0];
	$tmp->porthi = $ports[1];
	$tmp->info = $_POST['info'];
	$tmp->create();

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

function del() {
	check_();

	if (!isset($_GET['userid']) || !is_numeric($_GET['userid']))
		return;

	if (!isset($_GET['id']) || !is_numeric($_GET['id']))
		return;

	if ($_GET['userid'] != currentID())
		return;

	$tmp = Userport::load($_GET['userid'], $_GET['id']);
	$tmp->flags |= F_REMOVE;
	$tmp->save();

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

function update() {
	check_();

	if (!isset($_GET['userid']) || !is_numeric($_GET['userid']))
		return;

	if (!isset($_GET['id']) || !is_numeric($_GET['id']))
		return;

	$tmp = Userport::load($_GET['userid'], $_GET['id']);
	$ip = Userport::getUserIP($userid);

	if (($tmp->flags & F_ACCEPTED) && ($tmp->ip != $ip)) {
		// alten loeschen
		$tmp->flags |= F_REMOVE;
		$tmp->save();

		// neu anlegen
		$tmp->flags = (F_NEW | F_ACCEPTED);
		$tmp->ip = $ip;
		$tmp->create();
	}
}

function show($err = "") {
	if (!sessionIsValid()) {
		echo "Du musst eingeloggt sein, um dir Ports freischalten zu lassen.";
		return;
	}

	$userid = currentID();

	if (!userHasPayed($userid, MANDANTID)) {
		echo "Du musst bezahlt haben, um dir Ports freischalten zu lassen.";
		return;
	}

	$ip = Userport::getUserIP($userid);

	$list = Userport::load($userid);
	foreach ($list as $id => $row) {
		if ($row->flags & F_NEW)
			$list[$id]->status = "requested";

		if ($row->flags & F_ACCEPTED)
			$list[$id]->status = "accepted";

		if ($row->ip != $ip)
			$list[$id]->status = "wrong ip";

		if ($row->flags & F_DENIED)
			$list[$id]->status = "denied";

	}

	view($userid, $list, $err);
}


$action = isset($_GET['action']) ? $_GET['action'] : 'show';
switch ($action) {
	case 'add':	add();
			break;

	case 'del':	del();
			break;

	default:
	case 'show':	show();
			break;
}


?>