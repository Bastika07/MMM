<?php
include_once "dblib.php";
include_once "format.php";
include_once "upload.php";
include_once "session.php";
# Turniersystem-includes
include_once 't_compat.inc.php';
include_once "turnier/Turnier.class.php";
include_once "turnier/TeamSystem.class.php";
include_once "turnier/TurnierGroup.class.php";

$selectPartyID = (isset($_GET['selectPartyID'])) ? intval($_GET['selectPartyID']) : 0;
$archivID = (isset($_GET['archivID'])) ? intval($_GET['archivID']) : 0;
if (isset($_GET['selectTyp'])) {
	if (is_numeric($_GET['selectTyp'])) 
		$selectTyp = intval($_GET['selectTyp']);
	else if ($_GET['selectTyp'] == "youtube") 
		$selectTyp = "youtube";
	else if ($_GET['selectTyp'] == "img")
		$selectTyp = "img";
}

//Layout Cellpadding
$cCellp = 2;
//Layout Tabellenbreite
$cTableSize = "80%";

if (($selectPartyID < 1 && $selectPartyID != -1) || $selectPartyID > 10000000) {
	$selectPartyID = $nPartyID;
}

//mysql_select_db($dbname);

	if ($selectPartyID == -1) {
		// Übersicht aller Parties anzeigen
		$result = mysql_query("select MANDANTID, BESCHREIBUNG from MANDANT");
		//echo mysql_errno().": ".mysql_error()."<BR>";
		echo "<table class=\"rahmen_allg\" cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\" width=\"$cTableSize\">";
		echo "<tr><td class=\"TNListe\"><b>DAS Party Archiv</b></td></tr>";
		$sBGC = "TNListeTDA";
		while ($row = @mysql_fetch_array($result)) {
			echo "<TR><TD class=\"$sBGC\"><a href=\"?page=14&selectPartyID=$row[MANDANTID]\">".db2display($row['BESCHREIBUNG'])."</a></TD></tr>";
			if ($sBGC == "TNListeTDA") {
				$sBGC = "TNListeTDB";
			} else {
				$sBGC = "TNListeTDA";
			}
		}
		echo "</table>";
		
	} else {
		if (!isset($selectTyp)) {
			$selectTyp = '';
		}
		if ($selectTyp >= $KATEGORIE_ARCH_VIDEOS and $selectTyp <= $KATEGORIE_ARCH_LINK) {
			if ($archivID <= 0) {
				//######################################################
				// Übersichtsliste der gewählten Kategorie anzeigen

				$result = mysql_query("select BESCHREIBUNG from MANDANT where MANDANTID = '$selectPartyID'");
				$row0 = @mysql_fetch_array($result);
				echo "<h2>".db2display($row0['BESCHREIBUNG'])." : ".$KATEGORIEINFO[$selectTyp][0]."</h2>";

				if ($selectTyp == $KATEGORIE_ARCH_BILDER) {
					// Bei Bildern das alte und neue Archiv zusammen fassen. Typ 7 sind Bilder. Die Laufende Nummer und PartyID aus den
					// Beiden Archivtabelle bekomtm man nicht zusammen. Die Convertierung des Datums ist für die Korrekte Sortierung nötig.
					$sql = "SELECT convert(p.terminVon, unsigned integer) as LFDNR, USERID, LINK, ARCHIVID, KOMMENTAR, TYP, p.PARTYID
					FROM ARCHIV a join party p on a.partyid = p.partyid
					WHERE a.MANDANTID = '$selectPartyID'
					AND TYP = 'img'
					AND LOCKED = 'no'
					
					union all
					
					SELECT LFDNR, USERID, LINK, ARCHIVID, KOMMENTAR, 7, 0
					FROM ARCHIV_INFO
					WHERE PARTYID = '$selectPartyID'
					AND TYP = '7'
					AND LOCKED = 'no'
					
					ORDER BY LFDNR DESC , KOMMENTAR
					";
					
					$result2 = mysql_query($sql);
				} elseif ($selectTyp == $KATEGORIE_ARCH_VIDEOS) {
						// Bei Videos das alte und neue Archiv zusammen fassen. Typ 6 sind Videos. Die Laufende Nummer und PartyID aus den
						// Beiden Archivtabelle bekomtm man nicht zusammen. Die Convertierung des Datums ist für die Korrekte Sortierung nötig.
						$result2 = mysql_query("
						(
						SELECT convert(p.terminVon, unsigned integer) as LFDNR, USERID, LINK, ARCHIVID, KOMMENTAR, TYP, p.PARTYID
						FROM ARCHIV a join party p on a.partyid = p.partyid
						WHERE a.MANDANTID = '$selectPartyID'
						AND TYP = 'youtube'
						AND LOCKED = 'no'
						)
						union all
						(
						SELECT LFDNR, USERID, LINK, ARCHIVID, KOMMENTAR, 6, 0
						FROM ARCHIV_INFO
						WHERE PARTYID = '$selectPartyID'
						AND TYP = '6'
						AND LOCKED = 'no'
						)
						ORDER BY LFDNR DESC , KOMMENTAR
						");
				} elseif ($selectTyp == $KATEGORIE_ARCH_TURNIER) {
					// Bei Turnierergebnissen das alte und neue Archiv zusammen fassen. Typ 9 sind Turniere. Party ID 15 ist noch im alten Archiv daher oben ausschließen
					$result2 = mysql_query("
					(
					SELECT convert( terminVon, unsigned integer ) AS LFDNR, 2 AS USERID, '' AS LINK, '' AS ARCHIVID, '' AS KOMMENTAR, 'turnier' AS TYP, PARTYID
					FROM party
					WHERE MANDANTID = '$selectPartyID' and PARTYID > 15 and terminBis < NOW()
					)
					union all
					(
					SELECT LFDNR, USERID, LINK, ARCHIVID, KOMMENTAR, 9, 0
					FROM ARCHIV_INFO
					WHERE PARTYID = '$selectPartyID'
					AND TYP = '9'
					AND LOCKED = 'no'
					)
					ORDER BY LFDNR DESC , KOMMENTAR
					");
				} else {
					$result2 = mysql_query ("select * from ARCHIV_INFO where PARTYID = '$selectPartyID' and TYP='$selectTyp' and LOCKED='no' order by LFDNR desc, KOMMENTAR");
				}

				$nTheLFDNumber= 0;
				echo "<p><table class=\"rahmen_allg\" cellspacing=\"1\" cellpadding=\"$cCellp\" border=\"0\" width=\"$cTableSize\">";
				$sBGC = "TNListeTDA";
				while ($row2 = mysql_fetch_array($result2)) {
					if ($nTheLFDNumber != $row2['LFDNR']) {
						// Header für Party ausgeben
						if ( $row2['TYP'] == 'img' or $row2['TYP'] == 'youtube' or $row2['TYP'] == 'turnier') {
							$result = mysql_query ("select BESCHREIBUNG as NAME, terminVon as BEGINN, TEILNEHMER from party where PartyID = ".$row2['PARTYID']." and MANDANTID = ".$selectPartyID);
						//die("select NAME, terminVon, TEILNEHMER from PARTY where PartyID = ".$row2['PARTYID']." and MANDANTID = ".$selectPartyID);
						} else {
							$result = mysql_query ("select NAME, BEGINN, TEILNEHMER from PARTYHISTORIE where LFDNR = ".$row2['LFDNR']." and MANDANTID = ".$selectPartyID);
						}
						$row3 = @mysql_fetch_array($result);
						// Leerzeile ab 2. Party
						if ($nTheLFDNumber > 0 ) {
							echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
						}
						echo "<tr><td class=\"TNListe\" colspan=\"2\"><b>".db2display($row3['NAME'])."</b> (".db2display($row3['TEILNEHMER'])." Teilnehmer, ".dateDisplay2Short($row3['BEGINN']).")</td></tr>";
						echo "<tr><td class=\"TNListe\" width=70%>Titel</td><td class=\"TNListe\" width=30%>Autor</td></tr>";
						$nTheLFDNumber = $row2['LFDNR'];
					}
					$result = mysql_query ("select LOGIN from USER where USERID = ".$row2['USERID']);
					$row4 = @mysql_fetch_array($result);

					//### Unterschiedliche Verlinkung zB bei Kategorie Links und Turniere
					if ($selectTyp == $KATEGORIE_ARCH_TURNIER) {
						if ( $row2['TYP'] == 'turnier') {
							# Turnierdatensätze holen
							$turniere = Turnier::getTourneyList($row2['PARTYID']);
							$groups = TurnierGroup::getGroups();
							$groupid = 0;
							foreach ($turniere as $turnierInfo) {
								if ($turnierInfo['pturnierid'] == 0) {
									if ($groupid != $turnierInfo['groupid']) {
										if ($groups[$turnierInfo['groupid']]['flags'] & GROUP_SHOW) {
											$groupid = $turnierInfo['groupid'];
											printf("<tr><td colspan='2' class=\"TNListeTDA\"><b>%s</b></td></tr>\n",db2display($groups[$turnierInfo['groupid']]['name']));
										}
									}
									if ($turnierInfo['icon'] != "") {
										$bildStr = "<img src='".$turnierInfo['icon']."' width='16'>";
									} else {
										$bildStr =  "&nbsp;";
									}
									echo "<TR><TD class=\"$sBGC\"><img src=\"/gfx/pfeil.gif\" border=\"0\">
										<a href='?page=21&turnierid=".$turnierInfo['turnierid']."'><img src='".PELASHOST."/gfx/icon_user.gif' border='0' title='Turnierdetails und Teilnehmer'></a>
										<a href='?page=25&turnierid=".$turnierInfo['turnierid']."'><img src='".PELASHOST."/gfx/icon_network.gif' border='0' title='Turnierbaum'></a>
										<a href='?page=23&turnierid=".$turnierInfo['turnierid']."'><img src='".PELASHOST."/gfx/calendar.gif' border='0' title='Ranking'></a>
										".db2display($turnierInfo['name'])."</TD><TD class=\"$sBGC\"><a href=\"?page=4&nUserID=".$row2['USERID']."\">".db2display($row4['LOGIN'])."</TD></tr>";
									if ($sBGC == "TNListeTDA") {
										$sBGC = "TNListeTDB";
									} else {
										$sBGC = "TNListeTDA";
									}
								}
							}
						} elseif ($row2['LINK'] != "") {
						  // Link auf neues Turniersystem
						  $sTheLink = "<a href=\"".$row2['LINK']."\">";
						} else {
						  // klassische Anzeige mit Dateien
						  $sTheLink = "<a href=\"".PELASHOST."archiv/_$selectTyp/".$row2['ARCHIVID'].".phpl\">";
						}
					} elseif ($selectTyp == $KATEGORIE_ARCH_LINK) {
						$sTheLink = "<a href=\"".$row2['LINK']."\" target=\"_blank\">";
					} 
					else {
						$sTheLink = "<a href=\"?page=14&selectPartyID=$selectPartyID&selectTyp=".$row2['TYP']."&archivID=".$row2['ARCHIVID']."\">";
					}
					if ( $row2['TYP'] <> 'turnier') {
						echo "<TR><TD class=\"$sBGC\"><img src=\"/gfx/pfeil.gif\" border=\"0\"> ".$sTheLink.db2display($row2['KOMMENTAR'])."</a></TD><TD class=\"$sBGC\"><a href=\"?page=4&nUserID=".$row2['USERID']."\">".db2display($row4['LOGIN'])."</TD></tr>";
					}
					if ($sBGC == "TNListeTDA") {
						$sBGC = "TNListeTDB";
					} else {
						$sBGC = "TNListeTDA";
					}
				}
				if ($nTheLFDNumber == 0) {
					echo "<TR><TD>Es sind keine Eintr&auml;ge vorhanden.</TD></tr>";
				}
				echo "</table></p>";

				echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href=\"?page=14&selectPartyID=$selectPartyID\" class=\"forumlink\">Zur &Uuml;bersicht</a></td></tr></table></p>";

			} else {
				
				//################################
				// Detailansicht für Kategorie
				$result = mysql_query("select LFDNR, LINK, USERID, LOCKED, AUFLOESUNG, KOMMENTAR from ARCHIV_INFO where ARCHIVID = '$archivID'");
				$row3 = @mysql_fetch_array($result);
				$result = mysql_query("select LOGIN from USER where USERID = ".$row3['USERID']);
				$row2 = @mysql_fetch_array($result);
				$result = mysql_query("select NAME, BEGINN, TEILNEHMER from PARTYHISTORIE where LFDNR = ".$row3['LFDNR']." and MANDANTID = '".$selectPartyID."'");
				$row4 = @mysql_fetch_array($result);
				echo "<h2>".db2display($row4['NAME'])." : ".$KATEGORIEINFO[$selectTyp][0]."</h2>";
				echo "<p>Teilnehmer: ".db2display($row4['TEILNEHMER'])." / Datum: ".dateDisplay2Short($row4['BEGINN'])."</p>";
				if ($row3['LOCKED'] == "no") {
					if ($selectTyp == $KATEGORIE_ARCH_VIDEOS && is_readable(PELASDIR."/archiv/_".$selectTyp."/".$archivID."/".$row3['LINK'])) {
						// Dateigrösse rausfinden
						$nTheFileSize = filesize (PELASDIR."/archiv/_".$selectTyp."/".$archivID."/".$row3['LINK']);
						$nTheFileSize = $nTheFileSize / 1048576; // Grösse in MB ausrechnen
						$nTheFileSize =	number_format ($nTheFileSize, 2);
						$sOutAdd = "<i>($nTheFileSize MB)</i>";
					} else {
						$sOutAdd = "";
					}

					// Bei Videos / Files den Hinweis mit rechtsklicken und speichern unter anzeigen
					if ($selectTyp == $KATEGORIE_ARCH_VIDEOS) {
						echo "<p>Bitte die Bilder oder den Link mit der rechten Maustaste anklicken und dann &quot;Ziel speichern unter...&quot; ausw&auml;hlen.</p>";
					}

					echo "<table class=\"rahmen_allg\" cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\" width=\"$cTableSize\">";
					echo "<tr><td class=\"TNListe\" colspan=5><b>".db2display($row3['KOMMENTAR'])."</b> $sOutAdd</td></tr>";

					if ($selectTyp == $KATEGORIE_ARCH_BILDER || $selectTyp == $KATEGORIE_ARCH_ZEITUNG || $selectTyp == $KATEGORIE_ARCH_VIDEOS) {
						//### Bilder und Zeitungsartikel aus Dateisystem lesen
						$Sdir = PELASDIR."/archiv/_".$selectTyp."/".$archivID."/";
						exec("ls $Sdir",$Slines,$Src);
						$Scount = count($Slines) - 1;
						$counter = 0;
						$j = 0;
						for ($Si = 0; $Si <= $Scount ; $Si++) {
							if (substr($Slines[$Si],0,3) == "tn_") {
								if ( $j == 3 ) { $j = 0; echo "</tr>\n<tr>"; }
								$j++;

								$result0815 = mysql_query("select KOMMENTAR from ARCHIV_KOMMENTAR where ARCHIVID = '$archivID' and NAME = '".substr($Slines[$Si],3)."'");
								$row4 = @mysql_fetch_array($result0815);

								//### Für Kategorie Video Download aktivieren
								//### Name des Files steht im Feld Link
								if ($selectTyp == $KATEGORIE_ARCH_VIDEOS) {
									$sTheURL = PELASHOST."archiv/_".$selectTyp."/".$archivID."/".rawurlencode($row3['LINK']);
								} else {
									$sTheURL = PELASHOST."archiv/_".$selectTyp."/".$archivID."/".rawurlencode(substr($Slines[$Si],3));
								}
								$imageUrl = PELASHOST."archiv/_".$selectTyp."/".$archivID."/".rawurlencode($Slines[$Si]);
								//###echo "<td width=\"33%\" align=center class=\"TNListeTDA\" valign=\"top\"><a href=\"".$sTheURL."\data-lightbox=\"archiv\""><img src=\"".$imageUrl."\" border=0 alt=\"".$row4['KOMMENTAR']."\"></a><br>".db2display($row4['KOMMENTAR'])."</td>";
								echo "<td width=\"33%\" align=center class=\"TNListeTDA\" valign=\"top\"><a href=\"".$sTheURL."\" data-lightbox=\"archiv\"><img src=\"".$imageUrl."\" border=0 alt=\"".$row4['KOMMENTAR']."\"></a><br>".db2display($row4['KOMMENTAR'])."</td>";

								$counter ++;
							}
						}
						if ($counter> 0) {
							echo "</tr>";
						} elseif ($selectTyp == $KATEGORIE_ARCH_VIDEOS) {
							echo "<tr><td class=\"TNListeTDA\" colspan=\"3\">Kein Preview vorhanden, <a href=\"".PELASHOST."archiv/_".$selectTyp."/".$archivID."/".$row3['LINK']."\">Download hier</a>.</td></tr>";
						}
					} else {
						echo "<p>Fehler: Kann gew&auml;hlte Kategorie nicht anzeigen!</p>";
					}
					echo "</table>";


					echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href=\"?page=14&selectPartyID=$selectPartyID&selectTyp=$selectTyp\" class=\"forumlink\">Zur&uuml;ck zur Liste</a></td></tr></table></p><br>";

				} else {
					echo "<p>Diese Galerie ist nicht freigegeben.</p>";
				}
			}
		} else if ($selectTyp == "img") {
			//################################
			// Detailansicht für Kategorie
			$result = mysql_query("select PARTYID, ARCHIVID, USERID, LOCKED, KOMMENTAR from ARCHIV where ARCHIVID = '$archivID'");
			$row3 = @mysql_fetch_array($result);
			$result = mysql_query("select LOGIN from USER where USERID = ".$row3['USERID']);
			$row2 = @mysql_fetch_array($result);
			$result = mysql_query("select beschreibung, terminvon, TEILNEHMER from party where PARTYID = ".$row3['PARTYID']);
			$row4 = @mysql_fetch_array($result);
			echo "<h2>".db2display($row4['beschreibung'])." : ".$KATEGORIEINFO[7][0]."</h2>";
			echo "<p>Teilnehmer: ".db2display($row4['TEILNEHMER'])." / Datum: ".dateDisplay2Short($row4['terminvon'])."</p>";
			if ($row3['LOCKED'] == "no") {
				echo "<table class=\"rahmen_allg\" cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\" width=\"$cTableSize\">";
				if (!isset($sOutAdd)) $sOutAdd = '';
				echo "<tr><td class=\"TNListe\" colspan=5><b>".db2display($row3['KOMMENTAR'])."</b> $sOutAdd</td></tr>";
				//### Bilder und Zeitungsartikel aus Dateisystem lesen
				$Sdir = PELASDIR."/archiv/_".$selectTyp."/".$archivID."/";
				exec("ls $Sdir",$Slines,$Src);
				$Scount = count($Slines) - 1;
				$counter = 0;
				$j = 0;
				for ($Si = 0; $Si <= $Scount ; $Si++) {
					if (substr($Slines[$Si],0,3) == "tn_") {
						if ( $j == 3 ) { $j = 0; echo "</tr>\n<tr>"; }
						$j++;
						$result0815 = mysql_query("select KOMMENTAR from ARCHIV_KOMMENTAR where ARCHIVID = '$archivID' and NAME = '".substr($Slines[$Si],3)."'");
						$row4 = @mysql_fetch_array($result0815);
						//### Name des Files steht im Feld Link
						$sTheURL = PELASHOST."archiv/_".$selectTyp."/".$archivID."/".rawurlencode(substr($Slines[$Si],3));
						$imageUrl = PELASHOST."archiv/_".$selectTyp."/".$archivID."/".rawurlencode($Slines[$Si]);
						echo "<td width=\"33%\" align=center class=\"TNListeTDA\" valign=\"top\"><a href=\"".$sTheURL."\" data-lightbox=\"archiv\"><img src=\"".$imageUrl."\" border=0 alt=\"".$row4['KOMMENTAR']."\"></a><br>".db2display($row4['KOMMENTAR'])."</td>";
						$counter ++;
					}
				}
				if ($counter> 0) {
					echo "</tr>";
				}
			echo "</table>";


			echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href=\"?page=14&selectPartyID=$selectPartyID&selectTyp=7\" class=\"forumlink\">Zur&uuml;ck zur &Uuml;bersicht</a></td></tr></table></p><br>";

			} else {
				echo "<p>Diese Galerie ist nicht freigegeben.</p>";
			}
		} elseif ($selectTyp == "youtube") {
			//################################
			// Detailansicht für Kategorie
			$result = mysql_query("select PARTYID, ARCHIVID, USERID, LOCKED, KOMMENTAR, LINK from ARCHIV where ARCHIVID = '$archivID'");
			$row3 = @mysql_fetch_array($result);
			$result = mysql_query("select LOGIN from USER where USERID = ".$row3['USERID']);
			$row2 = @mysql_fetch_array($result);
			$result = mysql_query("select beschreibung, terminvon, TEILNEHMER from party where PARTYID = ".$row3['PARTYID']);
			$row4 = @mysql_fetch_array($result);
			echo "<h2>".db2display($row4['beschreibung'])." : ".$KATEGORIEINFO[6][0]."</h2>";
			echo "<p>Teilnehmer: ".db2display($row4['TEILNEHMER'])." / Datum: ".dateDisplay2Short($row4['terminvon'])."</p>";
			if ($row3['LOCKED'] == "no") {
				echo "<table class=\"rahmen_allg\" cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\" width=\"$cTableSize\">";
				if (!isset($sOutAdd)) $sOutAdd = '';
				echo "<tr><td class=\"TNListe\" colspan=5><b>".db2display($row3['KOMMENTAR'])."</b> $sOutAdd</td></tr>";
				echo "<tr><td width=\"33%\" align=center class=\"TNListeTDA\" valign=\"top\">";
				echo "<br><br><object width=\"425\" height=\"344\"><param name=\"movie\" value=\"https://www.youtube-nocookie.com/v/".$row3['LINK']."&hl=de&fs=1&rel=0\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"https://www.youtube-nocookie.com/v/".$row3['LINK']."&hl=de&fs=1&rel=0\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"425\" height=\"344\"></embed></object>";
				echo "<br><br></td><tr>";

			echo "</table>";


			echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href=\"?page=14&selectPartyID=$selectPartyID&selectTyp=6\" class=\"forumlink\">Zur&uuml;ck zur &Uuml;bersicht</a></td></tr></table></p><br>";

			} else {
				echo "<p>Diese Galerie ist nicht freigegeben.</p>";
			}
		} else {
			$result = mysql_query("select BESCHREIBUNG from MANDANT where MANDANTID = '$selectPartyID'");
			$row = @mysql_fetch_array($result);
			echo "<h2>".db2display($row['BESCHREIBUNG'])."</h2>\n";

			echo "<table class=\"rahmen_allg\" cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\" width=\"$cTableSize\">\n";
			echo "<tr><td class=\"TNListe\"><b>Kategorie w&auml;hlen</b></td></tr>\n";

			// Bilder zu den Kategorien
			$aFontIcons = Array (
				$KATEGORIE_ARCH_VIDEOS  => "fa fa-video-camera",
				$KATEGORIE_ARCH_BILDER  => "fa fa-camera",
				$KATEGORIE_ARCH_ZEITUNG => "fa fa-newspaper-o",
				$KATEGORIE_ARCH_TURNIER => "fa fa-trophy",
				$KATEGORIE_ARCH_LINK    => "fa fa-external-link-square"
			);

			$sBGC = "TNListeTDA";
			for ($i=$KATEGORIE_ARCH_VIDEOS;$i<=$KATEGORIE_ARCH_LINK;$i++) {
				if ($KATEGORIEINFO[$i][2]){

					#$rowCount = @mysql_fetch_array(mysql_db_query($dbname, "select count(*) as ANZAHL from ARCHIV_INFO where PARTYID='$selectPartyID' and TYP='$i' and locked='no'", $dbh), MYSQL_ASSOC);
					echo "<TR height=\"44\"><TD class=\"$sBGC\"><table><tr><td width=\"35\" style=\"text-align:center;\"><i class=\"archiv ".$aFontIcons[$i]."\"></i></td><td><a href=\"?page=14&selectPartyID=$selectPartyID&selectTyp=$i\">".db2display($KATEGORIEINFO[$i][0])."</a></td></tr></table></TD></tr>\n";
					if ($sBGC == "TNListeTDA") {
						$sBGC = "TNListeTDB";
					} else {
						$sBGC = "TNListeTDA";
					}
				}
			}
			echo "</table>\n";
			#echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href=\"?page=14&selectPartyID=-1\" class=\"forumlink\">Zu allen Parties</a></td><td class='forum_titel'><a href=\"?page=15&selectPartyID=$selectPartyID&sAction=upload\" class=\"forumlink\">Beitrag hochladen</a></td></tr></table></p>\n";
			
			echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href=\"?page=15&selectPartyID=$selectPartyID&sAction=upload\" class=\"forumlink\">Beitrag hochladen</a></td></tr></table></p>\n";

		}
	}
?>
