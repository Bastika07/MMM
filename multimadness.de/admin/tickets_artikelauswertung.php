<?php
require('controller.php');
require_once "dblib.php";
$iRecht = array("ACCOUNTINGADMIN", "STATISTIKADMIN");
include "checkrights.php";
include_once("format.php");
include_once "pelasfunctions.php";
include_once "PHPMailer/PHPMailerAutoload.php";
include "admin/vorspann.php";

$dbh = DB::connect();

echo "<h1>Accounting: Artikelauswertung</h1>";

?>
	
	<table width="600" cellspacing="0" cellpadding="0" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">

	<form method="post" name="filter" action="tickets_artikelauswertung.php">
	<?= csrf_field() ?>

	<tr><td class="navbar" colspan="6"><b>Filtereinstellungen</b></td></tr>
	<tr>

<?php

//**************************************
// Erste Stufe: Mandantauswahl

echo "<td class=\"dblau\">Mandant</td>";
		
echo "<td class=\"hblau\"><select name=\"mandantId\" OnChange=\"document.forms.filter.submit();\">";
echo "<option value=\"-1\">Bitte w&auml;hlen</option>";
$sql = "select distinct
					m.MANDANTID, 
 					m.BESCHREIBUNG 
 				from 
 					MANDANT m, 
 					RECHTZUORDNUNG r 
 				where 
 					r.MANDANTID=m.MANDANTID and 
 					r.USERID='".intval($loginID)."' and 
 					(r.RECHTID='ACCOUNTINGADMIN' or r.RECHTID='STATISTIKADMIN')
";
$result= DB::query($sql );
//echo DB::$link->errno.": ".DB::$link->error."<BR>";
while ($row = $result->fetch_array()) {
	echo "<option value=\"$row[MANDANTID]\"";
	if (isset($_POST['mandantId']) && $_POST['mandantId'] == $row['MANDANTID']) {echo " selected";}
	echo ">$row[BESCHREIBUNG]";
}
echo "</select></td>";

echo "</tr><tr>";

//**************************************
// Partyauswahl

echo "<td class=\"dblau\">Party</td>";
echo "<td class=\"hblau\">";

if (isset($_POST['mandantId'])) {
	// Mandant wurde gewählt, partys anzeigen

	$sql = "select distinct
					p.partyId, 
 					p.beschreibung,
 					p.terminVon
 				from 
 					party p, 
 					RECHTZUORDNUNG r 
 				where 
 					r.MANDANTID=p.mandantId and 
 					r.USERID='".intval($loginID)."' and 
 					(r.RECHTID='ACCOUNTINGADMIN' or r.RECHTID='STATISTIKADMIN') and
 					p.mandantId='".intval($_POST['mandantId'])."'
 				order by
 				  p.terminVon desc
	";
	$result= DB::query($sql );

	echo "<select name=\"partyId\" OnChange=\"document.forms.filter.submit();\">";
	echo "<option value=\"-1\">Bitte w&auml;hlen</option>";

	while ($row = $result->fetch_array()) {
		echo "<option value=\"$row[partyId]\"";
		if (isset($_POST['partyId']) && $_POST['partyId'] == $row['partyId']) {echo " selected";}
		echo ">".htmlspecialchars($row[beschreibung])." (".dateDisplay2Short($row['terminVon']).")";
	}
	echo "</select>";
	
} else {
	echo "Bitte zuerst Mandanten w&auml;hlen";
}
echo "</td>";

echo "</tr><tr>";


//**************************************
// Artikelauswahl

echo "<td class=\"dblau\">Artikel</td>";
echo "<td class=\"hblau\">";

if ($_POST['partyId'] > 0) {
	// Party wurde gewählt, Artikel anzeigen

	$sql = "select distinct
					y.typId, 
 					y.kurzbeschreibung
 				from 
 					acc_ticket_typ y,
 					RECHTZUORDNUNG r
 				where 
 					y.partyId = '".intval($_POST['partyId'])."' and 
 					r.USERID='".intval($loginID)."' and 
 					(r.RECHTID='ACCOUNTINGADMIN' or r.RECHTID='STATISTIKADMIN') and
 					r.mandantId='".intval($_POST['mandantId'])."'
	";
	
	$result= DB::query($sql );

	echo "<select name=\"typId\" OnChange=\"document.forms.filter.submit();\">";
	echo "<option value=\"-1\">Bitte w&auml;hlen</option>";

	while ($row = $result->fetch_array()) {
		echo "<option value=\"$row[typId]\"";
		if (isset($_POST['typId']) && $_POST['typId'] == $row['typId']) {echo " selected";}
		echo ">$row[kurzbeschreibung]</option>\n";
	}
	echo "</select>";
	
} else {
	echo "Bitte zuerst Party w&auml;hlen";
}
echo "</td>";

echo "</tr><tr>";


//**************************************
// Filtereinstellungen

echo "<td class=\"dblau\">Filter</td>";
echo "<td class=\"hblau\">";

if ($_POST['typId'] > 0) {
	// Typ wurde gewählt, Filter anzeigen

	echo "<input type=\"radio\" name=\"filter\" value=\"1\" ";
	if ($_POST['filter'] == 1) {
		echo "checked	";
	}
	echo "> Alle bezahlten<br>";
	echo "<input type=\"radio\" name=\"filter\" value=\"2\"";
	if ($_POST['filter'] == 2) {
		echo "checked	";
	}
	
	// Filter Vorselektieren wenn erster Aufruf, Storno Tage vor aktuellem Datum
	if (!isset($_POST['filter_tag'])) {
		// Stornofrist holen
		$stornoFrist  = CFG::getMandantConfig(TICKETVERKAUF_AUTOSTORNO, $_POST['mandantId']);
		// Zieldatum setzen
		//$zielDatum    = mktime(0, 0, 0, date("m"), date("d")-$stornoFrist, date("Y"));
		$zielDatum=mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$_POST['filter_tag']   = date("d", $zielDatum);
		$_POST['filter_monat'] = date("m", $zielDatum);
		$_POST['filter_jahr']  = date("Y", $zielDatum);
	}
	
	echo "> Status offen und Bestelldatum &auml;lter als <select name=\"filter_tag\">";
		for ($i=1;$i<= 31;$i++) {
			echo "<option";
			if ($i == $_POST['filter_tag']) {echo " selected";}
			echo ">$i";
		}
	echo "</select>. <select name=\"filter_monat\">";
		for ($i=1;$i<= 12;$i++) {
			echo "<option";
			if ($i == $_POST['filter_monat']) {echo " selected";}
			echo ">$i";
		}
	echo "</select>. <select name=\"filter_jahr\">";
		for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++) {
			echo "<option";
			if ($i == $_POST['filter_jahr']) {echo " selected";}
			echo ">$i";
		}
	echo "</select><br>";

	/*
	echo "<input type=\"radio\" name=\"filter\" value=\"3\"";
	if ($filter == 3) {
		echo "checked	";
	}
	echo "> Filter nach Daten TODO<br>";
	*/
	echo "<input type=\"radio\" name=\"filter\" value=\"4\"";
	if ($_POST['filter'] == 4) {
		echo "checked	";
	}
	echo "> Kauf- und Bestellstatistik<br>";
	echo "<input type=\"submit\">";
	
} else {
	echo "Bitte zuerst Artikel w&auml;hlen";
}
echo "</td>";

echo "</tr></table></td></tr></table>";

// Wenn Filter ausgewählt, dann Auswertung anzeigen

if ($_POST['typId'] > 0 && $_POST['filter'] > 0) {
	// Auwahl gültig, go
	
	if ($_POST['submitButton'] == "Liste stornieren") {
		// Storno-Action
		
		// Besondere Rechte-Abfrage auf ACCOUNTINGADMIN
		if (!BenutzerHatRechtMandant ("ACCOUNTINGADMIN", $_POST['mandantId'])) {
			echo "<p class=\"fehler\">Kein Recht f&uuml;r diese Aktion. Ben&ouml;tigt: ACCOUNTINGADMIN</p>";
		} else {
			echo "<p>";
			for ($i=1;$i<$_POST['bestellIdCount'];$i++) {
				echo "Storniere PartyId ".intval($_POST['partyId'])." und BestellId ".intval($_POST['bestellId'][$i])."<br>";
				$sql = "update
					  acc_bestellung
					set
					  status = ".ACC_STATUS_STORNIERT."
					where
					  bestellId = '".intval($_POST['bestellId'][$i])."' and
					  partyId   = '".intval($_POST['partyId'])."'
				";
				$result= DB::query($sql);
				// Tickets auch stornieren
				$sql = "update
					  acc_tickets
					set
					  statusId = ".ACC_STATUS_STORNIERT."
					where
					  bestellId = '".intval($_POST['bestellId'][$i])."' and
					  partyId   = '".intval($_POST['partyId'])."'
				";
				$result= DB::query($sql);
				// User informieren
				sendeBestellBestaetigung(intval($_POST['partyId']), intval($_POST['bestellId'][$i]), 1, ACC_STATUS_STORNIERT);
			}
			echo "</p>";
		}
	} elseif ($_POST['filter'] == 4) {
		// Statistik ausgeben
		echo "<p><img src=\"tickets_statistik_bild.php?partyId=".intval($_POST['partyId'])."&typId=".intval($_POST['typId'])."&temp=".time()."\" border=\"1\"></p>";
		
		// Zahlen der letzten 30 Tage nochmal in Summen
		
		echo "<p><table callpadding=\"3\" cellspacing=\"1\" border=\"0\"style=\"text-align: center;\"><tr><td class=\"navbar\"><b>Datum</b></td><td class=\"navbar\"><b>Artikel bestellt</b></td><td class=\"navbar\"><b>Artikel bezahlt</b></td><td class=\"navbar\"><b>Neue Registrierungen</b></td></tr>";
		
		//Von bis Tag des Jahres?
		$sql = "select 
			  min(TO_DAYS(wannAngelegt)) as MinDoy, max(TO_DAYS(wannAngelegt)) as MaxDoy 
			from 
			  acc_bestellung
			where 
			  partyId = '".intval($_POST['partyId'])."' and
			  ticketTypId = '".intval($_POST['typId'])."'";

		$result = DB::query($sql);
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";

		$row = $result->fetch_array();
		$MinDoy = $row[MinDoy];
		$MaxDoy = $row[MaxDoy];
		$timeframe=$MaxDoy-$MinDoy;
		$class = "hblau";
		
		for ($i=$MaxDoy;$i>=$MaxDoy-$timeframe;$i--) {
			// letzten 10 Tage loopen
			$sql = "select 
				  sum(anzahl) as summe, max(wannAngelegt) as DOY
				from 
				  acc_bestellung
				where 
				  partyId = ".intval($_POST['partyId'])." and 
				  TO_DAYS(wannAngelegt) = $i and
				  ticketTypId = '".intval($_POST['typId'])."'
				group by 
				  TO_DAYS(wannAngelegt)";
			$result = DB::query($sql);
			$rowBestellt = $result->fetch_array();
			
			// Bezahlungen
			$sql = "select 
				  sum(anzahl) as summe, max(wannBezahlt) as DOY
				from 
				  acc_bestellung
				where 
				  partyId = ".intval($_POST['partyId'])." and 
				  TO_DAYS(wannBezahlt) = $i and
				  ticketTypId = '".intval($_POST['typId'])."'
				group by 
				  TO_DAYS(wannBezahlt)";
			$result = DB::query($sql);
			$rowBezahlt = $result->fetch_array();
			
			// Registrierungen
			$sql = "select 
				  count(a.USERID) as summe, max(a.WANNANGELEGT) as DOY
				from 
				  ASTATUS a,
				  party p
				where 
				  p.partyId = ".intval($_POST['partyId'])." and 
				  p.MANDANTID=a.MANDANTID and
				  TO_DAYS(a.WANNANGELEGT) = $i 
				group by 
				  TO_DAYS(a.WANNANGELEGT)";
			$result = DB::query($sql);
			$rowRegistriert = $result->fetch_array();
			
			if($rowBestellt['summe']>0 || $rowBezahlt['summe'] > 0 || $rowRegistriert['summe'] > 0){
				//if ($rowRegistriert['DOY'] == "") {
				//	$sDatum = $rowBestellt['DOY'];
				//} else {
				//	$sDatum = $rowRegistriert['DOY'];
				//}
				$calcDate = date('d.m.Y',strtotime("1 January 0000 + ".$i."days"));
				echo "<tr><td class=\"$class\">".$calcDate."</td><td class=\"$class\">".$rowBestellt['summe']."</td><td class=\"$class\">".$rowBezahlt['summe']."</td><td class=\"$class\">".$rowRegistriert['summe']."</td></tr>";

				if ($class == "hblau") {
					$class = "dblau";
				} else {
					$class = "hblau";
				}
			}
			
			

		}
		
		echo "</table>";
		
	} else {
		// Filter setzen für SQL
		if ($_POST['filter'] == 2) {
			// Bestellzeit
			#$sAddWhere = "and wannAngelegt > '$filter_jahr-$filter_monat-$filter_tag'";
			$sAddWhere = sprintf("and b.wannAngelegt < '%04d-%02d-%02d'", intval($_POST['filter_jahr']), intval($_POST['filter_monat']), intval($_POST['filter_tag']));
			$sAddWhere .= "and b.status = '".ACC_STATUS_OFFEN."'";
		} elseif ($_POST['filter'] == 1) {
			$sAddWhere = "and b.status = '".ACC_STATUS_BEZAHLT."'";
		} else {
			$sAddWhere = "";
		}

		$sql = "select
						y.typId, 
						y.kurzbeschreibung,
						b.anzahl,
						b.bestellId,
						b.delivered,
						b.wannAngelegt,
						u.NAME,
						u.NACHNAME,
						u.PLZ,
						bs.beschreibung
					from 
						acc_ticket_typ y,
						acc_bestellung b,
						acc_ticket_bestellung_status bs,
						USER u
					where 
						u.USERID = b.bestellerUserId and
						y.typId = '".intval($_POST['typId'])."' and
						b.ticketTypId=y.typId and
						y.partyId = '".intval($_POST['partyId'])."' and 
						bs.statusId = b.status
						$sAddWhere
					order by
						b.bestellId
		";

		$result= DB::query($sql );
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		?>

			<script language="JavaScript">

	function openBestellung2(PartyId, BestellId, iStatus) {
	    detail = window.open("tickets_bestellungen.php?action=detail&iPartyId="+PartyId+"&iBestellId="+BestellId+"&iStatus="+iStatus,"Bestellung","width=620,height=520,locationbar=false,resize=false");
	    detail.focus();
	}

	</script>
		
		<p>
		<table width="600" cellspacing="0" cellpadding="0" border="0">
		<tr><td class="navbar">
		<table width="100%" cellspacing="1" cellpadding="3" border="0">

		<form method="post" name="filter" action="tickets_artikelauswertung.php">
		<?= csrf_field() ?>

		<tr><td class="navbar"><b>Best.-Nr.</b></td><td class="navbar"><b>Anzahl</b></td><td class="navbar"><b>Name</b></td><td class="navbar"><b>PLZ</b></td><td class="navbar"><b>Bestellt am</b></td><td class="navbar"><b>Status</b></td></tr>

		<?php

		$counter = 0;
		$countIt = 1;
		$farbe   = "hblau";
		while ($row = $result->fetch_array()) {
			echo "<input type=\"hidden\" name=\"bestellId[$countIt]\" value=\"".$row['bestellId']."\">";
			echo "<tr>";
			//echo "<td class=\"$farbe\"><a href=\"../admin/tickets_bestellungen.php?iBestellId=".PELAS::formatBestellNr($_POST['partyId'], $row['bestellId'])."&iStatus=-1\">".PELAS::formatBestellNr($_POST['partyId'], $row['bestellId'])."</a></td>";
			echo "<td class=\"$farbe\"><a href=\"javascript:openBestellung2('".intval($_POST['partyId'])."', '".$row['bestellId']."', '-1');\">".PELAS::formatBestellNr(intval($_POST['partyId']), $row['bestellId'])."</a></td>";
			echo "<td class=\"$farbe\">".$row['anzahl']."</td>";
			echo "<td class=\"$farbe\">".$row['NACHNAME'].", ".$row['NAME']."</td>";
			echo "<td class=\"$farbe\">".$row['PLZ']."</td>";
			echo "<td class=\"$farbe\">".dateDisplay2($row['wannAngelegt'])."</td>";
			echo "<td class=\"$farbe\">".$row['beschreibung'];
			if ($row['delivered'] == "J") {
				echo ", verschickt";
			}
			echo "</td>";
			echo "</tr>";
			$counter = $counter + $row['anzahl'];
			if ($farbe == "hblau") {
				$farbe = "dblau";
			} else {
				$farbe = "hblau";
			}
			$countIt++;
		}
		echo "<input type=\"hidden\" name=\"bestellIdCount\" value=\"".$countIt."\">";
		if ($counter < 1) {
			echo "<tr><td class=\"$farbe\" colspan=\"6\">Keine Artikel vorhanden.</td></tr>";	
		} else {
			echo "<tr><td class=\"".$farbe."\" colspan=\"6\">Insgesamt $counter Artikel. ";
			if ($_POST['filter'] == 2) {
				echo "<input name=\"submitButton\" type=\"submit\" value=\"Liste stornieren\">";
			}
			echo "</td></tr>";
		}
	}
  echo "</table></td></tr></table></p>";
}


include "admin/nachspann.php";
?>
