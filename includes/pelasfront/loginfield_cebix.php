<?php
require_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "pelasfunctions.php";

$selected = 1;

if ($nLoginID > 0) {
	$selected = 2;
}

//feststellen, ob noch nicht angemeldet
$result = DB::query("select STATUS from ASTATUS where USERID='$nLoginID' and MANDANTID='$nPartyID'");
$row = $result->fetch_array();
if ($row['STATUS'] == 1) {
	$selected = 3; 
} elseif (($row['STATUS'] == 2) || ($row['STATUS'] == 3)) {
	$selected = 4;
}

?>

<table cellspacing="0" cellpadding="1" border="0">
<tr><td class="anmeldungsinfo">

<?php

//select 1 = einloggen
if ($selected == 1) {
	echo "<b>1. <a class=\"navlink\" href=\"/login.php\"><b>Einloggen</b></a></b><br>".
	"2. Anmelden<br>".
	"3. Bezahlen<br>".
	"4. Platz reservieren<br>";
} elseif ($selected == 2) {
//select 2 = anmelden
	echo "1. Einloggen<br>".
	"<b>2. <a class=\"navlink\" href=\"/teilnahme.php\"><b>Anmelden</b></a></b><br>".
	"3. Bezahlen<br>".
	"4. Platz reservieren<br>";
} elseif ($selected == 3) {
//select 3 = bezahlen
	echo "1. Einloggen<br>".
	"2. Anmelden<br>".
	"<b>3. <a class=\"navlink\" href=\"/teilnahme.php\"><b>Bezahlen</b></a></b><br>".
	"4. Platz reservieren<br>";
} elseif ($selected == 4) {
//select 4 = setzen
	echo "1. Einloggen<br>".
	"2. Anmelden<br>".
	"3. Bezahlen<br>".
	"<b>4. <a class=\"navlink\" href=\"/sitzplan.php\"><b>Platz reservieren</b></a></b><br>";
}

if ($nLoginID > 0) {
	echo "<img src=\"/gfx_struct/lgif.gif\" width=\"1\" height=\"5\"><br>";
	echo "<img src=\"gfx/headline_pfeil.png\"> <a href=\"/login_edit.php\">Daten &auml;ndern</a>";
	echo "<br><img src=\"gfx/headline_pfeil.png\"> <a href=\"/login.php?Action=logout\">Ausloggen</a>";
}

?>

</td></tr>
</table>
