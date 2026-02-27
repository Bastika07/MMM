<?php
require_once('dblib.php');
require_once('format.php');
##include_once('sitzlib.php');


# TODO!!!
# Beim Hinzufügen oder Freigeben von Plätzen wird
# die Grafik zwar korrekt neu erzeugt, aber nicht
# das zugehörige HTML!
#
# Beim kompletten neuen Erzeugen des Sitzplans
# funktioniert das aber scheinbar.

function GeneriereSitzplanSelektiv($dieReihe, $nPartyID, $locateUser=0, $aktuellePartyID) {
    # Zeichnet nur die geänderte und benannte Reihe neu.
    global $SITZ_VORGEMERKT, $SITZ_RESERVIERT, $AUFNAHMESTATUS_OK;

    # Ebene und Daten zur Reihe herausfinden.
    $row = DB::getRow('
        SELECT MANDANTID, EBENE, XCORD, YCORD, REIHE, LAENGE, AUSRICHTUNG, ISTLOGE, ISTDEMO, ISTGESPERRT
        FROM SITZDEF 
        WHERE MANDANTID = ?
          AND REIHE = ?
        ', $nPartyID, $dieReihe);

    # Platzarray mit den Daten der Reihe füllen.
    $reihe = $row['REIHE'];
    $ebene = $row['EBENE'];
    $PlatzArray = array();
    $PlatzArray[$reihe] = array($row['LAENGE'], $row['XCORD'], $row['YCORD'], $row['AUSRICHTUNG'], $row['ISTLOGE'], $row['ISTDEMO'], $row['ISTDEMO']);

    $imagefile_png = PELASDIR . 'sitzbild/sitzplan_bild_' . $nPartyID . '_' . $ebene . '.png';
    $htmlfile_prefix = PELASDIR . 'sitzbild/sitzplan_html_' . $nPartyID . '_' . $ebene;
    $htmlfile_lock = $htmlfile_prefix . '.lock';
    $htmlfile_txt = $htmlfile_prefix . '.txt';

    # Fertiges Bild laden.
    $im = imageCreateFromPNG($imagefile_png);

    # Generierung aufrufen.
    $html = trim(GeneriereReihe($im, $nPartyID, $dieReihe, $aktuellePartyID, 1, $PlatzArray));
	
    # Ausgabe
    if ($locateUser == 0) {
        # Keinen Platz hervorheben, normalen Sitzplan schreiben.
        imagePNG($im, $imagefile_png);
        imageDestroy($im);

        # Warten bis Lockdatei gelöscht ist; maximal 2 Sekunden.
        $sCount = 0;
        while (file_exists($htmlfile_lock) and ($sCount++ < 1000)) {
            usleep(2000);
        }
	touch($htmlfile_lock);

        ##exec ("sed -i -e 's|<!--$dieReihe-->.*$|" . str_replace("'", "'\''", str_replace('|', '\|', str_replace('&', '\&', $html))) . "|' " . $htmlfile_txt);

        # Neue Ausführung mit PHPs escapeshell-Command.
        $html = escapeshellcmd($html);
        exec("sed -i -e 's|<!--$dieReihe-->.*$|" . str_replace("'", "'\''", $html) . "|' " . $htmlfile_txt);

        unlink($htmlfile_lock);
    } else {
        # User highlighten, Sitzplan direkt ausgeben.
        imagePNG($im);
        imageDestroy($im);
    }
}


# Funktion gibt die Gesamt-Platzanzahl zurück
function GeneriereSitzplan($nPartyID, $ebene, $locateUser=0, $aktuellePartyID) {  
    global $tbreite, $tlaenge, $tLogelaenge, $tDemolaenge, $maxreihen, $startreihen,
        $PlaetzeReihe, $SITZ_VORGEMERKT, $SITZ_RESERVIERT, $AUFNAHMESTATUS_OK;

    $PlaetzeGesamt = 0;

    # Grundlegende Daten aus DB holen
    $sql = "
        SELECT * 
	FROM SITZDEF 
	WHERE MANDANTID = '$nPartyID'
	  AND EBENE = '$ebene'";
    $rows = DB::getRows($sql);

    ##$PlatzArray = array($row['REIHE'] => array($row['LAENGE'], $row['XCORD'], $row['YCORD'], $row['AUSRICHTUNG'], $row['ISTLOGE']));
    $PlatzArray = array();
    foreach ($rows as $row) {
	$PlatzArray[$row['REIHE']][0] = $row['LAENGE'];
	$PlatzArray[$row['REIHE']][1] = $row['XCORD'];
	$PlatzArray[$row['REIHE']][2] = $row['YCORD'];
	$PlatzArray[$row['REIHE']][3] = $row['AUSRICHTUNG'];
	$PlatzArray[$row['REIHE']][4] = $row['ISTLOGE'];
	$PlatzArray[$row['REIHE']][5] = $row['ISTDEMO'];
	$PlatzArray[$row['REIHE']][6] = $row['ISTGESPERRT'];
    }

    # Maximale Reihen?
    $result = DB::query("select MAX(REIHE) as MAXROW from SITZDEF where MANDANTID=$nPartyID and EBENE=$ebene");
    $row = $result->fetch_array();
    $maxreihen = $row['MAXROW'];

    # Reihen beginnen ab?
    $result = DB::query("select MIN(REIHE) as MINROW from SITZDEF where MANDANTID=$nPartyID and EBENE=$ebene");
    $row = $result->fetch_array();
    $startreihen = $row['MINROW'];

    ##$im = @imageCreateFromGIF('sitzplan_halle.gif');
    $png = PELASDIR . 'sitzbild/vorlage_' . $nPartyID . '_' . $ebene . '.png';
    $im = imageCreateFromPNG($png);

    $tempLaenge = $tlaenge;

    $html = '<map name="mmm_map">' . "\n";

    $PlaetzeGesamt = 0;
    $colorAllocate = 1;

    # Alle Reihen durchgehen.
    for ($rc = $startreihen; $rc <= $maxreihen; $rc++) {
        # Diese Reihe generieren.
	$html .= GeneriereReihe($im, $nPartyID, $rc, $aktuellePartyID, $colorAllocate, $PlatzArray);
	$PlaetzeGesamt += $PlaetzeReihe;
	$colorAllocate = 0;
    }
    $html .= "</map>\n";

    if ($locateUser == 0) {
        # Keinen User highlighten, normalen Sitzplan schreiben.
       	imagePNG($im, PELASDIR . 'sitzbild/sitzplan_bild_' . $nPartyID . '_' . $ebene . '.png');
        imageDestroy($im);

        # Write HTML to file.
        $fp = fopen(PELASDIR . 'sitzbild/sitzplan_html_' . $nPartyID . '_' . $ebene . '.txt', 'w');
        fputs($fp, $html);
        fclose($fp);
    } else {
        # User highlighten, Sitzplan direkt ausgeben.
        imagePNG($im);
        imageDestroy($im);
    }

    return $PlaetzeGesamt;
}


function GeneriereReihe($im, $nPartyID, $dieReihe, $aktuellePartyID, $colorAllocate=1, $PlatzArray) {
    global $locateUser, $bg, $tischrand, $tischfrei, $tischbesetzt,
        $tischvorgemerkt, $tischmarkiert, $tischgesperrt, $PlaetzeReihe,
	$tbreite, $tlaenge, $tLogelaenge, $tDemolaenge, $maxreihen, $startreihen,
	$SITZ_VORGEMERKT, $SITZ_RESERVIERT, $AUFNAHMESTATUS_OK;

    $PlaetzeReihe = 0;

    # Tischbreite aus Config holen.
    $tbreite = CFG::getMandantConfig('SITZBREITE', $nPartyID);
    if ($tbreite <= 0) {
        $tbreite = 13;
    }

    # Tischlänge aus Config holen.
    $tlaenge = CFG::getMandantConfig('SITZTIEFE', $nPartyID);
    if ($tlaenge <= 0) {
        $tlaenge = 13;
    }

    # Sitzbreite in der Loge aus Config holen.
    $tLogelaenge = CFG::getMandantConfig('LOGE_SITZBREITE', $nPartyID);
    if ($tLogelaenge <= 0) {
        $tLogelaenge = 18;
    }

    # Sitzbreite Demo aus Config holen.
    $tDemolaenge = CFG::getMandantConfig('SITZBREITE_DEMO', $nPartyID);
    if ($tDemolaenge <= 0) {
        $tDemolaenge = 7;
    }

    # Farben festlegen.
    if ($colorAllocate == 1) {
        $bg = imagecolorresolve($im, 255, 255, 255);
        $tischrand = imagecolorresolve($im, 0, 0, 0);
        $tischfrei = imagecolorresolve($im, 13, 206, 4);
        $tischbesetzt = imagecolorresolve($im, 220, 0, 0);
        $tischvorgemerkt = imagecolorresolve($im, 226, 234, 0);
        $tischmarkiert = imagecolorresolve($im, 0, 0, 255);
        $tischgesperrt = imagecolorresolve($im, 240, 240, 0);
    }

    # kA was dies soll...
    $tempLaenge = $tlaenge;

    $rc = $dieReihe;

    # Logenplätze können breiter sein.
    if ($PlatzArray[$rc][4] == 1) {
      $tlaenge = $tLogelaenge;
    } elseif ($PlatzArray[$rc][5] == 1) {
			$tlaenge = $tDemolaenge; 
		} else {
        $tlaenge = $tempLaenge;
    }

		# Reihennummer als Kommentar ausgeben.
		$html = '<!--' . $rc . '-->';
	
		# Alle Plätze durchgehen.
		for ($Platz = 0; $Platz < $PlatzArray[$rc][0]; $Platz++) {
			$PlaetzeReihe++;
			if (($PlatzArray[$rc][3] == 1) or ($PlatzArray[$rc][3] == 3)) {
				$pltemp = $Platz;
			} else {
				$pltemp = $PlatzArray[$rc][0] - $Platz - 1;
			}
			$dbplatz = $pltemp + 1;

			# Ticket und zugeordneten User ermitteln.
			$sql = "SELECT 
				  u.BILD_VORHANDEN,
				  u.LOGIN,
				  u.USERID,
				  t.ticketId,
				  y.translation,
				  t.ticketId
				FROM 
				  USER u, 
				  acc_tickets t,
				  acc_ticket_typ y
				WHERE 
				  t.typId = y.typId AND
				  t.partyId = '$aktuellePartyID' AND
				  u.USERID = t.userId AND
				  t.sitzPlatz = '$dbplatz' AND 
				  t.sitzReihe = '$rc'
			";
			$result = DB::query($sql); 
			$row = $result->fetch_array();

			if ($PlatzArray[$rc][6] == 1)
			{
				# Gesperrt
				$tempcolor = $tischgesperrt;
			} else if (empty($row['ticketId'])) {
				# Frei
				$tempcolor = $tischfrei;
			} elseif (($row['USERID'] == $locateUser) and ($row['ticketId'] > 0)) {
				# Aktueller User ist der User, nach dem gesucht wird.
				# Tisch markieren.
				$tempcolor = $tischmarkiert;
			} 
			else
			{
				# reserviert
				$tempcolor = $tischbesetzt;
			}

			if ($PlatzArray[$rc][3] == 5) {
				# Speziell für 'The Summit': 45°-Winkel
				$xOffset = $PlatzArray[$rc][1];
				$yOffset = $PlatzArray[$rc][2];

				# Das goldene Dreieck - wie viele Pixel je nach oben/unten
				$MoveIt  = sqrt(pow(($Platz * $tlaenge), 2) / 2);
				# Ein einzelner Platz
				$MoveIt1 = sqrt(pow((1 * $tlaenge), 2) / 2);

				# die beiden oberen Punkte
				$points[0] = $xOffset + $MoveIt;  # x1
				$points[1] = $yOffset + $MoveIt;  # y1
				$points[2] = $xOffset + $MoveIt + $MoveIt1 + 1;  # x2
				$points[3] = $yOffset + $MoveIt + $MoveIt1 - 1;  # y2

				# nun die beiden unteren
				# Linksabweichung
				$abweichung = sqrt(pow($tbreite, 2) / 2);

				$points[4] = $points[2] - $abweichung + 1;  # x3
				$points[5] = $points[3] + $abweichung;      # y3
				$points[6] = $points[0] - $abweichung + 1;  # x4
				$points[7] = $points[1] + $abweichung;      # y4


				##echo "<p>X1: ".$points[0]." / Y1: ".$points[1]." / X2: ".$points[2]." / Y2: ".$points[3]." / X3: ".$points[4]." / Y3: ".$points[5]." / Abweichung: $abweichung / MoveIt1: $MoveIt1</p>";

				imagePolygon($im, $points, 4, $tischrand);

				# Um 1 Pixel verkleinern, damit der Rand durchkommt.
				$points[0] = $points[0] + 1;
				$points[1] = $points[1] + 1;
				$points[2] = $points[2] - 1;
				$points[3] = $points[3] + 1;
				$points[4] = $points[4] - 1;
				$points[5] = $points[5] - 1;
				$points[6] = $points[6] + 1;
				$points[7] = $points[7] - 1;

				imageFilledPolygon($im, $points, 4, $tempcolor);

				if ($PlatzArray[$rc][4] == 1) {
					// Text Loge
					imageString($im, 2, $points[0] + 1, $points[1] + 4, 'L', $tischrand);
				}
				if ($PlatzArray[$rc][5] == 1) {
					// Text Demoparty
					imageString($im, 2, $points[0] + 1, $points[1] + 4, 'C', $tischrand);
				}

			} elseif (($PlatzArray[$rc][3] == 3) or ($PlatzArray[$rc][3] == 4)) {
				$kx1 = $PlatzArray[$rc][1];
				$ky1 = $PlatzArray[$rc][2] + ($tlaenge * $Platz);
				$kx2 = $tbreite+$PlatzArray[$rc][1];
				$ky2 = $PlatzArray[$rc][2] + ($tlaenge * $Platz) + $tlaenge;

				imageRectangle($im, $kx1, $ky1, $kx2, $ky2, $tischrand);
				imageFilledRectangle($im, $kx1 + 1, $ky1 + 1, $kx2 - 1, $ky2 - 1, $tempcolor);
				if ($PlatzArray[$rc][4] == 1) {
					imageString($im, 2, $kx1 + 4, $ky1 + 3, 'L', $tischrand);
				}
				if ($PlatzArray[$rc][5] == 1) {
					// Text Demoparty
					imageString($im, 2, $kx1 + 4, $ky1 + 2, 'C', $tischrand);
				}
			} else {
				$kx1 = ($Platz * $tlaenge) + $PlatzArray[$rc][1];
				$ky1 = $PlatzArray[$rc][2];
				$kx2 = ($Platz * $tlaenge) + $PlatzArray[$rc][1] + $tlaenge;
				$ky2 = $PlatzArray[$rc][2] + $tbreite;

				imageRectangle($im, $kx1, $ky1, $kx2, $ky2, $tischrand);
				imageFilledRectangle($im, $kx1 + 1, $ky1 + 1, $kx2 - 1, $ky2 - 1, $tempcolor);
				if ($PlatzArray[$rc][4] == 1) {
					imageString($im, 2, $kx1 + 4, $ky1 + 3, 'L', $tischrand);
				}
				if ($PlatzArray[$rc][5] == 1) {
					// Text Demoparty
					imageString($im, 2, $kx1 + 4, $ky1 + 3, 'C', $tischrand);
				}
			}

			# Ende der Generierung einer Reihe

			# HTML-Part
			$file = 'userbild/' . $row['USERID'] . '.jpg'; // Check if avail
			if ($row['BILD_VORHANDEN'] == 'J' && file_exists(PELASDIR . $file)) {
			    $iShowBild = $row['USERID'];
			} else {
			    $iShowBild = 0;
			}
			
			if ($row['USERID'] < 1 ) {
			    $Besetzer = '';
			    $nResTyp = 0;
			} else {
			    $Besetzer = db2display($row['LOGIN']);
			    $Besetzer = str_replace('&', '&amp;', $Besetzer);
			    $Besetzer = 'Ticket ' . PELAS::formatTicketNr($row['ticketId']) . '<br/>' . $Besetzer;
			}
			if (($PlatzArray[$rc][3] == 1) or ($PlatzArray[$rc][3] == 3)) {
			    $pltemp = $Platz +1;
			} else {
			    $pltemp = $PlatzArray[$rc][0] - $Platz;
			}

			if ($PlatzArray[$rc][6] == 1)
			{
				# Platz ist gesperrt
				$sHref = "#";
			}
			else
			{
				$sHref = "href='javascript:gores($rc,$pltemp);'";
			}
			if ($PlatzArray[$rc][3] == 5) {
			    $html .= "<area shape='poly' coords='$points[0],$points[1],$points[2],$points[3],$points[4],$points[5],$points[6],$points[7]'";
			} else {
			    $html .= "<area shape='rect' coords='" . floor($kx1) . "," . floor($ky1) . "," . floor($kx2) . "," . floor($ky2) . "'";
			}
			if ($PlatzArray[$rc][6] == 1) {
					$strrestyp = 'gesperrt';
			} else if ($row['ticketId'] > 0) {
			    $strrestyp = '';
			} else {
			    $strrestyp = 'frei';
			}

			# Clan ermitteln.
			##$result2 = DB::query("SELECT c.CLANID, c.NAME FROM CLAN c, USER_CLAN uc WHERE c.CLANID = uc.CLANID AND uc.USERID = $row[USERID] AND uc.MANDANTID = $nPartyID AND uc.AUFNAHMESTATUS = '$AUFNAHMESTATUS_OK'");
			$sql = "SELECT
				  c.CLANID, c.NAME
				FROM
				  CLAN c, USER_CLAN uc
				WHERE
				  c.CLANID = uc.CLANID AND
				  uc.USERID = '$row[USERID]' AND
				  uc.MANDANTID = $nPartyID AND
				  uc.AUFNAHMESTATUS = '$AUFNAHMESTATUS_OK'";
			$result2 = DB::query($sql);

			$row2 = $result2->fetch_array();
			if ($row2['CLANID'] > 0) {
				$sClan = '<br/>Clan: ' . db2display($row2['NAME']);
				if (strlen($sClan) > 32 ) {
					$sClan = substr($sClan, 0, 32) . '...';
				}
			} else {
				$sClan = '';
			}

			##$html .= " onmouseover=\"Anz($rc, $pltemp,'$Besetzer',1,'$row[RESTYP]','$iShowBild');window.status='$rc - $pltemp: $Besetzer ($strrestyp)';return true;\" onmouseout=\"Anz(0,0,'',2);window.status='';return true;\" $sHref>";
			# geändert von muffi (18.11.2003) zwecks anpassung der onmouseover-Anzeige
			##$html .= " onmouseover=\"funk1($rc, $pltemp,'$Besetzer','$row[RESTYP]','$iShowBild', '$sClan');return true;\" onmouseout=\"funk2(); return false;\" $sHref>";
			# geändert von muffi (7.1.2005), soll nun mit overlib laufen
			if ($PlatzArray[$rc][6] == 1 && !isset($row[ticketId]) && $row[ticketId] <= 0)
				$tempTicketId = -1;
			else $tempTicketId = $row[ticketId];
			$html .= " onmouseover=\"return show($rc, $pltemp, '$Besetzer', '$tempTicketId', '$iShowBild', '$sClan');\" onmouseout=\"return nd();\" $sHref>";
			$html .= ' ';
			# HTML_END
		}
		$html .= "\n";
		
		# Generiertes HTML zurückgeben.
		return $html;
}
?>
