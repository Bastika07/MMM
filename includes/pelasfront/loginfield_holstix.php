<?php
include_once "dblib.php";
include_once "session.php";
include_once "format.php";

if ($nLoginID > 0) {
	echo "<tr><td> &nbsp;<img src=\"/gfx/pfeil.gif\"> <a class=\"navlink\" href=\"login_edit.php\">Meine Daten</a></td></tr>";
	echo "<tr><td> &nbsp;<img src=\"/gfx/pfeil.gif\"> <a class=\"navlink\" href=\"login.php?Action=logout\">Logout</a></td></tr>";
} else {
	echo "<table width=\"200\" height=\"80\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
	echo "<tr><td width=\"18\"><img src=\"/gfx_struct/loginfield_user.png\" width=\"10\" height=\"10\"></td>\n";
	echo "<td width=\"182\"><a href=\"login.php\" class=\"navlink\">Login</a></td></tr>";
	echo "</table>";
}
?>