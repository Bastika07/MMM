<?php
include_once "dblib.php";
include_once "session.php";
include_once "format.php";

if ($nLoginID > 0) {
	echo "&nbsp;<img src=\"/pfeil_tabelle.gif\" border=\"0\">&nbsp;<a class=\"navlink\" href=\"/login_edit.php\">Daten &auml;ndern</a><br>";
	echo "&nbsp;<img src=\"/pfeil_tabelle.gif\" border=\"0\">&nbsp;<a class=\"navlink\" href=\"/login.php?Action=logout\">Logout</a><br>";
} else {
	echo "&nbsp;<img src=\"/pfeil_tabelle.gif\" border=\"0\">&nbsp;<a href=\"/login.php\" class=\"navlink\">Login</a><br>";
}
?>
