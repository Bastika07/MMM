<?php
//###################################
// Diverse Funktionen für PELAS

include "language.inc.php";
//include_once "pelas_bot.php";

//Funktion zum Auslesen der Bankdaten&Userdaten für eine bestimmte Party, aus der Config-Tabelle
//if (!function_exists('LeseKontoDaten'))
//{
//	function LeseKontoDaten($nPartyID, $nLoginID)
//	{
//	  global $dbname;
//	  
//	  DB::connect();
//	    
//	  $config = Array();
//	  
//		$row = DB::query("select USERID, LOGIN, EMAIL from USER where USERID = $nLoginID")->fetch_assoc(); 
//		$config[USERID] = $row[USERID];
//		$config[LOGIN] = $row[LOGIN];
//		$config[EMAIL] = $row[EMAIL];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NAME' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[KONTO_NAME] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NUMMER' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[KONTO_NUMMER] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BLZ' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[KONTO_BLZ] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BANK' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[KONTO_BANK] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_NORMAL' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[EINTRITT_NORMAL] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_LOGE' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[EINTRITT_LOGE] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_XTRA' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[EINTRITT_XTRA] = " ".$row[STRINGWERT];
//		// Added IBAN and BIC to config Array
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_IBAN' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[KONTO_IBAN] = $row[STRINGWERT];
//		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BIC' and MANDANTID = $nPartyID")->fetch_assoc();
//		$config[KONTO_BIC] = $row[STRINGWERT];
//	}
//}

// Zeigt eine Rechnung anhand partyId und bestellId
// WICHTIG: Rechteprüfung muss von dem aufrufenden Script
// erledigt werden
if (!function_exists('zeigeRechnung')) {
	
	function zeigeRechnung($partyId, $bestellId) {

		$bestellId = intval($bestellId);
		
		// ezpdf-lib includen
		include ('class.ezpdf.php');

		// Daten des Bestellers und des Mandanten raussuchen
		$sql = "select
			  b.bestellId,
			  b.wannAngelegt,
			  b.mwstSatz,
			  b.wannBezahlt,
			  b.zahlungsweiseId,
			  u.NAME,
			  u.NACHNAME,
			  u.STRASSE,
			  u.PLZ,
			  u.ORT,
			  u.LAND,
			  m.FIRMA,
			  m.STRASSE as FaSTRASSE,
			  m.PLZ as FaPLZ,
			  m.ORT as FaORT,
			  m.STEUERNUMMER,
			  m.HANDELSREGISTER,
			  f.descGerman as landString
			from
			  MANDANT m,
			  USER u,
			  acc_bestellung b,
			  party p,
			  acc_flags f
			where
			  b.partyId     = '$partyId' and
			  b.bestellId   = '$bestellId' and
			  b.partyId     = p.partyId and
			  p.mandantId   = m.MANDANTID and
			  u.USERID      = b.bestellerUserId and
			  LCASE(f.isoCode) = LCASE(u.LAND)
		";

		$result_benutzer = DB::query($sql);
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$row = $result_benutzer->fetch_array();
		
		if ($result_benutzer->num_rows) {
			
			$bodySize = 11;
	
			// Kopf ausgeben & PDF-Setup
			$pdf = new Cezpdf('a4','portrait');
			$pdf->selectFont('../includes/fonts/Helvetica.afm');

			$pdf->ezSetDy(-86);

			$pdf->ezText(utf8_decode("<u>".$row['FIRMA']." * ".$row['FaSTRASSE']." * ".$row['FaPLZ']." ".$row['FaORT']."</u>"),7);
			$pdf->ezSetDy(-4);
			$pdf->ezText(utf8_decode($row['NAME']." ".$row['NACHNAME']),$bodySize);
			$pdf->ezText(utf8_decode($row['STRASSE']),$bodySize);
			$pdf->ezSetDy(-4);
			$pdf->ezText(utf8_decode($row['PLZ']." ".$row['ORT']),$bodySize);
			$pdf->ezSetDy(-4);
			$pdf->ezText(utf8_decode($row['landString']),$bodySize);

			$pdf->ezSetDy(-50);

			$pdf->ezText('Rechnung',30);
			
			$pdf->ezSetDy(-25);
			
			$pdf->ezText("Nummer: ".PELAS::formatBestellNr($partyId, $bestellId),$bodySize);
			$pdf->ezText("Datum: ".dateDisplay2Short($row['wannAngelegt']),$bodySize);

			$pdf->ezSetDy(-45);

			// Posten ausgeben
			$sql = "select
				  y.kurzbeschreibung,
				  y.beschreibung,
				  b.anzahl,
				  b.preis,
				  b.mwstSatz
				from
				  acc_bestellung b,
				  acc_ticket_typ y
				where
				  b.bestellId = '$bestellId' and
				  b.partyId   = '$partyId' and
				  y.typId     = b.ticketTypId
			";
			$result_ticket = DB::query($sql);
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			
			$summe = 0;
			$data = array();
			while ($rowTicket = $result_ticket->fetch_array()) {
				$data[] = 
				  array('Anzahl'=>$rowTicket['anzahl'],
				  	'Ticket/ Beschreibung'=>utf8_decode($rowTicket['kurzbeschreibung']),
				  	'Preis'=>sprintf("%01.2f",$rowTicket['preis'])." EUR",
				  	'Summe'=>sprintf("%01.2f",$rowTicket['preis']*$rowTicket['anzahl'])." EUR"
				  );
				$data[] = array('Ticket/ Beschreibung'=>"<i>".utf8_decode($rowTicket['beschreibung'])."</i>");
				  $summe = $summe + $rowTicket['preis']*$rowTicket['anzahl'];
			}
			$data[] = array('Anzahl'=>'');
			//$data[] = array('Ticket/ Beschreibung'=>'<b>Gesamtsumme inkl. '.$row['mwstSatz'].' % MwSt.</b> (netto: '.sprintf("%01.2f",($summe/(100+$row['mwstSatz'])*100)).', MwSt: '.sprintf("%01.2f",(
			$data[] = array('Ticket/ Beschreibung'=>'<b>Gesamtsumme',
				'Summe'=> "<b>".sprintf("%01.2f",$summe)." EUR</b>"
			);
				  
			$pdf->ezTable($data);

			$pdf->ezSetDy(-25);
			
			// Zahlungswiese-text raussuchen
			$sql = "select desc_german
				from acc_zahlungsweise
				where zahlungsweiseId = '".$row['zahlungsweiseId']."'";
			$result_zahlungsweise = DB::query($sql);
			$rowZahlung = $result_zahlungsweise->fetch_array();
						
			$pdf->ezText("Betrag erhalten am ".dateDisplay2Short($row['wannBezahlt'])." per ".utf8_decode($rowZahlung['desc_german']),$bodySize);

			// Footer ausgeben

			$pdf->ezSetDy(-45);

			$pdf->ezText("Steuernummer: ".$row['STEUERNUMMER'],$bodySize);
			
			$pdf->ezSetDy(-14);
			
			$pdf->ezText(utf8_decode("Gemäß $ 19 Abs. 1 UStG wird keine Umsatzsteuer ausgewiesen."),8);
			
			$pdf->ezSetDy(-2);
			
			$pdf->ezText(utf8_decode("Sofern nicht anders angegeben, entspricht das Liefer-Leistungsdatum dem Rechnungsdatum."),8);
			
			//$pdf->ezText($row['HANDELSREGISTER'],$bodySize);

			$pdf->ezStream();
		} else {
			echo "<p>Fehler beim einlesen der Rechnungsdaten.</p>";
		}
	}
}

// Schaltet eine Bestellung frei und legt bei Bedarf Tickets an
if (!function_exists('BestellungFreischalten')) {
	function BestellungFreischalten($partyId, $bestellId) {
		global $nLoginID, $STATUS_ZUORDBAR, $STATUS_BEZAHLT_SUPPORTERPASS;
		
		$bestellId = intval($bestellId);
		$aktuellePartyID = $partyId;
		$nPartyID	 = PELAS::AktuellePartyMandant($aktuellePartyID);
		
		// Bestelldaten lesen, inkl. Typdaten
		$sql = "select t.typId,
				b.anzahl,
				t.translation,
				b.bestellerUserId
			from acc_bestellung b,
			     acc_ticket_typ t
			where t.partyId = ".intval($aktuellePartyID)."
			and b.ticketTypId = t.typId
			and b.bestellId = '".intval($bestellId)."'
			and b.partyId = ".intval($aktuellePartyID);
		$resBestellung = DB::query($sql);

	// NEU 16.10.2012: Immer neue Tickets anlegen, alte immer storniert lassen!

			while ($rowTemp = $resBestellung->fetch_array()) {

				// Alle Bestelldatensätze durch	
				if ($rowTemp['translation'] > 0 && $rowTemp['translation'] != $STATUS_ZUORDBAR) {

					// Alle bestellten Tickets oder Supporterpässe durchnudeln
					for ($i=1; $i<=$rowTemp['anzahl']; $i++) {
						// Es kann im seltenen Fall passieren, dass die ticketid doppelt vergeben
						// wird. In diesem Fall gibt es einen DB-Fehler
						do {

							// Supporterpässe oder Tickets?
							if ($rowTemp['translation'] == $STATUS_BEZAHLT_SUPPORTERPASS) {

								// Bestelldatensatz ist ein Supporterticket: Anlegen!
								$sql = "insert into acc_supporterpass (
									mandantId,
									partyId,
									typId,
									ownerId,
									statusId,
									bestellId,
									werAngelegt,
									wannAngelegt,
									werGeaendert
									) values (
									'".intval($nPartyID)."',
									'".intval($aktuellePartyID)."',
									'".$rowTemp['typId']."',
									'".$rowTemp['bestellerUserId']."',
									'".ACC_STATUS_BEZAHLT."',
									'".intval($bestellId)."',
									'".intval($nLoginID)."',
									now(),
									'".intval($nLoginID)."'
									)
								";
								$resId = DB::query($sql);
		
							} else {
								// Ganz normal Tickets: Neue Ticket-ID ermitteln
								$sql = "select max(ticketId) as ticketId
 									from acc_tickets
									where partyId = ".intval($aktuellePartyID);
								$resId = DB::query($sql);
								$rowId = $resId->fetch_array();
								$ticketId = $rowId['ticketId'];
								$newTicketId = $ticketId + 1;

								// Ticket versuchen anzulegen
								$sql = "insert into acc_tickets (
									ticketId,
									mandantId,
									partyId,
									typId,
									ownerId,
									userId,
									statusId,
									bestellId,
									werAngelegt,
									wannAngelegt,
									werGeaendert
									) values (
									'".intval($newTicketId)."',
									'".intval($nPartyID)."',
									'".intval($aktuellePartyID)."',
									'".$rowTemp['typId']."',
									'".$rowTemp['bestellerUserId']."',
									'".$rowTemp['bestellerUserId']."',
									'".ACC_STATUS_BEZAHLT."',
									'".intval($bestellId)."',
									'".intval($nLoginID)."',
									now(),
									'".intval($nLoginID)."'
									)
								";
								$resId = DB::query($sql);
							}

						} while (DB::$link->errno == 1062);
					}
				}
			}
			

		// Bestellung selbst freischalten

		$sql = "update acc_bestellung b
			set b.status = '".ACC_STATUS_BEZAHLT."',
				b.wannBezahlt = now()
			where
			b.bestellId = '".intval($bestellId)."'
			and b.partyId = ".intval($aktuellePartyID);
		$resBestell = DB::query($sql); 
	
	}
}
	

// sendet eine Bestellbestätigung an den Benutzer
// action = 0/NULL: Bestätigung des Einganges
// action = 1: Status umgesetzt
// action = 2: Erinnerung
// newStatus: Neuer Status
if (!function_exists('sendeBestellBestaetigung'))
{
	function sendeBestellBestaetigung($partyId, $bestellId, $action = NULL, $newStatus = NULL) {
		global $sLang, $str;
		
		$bestellId = intval($bestellId);

		// Ermittlung der Benutzer-, Partydaten
		$sql = "select
		    b.bestellId,
		    u.LOGIN,
		    u.EMAIL as benutzerEmail,
		    u.NAME,
		    u.NACHNAME,
		    u.STRASSE as UsSTRASSE,
		    u.PLZ as UsPLZ,
		    u.ORT as UsORT,
		    u.LAND,
		    p.beschreibung as partyName,
		    m.BESCHREIBUNG,
		    m.EMAIL,
		    m.REFERER,
		    m.FIRMA,
		    m.STRASSE,
		    m.PLZ,
		    m.ORT,
		    m.TELEFON,
		    m.FAX,
		    m.HANDELSREGISTER,
		    b.wannAngelegt as bestellDatum,
		    b.mwstSatz,
		    s.beschreibung as StatusText
		  from 
		    acc_bestellung b,
		    acc_ticket_typ t,
		    MANDANT m,
		    party p,
		    USER u,
		    acc_ticket_bestellung_status s
		  where
		    u.USERID      = b.bestellerUserId and
		    b.ticketTypId = t.typId and
		    m.MANDANTID   = t.mandantId and
		    p.partyId     = t.partyId and
		    s.statusId    = b.status and
		    p.aktiv       = 'J' and
		    b.bestellId   = '".intval($bestellId)."' and
		    b.partyId     = '".intval($partyId)."'";
		$result_benutzer = DB::query($sql);
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$rowBenutzer = $result_benutzer->fetch_array();
		
		// Welche Sprache? Wenn sLang gesetzt ist, dann bestimmt der browser
		// TODO: Erstmal nur ein includen, wenn der Browser eine gesetzt hat zum testen
		if ($sLang == "en") {
			include("english.inc.php");
		} elseif ($sLang == "de") {
			include("german.inc.php");
		} elseif (strtolower($rowBenutzer['LAND']) != "de") {
			include("english.inc.php");
		} else {
			include("german.inc.php");
		}
		
		// Ermittlung der Bestellungsdaten
		$sql = "select
		    b.*,
		    t.*
		  from 
		    acc_bestellung b,
		    acc_ticket_typ t,
		    party p
		  where
		    b.ticketTypId = t.typId and
		    p.partyId     = t.partyId and
		    p.aktiv       = 'J' and
		    b.bestellId   = '".intval($bestellId)."' and
		    b.partyId     = '".intval($partyId)."'";
		$result_bestellung = DB::query($sql);
		

		// Anrede und Adressinformationen Besteller ausgeben
		$email  = "Hallo ".$rowBenutzer['NAME']." ".$rowBenutzer['NACHNAME'].",\n";
		$email .= "\n";
		
		if (isset($action) && $action == 1) {
			// Änderung des Status
			$email .= "$str[acc_statusgeaendert]: ".$rowBenutzer['StatusText']."\n";
		} elseif (isset($action) && $action == 2) {
			// Reminder nach X Tagen
			$email .= $str['acc_zahlungserinnerung1']." ".CFG::getMandantConfig("TICKETVERKAUF_AUTOSTORNO", PELAS::AktuellePartyMandant($partyId))." ".$str['acc_zahlungserinnerung2']."\n";
		} else {
			// Bestellbestätigung
			$email .= $str[acc_best_email_infotext]." ".CFG::getMandantConfig("TICKETVERKAUF_AUTOSTORNO", PELAS::AktuellePartyMandant($partyId))." ".$str[acc_best_email_infotext2]."\n";
		}
		
		$email .= "\n";
		$email .= "\n";
		$email .= "  $str[acc_bestno]: ".PELAS::formatBestellNr($partyId, $rowBenutzer['bestellId'])."\n";
		$email .= "  $str[acc_besteller]: ".$rowBenutzer['NAME']." ".$rowBenutzer['NACHNAME']."\n";
		$email .= "  $str[strasse]         : ".$rowBenutzer['UsSTRASSE']."\n";
		$email .= "  $str[plz] $str[ort]         : ".$rowBenutzer['UsPLZ']." ".$rowBenutzer['UsORT']."\n";
		$email .= "  $str[acc_bestdatum]: ".dateDisplay2Short($rowBenutzer['bestellDatum'])."\n";
		$email .= "  $str[acc_zahlbarbis]: ".date('d.m.Y' ,( strtotime($rowBenutzer['bestellDatum']) + CFG::getMandantConfig("TICKETVERKAUF_AUTOSTORNO", PELAS::AktuellePartyMandant($partyId)) * 86400))." *\n";
		$email .= "\n";
		$email .= "\n";
		$email .= "$str[acc_anz_art_pr_su]\n";
		$email .= "-------------------------------------------------------------------------\n";
		$summe = 0;
		while ($rowBestellung = $result_bestellung->fetch_array()) {
			$email .= " ".str_pad($rowBestellung['anzahl'], 2)."x ";
			$email .= str_pad($rowBestellung['kurzbeschreibung'], 50);
			$email .= str_pad((sprintf("%01.2f", $rowBestellung['preis'])), 7);
			$email .= sprintf("%01.2f", ($rowBestellung['preis']*$rowBestellung['anzahl']))." EUR\n";
			$email .= "     ".$rowBestellung['beschreibung']."\n";
			$summe = $summe + ($rowBestellung['preis']*$rowBestellung['anzahl']);
		}
		$email .= "-------------------------------------------------------------------------\n";
		$email .= str_pad("$str[gsummeinkl] ".$rowBenutzer['mwstSatz']."% $str[mwst]", 62).sprintf("%01.2f",$summe)." EUR\n";
		$email .= "\n";
		$email .= "\n";

		if (isset($action) && $action == 1) {
			// Änderung des Status
			if ($newStatus == ACC_STATUS_BEZAHLT) {
				$email .= $str[acc_kartenkoennenzugeordnet]."\n";
				$email .= $rowBenutzer['REFERER']."?page=6\n";
			} else {
				$email .= $str[acc_mail_beifragen]."\n";
			}
		} else {
			// Bestellbestätigung
			$email .= $str[acc_mail_zahlung]."\n";
			$email .= $rowBenutzer['REFERER']."?page=6&action=bill\n";
				
			// Hinweise zum Zahlungsziel
			$email .= "\n";
			$email .= "* ".$str['acc_mail_fristinfo']."\n";
		}
		
		/* 26.01.2020: Footer ab sofort automatisch in Mail-Funktion! (mgrimm)
		$email .= "\n";
		$email .= "\n";
		$email .= $str[acc_vielegruesse].",\n";
		$email .= "\n";
		$email .= $str[acc_dein]." ".$rowBenutzer['BESCHREIBUNG']."-Team\n";
		$email .= "\n";
		$email .= "--\n";
		$email .= "\n";
		$email .= $rowBenutzer['FIRMA']."\n";
		$email .= $rowBenutzer['STRASSE']."\n";
		$email .= $rowBenutzer['PLZ']." ".$rowBenutzer['ORT']."\n";
		$email .= $str[acc_telefon].": ".$rowBenutzer['TELEFON']."\n";
		$email .= $str[acc_telefax].": ".$rowBenutzer['FAX']."\n";
		$email .= "Mail   : ".$rowBenutzer['EMAIL']."\n";
		if (!empty($rowBenutzer['HANDELSREGISTER'])) {
			$email .= $rowBenutzer['HANDELSREGISTER']."\n";
		} */
		
		$betreff = $str['acc_ticketbestnr'].PELAS::formatBestellNr($partyId, $rowBenutzer['bestellId']);

		$header = 'From: '.$rowBenutzer['EMAIL']. "\r\n";
		$header .= 'Reply-To: '.$rowBenutzer['EMAIL']. "\r\n";
		$header .= 'X-Mailer: PHP/' . phpversion();

		if (LOCATION == "intranet") {
			// Keine Mail senden!
		} else {
			# @mail($rowBenutzer['benutzerEmail'], $betreff, $email, $header);
			$erfolg = sende_mail_text($rowBenutzer['benutzerEmail'], $betreff, $email);
		}

	}
}

// Statistisches Hilfsmittel
function verfuegbareTickets($typId, $aktuellePartyID)
{
	// Bereits bestellte Tickets/ Artikel ermitteln
	
	$sql = "select anzahl
		from acc_bestellung
		where ticketTypId = $typId
		and (status = ".ACC_STATUS_OFFEN." or status =".ACC_STATUS_BEZAHLT.")
		and partyId = '$aktuellePartyID'";
	$res2 = DB::query($sql);
	$bestellteTickets = 0;
	while ($rowTemp2 = $res2->fetch_array()) {
		$bestellteTickets = $bestellteTickets + $rowTemp2['anzahl'];
	}
	
	$sql = "select anzahlVorhanden
		from acc_ticket_typ
		where partyId = '$aktuellePartyID'
		and typId = '$typId'
	";
	$res3 = DB::query($sql);
	$rowTemp3 = $res3->fetch_array();
	
	return $rowTemp3['anzahlVorhanden'] - $bestellteTickets;
}


// Zeigt die offenen Posten nach user oder bestell-id im admin und fe
// wenn userId leer, dann wird bestellId genommen
// gibt ein array zurück, an Position:
// 0: Rechnungssumme
// 1: BestellId
// 2: Mandantname für Überweisung
// Funktion versendet Anmeldebestätigung
if (!function_exists('showOpenBill'))
{
	function showOpenBill($bestellId, $userId, $mandantId, $partyId = false) {
	  global $str, $sLang;
	  
		$bestellId = intval($bestellId);

	  // Wenn admin, dann deutsch includen, sonst FE-Language übernehmen
	  if (!isset($sLang)) {
		include("german.inc.php");
	  }
	  
	  if ($userId > 0) {
	    $sWhere = " b.bestellerUserId = ".intval($userId)." and
			b.status = ".ACC_STATUS_OFFEN;
	  } else {
	    $sWhere = " b.bestellId = ".intval($bestellId);
	  }
	  
	  if ($mandantId > 0) {
	    $sWhere .= " and p.mandantId = ".intval($mandantId);
	  }

	  if ($partyId !== false) {
		$sWhere .= " and p.partyId = ".intval($partyId);
	  } else {
		$sWhere .= " and p.aktiv = 'J'";
	  }

		// Ermittlung der Benutzerdaten
		$sql = "select
		    b.bestellId,
		    u.LOGIN,
		    u.EMAIL as benutzerEmail,
		    u.NAME,
		    u.NACHNAME,
		    u.STRASSE,
		    u.PLZ,
		    u.ORT,
		    b.wannAngelegt as bestellDatum,
		    b.partyId,
		    b.mwstSatz
		  from 
		    acc_bestellung b,
		    acc_ticket_typ t,
		    party p,
		    USER u
		  where
		    u.USERID      = b.bestellerUserId and
		    b.ticketTypId = t.typId and
		    p.partyId     = t.partyId and
		    b.partyId     = p.partyId and
		    ".$sWhere;
		$result_benutzer = DB::query($sql);
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$rowBenutzer = $result_benutzer->fetch_array();

	  $q = "select
		  b.*,
			b.preis as derPreis,
		  t.*,
		  m.BESCHREIBUNG
		from 
		  acc_bestellung b,
		  acc_ticket_typ t,
		  MANDANT m,
		  party p
		where
		  b.ticketTypId = t.typId and
		  m.MANDANTID   = t.mandantId and
		  p.partyId     = t.partyId and
		  b.partyId     = p.partyId and
		  ".$sWhere;
	  if ($res = DB::query($q)) {
	    
	    if ($res->num_rows > 0) {
	      echo "<p><table cellspacing=\"1\" cellpadding=\"2\">";
 	      echo "<tr><td class=\"dblau\">$str[acc_bestno]&nbsp; &nbsp; </td><td class=\"hblau\">".PELAS::formatBestellNr($rowBenutzer['partyId'], $rowBenutzer['bestellId'])."</td></tr>\n";
	      echo "<tr><td class=\"dblau\">$str[acc_besteller]</td><td class=\"hblau\">".db2display($rowBenutzer['NAME'])." ".db2display($rowBenutzer['NACHNAME'])."</td></tr>\n";
	      echo "<tr><td class=\"dblau\">$str[strasse]</td><td class=\"hblau\">".db2display($rowBenutzer['STRASSE'])."</td></tr>\n";
	      echo "<tr><td class=\"dblau\">$str[plz] $str[ort]</td><td class=\"hblau\">".db2display($rowBenutzer['PLZ'])." ".db2display($rowBenutzer['ORT'])."</td></tr>\n";
	      echo "<tr><td class=\"dblau\">$str[acc_bestdatum]</td><td class=\"hblau\">".dateDisplay2Short($rowBenutzer['bestellDatum'])."</td></tr>\n";
	      echo "<tr><td class=\"dblau\">$str[acc_zahlbarbis]</td><td class=\"hblau\">".date('d.m.Y' ,(strtotime($rowBenutzer['bestellDatum']) + CFG::getMandantConfig("TICKETVERKAUF_AUTOSTORNO", PELAS::AktuellePartyMandant($rowBenutzer['partyId'])) * 86400))." *</td></tr>\n";
	      echo "</table></p>\n";
	    }
	    
	    echo "<p><table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width=\"100%\">\n
		    <tr><td class=\"header\">$str[anzahl]</td><td class=\"header\">$str[ticketart]</td><td class=\"header\">$str[preis]</td><td class=\"header\">$str[summe]</td></tr>\n";
	    $color = "hblau";
	    $summe = 0;
	    while ($row = $res->fetch_assoc()) {
	      echo "<tr><td class=\"$color\">".$row['anzahl']."</td>";
	      echo "<td class=\"$color\">".db2display($row['kurzbeschreibung'])."<br><small>".db2display($row['beschreibung'])."</small></td>";
	      echo "<td class=\"$color\">".sprintf("%01.2f", $row['derPreis'])." EUR</td>";
	      echo "<td class=\"$color\">".sprintf("%01.2f",($row['anzahl']*$row['derPreis']))." EUR</td></tr>";
	      $summe = $summe + ($row['anzahl']*$row['derPreis']);
	      if ($color == "dblau") {
		$color="hblau";
	      } else {
		$color="dblau";
	      }
	      $retBestellId = $row['bestellId'];
	      $retMandant   = $row['BESCHREIBUNG'];
	    }
	    if ($summe <= 0) {
	      // Keine Bestellungen vorhanden
	      echo "<tr height=\"27\"><td class=\"$color\" colspan=\"4\">Keine offenen Bestellungen vorhanden.</b></td>";
	    } else {
	      echo "<tr height=\"27\"><td class=\"$color\" colspan=\"3\"><b>$str[gsummeinkl] ".$rowBenutzer['mwstSatz']."% $str[mwst]</b></td>";
	      echo "<td class=\"$color\"><b>".sprintf("%01.2f", $summe)." EUR</b></td></tr>";
	    }
	    echo "</table></p>";
	    
	    if ($summe > 0) {
	      echo "<p align=\"justify\">* ".$str['acc_mail_fristinfo']."</p>";
	    }
	  }
	  return array ($summe, $retBestellId, $retMandant);
	}
}


// Funktion versendet Anmeldebestätigung
if (!function_exists('SendeAnmeldeMail'))
{
	function SendeAnmeldeMail($nPartyID, $nLoginID)
	{
		global $str, $dbname, $STATUS_ANGEMELDET, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE;
	
		DB::connect();

	
		$row = DB::query("select * from MANDANT where MANDANTID = $nPartyID")->fetch_assoc(); 
			$config[BESCHREIBUNG] = $row[BESCHREIBUNG]; 
			$config[IRC] = $row[IRC];
			
			$Mandant[FIRMA] = $row[FIRMA];
			$Mandant[KONTAKTPERSON] = $row[KONTAKTPERSON];
			$Mandant[STRASSE] = $row[STRASSE];
			$Mandant[PLZ] = $row[PLZ];
			$Mandant[ORT] = $row[ORT];
			$Mandant[TELEFON] = $row[TELEFON];
			$Mandant[TELEFON2] = $row[TELEFON2];
			$Mandant[EMAIL] = $row[EMAIL];
			$Mandant[REFERER] = $row[REFERER];
			
		$row = DB::query("select USERID, LOGIN, EMAIL from USER where USERID = $nLoginID")->fetch_assoc(); 
			$config[USERID] = $row[USERID];
			$config[LOGIN] = $row[LOGIN];
			$config[EMAIL] = $row[EMAIL];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NAME' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[KONTO_NAME] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NUMMER' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[KONTO_NUMMER] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BLZ' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[KONTO_BLZ] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BANK' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[KONTO_BANK] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_NORMAL' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[EINTRITT_NORMAL] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_LOGE' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[EINTRITT_LOGE] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_XTRA' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[EINTRITT_XTRA] = " ".$row[STRINGWERT];
		// Added IBAN and BIC to config Array
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_IBAN' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[KONTO_IBAN] = $row[STRINGWERT];
		$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BIC' and MANDANTID = $nPartyID")->fetch_assoc();
			$config[KONTO_BIC] = $row[STRINGWERT];
		
		
		$row = DB::query("select BESCHREIBUNG, EMAIL from MANDANT where MANDANTID=$nPartyID")->fetch_array();
		$sMandant = $row[BESCHREIBUNG];
		$sMandantEmail = $row[EMAIL];
	
	
		//zu lange Partynamen kuerzen
		if (strlen($config[BESCHREIBUNG]) > 12 ) {
			$UBmandant = db2display(substr( $config[BESCHREIBUNG], 0, 12));
		} else {
			$UBmandant = db2display($config[BESCHREIBUNG]);
		}
	
		$absender = $config[EMAIL];
		$betreff = "$str[anmeldungzur] ". $config[BESCHREIBUNG];
		$email  = "Hallo ".$config[LOGIN].",\n\n";
		$email .= "$str[anmeldungzur1] ".$config[BESCHREIBUNG]." $str[anmeldungzur2] (".$config[EINTRITT_NORMAL]." EUR";

		if ($config[EINTRITT_LOGE] > 0) {
			$email .= " (Loge ".$config[EINTRITT_LOGE]." EUR)";
		}

		$email .= $config[EINTRITT_XTRA].") $str[anmeldungzur3]:\n\n";

		$email .= $config[KONTO_NAME]."\n";
		$email .= "$str[kontonummer] : ".$config[KONTO_NUMMER]."\n";
		$email .= "$str[blz] : ".$config[KONTO_BLZ]."\n";
		$email .= "Bank: ".$config[KONTO_BANK]."\n";
		$email .= "\n$str[internationaleKontodaten]\n";
		$email .= "$str[IBAN]: ".$config[KONTO_IBAN]."\n";
		$email .= "$str[BIC]: ".$config[KONTO_BIC]."\n\n";
		$email .= "$str[anmeldungzur5] \"".$UBmandant.", ID ".$config[USERID]."\" $str[anmeldungzur6].\n\n\n";
		$email .= "$str[anmeldungzur7] ".$config[BESCHREIBUNG]." Team";
		$email .= "\n".$config[REFERER];
		
		
		$email .= "\n\n-----------------------------------\n";
		$email .= $str[wendedich].":\n";
		$email .= $Mandant[FIRMA]."\n";
		$email .= $Mandant[KONTAKTPERSON]."\n";
		$email .= $Mandant[STRASSE]."\n";
		$email .= $Mandant[PLZ]." ".$Mandant[ORT]."\n";
		$email .= "Email: mailto:".$Mandant[EMAIL]."\n";
		$email .= "Telefon: ".$Mandant[TELEFON]."\n";
		$email .= "Mobil: ".$Mandant[TELEFON2]."\n";
		
		
		/* $header = "From: $sMandant <$sMandantEmail>\nError-To: $sMandantEmail\nReply-To: $sMandantEmail\nX-Priority: 3\nX-Mailer: PHP/". phpversion();
		@mail($absender, $betreff, $email, $header); 
		$erfolg = sende_mail_text("TODO", $betreff, $email);*/
		
		
		// angemeldet?
		$q = "select count(*) from ASTATUS a, USER u where (a.STATUS=$STATUS_ANGEMELDET or a.STATUS=$STATUS_BEZAHLT or a.STATUS=$STATUS_BEZAHLT_LOGE) and a.MANDANTID = $nPartyID and a.USERID=u.USERID";
		$res = DB::query($q);
		$row = $res->fetch_array();
		$AnzahlDS = $row[0];
		if ($AnzahlDS == 0) {
			$AnzahlDS = 1;
		}
		
		//Wie viele bezahlt?
		$q = "select count(*) from ASTATUS where (STATUS=$STATUS_BEZAHLT or STATUS=$STATUS_BEZAHLT_LOGE) and MANDANTID = $nPartyID";
		$res = DB::query($q);
		$row = $res->fetch_array();
		$AnzahlDSbezahlt = $row[0];
		
		//Wie viele Plaetze insgesamt?
		$q = "select STRINGWERT from CONFIG where MANDANTID=$nPartyID and PARAMETER='TEILNEHMER'";
		$res = DB::query($q);
		$row = $res->fetch_array();
		$partyPlaetze = $row[STRINGWERT];
		
		//Und an den Bot senden
		//escapen
		$sIRC_Channel = $config[IRC];
		$sLoginStr    = $config[LOGIN];
		
		//Send2Bot($sIRC_Channel, "$sLoginStr hat sich angemeldet. ($AnzahlDS Angemeldet / $AnzahlDSbezahlt Bezahlt / $partyPlaetze Plätze)");
	}
}


// Funktion versendet interne PELAS-Mail (bisher nur im Intranet funktionierend)
if (!function_exists('SendePelasMail'))
{
	function SendePelasMail($toUser, $fromUser, $betreff, $bodyInhalt)
	{
		global $dbname;
	
		// Nickname raussuchen zuvor
		$q = "select LOGIN from USER where USERID = '$fromUser'";
		$res = DB::query($q);
		$row = $res->fetch_array();
		$fromUserNick = $row['LOGIN'];
		
		// Einfügen in die Mailtabelle
		$q = "INSERT INTO PELASMAIL (
				TOUSER,
				FROMUSER,
				FROMUSER_STRING,
				HEADLINE,
				BODY
			) values (
				'$toUser',
				'$fromUser',
				'$fromUserNick',
				'$betreff',
				'$bodyInhalt'
			) ";
		$res = DB::query($q);
		return DB::$link->errno;
	}
}

/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
if (!function_exists('validEmail'))
{
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }	
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
}
?>
