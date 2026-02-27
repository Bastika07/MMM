<?php
include_once "dblib.php";
include_once "format.php";
include_once "language.inc.php";

function displayNews($sTitel, $sBeitrag, $sAutor, $sDatum, $nInhaltID) {
	global $str, $KATEGORIE_NEWSCOMMENT, $iAction, $nPartyID, $dbname, $dbh;
	$nInhaltID = intval($nInhaltID);
	echo "<table width=\"100%\">";
	echo "<tr><td class=\"pelas_newstitle\">".db2display($sTitel)."</td></tr>";
	echo "<tr><td class=\"pelas_newsautor\"><img src=\"gfx/headline_pfeil.png\" border=\"0\"> ".dateDisplay2($sDatum)." $str[von] ".db2display($sAutor)."</td></tr>";
	echo "<tr><td><img src=\"/gfx/lgif.gif\" width=\"0\" height=\"6\"></td></tr>";

	echo "<tr><td valign=\"top\">".db2displayNews($sBeitrag)."</td></tr>";

	echo "<tr><td><img src=\"/gfx/lgif.gif\" width=\"0\" height=\"4\"></td></tr>";

	$result_count= DB::query("select COUNT(*) from INHALT where PARENTID='$nInhaltID' and KATEGORIEID=$KATEGORIE_NEWSCOMMENT");
	$row_count = $result_count->fetch_array();
	if ($iAction != "comment" && $iAction != "add") {
		if ($row_count[0] <= 0) {
			echo "<tr><td align=\"right\"><a href=\"news.php?iAction=add&nInhaltID=$nInhaltID#checkpoint\">$str[abgeben]</a></td></tr>";
		} else {
			echo "<tr><td align=\"right\"><a href=\"news.php?iAction=comment&nInhaltID=$nInhaltID\">$str[kommentare]($row_count[0])</a></td></tr>";
		}
	}
	echo "<tr><td><hr></td></tr>";
	echo "</table>";
}

if ($iAction == "comment") {
	//Die News nur beim ersten Mal anzeigen
	if ($savedComment != 1) {
		if (!isset($dbh))
			$dbh = DB::connect();
		$nInhaltID = intval($nInhaltID);
		$result = DB::query("select INHALTID, TITEL, DERINHALT, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and AKTIV='J' and INHALTID='$nInhaltID'");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$row = $result->fetch_array();

		displayNews($row["TITEL"],$row["DERINHALT"],$row["AUTORNAME"],$row["WANNANGELEGT"],$row["INHALTID"]);

		//Kommentare ausgeben, nur wenn nicht auf add
		displayInhalte($KATEGORIE_NEWSCOMMENT, $row[INHALTID]);
	}
} elseif ($iAction == "add") {
	//News nicht ausgeben
} else {
	//Ab hier ist news v2 ander

	if (!isset($dbh))
		$dbh = DB::connect();

	$result = DB::query("select INHALTID, TITEL, DERINHALT, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and AKTIV='J' and KATEGORIEID=$KATEGORIE_NEWS order by WANNANGELEGT desc limit 1");
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$row = $result->fetch_array();
	
	displayNews($row["TITEL"],$row["DERINHALT"],$row["AUTORNAME"],$row["WANNANGELEGT"],$row["INHALTID"]);
	
	echo "</table><table width=\"100%\" cellpadding=\"8\"><tr><td valign=\"top\">";
	
	$result = DB::query("select INHALTID, TITEL, DERINHALT, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and AKTIV='J' and KATEGORIEID=$KATEGORIE_NEWS order by WANNANGELEGT desc limit 1,10");
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	while ($row = $result->fetch_array()) {
		echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td class=\"pelas_newstitle_small\"><a class=\"newslink\" href=\"news.php?iAction=comment&nInhaltID=$row[INHALTID]#checkpoint\">$row[TITEL]</a></td></tr>";
		echo "<tr><td class=\"pelas_newsautor\"><img src=\"gfx/headline_pfeil.png\" border=\"0\"> ".dateDisplay2($row[WANNANGELEGT])." $str[von] ";
		if ($row[AUTOR] > 0) {
			echo "<a href=\"benutzerdetails.php?nUserID=$row[AUTOR]\">".db2display($row[AUTORNAME])."</a>";
		} else {
			echo db2display($row[AUTORNAME]);
		}
		echo "</td></tr><tr><td class=\"PelasSmallComment\">";

		$result_count= DB::query("select COUNT(*) from INHALT where PARENTID=$row[INHALTID] and KATEGORIEID=$KATEGORIE_NEWSCOMMENT");
		$row_count = $result_count->fetch_array();
		if ($row_count[0] <= 0) {
			echo "<a href=\"news.php?iAction=add&nInhaltID=$row[INHALTID]#checkpoint\">$str[abgeben]</a>";
		} else {
			echo "<a href=\"news.php?iAction=comment&nInhaltID=$row[INHALTID]\">$str[kommentare]($row_count[0])</a>";
		}
		echo "</td></tr>";
		echo "<tr><td height=\"12\"><img src=\"/gfx/lgif.gif\" height=\"12\" border=\"0\"></td></tr></table>";
		
	}
	echo "</td><td valign=\"top\" align=\"right\">";

	// Hautpsponsor
	echo "<table cellspacin=\"0\" cellpadding=\"1\" border=\"0\">";
	echo "<tr><td valign=\"top\" align=\"right\"><b>$str[hauptsponsor]</b></td>";
	echo "<td><a href=\"http://www.powertech-electronics.de\" target=\"_blank\"><img src=\"/gfx/hauptsponsor_pixelview.jpg\" width=\"112\" height=\"150\" border=\"0\" alt=\"$str[hauptsponsor] Pixelview\"></a></td></tr>";
	echo "</table><br>";

	$result= DB::query("select STRINGWERT from CONFIG where PARAMETER='NEUESTES_GESICHT' and MANDANTID=$nPartyID");
	if ($result) {
		$row = $result->fetch_array();
		$nGesichtID = $row[0];
		if ($nGesichtID != "") {
			$result2= DB::query("select USERID, LOGIN, PLZ, Ort from USER where USERID='$nGesichtID'");
			$row2 = $result2->fetch_array();
				echo "<table cellspacin=\"0\" cellpadding=\"1\" border=\"0\"><tr><td valign=\"top\" align=\"right\"><b>$str[neuestesgesicht]</b><br>";
				echo "<br><a href=\"benutzerdetails.php?nUserID=$row2[USERID]\">$row2[LOGIN]</a><br>";
				echo "$str[aus] $row2[PLZ] $row2[Ort]<br>";
				
				echo "</td><td>";
				if ($result2) {
					
					displayUserPic($nGesichtID);
					
				}
				echo "</td></tr></table><br>";
		}
	}

	// Partner
	echo "<br><table cellspacin=\"0\" cellpadding=\"1\" border=\"0\">";
	echo "<tr><td><a href=\"http://www.beat-up.de\" target=\"_blank\"><img src=\"/gfx/coverage_beat_logo.jpg\" width=\"110\" height=\"37\" border=\"0\" alt=\"Beat Radio\"></a></td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td><a href=\"http://www.gamesports.de\" target=\"_blank\"><img src=\"/gfx/coverage_gamesports_logo.gif\" width=\"110\" height=\"37\" border=\"0\" alt=\"Gamesports FM\"></a></td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td><a href=\"http://www.lanparty.de\" target=\"_blank\"><img src=\"/gfx/coverage_lpde.jpg\" width=\"110\" height=\"37\" border=\"0\" alt=\"Lanparty.de\"></a></td></tr>";
	
	echo "</table><br>";

}

?>
