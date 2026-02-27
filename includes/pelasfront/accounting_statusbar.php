<?php

// $sStyle regelt die Ausgabeform:
// picture: Bild-Datenstrom, WICHTIG: Hintergrundbild mit $sBildPfad übergeben!

include_once "dblib.php";
include_once "pelasfunctions.php";

$nHoehe     = 6;
$nTblHeight = 10;


$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

// Anzahl der Plätze
$sql = "select
          y.anzahlVorhanden,
          y.typId
        from 
          acc_ticket_typ y
        where 
          y.partyId = '$aktuellePartyID' and
	  (y.translation > 1 and y.translation != '$STATUS_BEZAHLT_SUPPORTERPASS')
          ";
$res = DB::query($sql);         
$partyPlaetze     = 0;
$partyRestplaetze = 0;
while ($row = $res->fetch_array()) {
	$partyPlaetze = $partyPlaetze + $row['anzahlVorhanden'];
	$partyRestplaetze = $partyRestplaetze+ verfuegbareTickets($row['typId'], $aktuellePartyID);
}

// Anzahl fest bezahlt
$sql = "select
          count(t.ticketId) as tickets
        from 
          acc_ticket_typ y,
          acc_tickets t
        where 
          y.partyId  = '$aktuellePartyID' and
	  y.typId    = t.typId and
	  t.statusId = ".ACC_STATUS_BEZAHLT."
";
$res = DB::query($sql);         
$row = $res->fetch_row();
$partyBezahlt = $row[0];

// Anzahl Session registrierte Benutzer
  $sql = "select
            count(*)
          from
						php_session s
          where
            UNIX_TIMESTAMP(s.session_time) >= UNIX_TIMESTAMP()-".USER_ONLINE_TIMEOUT."
					and 
						s.mandantId = '$nPartyID'
";
$anzahlSessions  = DB::getOne($sql);


// Anzahl Benutzer für Mandant
$sql = "select 
          count(*) as cnt 
        from 
          ASTATUS 
        where 
          MANDANTID='$nPartyID'";
$res = DB::query($sql);         
$row = $res->fetch_row();
$anzahlAccounts = $row[0];


// Anzahl Forenpostings
$sql = "select 
          count(*) as cnt 
        from 
          forum_boards b, forum_content c 
        where 
          b.mandantID='$nPartyID' and 
          b.boardID = c.boardID and 
          b.type IN (1, 2)";
$res = DB::query($sql);         
$row = $res->fetch_row();
$anzahlForenpostings = $row[0];


if (!isset($theWidth) || $theWidth < 1) { $theWidth = 100; }

$freiePlaetze = $partyPlaetze-$partyAngemeldet-$partyBezahlt;

if ($sStyle == "picture") {
	// Das Script gibt einen Bild-Datenstrom aus, der mit einem Frontend-Script entsprechend an den
	// Browser weitergeleitet werden kann
	// WICHTIG: Hintergrundbild (PNG) mit $sBildPfad übergeben!
	
	
	// Countdown-Wert: Restliche Tage bis zur Party auslesen
	$sql = "select 
	          TO_DAYS(p.terminVon)-TO_DAYS(now()) as RestTage
	        from 
	          party p
	        where 
	          p.partyId='$aktuellePartyID'";
	$res = DB::query($sql);
	$rowTemp = $res->fetch_array();
	$RestTage = $rowTemp['RestTage'];
	
	// Bild erstellen aus angegebener Datei
	$im = imageCreateFromPNG($sBildPfad);
	//$im = imageCreateFromJpeg(sBildPfad);

	if ($rgb1 < 1) {
		$rgb1 = 255;
	}
	if ($rgb2 < 1) {
		$rgb2 = 255;
	}
	if ($rgb3 < 1) {
		$rgb3 = 255;
	}
	$farbe = ImageColorAllocate($im,$rgb1,$rgb2,$rgb3);
		
	// Bildstream ausgeben
	Header("Content-type: image/png");
	Header ("Pragma: no-cache");
	header ("Cache-Control: no-cache, must-revalidate");
	
	$x1 = 12;
	$x2 = 100;
	$yOffset = 71;
	
	ImageString ($im, 2, $x1, $yOffset, "Tage bis", $farbe);
	ImageString ($im, 2, $x2, $yOffset, $RestTage, $farbe);

	ImageString ($im, 2, $x1, $yOffset+12, "Tickets gesamt", $farbe);
	ImageString ($im, 2, $x2, $yOffset+12, $partyPlaetze, $farbe);
	
	$sOutput = $partyPlaetze-$partyRestplaetze-$partyBezahlt;
	ImageString ($im, 2, $x1, $yOffset+24, "Bestellt", $farbe);
	ImageString ($im, 2, $x2, $yOffset+24, $sOutput, $farbe);
	
	ImageString ($im, 2, $x1, $yOffset+36, "Bezahlt", $farbe);
	ImageString ($im, 2, $x2, $yOffset+36, $partyBezahlt, $farbe);
	
	ImageString ($im, 2, $x1, $yOffset+48, "Verfügbar", $farbe);
	ImageString ($im, 2, $x2, $yOffset+48, $partyRestplaetze, $farbe);
	
	imagepng($im) or die ("Kann keinen neues GD-Bild ausgeben");
	ImageDestroy($im);
	
	// Ende
	
} elseif ($sStyle == "northcon") {
	// NorthCon-Style mit coolem Aussehen
   
        echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">";
		echo "<b>";
		echo "<font style=\"font-size:18px; color:#FFFFFF;\">$partyBezahlt Tickets</font>";
		echo "<font style=\"font-size:15px; color:#FFFFFF;\"> sind bezahlt</font><br>";
		echo "<font style=\"font-size:10px; color:#FFFFFF;\">".($partyPlaetze-$partyRestplaetze-$partyBezahlt)." Tickets sind unverbindlich reserviert</font><br>";
		echo "<font style=\"font-size:12px; color:#FFFFFF;\">$partyRestplaetze Tickets sind noch verf&uuml;gbar</font><br>";
		echo "<font style=\"font-size:18px; color:#FFFFFF;\">$anzahlAccounts aktive Accounts<br></font>";
		echo "<font style=\"font-size:10px; color:#FFFFFF;\">haben $anzahlForenpostings Postings im Forum erstellt<br></font>";
		echo "<a style=\"text-decoration: none;\" href=\"/online.php\"><font style=\"font-size:12px; color:#FFFFFF;\">$anzahlSessions registrierte Benutzer sind online</font></a>";
		echo "</b>";
        echo "</table>";

} elseif ($sStyle == "text") {
	// Nur Text Version
	echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
	echo "<tr><td>$str[acc_tickets_gesamt]</td><td><b>$partyPlaetze</b></td></tr>";
	echo "<tr><td>$str[acc_bestellt]</td><td><b>".($partyPlaetze-$partyRestplaetze-$partyBezahlt)."</b></td></tr>";
	echo "<tr><td>$str[acc_bezahlt]</td><td><b>".$partyBezahlt."</b></td></tr>";
	
	// Geeignete Hervorhebung ermitteln.
	if ($partyPlaetze <= 0) {  // Verhindert von Division durch 0
		$color = "#AA0000";
	} else {
		$Anteil = $partyRestplaetze / $partyPlaetze * 100;
		switch ($Anteil) {
			case ($Anteil > 50):
				$color = "#00AA00";
				break;
			case ($Anteil <=50 && $Anteil >= 10): 
				$color = "#00AAAA";
				break;
			case ($Anteil < 10):
				$color = "#AA0000";
				break;
		}
	}

	echo "<tr><td>$str[acc_verfuegbar]</td><td><b><font color=\"#00AA00\">$partyRestplaetze</font></b></td></tr>";
	echo "<tr><td colspan=\"2\"><img src=\"/gfx/lgif.gif\" height=\"1\" width=\"0\"></td></tr>";
	echo "<tr><td colspan=\"2\" align=\"center\"><small>$anzahlSessions <a class=\"navlink\" href=\"/online.php\">users online</a></small></td></tr>";
	
	echo "</table>\n";

} elseif ($sStyle == "semanticHtml") {
	// Semantisches HTML, auf das gezielt mittels CSS formatiert werden kann.
	// Mit der HTML5-Spezifikation ist die Verwendung von Definitionslisten fÃr Key-/Value-Paare erlaubt.

	function echoDlRow($term, $description) {
		echo '<dt>' . $term . '</dt><dd>' . $description . "</dd>\n";
	}

	echo '<dl class="ticket-stats">' . "\n";
	echoDlRow($str['acc_tickets_gesamt'], $partyPlaetze);
	echoDlRow($str['acc_bestellt'], $partyPlaetze - $partyRestplaetze - $partyBezahlt);
	echoDlRow($str['acc_bezahlt'], $partyBezahlt);
	echoDlRow($str[acc_verfuegbar], $partyRestplaetze);
	echoDlRow('User online', '<a href="/online.php">' . $anzahlSessions . '</a>');
	echo "</dl>\n";
}
?>
