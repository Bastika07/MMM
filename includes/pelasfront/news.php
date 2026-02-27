<?php
require_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "language.inc.php";

function displayNews($sTitel, $sBeitrag, $sAutor, $sDatum, $nInhaltID) {
	global $str, $KATEGORIE_NEWSCOMMENT, $iAction, $nPartyID;
	$nInhaltID = intval($nInhaltID);
	echo "<table width=\"100%\">";
	echo "<tr><td class=\"pelas_newstitle\">".db2display($sTitel)."</td></tr>";
	echo "<tr><td class=\"pelas_newsautor\"><img src=\"gfx/headline_pfeil.png\" border=\"0\"> ".dateDisplay2($sDatum)." $str[von] ".db2display($sAutor)."</td></tr>";
	echo "<tr><td><img src=\"/gfx/lgif.gif\" width=\"0\" height=\"6\"></td></tr>";

	echo "<tr><td valign=\"top\"><p align=\"justify\">".db2displayNews($sBeitrag)."</p></td></tr>";

	echo "<tr><td><img src=\"/gfx/lgif.gif\" width=\"0\" height=\"4\"></td></tr>";

	$result_count = DB::query("select COUNT(*) from INHALT where PARENTID='$nInhaltID' and KATEGORIEID=$KATEGORIE_NEWSCOMMENT");
	$row_count = $result_count->fetch_array();
	if ($iAction != "comment" && $iAction != "add") {
		if ($row_count[0] <= 0) {
			echo "<tr><td align=\"right\"><a href=\"news.php?iAction=add&nInhaltID=$nInhaltID\">$str[abgeben]</a></td></tr>";
		} else {
			echo "<tr><td align=\"right\"><a href=\"news.php?iAction=comment&nInhaltID=$nInhaltID\">$str[kommentare]($row_count[0])</a></td></tr>";
		}
	}
	echo "<tr><td><hr></td></tr>";
	echo "</table>";
}

if ($iAction == "comment") {
	//Die News nur beim ersten Mal anzeigen

	$nInhaltID = intval($nInhaltID);

	if ($savedComment != 1) {
		$result = DB::query("select INHALTID, TITEL, DERINHALT, AUTORNAME, WANNANGELEGT, AKTIV from INHALT where MANDANTID=$nPartyID and INHALTID='$nInhaltID'");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$row = $result->fetch_array();
    if ($row['AKTIV'] == 'N' && ($nLoginID < 1 || !User::hatRecht('NEWSADMIN', $nLoginID, $nPartyID)))
	    PELAS::fehler('Du bist nicht eingeloggt oder hast nicht die erforderlichen Rechte.');
	  else {
		  displayNews($row["TITEL"],$row["DERINHALT"],$row["AUTORNAME"],$row["WANNANGELEGT"],$row["INHALTID"]);
  		//Kommentare ausgeben, nur wenn nicht auf add		
	  	displayInhalte($KATEGORIE_NEWSCOMMENT, $row[INHALTID]);
		}
	}
} elseif ($iAction == "add") {
	//News nicht ausgeben
} else {
	$newsID = intval($newsID);
	if (isset($_GET['newsID']) && is_numeric($_GET['newsID'])) {
	  if ($nLoginID < 1 || !User::hatRecht('NEWSADMIN', $nLoginID, $nPartyID))
	    PELAS::fehler('Du bist nicht eingeloggt oder hast nicht die erforderlichen Rechte.');
	  else {
	    // preview-funktion, nur wenn man eingeloggt ist und das Recht NEWSADMIN hat
	    $result = DB::query("select INHALTID, TITEL, DERINHALT, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and KATEGORIEID=$KATEGORIE_NEWS and INHALTID='$_GET[newsID]'");
	    $row = $result->fetch_array();
	    displayNews($row["TITEL"],$row["DERINHALT"],$row["AUTORNAME"],$row["WANNANGELEGT"],$row["INHALTID"]);
	  }
	}	else {
	  // normale Anzeige show = -1 entspricht alle
	  if ($show == -1) {
	  	$show = 999999999;
	  } elseif ($show < 10) {
	  	$show = 10;
	  } else {
			$show = 999999999;
		}
	  $result = DB::query("select INHALTID, TITEL, DERINHALT, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and AKTIV='J' and KATEGORIEID=$KATEGORIE_NEWS order by WANNANGELEGT desc limit $show");
	  //echo DB::$link->errno.": ".DB::$link->error."<BR>";
	  while ($row = $result->fetch_array()) {
  		displayNews($row["TITEL"],$row["DERINHALT"],$row["AUTORNAME"],$row["WANNANGELEGT"],$row["INHALTID"]);
	  }
	  
	  echo "<p align=\"center\"><a href=\"news.php?show=10\">10</a> | <a href=\"news.php?show=25\">25</a> | <a href=\"news.php?show=50\">50</a> | <a href=\"news.php?show=-1\">Alle</a> zeigen</p>";
	}
}
?>
