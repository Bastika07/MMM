<?php
include_once "dblib.php";
include_once "format.php";
include_once "session.php";

$sMeldung = "ok";

if (!isset($dbh))
	$dbh = DB::connect();

// Astatus und pers. Daten
$sql = "select a.STATUS, a.RABATTSTUFE, a.WANNBEZAHLT, a.STATUS,u.STRASSE, u.PLZ, u.ORT, u.NAME, u.NACHNAME from USER u, ASTATUS a where a.USERID=u.USERID and a.USERID=$nLoginID and a.MANDANTID=$nPartyID";
$res = DB::query($sql);
$rowUser = mysql_fetch_assoc($res);
//echo mysql_errno().": ".mysql_error()."<BR>";

// Daten zur Party
$sql = "select BESCHREIBUNG, FIRMA, STRASSE, PLZ, ORT, STEUERNUMMER from MANDANT where MANDANTID=$nPartyID";
$res = DB::query($sql);
$rowMandant = mysql_fetch_assoc($res);

// Eintrittspreise und Rabatte holen
$nEintrittNormal = CFG::getMandantConfig('EINTRITT_NORMAL', $nPartyID);
$nEintrittLoge   = CFG::getMandantConfig('EINTRITT_LOGE', $nPartyID);

if ($rowUser['STATUS'] == $STATUS_BEZAHLT_LOGE) {
	$nBetrag = $nEintrittLoge;
} else {
	$nBetrag = $nEintrittNormal;
}

//Rabattstufe mit einrechnen
if ($rowUser['RABATTSTUFE'] > 0) {
	// Rabatt ermitteln

	//Welche Rabatte werden angeboten?
	$row = @mysql_fetch_array(mysql_db_query($dbname, "select STRINGWERT from CONFIG where PARAMETER = 'RABATT5' and MANDANTID = $nPartyID", $dbh), MYSQL_ASSOC);
	$Rabatt5 = $row[STRINGWERT];
	$row = @mysql_fetch_array(mysql_db_query($dbname, "select STRINGWERT from CONFIG where PARAMETER = 'RABATT10' and MANDANTID = $nPartyID", $dbh), MYSQL_ASSOC);
	$Rabatt10 = $row[STRINGWERT];
	$row = @mysql_fetch_array(mysql_db_query($dbname, "select STRINGWERT from CONFIG where PARAMETER = 'RABATT15' and MANDANTID = $nPartyID", $dbh), MYSQL_ASSOC);
	$Rabatt15 = $row[STRINGWERT];

	if ($rowUser['RABATTSTUFE'] == 5) {
		$nBetrag = ($nBetrag - $nBetrag / 100 * $Rabatt5);
	} elseif ($rowUser['RABATTSTUFE'] == 10) {
		$nBetrag = ($nBetrag - $nBetrag / 100 * $Rabatt10);
	} elseif ($rowUser['RABATTSTUFE'] == 15) {
		$nBetrag = ($nBetrag - $nBetrag / 100 * $Rabatt15);
	}
}


if (User::hatBezahlt($nLoginID, $nPartyID)) {
	echo "
	<body bgcolor=\"#FFFFFF\">
	<table width=\"675\"><tr><td>
	<font face=\"Arial\">
	<p>".db2display($rowMandant['FIRMA'])."<br>
	Veranstalter der ".db2display($rowMandant['BESCHREIBUNG'])."<br>
	".db2display($rowMandant['STRASSE'])."<br><br>
	".db2display($rowMandant['PLZ']." ".$rowMandant['ORT'])."<br><br>
	Steuernummer ".db2display($rowMandant['STEUERNUMMER'])."<br>
	<br><br><br>
	<p>".db2display($rowUser['NAME']." ".$rowUser['NACHNAME'])."<br>
	".db2display($rowUser['STRASSE'])."<br><br>
	".db2display($rowUser['PLZ']." ".$rowUser['ORT'])."
	</p><br><br><br>

	<h1>Rechnung ".str_replace(".","",dateDisplay2Short($rowUser['WANNBEZAHLT']))."_".$nLoginID."</h1>
	<br>
	<p align=\"justify\">Am ".dateDisplay2Short($rowUser['WANNBEZAHLT']).
	" haben wir $nBetrag Euro als Teilnahmegeb&uuml;hr f&uuml;r die Veranstaltung
	<i>".db2display($rowMandant['BESCHREIBUNG'])."</i> 
	per &Uuml;berweisung erhalten.
	In diesem Betrag sind 16% MwSt. (".round (($nBetrag/116*16),2)." Euro, Nettobetrag ".round (($nBetrag/116*100),2)." Euro) enthalten.
	</p>
	<br><br>
	<p align=\"justify\"><small><b>Hinweis:</b>
	Bei Differenzen zum tats&auml;chlichen Zahlungsstand des Benutzers, sind die tats&auml;chlichen
	Buchungsvorg&auml;nge auf dem Konto des Veranstalters ma&szlig;gebend.</small></p>
	
	</td></tr>
	</table>
	";
} else {
	PELAS::Fehler("Der Benutzer hat f&uuml;r diese Party nicht gezahlt.");
}

?>
