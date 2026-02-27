<?php

// Execution-Limit auf 300 Sekunden setzen
set_time_limit(300);

include_once "dblib.php";
include_once "format.php";
include_once "pelasfunctions.php";
include_once "language.inc.php";
include_once 'PHPMailer/PHPMailerAutoload.php';

$theSummary = "";

$dbh = DB::connect();

// Aktuelle Party des Mandanten in Variable zwischenspeichern

// Alle Mandanten durchloopen, wo party aktiv ist und nicht vergangen

$sql = "select 
	  partyId,
	  mandantId
	from 
	  party p
	where 
	  p.aktiv     = 'J' and
	  p.terminVon > now()";

$res = DB::query($sql);

$theSummary = "Erinnerungen:\n";
$counter = 0;

while ($row = mysql_fetch_array($res)) {

	// Bestellungen raussuchen Zahlungserinnerung
	// Kriterien: 
	// - Heute ist X Tage neuer als Bestelldatum (Aus config)
	// - reminder < 1

	$reminderOffset = CFG::getMandantConfig("TICKETVERKAUF_REMINDER", $row['mandantId']);

	$sql = "select distinct
		  b.bestellId,
		  b.wannAngelegt
		from 
		  acc_bestellung b
		where 
		  b.partyId      = '".$row['partyId']."' and
		  to_days(b.wannAngelegt)+$reminderOffset < to_days(now()) and
		  b.reminder     < '1' and
		  b.status       = ".ACC_STATUS_OFFEN;
	
	$resBestellung = DB::query($sql);
	while ($rowBestellung = mysql_fetch_array($resBestellung)) {
		// Erinnerungsmail senden, 2 = Zahlungserinnerung
		$counter++;
		$theSummary .= "PartyID: ".$row['partyId']." / Bestellid: ".$rowBestellung['bestellId']." / Bestdatum ".$rowBestellung['wannAngelegt']."\n";
		sendeBestellBestaetigung($row['partyId'], $rowBestellung['bestellId'], 2);
		
		// Bestelldatensatz anpassen, anzahl reminder auf 1 setzen
		$sql = "update
			  acc_bestellung
			set
			  reminder = '1'
			where
			  partyId   = '".$row['partyId']."' and
			  bestellId = '".$rowBestellung['bestellId']."'";
		DB::query($sql);
	}
}
$theSummary .= "Summe: $counter \n";

$theSummary .= "\nStornierungen:\n";
$theSummary .= "(noch deaktiviert)\n";

while ($row = mysql_fetch_array($res)) {
	// Bestellungen raussuchen zur Stornierung
	// Kriterien:
	// - Heute ist X Tage neuer als Bestelldatum (Aus config)
		
	// TODO! Stornierung derzeit noch manuell
	
}


// Zusammenfassung an il.de mailen
$betreff = "PELAS: Zusammenfassung der Erinnerungen und Stornierungen";
/* $header  = "From: noreply@innovalan.de <noreply@innovalan.de>\nTo: info@innovalan.de <info@innovalan.de>\nError-To: noreply@innovalan.de\nReply-To: noreply@innovalan.de\nX-Priority: 3\nX-Mailer: PHP/". phpversion();
mail("info@innovalan.de", $betreff, $theSummary, $header); */

$erfolg = sende_mail_text(ADMIN_MAIL, $betreff, $theSummary);

?>
