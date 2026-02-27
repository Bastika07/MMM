<?php
require_once "dblib.php";
include_once "format.php";
include_once "language.inc.php";

?>

<p>Hier kannst Du alle Clans einsehen, die f&uuml;r diese Party registriert sind.</p>

<table class="rahmen_allg" cellpadding='2' cellspacing='1' border='0' width="530">

<?php

	$result = DB::query("select CLANID, NAME from CLAN where MANDANTID = $nPartyID order by NAME");
	
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	
	while ($row = $result->fetch_array()) {
		echo "<TR>";
		
		echo "<TD width=\"20\" class='TNListeTDB' align=\"center\"><a href=\"clandetails.php?nClanID=$row[CLANID]\"><img align=\"middle\" src=\"gfx/userinfo.gif\" border=\"0\"></a></td>";
		
		echo "<TD class='TNListeTDA' align=\"center\">";

		$inClan = $row['CLANID'];
		
		displayClanPic($inClan,$nPartyID);
		
		echo "</td>";
		
		echo "<TD class='TNListeTDB'>".db2display($row['NAME']);
		
		$arry = DB::query("select count(*) as anzahl from USER_CLAN where MANDANTID = $nPartyID and CLANID = $row[CLANID] and AUFNAHMESTATUS=$AUFNAHMESTATUS_OK")->fetch_array();
		
		echo " <i>(".$arry['anzahl'].")</i></td></tr>\n";
	}

echo "</table>";
?>
