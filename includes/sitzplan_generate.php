<?php
include_once "dblib.php";
include_once "format.php";
//include_once "sitzlib.php";

function GeneriereSitzplan($nPartyID, $ebene, $locateUser = 0)
{  
	global $dbname, $dbh, $PlatzArray, $tbreite, $tlaenge, $tLogelaenge, $maxreihen, $startreihen, $SITZ_VORGEMERKT, $SITZ_RESERVIERT, $AUFNAHMESTATUS_OK;

	DB::connect();

	//####################################
	// Grundlegende Daten aus DB holen
	$sql = "select 
		  * 
		from 
		  SITZDEF 
		where 
		  MANDANTID=$nPartyID and 
		  EBENE=$ebene";
	$result = DB::query($sql);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	//$PlatzArray = Array ( $row[REIHE] => Array ($row[LAENGE], $row[XCORD], $row[YCORD], $row[AUSRICHTUNG], $row[ISTLOGE] ));
	$PlatzArray = "";
	while ($row = $result->fetch_array()) {
		//Kommentar ausgeben
		$PlatzArray[$row['REIHE']][0] = $row['LAENGE'];
		$PlatzArray[$row['REIHE']][1] = $row['XCORD'];
		$PlatzArray[$row['REIHE']][2] = $row['YCORD'];
		$PlatzArray[$row['REIHE']][3] = $row['AUSRICHTUNG'];
		$PlatzArray[$row['REIHE']][4] = $row['ISTLOGE'];
	}

	//groessenkonstanten
	$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='SITZTIEFE' and MANDANTID=$nPartyID");
	$row = $result->fetch_array();
	$tTempTiefe = $row['STRINGWERT'];
	if ($tTempTiefe > 0) {
		$tbreite = $tTempTiefe;
	} else {
		$tbreite = 13;
	}
	$tbreite = $tbreite * 1;
	$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='SITZBREITE' and MANDANTID=$nPartyID");
	$row = $result->fetch_array();
	$tTempBreite = $row['STRINGWERT'];
	if ($tTempBreite > 0) {
		$tlaenge = $tTempBreite;
	} else {
		$tlaenge = 13;
	}

	//Breite der Loge
	$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='LOGE_SITZBREITE' and MANDANTID=$nPartyID");
	$row = $result->fetch_array();
	$tTempBreite = $row['STRINGWERT'];
	if ($tTempBreite > 0) {
		$tLogelaenge = $tTempBreite;
	} else {
		$tLogelaenge = 18;
	}

	//Maximale Reihen
	$result = DB::query("select MAX(REIHE) as MAXROW from SITZDEF where MANDANTID=$nPartyID and EBENE=$ebene");
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$row = $result->fetch_array();
	$maxreihen = $row['MAXROW'];

	//Reihen starten ab
	$result = DB::query("select MIN(REIHE) as MINROW from SITZDEF where MANDANTID=$nPartyID and EBENE=$ebene");
	$row = $result->fetch_array();
	$startreihen = $row['MINROW'];
	//# Daten Ende
	//#############################



	// Vormerkungen löschen, wenn älter als 14 Tage
	$result = DB::query("delete from SITZ where MANDANTID=$nPartyID and RESTYP=$SITZ_VORGEMERKT and (TO_DAYS(NOW()) - TO_DAYS(WANNRESERVIERT) > 14)");

	//$im = @imageCreateFromGif("sitzplan_halle.gif");
	$im = imageCreateFromPNG(PELASDIR."sitzbild/vorlage_".$nPartyID."_".$ebene.".png");

	//$im = imageCreateFromJpeg("factory/halle.jpg");
	$bg = ImageColorAllocate($im,255,255,255);
	$tischrand = ImageColorAllocate($im,0,0,0);
	$tischfrei = ImageColorAllocate($im,13,206,4);
	$tischbesetzt = ImageColorAllocate($im,220,0,0);
	$tischvorgemerkt = ImageColorAllocate($im,226,234,0);
	$tischmarkiert = ImageColorAllocate($im,0,0,255);

	$tempLaenge = $tlaenge;

	$tempTheHTML = "<map name=\"mmm_map\">\n";

	$PlaetzeGesamt = 0;
	//Alle Reihen durch

	for ($rc=$startreihen;$rc<=$maxreihen;$rc++) {
		// Logenplaetze koennen breiter sein
		if ($PlatzArray[$rc][4] == 1) {
			$tlaenge = $tLogelaenge;
		} else {
			$tlaenge = $tempLaenge;
		}

		//Alle Plaetze durch
		for ($Platz=0; $Platz < $PlatzArray[$rc][0]; $Platz++) {
			$PlaetzeGesamt++;

			if ($PlatzArray[$rc][3] == 1 || $PlatzArray[$rc][3] == 3) {
				$pltemp = $Platz;
			} else {
				$pltemp = $PlatzArray[$rc][0] - $Platz- 1;
			}
			$dbplatz = $pltemp +1;

			// Besetzer rausfinden
			$sql = "select 
				  u.BILD_VORHANDEN, u.LOGIN, b.USERID, b.RESTYP from USER u, SITZ b 
				where 
				  u.USERID = b.USERID and 
				  b.PLATZ = $dbplatz and 
				  b.REIHE = $rc and 
				  b.MANDANTID = $nPartyID";
			$result = DB::query($sql); 
			$row = $result->fetch_array();

			if (empty($row['USERID'])) {
				$tempcolor = $tischfrei;
			} else if ($row['USERID'] == $locateUser && $row['RESTYP'] ==  $SITZ_RESERVIERT) {
				// aktueller User ist der User, nach dem gesucht wird
				// Tisch markieren
				$tempcolor = $tischmarkiert;
			} else if ($row['RESTYP'] ==  $SITZ_VORGEMERKT) {
				//vorgemerkt
				$tempcolor = $tischvorgemerkt;
			} else {
				//reserviert
				$tempcolor = $tischbesetzt;
			}

			if ($PlatzArray[$rc][3] == 5) {
				//Speziell fuer The Summit 45Grad-Winkel
				$xOffset = $PlatzArray[$rc][1];
				$yOffset = $PlatzArray[$rc][2];

				//Das goldene Dreieck - wie viele Pixel je nach oben / unten
				$MoveIt  = sqrt (pow(($Platz*$tlaenge),2) / 2);
				//ein einzelner Platz
				$MoveIt1 = sqrt (pow((1*$tlaenge),2) / 2);

				//die beiden oberen Punkte
				$points[0] = $xOffset+$MoveIt; // x1
				$points[1] = $yOffset+$MoveIt; // y1
				$points[2] = $xOffset+$MoveIt+$MoveIt1 +1; // x2
				$points[3] = $yOffset+$MoveIt+$MoveIt1 -1; // y2

				//nun die beiden unteren
				//linksabweichung
				$abweichung = sqrt (pow(($tbreite),2) / 2);

				$points[4] = $points[2] - $abweichung + 1; // x3
				$points[5] = $points[3] + $abweichung; // y3
				$points[6] = $points[0] - $abweichung + 1; // x4
				$points[7] = $points[1] + $abweichung; // y4


				//echo "<p>X1: ".$points[0]." / Y1: ".$points[1]." / X2: ".$points[2]." / Y2: ".$points[3]." / X3: ".$points[4]." / Y3: ".$points[5]." / Abweichung: $abweichung / MoveIt1: $MoveIt1</p>";

				imagepolygon ($im, $points, 4, $tischrand);

				//um 1 Pixel verkleinern, damit der Rand durchkommt
				$points[0] = $points[0] + 1;
				$points[1] = $points[1] + 1;
				$points[2] = $points[2] - 1;
				$points[3] = $points[3] + 1;
				$points[4] = $points[4] - 1;
				$points[5] = $points[5] - 1;
				$points[6] = $points[6] + 1;
				$points[7] = $points[7] - 1;

				imagefilledpolygon ($im, $points, 4, $tempcolor);

				if ($PlatzArray[$rc][4] == 1) {
					ImageString ($im, 1, $points[0]+1, $points[1]+4, "L", $tischrand);
				}

			} elseif ($PlatzArray[$rc][3] == 3 || $PlatzArray[$rc][3] == 4) {
				$kx1 = $PlatzArray[$rc][1];
				$ky1 = $PlatzArray[$rc][2]+$tlaenge*$Platz;
				$kx2 = $tbreite+$PlatzArray[$rc][1];
				$ky2 = $PlatzArray[$rc][2]+$tlaenge*$Platz+$tlaenge;

				ImageRectangle($im,$kx1,$ky1,$kx2,$ky2,$tischrand);
				ImageFilledRectangle($im,$kx1+1,$ky1+1,$kx2-1,$ky2-1,$tempcolor);
				if ($PlatzArray[$rc][4] == 1) {
					ImageString ($im, 1, $kx1+4, $ky1+3, "L", $tischrand);
				}
			} else {
				$kx1 = $Platz*$tlaenge+$PlatzArray[$rc][1];
				$ky1 = $PlatzArray[$rc][2];
				$kx2 = $Platz*$tlaenge+$PlatzArray[$rc][1]+$tlaenge;
				$ky2 = $PlatzArray[$rc][2]+$tbreite;

				ImageRectangle($im,$kx1,$ky1,$kx2,$ky2,$tischrand);
				ImageFilledRectangle($im,$kx1+1,$ky1+1,$kx2-1,$ky2-1,$tempcolor);
				if ($PlatzArray[$rc][4] == 1) {
					ImageString ($im, 1, $kx1+4, $ky1+3, "L", $tischrand);
				}
			}

			//HTML-Part
			if ($row['BILD_VORHANDEN'] == "J") {
				$iShowBild = $row['USERID'];
			} else {
				$iShowBild = 0;
			}
			if ($row['LOGIN'] == "" ) {
				$Besetzer = "";
				$nResTyp = 0;
			} else {
				$Besetzer = $row['LOGIN'];
				$Besetzer = db2display ($Besetzer);
				$Besetzer = str_replace('&', '&amp;', $Besetzer);
			}
			if ($PlatzArray[$rc][3] == 1 || $PlatzArray[$rc][3] == 3)
			{
				$pltemp = $Platz +1;
			} else {
				$pltemp = $PlatzArray[$rc][0] - $Platz;
			}

			$sHref = "href='javascript:gores($rc,$pltemp);'";
			if ($PlatzArray[$rc][3] == 5) {
				$tempTheHTML .= "<area shape='poly' coords='$points[0],$points[1],$points[2],$points[3],$points[4],$points[5],$points[6],$points[7]'";
			} else {
				$tempTheHTML .= "<area shape='rect' coords='".floor($kx1).",".floor($ky1).",".floor($kx2).",".floor($ky2)."'";
			}
                        switch($row['RESTYP']) {
                          case 2: $strrestyp = 'vorgemerkt'; break;
                          case 1: $strrestyp = 'reserviert'; break;
                          default: $strrestyp = 'frei'; break;
                        }
                        
			// Clan raussuchen
			//$result2 = DB::query("select c.CLANID, c.NAME from CLAN c, USER_CLAN uc where c.CLANID = uc.CLANID and uc.USERID=$row[USERID] and uc.MANDANTID=$nPartyID and uc.AUFNAHMESTATUS='$AUFNAHMESTATUS_OK'");
			$sql = "select
				  c.CLANID, c.NAME
				from
				  CLAN c, USER_CLAN uc
				where
				  c.CLANID = uc.CLANID and
				  uc.USERID = '$row[USERID]' and
				  uc.MANDANTID = $nPartyID and
				  uc.AUFNAHMESTATUS = '$AUFNAHMESTATUS_OK'";
			// TODO: notwendig?
            		DB::connect();
			$result2 = DB::query($sql);

  			$row2    = $result2->fetch_array();
	  		if ($row2['CLANID'] > 0) {
				$sClan   = "<br>Clan: ".db2display($row2['NAME']);
				if (strlen($sClan) > 32 ) {
					$sClan = substr( $sClan, 0, 32)."...";
				}
  			} else {
				$sClan = "";
  			}
                        
			//$tempTheHTML .= " onMouseOver=\"Anz($rc, $pltemp,'$Besetzer',1,'$row[RESTYP]','$iShowBild');window.status='$rc - $pltemp: $Besetzer ($strrestyp)';return true;\" onMouseOut=\"Anz(0,0,'',2);window.status='';return true;\" $sHref>";
			// geändert von muffi (18.11.2003) zwecks anpassung der onMouseOver-Anzeige
			//$tempTheHTML .= " onMouseOver=\"funk1($rc, $pltemp,'$Besetzer','$row[RESTYP]','$iShowBild', '$sClan');return true;\" onMouseOut=\"funk2(); return false;\" $sHref>";
			// geändert von muffi (7.1.2005), soll nun mit overlib laufen
			$tempTheHTML .= " onMouseOver=\"return show($rc, $pltemp, '$Besetzer', '$row[RESTYP]', '$iShowBild', '$sClan');\" onMouseOut=\"return nd();\" $sHref>";
			$tempTheHTML .= "\n";
			//HTML_END
		}
	};

	$tempTheHTML .= '</map>';
	
	$tempTheHTML .= "\n<!-- Plaetze gesamt: $PlaetzeGesamt -->\n";

        if ($locateUser == 0) {
          // keinen User highlighten, normalen Sitzplan schreiben
          ImagePng($im, PELASDIR."sitzbild/sitzplan_bild_".$nPartyID."_".$ebene.".png");
          ImageDestroy($im);

          //write HTML to File
          $fp = fopen(PELASDIR."sitzbild/sitzplan_html_".$nPartyID."_".$ebene.".txt","w");
          fputs($fp, $tempTheHTML);
          fclose($fp);
        } else {
          // User highlighten, Sitzplan direkt ausgeben
          ImagePng($im);
          ImageDestroy($im);
        }


//Funktion Ende
}


?>
