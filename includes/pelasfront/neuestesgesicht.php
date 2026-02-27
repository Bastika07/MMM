<?php
require_once "dblib.php";
include_once "format.php";
include_once "language.inc.php";

$result = mysql_query("select STRINGWERT from CONFIG where PARAMETER='NEUESTES_GESICHT' and MANDANTID=$nPartyID");
if ($result) {
	$row = mysql_fetch_array($result);
	$nGesichtID = $row[0];
	if ($nGesichtID != "") {
		$result = mysql_query("select USERID, LOGIN, PLZ, ORT, LAND from USER where USERID=$nGesichtID");
		$row = mysql_fetch_array($result);
			if ($result) {
				if ($sStyle == "northcon") {
					// verkleinerter Avatar und mehr Infos links daneben
					echo "<table width=\"200\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "<tr><td valign=\"top\">\n";
					echo "<a class=\"navlink\" href=\"/benutzerdetails.php?nUserID=$row[USERID]\">".db2display($row['LOGIN'])."</a><br>\n";
					echo PELAS::displayFlag($row['LAND'])." ".$row['PLZ']." ".$row['ORT'];
					echo "</td><td width=\"55\" valign=\"top\">\n";
					displayUserPic($nGesichtID, 55, 75);
					echo "</td></tr></table>\n";

				} elseif ($sStyle == "newsticker") {
					// einfach textausgabe für newsticker
					echo "<a class=\"newstickerlink\" href=\"/benutzerdetails.php?nUserID=".$row['USERID']."\">".db2display($row['LOGIN'])."</a>";
				} else {
					// Ausgabe mit Bild klassisch
					displayUserPic($nGesichtID);
					echo "<br><div align=\"center\"><a class=\"navlink\" href=\"/benutzerdetails.php?nUserID=$row[USERID]\">".db2display($row['LOGIN'])."</a></div>";
				}
			}
	}
}
?>
