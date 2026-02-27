<?php
include_once 'dblib.php';
include_once 'format.php';
include_once 'session.php';
include_once 'pelasfunctions.php';
include_once "language.inc.php";

// Sicherheitsabfrage der Variablen, die übergeben und in SQLs verarbeitet werden
if (isset($_GET['iBestellId']) && !is_numeric($_GET['iBestellId'])) {
	echo "<p class='fehler'>Es wurden falsche Daten geliefert. Weitere Verarbeitung gestoppt.</p>";
	exit;
}

$dbh = DB::connect();

# Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);


if (isset($_GET['action']) && $_GET['action'] == 'printTickets2')
{
	// Ticketdruck ab 2023/24
	
    if (! $nLoginID) {
        die('Bitte einloggen.');
    }

    $sql_where = '';
    $printedTickets = 0;
    
    # Einzeldruck angefordert?
    if ($_GET['ticketId'] > 0) {
      $singleTicket = True;
      $sql_where = "and ticketId = '".intval($_GET['ticketId'])."'";
    }

    # Tickets als PDF für den Einlass ausdrucken

		# Tickets
		$sql = "select
			  m.REFERER,
			  t.ticketId,
			  t.sitzReihe,
			  t.sitzPlatz,
			  u.LOGIN,
			  u.USERID,
			  y.kurzbeschreibung,
			  y.beschreibung,
			  s.beschreibung as StatusText,
			  p.beschreibung AS Partyname,
			  p.partyId,
        u2.name AS owner_name, 
        u2.nachname AS owner_nachname,
			  u2.LOGIN AS owner_login,
        u.name, 
        u.nachname,
        p.terminVon,
        p.terminBis
			from
			  acc_tickets t,
			  acc_ticket_typ y,
			  acc_ticket_bestellung_status s,
			  party p,
			  USER u,
			  USER u2,
			  MANDANT m
			where
			  m.MANDANTID = p.mandantId
			  and t.partyId   = p.partyId
        AND p.partyId = '".intval($_GET['iPartyId'])."'
        AND t.statusId = '".ACC_STATUS_BEZAHLT."'
        AND p.mandantId = '".intval($nPartyID)."'
        AND u.USERID = t.userId
        AND u2.USERID = t.ownerId
        AND y.typId = t.typId
        AND s.statusId = t.statusId
        AND (
        t.userId = '$nLoginID'
        OR t.ownerId = '$nLoginID'
        )
        $sql_where
		"; # nur bezahlte Tickets anzeigen, denen auch schon ein sitzplatz zugewiesen ist.
	$res = DB::query($sql);
	
	
	// Aktuell nur 1 Ticket zur Zeit
	$row = mysql_fetch_array($res);  
	
	
	// Und nun den HTML-Code erzeugen, der später in ein PDF umgewandelt wird.
	ob_start();
	
	$num = 'CMD01-'.date('ymd');
	$nom = 'DUPONT Alphonse';
	$date = '01/01/2012';
	
	// Vorlage vorhanden?
	$vorlage_party = "pelas/ticketvorlagen/".intval($row['partyId']).".png";
	
	if (is_file($vorlage_party))
		$vorlage = $vorlage_party;
	else
		$vorlage = "pelas/ticketvorlagen/default.png";
	
	?>
	<style type="text/css">
	<!--
		div.zone { border: none; border-radius: 6mm; background: #FFFFFF; border-collapse: collapse; padding:3mm; font-size: 2.7mm;}
		h1 { padding: 0; margin: 0; color: #DD0000; font-size: 7mm; }
		h2 { padding: 0; margin: 0; color: #222222; font-size: 5mm; position: relative; }
	-->
	</style>
	<page format="192x69" orientation="L" style="font: arial;" backimg="<?= $vorlage; ?>" backimgw="192mm" backimgx="left" backimgy="top">
		<div style="rotate: 90; position: absolute; width: 69mm; height: 5mm; left: 157mm; top: 0; font-weight: bold; text-align: center; font-size: 4mm;">
			<?= db2display($row['name'])." '".db2display($row['LOGIN'])."' ".db2display($row['nachname']); ?>
		</div>
		<div style="rotate: 90; position: absolute; width: 69mm; height: 3mm; left: 163mm; top: 0; font-weight: bold; text-align: center; font-size: 2.5mm;">
			Platz/Seat: <?= $row['sitzReihe']; ?> - <?= $row['sitzPlatz']; ?>
		</div>
		
		<div style="rotate: 90; position: absolute; width: 69mm; height: 3mm; left: 169mm; top: 0; text-align: center; font-size: 2.5mm;">
		   Gültig von <?= datedisplay2short($row['terminVon']); ?> bis <?= datedisplay2short($row['terminBis']); ?>
        </div>
		
		<div style="rotate: 90; position: absolute; width: 59mm; height: 3mm; left: 176mm; top: 5mm;">
			<barcode dimension="1D" type="C128" value="<?= sprintf('%07d%07d', $row['USERID'], $row['ticketId']); ?>" label="label" style="width:59mm; height:8mm; font-size: 3mm"></barcode>
		</div>
	
		
	</page>


	<?php
		 $content = ob_get_clean();

		// convert to PDF
		require_once("html2pdf/html2pdf.class.php");
		try
		{
			$html2pdf = new HTML2PDF('P', 'A4', 'de', true, 'UTF-8', 0);
			$html2pdf->pdf->SetDisplayMode('fullpage');
			$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
			$html2pdf->Output('ticket.pdf');
		}
		catch(HTML2PDF_exception $e) {
			echo $e;
			exit;
		}

	die();

}
else if (isset($_GET['action']) && $_GET['action'] == 'printTickets')
{
    if (! $nLoginID) {
        die('Bitte einloggen.');
    }

    $sql_where = '';
    $printedTickets = 0;
    
    # Einzeldruck angefordert?
    if ($_GET['ticketId'] > 0) {
      $singleTicket = True;
      $sql_where = "and ticketId = '".intval($_GET['ticketId'])."'";
    }

    # Tickets als PDF für den Einlass ausdrucken

		# Tickets
		$sql = "select
			  m.REFERER,
			  t.ticketId,
			  t.sitzReihe,
			  t.sitzPlatz,
			  u.LOGIN,
			  u.USERID,
			  y.kurzbeschreibung,
			  y.beschreibung,
			  s.beschreibung as StatusText,
			  p.beschreibung AS Partyname,
        u2.name AS owner_name, 
        u2.nachname AS owner_nachname,
			  u2.LOGIN AS owner_login,
        u.name, 
        u.nachname,
        p.terminVon,
        p.terminBis
			from
			  acc_tickets t,
			  acc_ticket_typ y,
			  acc_ticket_bestellung_status s,
			  party p,
			  USER u,
			  USER u2,
			  MANDANT m
			where
			  m.MANDANTID = p.mandantId
			  and t.partyId   = p.partyId
        AND p.partyId = '".intval($_GET['iPartyId'])."'
        AND t.statusId = '".ACC_STATUS_BEZAHLT."'
        AND p.mandantId = '".intval($nPartyID)."'
        AND u.USERID = t.userId
        AND u2.USERID = t.ownerId
        AND y.typId = t.typId
        AND s.statusId = t.statusId
        AND (
        t.userId = '$nLoginID'
        OR t.ownerId = '$nLoginID'
        )
        $sql_where
		"; # nur bezahlte Tickets anzeigen, denen auch schon ein sitzplatz zugewiesen ist.
	$res = DB::query($sql);
    
    #die($sql);
    require_once('class.ezpdf.php');
    require_once('barcode.php');
    require_once('barcode_i25object.php');
	
    # Y-Achse bei mehreren Tickets verschieben
    $displacement = 0;
    
    # Kopf ausgeben und PDF-Setup
	 $options[0] = 122;
     $options[1] = 122;
     $options[2] = 122;
    $pdf =& new Cezpdf('a4', 'portrait','color',$options);
    //$pdf =& new Cezpdf('a4', 'portrait');
    //$pdf->selectFont('fonts/Helvetica.afm');
    $pdf->selectFont('../includes/fonts/Helvetica.afm');
    # Seitenränder einstellen
    $pdf->ezSetCmMargins(2, 2.5, 2, 2);
    
    if (! $singleTicket) {
      # Titeltext drucken
      $pdf->ezText('Alle Deine Tickets / All your tickets:', 11);
    }

    while ($row = mysql_fetch_array($res)) {   
        # Anzahl Tickets zählen, nach 3 Tickets neue Seite
        if ($printedTickets == 3) {
          $printedTickets = 0;
          $displacement = 0;
          $pdf->ezNewPage();
        }
        $printedTickets++;
                
        # Linienstärke
        $pdf->setLineStyle(0.5);
        # Rahmen
        $pdf->rectangle(50, 530 + $displacement, 470, 190);
		#$pdf->ezSetDy(-50);
		#$pdf->ezImage('Ticket.jpg', 0, 400, 'full');
		#$pdf->ezImage('https://multimadness.de/img/MMMBanner37.png', 0, 400, 'full');
		#$pdf->ezSetDy(0);	
        # Partyname
        $pdf->addText(55, 690 + $displacement, 23, db2display($row['Partyname']));
         
        
        # linker Ticketbereich oben
        $pdf->addText(55, 650 + $displacement, 14, utf8_decode(db2display($row['kurzbeschreibung'])));
        $pdf->addText(55, 638 + $displacement, 10, utf8_decode(db2display($row['beschreibung'])));
        $pdf->addText(55, 620 + $displacement, 8, utf8_decode("Gültig von/Valid from ".datedisplay2short($row['terminVon'])." bis/to ".datedisplay2short($row['terminBis']).""));
        $pdf->addText(55, 605 + $displacement, 8, utf8_decode("Bezahlt von/Bought by ".db2display($row['owner_name'])." '".$row['owner_login']."' ".db2display($row['owner_nachname'])));
        
        # linker Ticketbereich unten
        $pdf->addText(55, 580 + $displacement, 14, utf8_decode(db2display($row['name'])." '".$row['LOGIN']."' ".db2display($row['nachname'])));
        $pdf->addText(55, 565 + $displacement, 10, utf8_decode("Platz/Seat: ".$row['sitzReihe']."-".$row['sitzPlatz']));
        # Fuänoten
        $pdf->addText(55, 548 + $displacement, 5, utf8_decode("Rechnung/Invoice: ".$row['REFERER']."/accounting.php?action=bill"));
        $pdf->addText(55, 540 + $displacement, 5, utf8_decode("Ticket nur mit Personalausweis und Zuordnung im Ticketsystem gültig. Ticket gilt nicht als Zahlungsnachweis."));			
        $pdf->addText(55, 535 + $displacement, 5, utf8_decode("Ticket only valid with identity card and corresponding allocation within the ticketsystem. Ticket does not apply as payment verification."));
        $pdf->addText(455, 535 + $displacement, 5, utf8_decode("print date: ".date('d.m.Y')));

        # Barcodenummer generieren
		$code = sprintf('%07d%07d', $row['USERID'], $row['ticketId']);

        # Barcode erzeugen
        define(__TRACE_ENABLED__, false);
        define(__DEBUG_ENABLED__, false);
          
        $style = BCS_IMAGE_JPEG;
        $width = 220;
        $height = 120;
        $xres = BCD_DEFAULT_XRES;
        $font = BCD_DEFAULT_FONT;
        
        $obj = new I25Object($width, $height, $style, $code);
        $obj->SetFont($font);   
        $obj->DrawObject($xres);
        $barcode_image = $obj->mImg;
		$rotate = imagerotate($barcode_image, 90, 0);
        unset($obj);  # clean
        
        # Barcode ins PDF einfügen
        $pdf->addImage($rotate, 390, 560 + $displacement, 60, 150, 96);
        #$pdf->addImage($rotate, 390, 560 + $displacement, 110, 60, 96);
		
        # User-ID und Ticket-ID ausgeben.
        #$pdf->addText(390, 555 + $displacement, 10, sprintf('%07d-%07d', $row['USERID'], $row['ticketId']));
        
	$displacement -= 200;
    }

    # PDF ausgeben
    $pdf->ezStream();
	

} else {
    # prüfen, ob der angemeldete User Rechte auf die Rechnungsdaten hat
    $sql = "select 
    	  bestellerUserId
    	from
    	  acc_bestellung
    	where
    	  partyId   = '".intval($_GET['iPartyId'])."' and
    	  bestellId = '".intval($_GET['iBestellId'])."'
    ";
    $res = DB::query($sql);
    $rowTemp = mysql_fetch_array($res);
    
    if ($rowTemp['bestellerUserId'] != $nLoginID) {
    	echo "<p class=\"fehler\">Keine Berechtigung - no access!</p>";
    } else {
    	zeigeRechnung($_GET['iPartyId'], $_GET['iBestellId']);
    }
}

exit;
?>