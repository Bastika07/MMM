<?php
include_once "dblib.php";
include_once "session.php";
//$prof->startTimer( "nachspanninclude_format" );
include_once "format.php";
//$prof->stopTimer( "nachspanninclude_format" );

if (!isset($dbh))
	$dbh = DB::connect();

if (isset($nLoginID) && $nLoginID > 0) {
	/* echo "&nbsp;<img src=\"pfeil_tabelle.gif\" border=\"0\"> <a class=\"navlink\" href=\"login_edit.php\">Daten &auml;ndern</a><br>";
	echo "&nbsp;<img src=\"pfeil_tabelle.gif\" border=\"0\"> <a class=\"navlink\" href=\"login.php?Action=logout\">Logout</a>"; */
	
	echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">";	
	echo "<tr><td> &nbsp; Eingeloggt als:</td></tr>";
	echo "<tr><td> &nbsp; <small>".db2display($sLogin)."</td></tr>";
	if (!User::istAngemeldet($nLoginID, $nPartyID) && !User::hatBezahlt($nLoginID, $nPartyID)) {
		echo "<tr><td> &nbsp; <img src=\"gfx/headline_pfeil.png\"> <a href=\"/teilnehmen.php\" class=\"navlink\">Teilnehmen</a></td></tr>";
	}
	echo "<tr><td> &nbsp; <img src=\"gfx/headline_pfeil.png\"> <a href=\"/login_edit.php\" class=\"navlink\">Meine Daten</a></td></tr>";
	echo "<tr><td> &nbsp; <img src=\"gfx/headline_pfeil.png\"> <a href=\"/login.php?Action=logout\" class=\"navlink\">Ausloggen</a></td></tr>";
	echo "</table>";
} else {
	echo "<form method=\"post\" action=\"/login.php\">";
	echo csrf_field() . "\n";
	echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">";
	echo "<tr><td> &nbsp; <img src=\"gfx/headline_pfeil.png\"> Benutzername</td></tr>";
	echo "<tr><td> &nbsp;  &nbsp;  &nbsp; <input type=\"text\" name=\"iLogin\" size=\"10\" maxlength=\"50\" value=\"".(isset($iLogin) ? $iLogin : '')."\"></td></tr>\n";
	echo "<tr><td> &nbsp; <img src=\"gfx/headline_pfeil.png\"> Passwort</td></tr>";
	echo "<tr><td> &nbsp;  &nbsp;  &nbsp; <input type=\"password\" name=\"iPassword\" size=\"10\" maxlength=\"50\" value=\"".(isset($iPassword) ? $iPassword : '')."\"></td></tr>\n";
	echo "<tr><td> &nbsp;  <input type=\"submit\" value=\"Einloggen\"></td></tr>\n";
	echo "</form>";
	echo "</table>";
}
?>
