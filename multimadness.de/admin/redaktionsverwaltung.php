<?php
require('controller.php');
require_once "dblib.php";
$iRecht = $KATEGORIEINFO[$_GET['nKategorieID']][1];
include "checkrights.php";
include('format.php');
include "admin/vorspann.php";

echo "<h1>Inhaltstyp ".htmlspecialchars($KATEGORIEINFO[$_GET['nKategorieID']][0] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')." verwalten</h1>";

$dbh = DB::connect();

if ($_GET['iAction']=="deactivate") {
	$result = mysql_db_query ($dbname, "update INHALT set AKTIV = 'N', WERGEAENDERT=".intval($loginID)." where INHALTID=".intval($_GET['nInhaltID'])." and KATEGORIEID=".intval($_GET['nKategorieID']), $dbh);
	echo "<p>Der Inhalt wurde deaktiviert.</p>";
} elseif ($_GET['iAction']=="activate") {
	$result = mysql_db_query ($dbname, "update INHALT set AKTIV = 'J', WERGEAENDERT=".intval($loginID)." where INHALTID=".intval($_GET['nInhaltID'])." and KATEGORIEID=".intval($_GET['nKategorieID']), $dbh);
	echo "<p>Der Inhalt wurde aktiviert.</p>";
} else {
	$result= mysql_db_query ($dbname, "select i.INHALTID, i.TITEL, i.AKTIV, i.DATE1, m.BESCHREIBUNG, m.REFERER from INHALT i, MANDANT m, RECHTZUORDNUNG r where i.KATEGORIEID=".intval($_GET['nKategorieID'])." and i.MANDANTID=m.MANDANTID and i.MANDANTID=r.MANDANTID and r.USERID=".intval($loginID)." and r.RECHTID='".$KATEGORIEINFO[$_GET['nKategorieID']][1]."' order by i.WANNANGELEGT desc", $dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";

	echo "<table cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "<tr><td class=\"navbar\">\n";
	echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\">\n";
	echo "<tr><td class=\"navbar\"><b>Titel</b></td><td class=\"navbar\"><b>";
	if ($_GET['nKategorieID'] == $KATEGORIE_MAILING) {
		echo "Verschickt";
	} else {
		echo "Aktiv";
	}
	
	echo "</b></td><td class=\"navbar\"><b>Mandant</b></td><td class=\"navbar\"><b>Aktion</b></td></tr>\n";

	$sKlasse = "dblau";
	while ($row = mysql_fetch_array($result)) {
		if ($row[AKTIV] == "J") {
		  if ($_GET['nKategorieID'] == $KATEGORIE_MAILING) {
		    $sAktiv = dateDisplay2($row['DATE1']);
		  } else {
		    $sAktiv = "Ja";
		  }
		} else {
			$sAktiv = "Nein";
		}
		echo "<tr><td class=\"$sKlasse\">".htmlspecialchars($row[TITEL])."</td><td class=\"$sKlasse\">".$sAktiv."</td><td class=\"$sKlasse\">".htmlspecialchars($row['BESCHREIBUNG'])."</td><td class=\"$sKlasse\"><a href=\"redaktion.php?iAction=edit&nInhaltID=$row[INHALTID]&nKategorieID=".intval($_GET['nKategorieID'])."\">&auml;ndern</a> | ";

		if ($row[AKTIV] == "J") {
			if ($_GET['nKategorieID'] == $KATEGORIE_MAILING) {
				echo "<a href=\"rundmail_front.php?nInhaltID=$row[INHALTID]&nKategorieID=".intval($_GET['nKategorieID'])."\">erneut verschicken</a>";
			} else {
				echo "<a href=\"redaktionsverwaltung.php?iAction=deactivate&nInhaltID=$row[INHALTID]&nKategorieID=".intval($_GET['nKategorieID'])."\">deaktivieren</a>";
			}
		} else {
			if ($_GET['nKategorieID'] == $KATEGORIE_MAILING) {
				echo "<a href=\"rundmail_front.php?nInhaltID=$row[INHALTID]&nKategorieID=".intval($_GET['nKategorieID'])."\">verschicken</a>";
			} else {
				echo "<a href=\"redaktionsverwaltung.php?iAction=activate&nInhaltID=$row[INHALTID]&nKategorieID=".intval($_GET['nKategorieID'])."\">aktivieren</a>";
			}					
		}
		if ($_GET['nKategorieID'] == $KATEGORIE_NEWS) {
		  if ($row[AKTIV] == "J") {
  		  echo " | <a href=\"$row[REFERER]/news.php?newsID=$row[INHALTID]\" target=\"_new\">view</a>";
	  	} else {
		   echo " | <a href=\"$row[REFERER]/news.php?newsID=$row[INHALTID]\" target=\"_new\">preview</a>";
		  }
	  }

		echo "</td></tr>\n";
		if ($sKlasse == "dblau") {
			$sKlasse = "hblau";
		} else {
			$sKlasse = "dblau";
		}
	}

	echo "</table></td></tr></table></form>\n";
}

include "admin/nachspann.php";
?>
