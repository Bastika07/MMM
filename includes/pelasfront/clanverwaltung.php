<?php
require_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "pelasfunctions.php";

$nClanmate = intval($_POST['nClanmate']);
$member = intval($_POST['member']);
$inClan = intval ($_POST['inClan']);

if ($nLoginID == "") {
	echo "<p>Du musst eingeloggt sein, um die Clanverwaltung zu benutzen.</p>";
	echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=5\">$str[loginodererstellen]</a></p>";
} else {

	//eingeloggt, feststellen, ob schon in einem Clan
	$row = @mysql_fetch_array(mysql_query("select c.NAME, uc.CLANID, uc.AUFNAHMESTATUS from USER_CLAN uc, CLAN c where c.MANDANTID = $nPartyID and c.CLANID = uc.CLANID and uc.USERID = ".intval($nLoginID)." and uc.MANDANTID = $nPartyID"), MYSQL_ASSOC);
	$inClan = $row['CLANID'];
	$Status = $row['AUFNAHMESTATUS'];
	$derClan= $row['NAME'];
	//eingeloggter User bezahlt?
	$row = @mysql_fetch_array(mysql_query("select STATUS, RABATTSTUFE, BEZ_IN_CLAN from ASTATUS where MANDANTID = $nPartyID and USERID = ".intval($nLoginID).""), MYSQL_ASSOC);
	$userRabatt  = $row['RABATTSTUFE'];
	$userBezClan = $row['BEZ_IN_CLAN'];
	$userStatus  = $row['STATUS'];
	if ($userStatus == $STATUS_BEZAHLT || $userStatus == $STATUS_BEZAHLT_LOGE) {
		$userBezahlt = true;
	} else {
		$userBezahlt = false;
	}

	// Namen von $nClanmate rausfinden
	$row = @mysql_fetch_array(mysql_query("select LOGIN from USER where USERID = '".intval($_GET['nClanmate'])."'"), MYSQL_ASSOC);
	$sClanmateName = db2display($row['LOGIN']);
	// Clan von $nClanmate rausfinden
	$row = @mysql_fetch_array(mysql_query("select uc.CLANID from USER_CLAN uc, CLAN c where c.MANDANTID = $nPartyID and c.CLANID = uc.CLANID and uc.USERID = '".intval($_GET['nClanmate'])."' and uc.MANDANTID = $nPartyID"), MYSQL_ASSOC);
	$nClanmatesClan = $row['CLANID'];

	if ($_POST['actionUpload'] == "upload") {
		
		//Neues Clanbild hochladen
		if ($_FILES['iUserbild']['name'] != "") {
			if ($_FILES['iUserbild']['size'] > 20000) {
				echo "<p>$str[zugross]</p>";
			} else {
				$row = mysql_fetch_array(mysql_query("select CLANID from USER_CLAN where MANDANTID = '$nPartyID' and USERID = '".intval($nLoginID)."'"), MYSQL_ASSOC);
				//echo mysql_errno().": ".mysql_error()."<BR>";
				$derClan = $row['CLANID'];
				
				// höhe checken, Max. 60 px
				$aImageSize = GetImageSize($_FILES['iUserbild']['tmp_name']);
				if ($aImageSize[1] > 60) {
					echo "<p>Das Bild ist zu hoch. Es sind max. 60 Pixel erlaubt.</p>";
				} else {
				
					$newfile = PELASDIR."clanlogo/".$nPartyID."_".intval($derClan).".jpg";
					
					if (!copy($_FILES['iUserbild']['tmp_name'], $newfile)) {
						echo "<p>$str[interrror]</p>";
					} else {
						echo "<p>Das Clanlogo wurde erfolgreich hochgeladen.</p>";
					}
				}
			}
		}
	}
		
	if ($_GET['action'] == "entf") {
		if ($nClanmatesClan != $inClan) {
			echo "<p>Fehler: Dieses Clanmitglied ist nicht in Deinem Clan.</p>";
			echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
		} elseif ($_POST['check'] != "yes") {
			echo "<p>M&ouml;chtest Du wirklich Dein bisheriges Clanmitglied <i>&quot;".htmlspecialchars($sClanmateName)."&quot;</i> aus Deinem Clan entfernen?</p>";
			
			echo "<form method=\"post\" action=\"?page=18&action=entf&nClanmate=".intval($_GET['nClanmate'])."\">";
			echo "<input type=\"hidden\" name=\"check\" value=\"yes\">";
			echo "<input type=\"submit\" class=\"button\" value=\"Ja\"> &nbsp; <input type=\"button\" value=\"Nein\" OnClick=\"window.history.back();\">";
			echo "</form>";
		} else {
			$row = @mysql_fetch_array(mysql_query("delete from USER_CLAN where MANDANTID = '$nPartyID' and USERID = '".intval($_GET['nClanmate'])."'"), MYSQL_ASSOC);
			echo "<p><i>&quot;".htmlspecialchars($sClanmateName)."&quot;</i> wurde soeben aus Deinem Clan entfernt.</p>";
			echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
		}
	} elseif ($_GET['action'] == "ueber") {
		// Ueberweisung uebergeben
		/*$result = mysql_query("select u.LOGIN, uc.CLANID from USER_CLAN uc, CLAN c, USER u where u.USERID = uc.USERID and c.MANDANTID = $nPartyID and c.CLANID = uc.CLANID and uc.USERID = $nClanmate and uc.MANDANTID = $nPartyID");
		$row = mysql_fetch_array($result);
		if ($inClan != $row[CLANID]) {
			echo "<p>Fehler: Dieses Clanmitglied ist nicht in Deinem Clan.</p>";
		} elseif ($userStatus != $STATUS_BEZAHLT && $userStatus != $STATUS_BEZAHLT_LOGE) {
			echo "<p>Fehler: Du hast nicht bezahlt.</p>";
		} else {
			if ($check != "yes") {
				echo "<p>M&ouml;chtest Du wirklich Deine &Uuml;berweisung und Deinen Sitzplatz diesem Benutzer &uuml;bergeben?</p>";
				echo "<form method=\"post\" action=\"clanverwaltung.php?action=$action&nClanmate=$nClanmate\">";
				echo "<input type=\"hidden\" name=\"check\" value=\"yes\">";
				echo "<input type=\"submit\" value=\"Ja\"> &nbsp; <input type=\"button\" value=\"Nein\" OnClick=\"window.history.back();\">";
				echo "</form>";
			} else {
				//ok, Felder mit Rabattstufe nicht vergessen
				mysql_query("update ASTATUS set STATUS = '$userStatus', RABATTSTUFE = '$userRabatt', BEZ_IN_CLAN = '$userBezClan' where MANDANTID = $nPartyID and USERID = '$nClanmate'");
				//echo mysql_errno().": ".mysql_error()."<BR>";
				mysql_query("update ASTATUS set STATUS = $STATUS_ANGEMELDET, RABATTSTUFE = 0, BEZ_IN_CLAN = 0, WERGEAENDERT = $nLoginID where MANDANTID = $nPartyID and USERID = $nLoginID");
				echo "<p>Du hast Deine &Uuml;berweisung und Sitzplatz dem User &quot;".db2display($row[LOGIN])."&quot; zugeordnet.</p>";
				
				//Sitzplatz? Welche Ebene?
				$result = mysql_query("select d.EBENE from SITZ s, SITZDEF d where d.MANDANTID = $nPartyID and d.REIHE=s.REIHE and s.USERID = $nLoginID and s.MANDANTID = $nPartyID");
				$row = mysql_fetch_array($result);
				$ebene = $row[EBENE];
				
				// Neuen User auf alten Seat
				mysql_query("update SITZ set USERID = $nClanmate where MANDANTID = $nPartyID and USERID = $nLoginID");
				$generate_sitzplan = true;
			}
		}*/
		echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
	} elseif ($_GET['action'] == "anmelden") {
		//Clanmate anmelden
		//Checken, on Anmeldung eroeffnet
		$result = mysql_query("select STRINGWERT from CONFIG where MANDANTID = $nPartyID and PARAMETER='ANMELDUNG_OFFEN'");
		$row = mysql_fetch_array($result);
		if ($row['STRINGWERT'] != "J") {
			echo "<p>Die Anmeldung zu dieser Party wurde noch nicht er&ouml;ffnet.</p>";
		} else {
			//Checken, ob auch in gleichem Clan!
			$result = mysql_query("select uc.CLANID from USER_CLAN uc, CLAN c where c.MANDANTID = $nPartyID and c.CLANID = uc.CLANID and uc.USERID = '".intval($nClanmate)."' and uc.MANDANTID = $nPartyID");
			$row = mysql_fetch_array($result);

			if ($inClan != $row[CLANID]) {
				echo "<p>Fehler: Dieses Clanmitglied ist nicht in Deinem Clan.</p>";
			} else {
				$result = mysql_query("select USERID from ASTATUS where USERID=".intval($nClanmate)." and MANDANTID=$nPartyID");
				$row = mysql_fetch_array($result);
				if ($row['USERID'] > 0 ) {
					@mysql_query("update ASTATUS set STATUS = $STATUS_ANGEMELDET, WERGEAENDERT='".intval($nLoginID)."', WANNANGEMELDET=NOW() where USERID='".intval($nClanmate)."' and MANDANTID=$nPartyID");
				} else {
					@mysql_query("insert into ASTATUS (MANDANTID, USERID, STATUS, WERGEAENDERT, WANNANGEMELDET) values ($nPartyID, '".intval($nClanmate)."', $STATUS_ANGEMELDET, '".intval($nLoginID)."', NOW())");
				}
				
				// Anmeldebestätigung verschicken
				SendeAnmeldeMail($nPartyID, $nClanmate);
				
				echo "<p>Dein Clanmitglied ist nun angemeldet.</p>";
			}
		}
		echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
	} elseif ($_GET['action'] == "create" || $_GET['action'] == "edit") {
		//###################################
		//Clan erstellen oder aendern
		$row = @mysql_fetch_array(mysql_query("select CLANID, NAME, URL, FANPROGRAMM from CLAN where NAME = '".safe($_POST['iClanName'])."' and MANDANTID = $nPartyID and CLANID <> '".intval($inClan)."'"), MYSQL_ASSOC);
		$exists = 0;
		if ($row['CLANID'] > 0) {
			$exists = 1;
		}
		
		//Beim aendern vorbelegen
		if ($_GET['action'] == "edit" && !isset($_POST['go'])) {
			$sql = "select CLANID, NAME, URL, IRC_CHANNEL, FANPROGRAMM from CLAN where MANDANTID = $nPartyID and CLANID = ".intval($inClan);
			$row = @mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
			$_POST['iClanName'] = $row['NAME'];
			$_POST['iURL']      = $row['URL'];
			$_POST['iIRC_CHANNEL'] = $row['IRC_CHANNEL'];
			$_POST['iFanprogramm'] = $row['FANPROGRAMM'];
		}
		
		if (!isset($_POST['go']) ) {
			ShowForm();
		} elseif (empty($_POST['iClanName'])) {
			echo "<p>Bitte alle Felder ausf&uuml;llen, die mit einem * gekennzeichnet sind.</p>";
			ShowForm();
		} elseif ($exists == 1) {
			echo "<p>Dieser Clanname existiert bereits.</p>";
			ShowForm();
		} else {
			//Sicherheitsabfrage
			if ($_GET['action'] == "edit") {
				//update!
				$result = mysql_query("update CLAN set NAME = '".safe($_POST['iClanName'])."', URL = '".safe($_POST['iURL'])."', IRC_CHANNEL = '".safe($_POST['iIRC_CHANNEL'])."', FANPROGRAMM='".safe($_POST['iFanprogramm'])."', WERGEAENDERT = ".intval($nLoginID)." where MANDANTID = $nPartyID and CLANID = ".intval($inClan) );
				//echo mysql_errno().": ".mysql_error()."<BR>";
				echo "<p>Die Clandaten wurden aktualisiert.</p>";
			} elseif ($inClan > 0 ) {
				echo "<p>Fehler: Du bist bereits in einem Clan.</p>";
			} else {
				//Clan erstellen
				$result = mysql_query("insert into CLAN (MANDANTID, NAME, URL, IRC_CHANNEL, FANPROGRAMM, WANNANGELEGT, WERANGELEGT, WERGEAENDERT) values ($nPartyID, '".safe($_POST['iClanName'])."', '".safe($_POST['iURL'])."', '".safe($_POST['iIRC_CHANNEL'])."', '".safe($_POST['iFanprogramm'])."', NOW(), ".intval($nLoginID).", ".intval($nLoginID).")");
				//ID des grad erstellten Clans feststellen
				$row = @mysql_fetch_array(mysql_query("select CLANID from CLAN where NAME = '".safe($_POST['iClanName'])."' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
				$newClanID = $row['CLANID'];
				//Ersteller hinzufuegen
				$result = mysql_query("insert into USER_CLAN (MANDANTID, USERID, CLANID, AUFNAHMESTATUS, WANNANGELEGT, WERANGELEGT, WERGEAENDERT) values ('$nPartyID', ".intval($nLoginID).", ".intval($newClanID).", $AUFNAHMESTATUS_OK, NOW(), ".intval($nLoginID).", ".intval($nLoginID).")");
				if (mysql_errno() == 0) {
					echo "<p>Dein Clan wurde erstellt. Du kannst ihn ab sofort unter dem Men&uuml;punkt ";
					echo "Clanverwaltung erreichen.</p>";
				} else {
					echo "<p>Ein Fehler ist bei dem Erstellen des Clans aufgetreten.</p>";
				}
			}
			echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
		}
		//###################################
	} elseif ($_GET['action'] == "join") {
		//Clan beitreten
		if ($_GET['clan'] < 1) {
			echo "<p>Bitte w&auml;hle den Clan, dem Du beitreten m&ouml;chtest:</p>";
			$result = mysql_query("select CLANID, NAME, URL from CLAN where MANDANTID = $nPartyID order by NAME");
			while ($row=mysql_fetch_array($result)) {
				echo "<img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18&action=join&clan=".$row['CLANID']."\">".db2display($row['NAME'])."</a><br>";
			}
		} else {
			//Clan wurde ausgewaehlt, join versuchen
			//Sicherheitsabfrage
			if ($inClan > 0) {
				echo "<p>Fehler: Du bist bereits in einem Clan.</p>";
			} else {
				//Clan joinen, status anfrage
				$sql = "insert into USER_CLAN (MANDANTID, USERID, CLANID, AUFNAHMESTATUS, WANNANGELEGT, WERANGELEGT, WERGEAENDERT) values ('$nPartyID', ".intval($nLoginID).", '".intval($_GET['clan'])."', $AUFNAHMESTATUS_WARTEND, NOW(), ".intval($nLoginID).", ".intval($nLoginID).")";
				$result = mysql_query($sql);
				if (mysql_errno() == 0) {
					echo "<p>Du hast nun einen Antrag gestellt, diesem Clan beizutreten. Sobald ein Mitglied ";
					echo "dieses Clans Deinen Antrag best&auml;tigt, bist Du aufgenommen. Deinen Antrag kannst Du jederzeit ";
					echo "zur&uuml;ck ziehen und bei einem anderen Clan stellen.</p>";
					echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
				} else {
					echo "<p>Ein Fehler ist bei dem joinen des Clans aufgetreten.</p>";
				}
			}
		}
	} else {
		if ($inClan == "") {
			//Noch nicht zu einem Clan zugehoerig
			echo "<p>Hallo ".htmlspecialchars($sLogin).", Du geh&ouml;rst noch nicht zu einem Clan. ";
			echo "Du kannst nun entweder einen Clan erstellen oder einem bestehenden Clan beitreten. ";
			echo "Wenn Du in einem Clan bist, hast Du den Vorteil, dass Du f&uuml;r Deine Clanmates ";
			echo "s&auml;mtliche Verwaltungsaufgaben &uuml;bernehmen kannst und umgekehrt.</p>";
			echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18&action=join\">Clan beitreten</a>";
			echo "<br><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18&action=create\">Clan erstellen</a></p>";
		} else {
			//Clanzugehoerig
			//Muss er noch bestaetigt werden?
			if ($Status == $AUFNAHMESTATUS_WARTEND) {
				if ($_GET['action'] == "revoke") {
					$row = @mysql_fetch_array(mysql_query(" delete from USER_CLAN where AUFNAHMESTATUS = $AUFNAHMESTATUS_WARTEND and MANDANTID = $nPartyID and USERID = ".intval($nLoginID)), MYSQL_ASSOC);
					echo "<p>Dein Antrag auf den Clan wurde gel&ouml;scht. Du kannst nun einen neuen Antrag stellen.</p>";
					echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Ab in die jungfreudige Clanverwaltung</a></p>";
				} else {
					echo "<p>Du wartest derzeit noch auf die Aufnahme in den Clan &quot;".db2display($derClan)."&quot;. ";
					echo "Diese Aufnahme kann nur ein aktives Mitglied dieses Clans best&auml;tigen. ";
					echo "An dieser Stelle hast Du die M&ouml;glichkeit, diesen Antrag zur&uuml;ck zu ziehen.</p>";
					echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18&action=revoke&clan=".intval($inClan)."\">Antrag zur&uuml;ckziehen</a></p>";
				}
			} else {
				//#####################################
				//Clanbereich
				if ($_GET['action'] == "finaljoin") {
					$row = @mysql_fetch_array(mysql_query("update USER_CLAN set AUFNAHMESTATUS = $AUFNAHMESTATUS_OK where MANDANTID = $nPartyID and USERID = '".intval($_GET['member'])."'"), MYSQL_ASSOC);
					echo "<p>Du hast soeben ein neues Mitglied aufgenommen.</p>";
					echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
				} elseif ($_GET['action'] == "off") {
					//austreten nur, wenn nicht bezahlt!
					$row = @mysql_fetch_array(mysql_query("select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID and RABATTSTUFE > 0"), MYSQL_ASSOC);
					//echo mysql_errno().": ".mysql_error()."<BR>";
					
					if ($row['STATUS'] == $STATUS_BEZAHLT || $row['STATUS'] == $STATUS_BEZAHLT_LOGE) {
						echo "<p>Du kannst nicht aus diesem Clan austreten, da Du mit Clanrabatt gezahlt hast. Alternativ kannst Du Deine Zahlung einem anderen Clanmitglied zuordnen und dann austreten.</p>";
					} else {
						$row = @mysql_fetch_array(mysql_query("delete from USER_CLAN where AUFNAHMESTATUS = $AUFNAHMESTATUS_OK and MANDANTID = $nPartyID and USERID = $nLoginID and CLANID='".intval($_GET['clan'])."'"), MYSQL_ASSOC);
						//wenn letztes Mitglied, alle Relikte loeschen
						$result = mysql_query("select count(*) from USER_CLAN where AUFNAHMESTATUS = $AUFNAHMESTATUS_OK and MANDANTID = $nPartyID and CLANID='".intval($_GET['clan'])."'");
						$row_count = mysql_fetch_array($result);
						if ($row_count[0] == 0 || $row_count[0] == "") {
							//delete clan completely!
							$row = @mysql_fetch_array(mysql_query("delete from USER_CLAN where AUFNAHMESTATUS = $AUFNAHMESTATUS_WARTEND and MANDANTID = $nPartyID and CLANID='".intval($_GET['clan'])."'"), MYSQL_ASSOC);
							$row = @mysql_fetch_array(mysql_query("delete from CLAN where MANDANTID = $nPartyID and CLANID='".intval($_GET['clan'])."'"), MYSQL_ASSOC);
						}
						echo "<p>Du bist soeben aus dem Clan &quot;".db2display($derClan)."&quot; ausgetreten.</p>";
					}
					echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18\">Zur Clanverwaltung</a></p>";
				} else {
					//##############################################
					//# Eingangs-Ansicht Clanverwaltung
					//##############################################
					
					$row = @mysql_fetch_array(mysql_query("select * from CLAN where CLANID = '".intval($inClan)."'"), MYSQL_ASSOC);
					echo "<h2>".db2display($row[NAME])."</h2>";
					
					echo "<p><table cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";
					echo "<tr><td width=\"50\">URL </td><td><a href=\"".db2display($row['URL'])."\" target=\"blank\">".db2display($row[URL])."</a></td></tr>";
					echo "<tr><td>IRC </td><td>".db2display($row[IRC_CHANNEL])."</td></tr>";
					echo "</table></p>";
					
					echo "<table width=\"100%\"><tr><td valign=\"top\">";

					echo "<table class=\"rahmen_allg\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" width=\"280\"><tr><td class=\"TNListe\" colspan=\"3\"><b>Clanmitglieder</b></td></tr>";
					$result = mysql_query("select u.LOGIN, u.USERID from USER u, USER_CLAN uc where uc.AUFNAHMESTATUS = $AUFNAHMESTATUS_OK and u.USERID = uc.USERID and uc.CLANID = '".intval($inClan)."' and uc.MANDANTID = $nPartyID");
				
					while ($row=mysql_fetch_array($result)) {
						
						echo "<tr><td class=\"TNListeTDA\">".db2display($row[LOGIN])."</td><td class=\"TNListeTDB\">";
						
						if (ACCOUNTING == "OLD") {
							// Sitzanzeige altes Accounting
						
							$result2 = mysql_query("select a.STATUS from ASTATUS a where a.USERID = $row[USERID] and a.MANDANTID = $nPartyID");
							$row_status = mysql_fetch_array($result2);
	
							if ( $row_status[STATUS] == $STATUS_BEZAHLT_LOGE) { 
								echo "<img src=\"/gfx/te_lg.gif\" alt=\"Bezahlt f&uuml;r Loge\">"; 
							} else if ( $row_status[STATUS] == $STATUS_BEZAHLT) {
								echo "<img src=\"/gfx/te_bz.gif\" alt=\"Bezahlt\">"; 
							} else if ( $row_status[STATUS] == $STATUS_ANGEMELDET) {
								echo "<img src=\"/gfx/te_an.gif\" alt=\"Angemeldet\">"; 
							} else {
								//Nicht angemeldet/bezahlt: Anmeldemöglichkeit
								echo "<a href=\"?page=18&action=anmelden&nClanmate=$row[USERID]\">Anmelden</a>";
							}
							
							// Sitzplatz
							$sql = "select s.REIHE, s.PLATZ from SITZ as s where s.USERID = $row[USERID] and s.MANDANTID = $nPartyID and (s.RESTYP= 1 or s.RESTYP = 3)";
							$row_platz = mysql_fetch_array(mysql_query($sql));
							if (isset($row_platz[REIHE])) {
							    echo "&nbsp;(".$row_platz[REIHE].'-'.$row_platz[PLATZ].')';
							}
							
						} else {
							// Sitzanzeige neues Accounting (default)
						
							// Tickets heraussuchen
							$sql = "select
								  t.ticketId,
								  t.sitzReihe,
								  t.sitzPlatz,
								  t.userId
								from
								  acc_tickets t,
								  party p
								where
								  t.partyId   = p.partyId and
								  p.aktiv     = 'J' and
								  t.userId    = '".$row['USERID']."' and
								  t.statusId  = '".ACC_STATUS_BEZAHLT."' and
								  p.mandantId = '$nPartyID'
							";
							$res = mysql_query($sql);
							$counter = 0;
							while ($rowTemp = mysql_fetch_array($res)) {
								if ($counter >= 1) {
									echo " ";
								}
								echo PELAS::formatTicketNr($rowTemp['ticketId'])."/";
								$sql = "select 
									  EBENE
									from 
									  SITZDEF
									where 
									  MANDANTID ='$nPartyID' and
									  REIHE     = '".$rowTemp['sitzReihe']."'";
								$resTemp2 = DB::query($sql);
								$rowTemp2 = mysql_fetch_array($resTemp2);
								$ebene   = $rowTemp2['EBENE'];
								echo "<a href=\"?page=13&ebene=$ebene&locateUser=".$rowTemp['userId']."\">";
								echo $rowTemp['sitzReihe']."-".$rowTemp['sitzPlatz'];
								echo "</a>";
								$counter++;
							}
							if ($counter == 0) {
								echo "(".$str[keine].")";
							}
						
						}						
						
						echo "</td><td class=\"TNListeTDA\">";
						// Überweisung weitergeben aus Sicherheitsgründen deaktiviert
						/*if ($userBezahlt = true && $row_status[STATUS] == $STATUS_ANGEMELDET && $row[USERID] != $nLoginID) {
							echo "&nbsp;"; //echo "<a href=\"clanverwaltung.php?action=ueber&nClanmate=$row[USERID]\">&Uuml;bergeben</a>";
						} else {
							echo "&nbsp;";
						}*/
						
						// Clanmember kicken
						if ($row[USERID] != $nLoginID) {
							echo "<a href=\"?page=18&action=entf&nClanmate=$row[USERID]\">kick</a>";
						} else {
							echo "&nbsp;";
						}
						
						echo "</td></tr>";
					}

					echo "</table></td><td valign=\"top\">";

					echo "<table class=\"rahmen_allg\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" width=\"230\"><tr><td class=\"TNListe\" colspan=\"2\"><b>Wartend auf Aufnahme</b></td></tr>";
					$result = mysql_query("select u.LOGIN, u.USERID from USER u, USER_CLAN uc where uc.AUFNAHMESTATUS = $AUFNAHMESTATUS_WARTEND and u.USERID = uc.USERID and uc.CLANID = '".intval($inClan)."' and uc.MANDANTID = $nPartyID");
					while ($row=mysql_fetch_array($result)) {
						echo "<tr><td class=\"TNListeTDA\">".db2display($row[LOGIN])." (<i><a href=\"?page=18&action=finaljoin&member=$row[USERID]\">aufnehmen</a></i>)";
						echo "</td></tr>";
					}

					echo "</table></td></tr></table></p>";
				
					echo "<hr><p><b>Clanrabatte</b></p>";
						//Wie viele Mebers?					
						$result = mysql_query("select count(*) from USER_CLAN where AUFNAHMESTATUS = $AUFNAHMESTATUS_OK and MANDANTID = $nPartyID and CLANID='".intval($inClan)."'");
						$row_count = mysql_fetch_array($result);
						$anzahl= $row_count[0];
					
						//Welche Rabatte werden angeboten?
						$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'RABATT5' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
						$Rabatt5 = $row[STRINGWERT];
						$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'RABATT10' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
						$Rabatt10 = $row[STRINGWERT];
						$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'RABATT15' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
						$Rabatt15 = $row[STRINGWERT];
						
						if ($Rabatt5 <= 0 && $Rabatt10 <=0 && $Rabatt15 <=0) {
							echo "<p>F&uuml;r diese Party werden keine Rabatte angeboten.</p>";
						} else {
							echo "<p>";
							if ($Rabatt5 >= 0) {
								echo "<li> ab 5 Mitglieder: $Rabatt5 %";
							}
							if ($Rabatt10 >= 0) {
								echo "<li> ab 10 Mitglieder: $Rabatt10 %";
							}
							if ($Rabatt15 >= 0) {
								echo "<li> ab 15 Mitglieder: $Rabatt15 %";
							}
							echo "</p>";
							$rabatt = "-1";
							
							if ($anzahl >= 5) {
								// Rabatte anzeigen
								echo "<p>Somit ergeben sich folgende Preise f&uuml;r Deinen Clan:</p>";
								$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_NORMAL' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
								$ParkettPreis = $row[STRINGWERT];

								$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_LOGE' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
								$LogenPreis = $row[STRINGWERT];
								
								echo "<table><tr><td class=\"TNListe\" width=\"80\"><b>Mitglieder</b></td><td class=\"TNListe\" width=\"90\"><b>Preis Parkett</b></td><td class=\"TNListe\" width=\"90\"><b>Preis Loge</b></td></tr>";
								for ($i=1;$i<=$anzahl;$i++) {
									if ($i >= 5 && $i < 10) {
										$rabatt = "RABATT5";
									} elseif ($i >= 10 && $i < 15) {
										$rabatt = "RABATT10";
									} elseif ($i >= 15) {
										$rabatt = "RABATT15";
									}
									$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = '$rabatt' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
									$RabattStufe = $row[STRINGWERT];
									
									echo "<tr><td class=\"TNListeTDA\">$i</td><td class=\"TNListeTDB\">".$i*$ParkettPreis/100*(100-$RabattStufe)." EUR</td><td class=\"TNListeTDA\">".$i*$LogenPreis/100*(100-$RabattStufe)." EUR</td></tr>";

								}
								echo "</table>";

								echo "<p>Stellt fest, mit wie vielen Mitgliedern ihr kommen m&ouml;chtet. Aus der Liste k&ouml;nnt ihr dann den zu &uuml;berweisenden Betrag";
								echo " entnehmen. Der Clanrabatt wird nur gew&auml;hrt, wenn mit einer &Uuml;berweisung zugleich f&uuml;r alle Mitglieder gezahlt wird.</p>";
								$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NAME' and MANDANTID = $nPartyID"), MYSQL_ASSOC); $config[KONTO_NAME] = $row[STRINGWERT];
								$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NUMMER' and MANDANTID = $nPartyID"), MYSQL_ASSOC); $config[KONTO_NUMMER] = $row[STRINGWERT];
								$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BLZ' and MANDANTID = $nPartyID"), MYSQL_ASSOC); $config[KONTO_BLZ] = $row[STRINGWERT];
								$row = @mysql_fetch_array(mysql_query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BANK' and MANDANTID = $nPartyID"), MYSQL_ASSOC); $config[KONTO_BANK] = $row[STRINGWERT];
								$row = @mysql_fetch_array(mysql_query("select BESCHREIBUNG, EMAIL from MANDANT where MANDANTID=$nPartyID"), MYSQL_ASSOC); $config[MANDANTNAME] = $row[BESCHREIBUNG];
								
								//zu lange Partynamen kuerzen
								if (strlen($config[MANDANTNAME]) > 12 ) {
									$UBmandant = db2display(substr( $config[MANDANTNAME], 0, 12));
								} else {
									$UBmandant = db2display($config[MANDANTNAME]);
								}
								
								echo "<p><table cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";
								echo "<tr><td width=\"120\">Verwendungszweck</td><td>".db2display($UBmandant).", ClanID $inClan ".db2display($derClan)."</td></tr>";
								echo "<tr><td>Kontoinhaber</td><td>$config[KONTO_NAME]</td></tr>";
								echo "<tr><td>Kontonummer</td><td>$config[KONTO_NUMMER]</td></tr>";
								echo "<tr><td>BLZ</td><td>$config[KONTO_BLZ]</td></tr>";
								echo "<tr><td>Bank</td><td>$config[KONTO_BANK]</td></tr>";
								echo "</table></p>";
							} else {
								echo "";
							}
						}
						
					echo "<hr><p><b>Aktionen</b></p>";
					echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18&action=off&clan=$inClan\">Aus dem Clan austreten</a><br>";
					echo "<img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=18&action=edit&clan=$inClan\">Clandaten &auml;ndern</a></p>";


					if (CFG::getMandantConfig("FANCOUNTER_AKTIV", $nPartyID) == "J") {
						?>
						
						<hr>
						<a href="http://www.northcon.de/fanprogramm.php?linkus=<?= $inClan; ?>"><img src="/countdown.php" width="135" height="116" border="0" align="right" style="margin-left: 10px; margin-top:10px;"></a>
						<p><b>Fanprogramm &quot;Link us&quot;</b></p>
						<p align="justify">Wenn ihr mit eurem Clan am Fanprogramm &quot;Link us&quot; teilnehmen möchtet, denn braucht
						ihr einfach eines unserer Banner auf eurer Website einbinden und mit nachfolgendem Link versehen. Wenn ihr dann noch in
						den Clandaten den Zähler aktiviert, taucht euer Clan in der Liste auf.
						Nachfolgend der Beispiel-Quellcode für den Countdown-Banner:</p>

<p>
<pre>
  &lt;a href="http://www.northcon.de/fanprogramm.php?linkus=<?= $inClan; ?>"&gt;
  &lt;img src="http://www.northcon.de/countdown.php" 
  width="135" height="116" border="0"&gt;&lt;/a&gt;</pre>
</p>

						<img src="gfx/headline_pfeil.png" border="0"> <a href="?page=18&action=edit&clan=$inClan">Am Fanprogramm teilnehmen (Clandaten ändern)</a><br>
						<img src="gfx/headline_pfeil.png" border="0"> <a href="/fanprogramm.php">Fanprogramm-Site</a><br>
						<img src="gfx/headline_pfeil.png" border="0"> <a href="/linkus.php">Weitere Banner</a></p>

						<p align="justify">Der Link kann auch ohne die Grafik an beliebiger Stelle eingebunden werden. Flashbanner können aus
						technischen Gründen nicht verwendet werden.</p>

						<?php
					}

						
					echo "<hr>";
					//Das Clanlogo!
					
					echo "<p><b>Clanlogo</b></p>";
					
					echo "<table><tr><td valign=\"top\">";

					displayClanPic(intval($inClan),$nPartyID);
						
					echo "</td><td valign=\"top\">$str[voraussetzungen]: <br><li>JPEG-Format <li>$str[breite](!) 220 Px <li>$str[maxhoehe] 60 Pixel <li>$str[dateigroesse] 10 KB</td></tr>";
					echo "<form method=\"post\" enctype=\"multipart/form-data\" name=\"bildupload\" action=\"?page=18\"><tr><td>$str[neuesbild]:</td><td><input type=\"file\" name=\"iUserbild\" size=\"25\">";
					echo "<input type='hidden' name='actionUpload' value='upload'>";
					echo "</td></tr><tr><td colspan=\"2\"><input type=\"submit\" value=\"$str[upload]\"></td></tr></form>";
					echo "</table>";
				}
				//#####################################
			}
		}
	}
}

//Sitzplan bei Bedarf erneuern
if ($generate_sitzplan) include_once("sitzplan_generate.php");

function ShowForm() {
	global $nPartyID, $str, $sLogin, $nLoginID, $action, $iClanName, $iURL, $iIRC_CHANNEL, $iFanprogramm;
	
	?>
	<form name="clanverwaltung" method="post" action="?page=18&action=<?=$_GET['action'];?>">
  <input type="hidden" name="go" value="true" />
	<table>
	<tr>
		<td>Clanname</td><td><input type="text" size="25" maxlength="30" name="iClanName" value="<?=$_POST['iClanName'];?>"> *</td>
	</tr><tr>
		<td>URL</td><td><input type="text" size="25" maxlength="120" name="iURL" value="<?=$_POST['iURL'];?>"></td>
	</tr><tr>
		<td>IRC-Channel</td><td><input type="text" size="25" maxlength="40" name="iIRC_CHANNEL" value="<?=$_POST['iIRC_CHANNEL'];?>"></td>
	</tr>
	<?php
	// Fanprogramm Ja/ Nein/ anzeigen
	if (CFG::getMandantConfig("FANCOUNTER_AKTIV", $nPartyID) == "J") {
		echo "<tr>\n";
		echo "<td>Teilnahme Fanprogramm</td><td><input type=\"checkbox\" name=\"iFanprogramm\" value=\"J\" ";
		if ($iFanprogramm == "J") {
			echo "checked";
		}
		echo "> **</td>\n";
		echo "</tr>\n";
	}
	?>
	<tr>
		<td colspan="2"><input type="submit" value="<?=$str[speichern]?>"></td>
	</tr>

	</table>
	</form>
	
	<?php
	if (CFG::getMandantConfig("FANCOUNTER_AKTIV", $nPartyID) == "J") {
		echo "<p>** Eklärung hierzu in der Clanverwaltung, kann noch nachträglich aktiviert werden.</p>";
	}
}

?>
