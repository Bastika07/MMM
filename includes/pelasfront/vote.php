<?php
require_once "dblib.php";
include_once "format.php";
include_once "pelasfunctions.php";
include "language.inc.php";

// Umfrage sichtbar?
$sql = "select 
	  STRINGWERT 
	from 
	  CONFIG 
	where 
	  PARAMETER = 'UMFRAGE_AKTIV' AND
	  MANDANTID = $nPartyID";
$result = DB::query($sql);
$row = $result->fetch_assoc();
// checken, ob get-variable on
if ($row['STRINGWERT'] == "J") {

	// aktuelle Umfrage feststellen
	$row = DB::query("select UMFRAGE_UMFRAGEID,UMFRAGE_AUSWAHL_ANZAHL from UMFRAGE where UMFRAGE_MANDANTID = ".intval($nPartyID)." and UMFRAGE_AKTUELL='J'")->fetch_assoc();
	$aktuelle_umfrage = $row['UMFRAGE_UMFRAGEID'];
	$anzahl = $row['UMFRAGE_AUSWAHL_ANZAHL'];

	if (!isset($_GET['UmfrageID']) && intval($_GET['UmfrageID']) < 1) {
		$UmfrageID = $aktuelle_umfrage;
	} else {
		$UmfrageID = intval($_GET['UmfrageID']);
	}

	if ($_GET['action'] == "results") {
		// Ergebnisse anzeigen
		if ($UmfrageID < 1) {$UmdfrageID = -1;}

		$UmfrageID = $UmfrageID * 1;
		$result = DB::query("select UMFRAGE_BESCHREIBUNG from UMFRAGE where UMFRAGE_UMFRAGEID = ".intval($UmfrageID)." and UMFRAGE_MANDANTID=".intval($nPartyID)." ORDER BY UMFRAGE_BESCHREIBUNG");
		$row = $result->fetch_array();
		$putout = $row ['UMFRAGE_BESCHREIBUNG'];
		echo "<p>".htmlspecialchars($putout)."</p>";

		$resultGes = DB::query("select count(*) from UMFERG where UMFERG_UMFRAGEID = ".intval($UmfrageID)." and UMFERG_MANDANTID=".intval($nPartyID));
		$row = $resultGes->fetch_array();
		$Gesamt = $row[0];
		if ($Gesamt < 1) { $Gesamt = 0.1; }

		$result = DB::query("select * from UMFVAUS where UMFVAUS_UMFRAGEID=".intval($UmfrageID)." order by UMFVAUS_VOTEORDER");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		echo "<table >";
		while ($row = $result->fetch_array()) {
			$result2 = DB::query("select count(*) from UMFERG where UMFERG_UMFRAGEID=".intval($UmfrageID)." and UMFERG_VOTENR = $row[UMFVAUS_VOTENR]");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";		
			$row_count = $result2->fetch_array();
			echo "<tr><td>".htmlspecialchars($row['UMFVAUS_VOTEBESCHREIBUNG'])."</td><td>";
			
			echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
			echo "<tr><td valign=\"middle\"><img src=\"/gfx/vote_balken_left.gif\" width=\"2\"><img src=\"/gfx/vote_balken.gif\" width=".(400 * ($row_count[0]/$Gesamt))." height=\"10\"><img src=\"/gfx/vote_balken_right.gif\" width=\"2\"></td><td> &nbsp;".round($row_count[0]/$Gesamt*100)." % ($row_count[0])</nobr></td></tr>";
			echo "</table>";
		}
		echo "</td></tr></table>";

		echo "<br><p><img src=\"/gfx/pfeil_tabelle.gif\" border=\"0\"> <a href=\"?page=32&UmfrageID=".intval($UmfrageID)."\">Jetzt abstimmen</a></p>";

	} else {

		//Nur bezahlte User duerfen abstimmen, ausser in der Config ist eingestellt dass jeder darf
		if (!User::hatBezahlt() && CFG::getMandantConfig("VOTE_UNBEZAHLTE") != "J") {
			$voteDenied = 1;
		} else {
			$voteDenied = 0;
		}

		if ($nLoginID == "") {
			echo "<p>".$str['vote_err_eingeloggt']."</p>";
		} elseif ($voteDenied == 1) {
			echo "<p>".$str['vote_err_bezahlt']."</p>";
		} elseif ($UmfrageID != $aktuelle_umfrage) {
			echo "<p>Umfrage beendet. Es kann nur f&uuml;r die aktuelle Umfrage gestimmt werden.</p>";
		} elseif ($_POST['Umfrage'] < 1) {
			echo "<p>".$str['vote_err_waehlen']."</p>";
			include "pelasfront/vote_choices.php"; # Antwortmöglichkeiten zeigen
		} elseif (count($_POST['Umfrage']) > $anzahl) {
			echo "<p>".str_replace("%s",$anzahl,$str['vote_err_maxzahl'])."</p>";
			include "pelasfront/vote_choices.php"; # Antwortmöglichkeiten zeigen
			
		} else {
			$UmfrageID = $UmfrageID * 1;
			$result = DB::query("select * from UMFERG where UMFERG_USERID = ".intval($nLoginID)." and UMFERG_UMFRAGEID = '".intval($UmfrageID)."'");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			$row = $result->fetch_array();
			$voted = $row['UMFERG_USERID'];
			if ($voted != "") {
				echo "<p>".$str['vote_err_schon']."</p>";
			} else {
				echo "<p>".$str['vote_danke']."</p>";

				if($anzahl==1){
					$result = DB::query("insert into UMFERG (UMFERG_USERID, UMFERG_MANDANTID, UMFERG_UMFRAGEID, UMFERG_VOTENR) values ($nLoginID, $nPartyID, '".intval($UmfrageID)."', '".intval($_POST['Umfrage'])."')");
				} else{
					foreach ($_POST['Umfrage'] as $ergebnis) {
						//echo $ergebnis;
						$result = DB::query("insert into UMFERG (UMFERG_USERID, UMFERG_MANDANTID, UMFERG_UMFRAGEID, UMFERG_VOTENR) values ($nLoginID, $nPartyID, '".intval($UmfrageID)."', '".intval($ergebnis)."')");
					}
				}
				
				//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			}
			echo "<p><img src=\"/gfx/pfeil_tabelle.gif\" border=\"0\"> <a href=\"?page=32&action=results&UmfrageID=".intval($UmfrageID)."\">".$str['ergebnis']."</a></p>";
		}
	}
} else {
	 echo '<h4>Derzeit ist keine Umfrage aktiv!</h4>';
}