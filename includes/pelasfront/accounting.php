<?php
include_once "dblib.php";

if (isset($callFromAdmin) && $callFromAdmin == 1) {
	// Session-Krams nur aufrufen wenn nicht vom Admin aufgerufen
} else {
	include_once "session.php";
}
include_once "PHPMailer/PHPMailerAutoload.php";
include_once "format.php";
include_once "pelasfunctions.php";
include_once "language.inc.php";


// Aktuelle Party des Mandanten in Variable zwischenspeichern
if (isset($nPartyNummerID))
	$aktuellePartyID = $nPartyNummerID ;
else
	$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);


// Maximale Anzahl zu bestellender Tickets
$maxTickets = 10;

if (!isset($_GET['action']))
	$action = "";
else
	$action = $_GET['action'];

function showBestellschein()
{
	// Bestellschein
	global $action, $str, $aktuellePartyID, $maxTickets, $nPartyID, $AlteBestellAnzahl;
	?>
	
	<script language='JavaScript'>
	function ausrechnen(anz,feld,preis)
	{
		document.forms.bestellung.summe.value = parseFloat(document.forms.bestellung.summe.value) - document.getElementById(feld).value;
		wert = preis * parseFloat(document.getElementById(anz).value);
		document.getElementById(feld).value = wert.toFixed(2);
		wert2 = parseFloat(document.forms.bestellung.summe.value) + parseFloat(wert);
		document.forms.bestellung.summe.value = wert2.toFixed(2);
	}
	</script>
	
	<?php

	echo "\n<form name=\"bestellung\" action=\"".$_SERVER['REQUEST_URI']."\" method=\"post\">\n";
	echo csrf_field() . "\n";
	echo "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width=\"99%\">\n
	<tr><td class=\"header\">".$str['anzahl']."</td><td class=\"header\">$str[ticketart]</td><td class=\"header\">$str[verfuegbar]</td><td class=\"header\">$str[preis]</td><td class=\"header\">$str[summe]</td></tr>\n";
	$sql = "select *
		from acc_ticket_typ
		where partyId = $aktuellePartyID
		order by (translation IS NULL OR translation='') ASC, translation ASC, beschreibung
	";
	$res = DB::query($sql);
	$color   = "hblau";
	$counter = 0;
	$summe   = 0;
	while ($rowTemp = $res->fetch_array()) {
		
		$ticketsVerfuegbar = verfuegbareTickets($rowTemp['typId'], $aktuellePartyID);
		
		if ($ticketsVerfuegbar > 0 || $GLOBALS["anzId".$rowTemp['typId']] > 0) {
			// Nur Artikel anzeigen wenn noch welche verfügbar

			if ($ticketsVerfuegbar <= $maxTickets) {
				$limit = $ticketsVerfuegbar + $AlteBestellAnzahl[$rowTemp['typId']];
			} else {
				$limit = $maxTickets;
			}

			echo "<tr><td class=\"$color\">\n<select name=\"anzId".$rowTemp['typId']."\" id=\"anzId".$rowTemp['typId']."\" onchange=\"ausrechnen('anzId".$rowTemp['typId']."','summeId".$rowTemp['typId']."','".$rowTemp['preis']."')\">\n";
			for ($i=0; $i<=$limit; $i++) {
				echo "<option value=\"$i\" ";
				if ($GLOBALS["anzId".$rowTemp['typId']] == $i) {
					echo "selected=\"selected\"";
				}
				echo ">$i</option>\n";
			}
			echo "</select></td>\n";
			echo "<td class=\"$color\">".$rowTemp['kurzbeschreibung']."<br>
				<small>".$rowTemp['beschreibung']."</small></td>";

			echo "<td class=\"$color\">".(verfuegbareTickets($rowTemp['typId'], $aktuellePartyID))."</td>\n";

			echo "<td class=\"$color\"><nobr>".$rowTemp['preis']." EUR</nobr></td>\n";

			$position = $GLOBALS["anzId".$rowTemp['typId']] * $rowTemp['preis'];
			$summe = $summe + $position;

			echo "<td class=\"$color\"><nobr><input type=\"text\" readonly size=\"4\" value=\"".sprintf("%01.2f", $position)."\" id=\"summeId".$rowTemp['typId']."\"> EUR</nobr></td>\n";
			echo "</tr>\n\n";
			$counter++;
			if ($color == "dblau") {
				$color="hblau";
			} else {
				$color="dblau";
			}
		}
	}
	if ($counter == 0) {
		echo "<tr><td class=\"$color\" colspan=\"5\">$str[err_keinetickets]</td></tr>";
	} else {
		echo "<tr><td class=\"$color\" colspan=\"4\">$str[gsummeinkl] ".CFG::getMandantConfig("MWST-SATZ", $nPartyID)."% $str[mwst]</td>";
		echo "<td class=\"$color\"><input type=\"text\" readonly size=\"4\" value=\"".sprintf("%01.2f", $summe)."\" id=\"summe\"> EUR</td></tr>\n";
		echo "<tr><td class=\"$color\" colspan=\"5\" align=\"center\"><input type=\"submit\" value=\"$str[bestaufgeben]\"></td></tr>\n";
		echo "<input type=\"hidden\" name=\"posted\" value=\"yes\">\n";
	}

	echo "\n</table>\n</form>\n\n";
	
}


function PayPal ($summe, $bestellId, $party)
{
	global $aktuellePartyID, $nPartyID, $dbname, $dbh, $nLoginID, $config, $str;

	
	//Namen aus DB holen
	$rowTemp = DB::query("select NAME, NACHNAME from USER where USERID='$nLoginID'")->fetch_assoc(); $sRealName = $rowTemp[NAME]." ".$rowTemp[NACHNAME];

	/* Gebühren hinzurechnen */
	$summeMit = $summe + PELAS::PayPalGebuehr($summe);

	?>
	
	<table cellspacing="0" cellpadding="0" border="0">
	<tr><td>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="paypal@innovalan.de">
		<input type="hidden" name="item_name" value="<?php echo PELAS::formatBestellNr($aktuellePartyID, $bestellId) ?>">
		<input type="hidden" name="amount" value="<?=round($summeMit,2);?>">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="currency_code" value="EUR">
		<input type="image" src="https://www.paypal.com/images/x-click-but5.gif" border="0" name="submit" alt="Make payments with PayPal">
		</form>
	</td>
	</tr></table>
	
	<?php
}

// Ausgelagert um Redundanz zu verhindern 
function ÜberweisungsDatenAnzeigen($summe, $bestellId, $party) {
  
    global $str, $aktuellePartyID;
  
  	echo "<table width=\"550\" border=\"1\" style=\"border-color: #cf9790; background-color: #ffffcf; border-width: 1px; border-spacing: 4px;\">";
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"350\" colspan=\"3\" style=\"color: #7f0000; border-width: 0px;\"><small>Beg&uuml;nstigter: Name, Vorname</small></td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"350\" colspan=\"3\" style=\"border-color: #cf9790; color:#000000; background-color: #ffffFf; border-width: 1px;\">".CFG::getMandantConfig("KONTO_NAME")."</td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"350\" colspan=\"2\" style=\"color: #7f0000; border-width: 0px;\"><small>IBAN</small></td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"350\" style=\"border-color: #cf9790; background-color: #ffffFf; color:#000000; border-width: 1px;\">".CFG::getMandantConfig("KONTO_IBAN")."</td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";


  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"350\" colspan=\"2\" style=\"color: #7f0000; border-width: 0px;\"><small>BIC des Kreditinstituts</small></td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
				
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"170\" style=\"border-color: #cf9790; background-color: #ffffFf; color:#000000; border-width: 1px;\">".CFG::getMandantConfig("KONTO_BIC")."</td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
		
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"240\" colspan=\"2\" align=\"right\" style=\"color: #7f0000; border-width: 0px;\"></td>";
  	echo "<td width=\"120\" style=\"color: #7f0000; border-width: 0px;\"><small> &nbsp; Betrag</small></td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"240\" colspan=\"2\" align=\"right\" style=\"color: #7f0000; border-width: 0px;\"><small>EUR</small></td>";
  	echo "<td width=\"120\" style=\"border-color: #cf9790; background-color: #ffffFf; color:#000000; border-width: 2px;\">".str_replace (".", ",", sprintf("%01.2f", $summe))."</td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "<tr><td width=\"50\" style=\"border-width: 0px;\"></td>";
  	echo "<td width=\"350\" colspan=\"3\" style=\"color: #7f0000; border-width: 0px;\"><small>Kunden-Referenznummer - Verwendungszweck</small></td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "<tr><td width=\"50\" align=\"right\" style=\"border-width: 0px; color: #7f0000;\"><small>1:</small></td>";
  	echo "<td width=\"350\" colspan=\"3\" style=\"border-color: #cf9790; background-color: #ffffFf; color:#000000; border-width: 1px;\">".PELAS::formatBestellNr($aktuellePartyID, $bestellId)."</td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";

  	echo "<tr><td width=\"50\" align=\"right\" style=\"border-width: 0px; color: #7f0000;\"><small>2:</small></td>";
  	echo "<td width=\"350\" colspan=\"3\" style=\"border-color: #cf9790; background-color: #ffffFf; color:#000000; border-width: 1px;\">".db2display($party)."</td>";
  	echo "<td width=\"50\" style=\"border-width: 0px;\"></td></tr>";
  	
  	echo "</table><br>";
  
}



// *******************************************************

if ($_GET['action'] == "select") {
	// wir ordnen Extras einem Artikel zu
	
	// Sicherheitsabfrage: Ist der User auch inhaber der Tickets?
	$sql = "select bestellerUserId
			from acc_bestellung
			where partyId = '$aktuellePartyID'
			and bestellId = '$bestellId'
	";
	
	$resTemp = DB::query($sql);
	$rowTemp = $resTemp->fetch_array();
	
	if ($rowTemp['bestellerUserId'] != $nLoginID) {
		echo '<p class="fehler">Keine Berechtigung auf den ausgewählten Artikel!</p>';
	} else {
		// Insert oder Aktualisierung
		$sql = "insert into acc_extrazuordnung
					(partyId, bestellId, lfdNr, ticketId)
				VALUES
					($aktuellePartyID, '$bestellId', '$lfdNr', '$ticketId')
				ON DUPLICATE KEY UPDATE
					partyId = $aktuellePartyID,
					bestellId = '$bestellId',
					lfdNr = '$lfdNr',
					ticketId = '$ticketId'
				";
				
		$resTemp = DB::query($sql);

		header ("Location: ?page=6");
	}
	
} elseif ($_GET['action'] == "ticketfaq") {
	// Ticket-FAQ anzeigen
	
	echo "<p><a href=\"?page=6\">$str[ticketsverwalten]</a> <img src=\"gfx/headline_pfeil.png\" border=\"0\"> Ticket-FAQ</p>";
	
	echo $str['ticketfaq_text'];
	
} elseif ($aktuellePartyID < 1) {
	// keine aktuelle Party hinterlegt.
	echo "<p class=\"fehler\">$str[err_noparty]</p>";
} elseif (!$nLoginID) {
	// Nicht eingeloggt
	echo "<p class=\"fehler\">$str[acc_info_einloggen]</p>";
	echo "<p><a class=\"arrow\" href=\"?page=5\">$str[loginodererstellen]</a><br>";
	echo "</p>";
} else {
	// eingeloggt
	if ($_GET['action'] == "reassign") {
		// Ticket neu zuordnen, Sicherheit kommt dadurch dass immer auch $nLoginID auf ownerId abgefragt wird
		echo "<p><a href=\"?page=6\">$str[ticketsverwalten]</a> <img src=\"gfx/headline_pfeil.png\" border=\"0\"> $str[ticketneuzuordnen]</p>";
		echo "<p align=\"justify\">$str[acc_info_zuordnung]</p>";

		
		if (isset($_POST['iUserNeu']) && isset($_POST['btnReassign'])) {
			// Neuzuordnungsuser ausgesucht
			
			// prüfen ob zuordnung eröffnet
			if (CFG::getMandantConfig("TICKETZUORDNUNG_OFFEN") != "J") {
				echo "<p class=\"fehler\">$str[acc_zuordnung_zu]</p>";
			} else {

				// prüfen ob recht vorhanden
				$sql = "select
					  t.ownerId
					from 
					  acc_tickets t,
					  acc_ticket_typ y
					where 
					  t.typId    = y.typId and
					  t.ownerId  = '".intval($nLoginID)."' and
					  t.ticketId = '".intval($_GET['ticketId'])."' and
					  t.statusId = ".ACC_STATUS_BEZAHLT." and
					  y.partyId  = '".intval($aktuellePartyID)."'
				";
				$res = DB::query($sql);
				if (!$res->num_rows) {
					echo "<p class=\"fehler\">$str[acc_keinrecht]</p>";
				} else {
					// prüfen ob User vorher schon zugeordnet
					$sql = "select
						  userId
						from 
						  acc_tickets
						where 
						  userId  = '".intval($_POST['iUserNeu'])."' and
						  partyId = '".intval($aktuellePartyID)."' and
						  userId  != '".intval($nLoginID)."' and
						  statusId = '".ACC_STATUS_BEZAHLT."'
					";
					$res = DB::query($sql);
					if (!$res->num_rows) {
						// TODO: Zuordnung durchführen
						$sql = "update
							  acc_tickets
							set
							  userId = '".intval($_POST['iUserNeu'])."'
							where 
							  ownerId  = '".intval($nLoginID)."' and
							  partyId  = '".intval($aktuellePartyID)."' and
							  ticketId = '".intval($_GET['ticketId'])."'
						";
						$res = DB::query($sql);

						// Sitzreihe aktualisieren, vorher betr. reihe raussuchen
						// Nur aktualisieren wenn Ticket eine Reihe hat
						$sql = "select
							  sitzReihe
							from
							  acc_tickets
							where 
							  partyId  = '".intval($aktuellePartyID)."' and
							  ticketId = '".intval($_GET['ticketId'])."'
						";
						$res = DB::query($sql);
						if ($res->num_rows) {
							$rowTemp = $res->fetch_array();
							$tempReihe = $rowTemp['sitzReihe'];
							if ($tempReihe > 0) {
								//include_once "sitzlib.php";
								include_once("sitzplan_generate_newaccounting.php");
								GeneriereSitzplanSelektiv($tempReihe, $nPartyID, 0, $aktuellePartyID);
							}
						}

						echo "<p>$str[acc_zuordnung_erfolgreich]</p>";
					} else {
						// Ausgewählter Benutzer hat bereits Karten zugeordnet
						echo "<p class=\"fehler\">$str[acc_err_bereitszugeordnet]</p>";
					}
				}
			}
		}
		
		//Egal was vorher war, Formular ordentlich füllen
		$sql = "select
			  u.USERID,
			  u.LOGIN,
			  u.PLZ,
			  u.ORT,
			  u.LAND
			from 
			  USER u,
			  acc_tickets t,
			  acc_ticket_typ y
			where 
			  t.ownerId  = '".intval($nLoginID)."' and
			  t.ticketId = '".intval($_GET['ticketId'])."' and
			  u.USERID   = t.userId and
			  y.partyId  = '".intval($aktuellePartyID)."' and
			  t.partyId  = '".intval($aktuellePartyID)."' and
			  t.typId    = y.typId and
			  t.statusId = ".ACC_STATUS_BEZAHLT."
		";
		$res = DB::query($sql);
		if (!$res->num_rows) {
			echo "<p class=\"fehler\">$str[acc_keinrecht]</p>";
		} else {
			$rowTemp = $res->fetch_array();
			echo "<p><table cellspacing=\"1\" cellpadding=\"2\" border=\"0\">\n
				<tr><td class=\"header\" colspan=\"2\">$str[acc_aktuellez] ".PELAS::formatTicketNr($_GET['ticketId'])."</td></tr>\n";
			echo "<tr><td class=\"dblau\" width=\"90\">User-ID</td><td width=\"260\" class=\"hblau\">".$rowTemp['USERID']."</td></tr>";
			echo "<tr><td class=\"dblau\">Login</td><td class=\"hblau\">".db2display($rowTemp['LOGIN'])."</td></tr>";
			echo "<tr><td class=\"dblau\">$str[plz], $str[ort]</td><td class=\"hblau\">";
			echo PELAS::displayFlag($rowTemp['LAND']);
			echo " ".db2display($rowTemp['PLZ'])." ".db2display($rowTemp['ORT'])."</td></tr>";
			echo "<tr><td class=\"dblau\">$str[kontakt]</td><td class=\"hblau\"><a href=\"?page=17&nUserID=".$rowTemp['USERID']."\">Kontaktformular</a></td></tr>";
			echo "</table></p>";

			if (isset($_POST['iLoginID'])) {
				// Suchformular abgeschickt
				
				$sOutput = "";
				if ((empty($_POST['iLoginID']) && empty($_POST['iLogin'])) || (empty($_POST['iLoginID']) && strlen($_POST['iLogin'])<3)) {
					echo "<p class=\"fehler\">$str[acc_err_loginangeben]</p>";
				} elseif (empty($_POST['iLoginID']) && substr_count($_POST['iLogin'], '%') > (strlen($_POST['iLogin']) - 3)) {
					echo "<p class=\"fehler\">$str[acc_err_zuvielprozent]</p>";
				} elseif ($_POST['iLoginID'] < 1 && $_POST['iLoginID'] != "") {
					echo "<p class=\"fehler\">$str[acc_err_userid]</p>";
				} else {
					// gültige Suche, Ergebnisse anzeigen
					$sWhere = "";
					if ($_POST['iLoginID'] > 0){
						$sWhere .= "and u.USERID = '".intval($_POST['iLoginID'])."' ";
					}
					if ($_POST['iLogin'] != ""){
						$sWhere .= "and u.LOGIN like '%".safe($_POST['iLogin'])."%' ";
					}
					if ($_POST['iPLZ'] != ""){
						$sWhere .= "and u.PLZ = '".safe($_POST['iPLZ'])."' ";
					}
					
					$sql = "select
						  u.USERID,
						  u.LOGIN,
						  u.PLZ,
						  u.ORT,
						  u.LAND,
						  count(t.userId)  as countIt
						from 
						  ASTATUS as a,
						  USER as u
						left join
            					  acc_tickets as t on (
              					  t.userId  = u.USERID and
              					  t.partyId = '".intval($aktuellePartyID)."' and
              					  t.statusId = ".ACC_STATUS_BEZAHLT." )
						where 
						  1
						  $sWhere and
						  u.USERID    = a.USERID and
						  a.MANDANTID = '".intval($nPartyID)."'
						group by
						  u.USERID
						order by
						  u.LOGIN
					";
					$res = DB::query($sql);
					if ($res->num_rows){
						$sOutput .= "<select name=\"iUserNeu\">";
						while ($row2 = $res->fetch_array()) {
							$sOutput .= "<option value=\"".$row2['USERID']."\" ";
							  if ($iUserNeu == $row2['USERID']) {
							    $sOutput .= "selected";
							  }
							  $sOutput .= ">".
							    db2display($row2['LOGIN']).
							    " (ID: ".$row2['USERID'].
							    ", PLZ: ".db2display($row2['PLZ']).
							    ", ".$row2['countIt']."x zugeordnet)";
							  $sOutput .= "</option>";
						}
						$sOutput .= "</select>";
					}
				}
			}

			// prüfen ob zuordnung eröffnet
			if (CFG::getMandantConfig("TICKETZUORDNUNG_OFFEN") != "J") {
				echo "<p class=\"fehler\">$str[acc_zuordnung_zu]</p>";
			} else {
				// SUchformular anzeigen wenn Zuordnung offen
				echo "<p><form name=\"suchen\" method=\"post\" action=\"?page=6&action=reassign&ticketId=".intval($_GET['ticketId'])."\">";
				echo csrf_field() . "\n";

				echo "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" >\n
					<tr><td class=\"header\" colspan=\"2\">$str[acc_neuerbenutzerzuordnung]</td></tr>\n";
				echo "<tr><td class=\"dblau\" width=\"90\">User-ID</td><td width=\"290\" class=\"hblau\"><input type=\"text\" name=\"iLoginID\" value=\"".$_POST['iLoginID']."\" maxlength=\"8\" size=\"8\"></td></tr>";
				echo "<tr><td class=\"dblau\" width=\"90\">Login</td><td class=\"hblau\"><input type=\"text\" name=\"iLogin\" value=\"".$_POST['iLogin']."\" maxlength=\"100\" size=\"25\"></td></tr>";
				echo "<tr><td class=\"dblau\" width=\"90\">$str[plz]</td><td class=\"hblau\"><input type=\"text\" name=\"iPLZ\" value=\"".$_POST['iPLZ']."\" maxlength=\"5\" size=\"6\"></td></tr>";
				echo "<tr height=\"40\"><td class=\"dblau\" colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$str[acc_benutzersuchen]\"></td></tr>";
				echo "<tr><td class=\"hblau\" colspan=\"2\">";
				if ($sOutput != "") {
					echo $sOutput;
					echo "<tr height=\"40\"><td align=\"center\" class=\"dblau\" colspan=\"2\"><input name=\"btnReassign\" type=\"Submit\" value=\"$str[acc_benutzerzuordnen]\"></td></tr>";
				} else {
					echo $str[acc_keinbenutzer];
				}
				echo "</td></tr>";
				echo "</table></p></form>";
			}

		}
	} elseif ($action == "bill") {
		//Rechnung und/ oder Auftragsbestätigung anzeigen
		echo "<p><a href=\"?page=6\">$str[ticketsverwalten]</a> <img src=\"gfx/headline_pfeil.png\" border=\"0\"> $str[acc_rechnungzeigen]</p>";
		
		echo "<p align=\"justify\">$str[acc_info_rechnunzeigen] ".CFG::getMandantConfig("TICKETVERKAUF_AUTOSTORNO", $nPartyID)." $str[acc_info_rechnunzeigen2]</p>";
		
		$returnArray = showOpenBill(0, $nLoginID, $nPartyID);
		
		if ($returnArray[0] > 0) {
			// Überweisungsdaten etc. anzeigen
			echo "<hr style=\"height: 1px; width: 99%\">";
		
			echo "<h2>$str[acc_bezperueber]</h2>";
			echo "<p align=\"justify\">$str[acc_info_ueber]</p>";
			
			echo "<p>";
			
			ÜberweisungsDatenAnzeigen($returnArray[0], $returnArray[1], $returnArray[2]);
			
			
			// Nur PayPal anzeigen wenn aktiviert in config
			if (CFG::getMandantConfig("PAYPAL", $nPartyID) == "J") {
				echo "</p><hr style=\"height: 1px; width: 99%\">";
			
				echo "<h2>$str[acc_payment_paypal]</h2>";
				echo "<p align=\"justify\">$str[acc_info_paypal] <b>".PELAS::PayPalGebuehr($returnArray[0])." Euro</b></p>";
			
				PayPal ($returnArray[0], $returnArray[1], $returnArray[2]);
			
			}
		}
				
		echo "<hr style=\"height: 1px; width: 99%\">";
		
		echo "<p><table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" >\n
			<tr><td class=\"header\">$str[acc_rgvorhanden]</td></tr>\n";

		$sql = "select distinct
			  b.partyId,
			  b.bestellId,
			  b.wannAngelegt
			from 
			  acc_bestellung b,
			  acc_ticket_typ t
			where
			  t.mandantId       = '$nPartyID' and
			  b.bestellerUserId = '$nLoginID' and
			  t.typId           = b.tickettypId and
			  b.status          = '".ACC_STATUS_BEZAHLT."'
			order by
			  b.wannAngelegt desc,
			  b.bestellId desc
			";
		$res = DB::query($sql);
		$counter = 0;
		$color   = "blau";
		while ($rowTemp = $res->fetch_array()) {
			if ($color == "hblau") {
				$color = "dblau";
			} else {
				$color = "hblau";
			}
			echo "<tr><td class=\"$color\"><a href=\"?page=7&iPartyId=".$rowTemp['partyId']."&iBestellId=".$rowTemp['bestellId']."\" target=\"_blank\" class=\"inlink\">Rechnung ".PELAS::formatBestellNr($rowTemp['partyId'], $rowTemp['bestellId'])." vom ".dateDisplay2Short($rowTemp['wannAngelegt'])."</a></td></tr>";
			$counter++;
		};
		if ($counter == 0) {
			echo "<tr><td class=\"dblau\">$str[acc_info_keinrechnung]</td></tr>";
		}

		echo "</table></p>";
		
		
	} elseif ($action == "order" || $action == "edit") {
		// Bestellvorgang oder (NEU!) Ändern
		echo "<p><a href=\"?page=6\">$str[ticketsverwalten]</a> <img src=\"gfx/headline_pfeil.png\" border=\"0\"> ";
		if ($action == "order") {
			echo $str['bestaufgeben'];
		} else {
			echo $str['acc_edit_order'];

			$sql = "select b.ticketTypId,
					b.anzahl
				from acc_bestellung b,
				     acc_ticket_typ t
				where t.partyId = $aktuellePartyID
				and b.partyId = $aktuellePartyID
				and b.ticketTypId = t.typId
				and b.status = ".ACC_STATUS_OFFEN."
				and b.bestellerUserId = '$nLoginID'
				and b.partyId = ".$aktuellePartyID;

			$res = DB::query($sql);
			while ($rowTemp = $res->fetch_array()) {
				if ($posted != "yes") {
				// Prefill Form-Data if not posted
					$GLOBALS["anzId".$rowTemp['ticketTypId']] = $rowTemp['anzahl'];
				}
				// Infodatensatz immer füllen
				$AlteBestellAnzahl[$rowTemp['ticketTypId']] = $rowTemp['anzahl'];
			}
		}
		echo "</p>";
		
		echo "<p align=\"justify\">$str[acc_info_bestellung1] ".$maxTickets." $str[acc_info_bestellung2]</p>";
		
		// prüfen ob für den Besteller ein ASTATUS-Datensatz vorliegt, ansonsten erstellen
		$sql = "select count(*) as vorhanden
			from 
				ASTATUS
			where 
				MANDANTID = '$nPartyID' and
				USERID    = '$nLoginID'
		";
		$res = DB::query($sql);
		$rowTemp = $res->fetch_array();
		if ($rowTemp['vorhanden'] < 1) {
			// Nicht vorhanden, einfügen!
			$sql = "insert into ASTATUS (
					MANDANTID, 
					USERID, 
					STATUS,
					WANNANGELEGT,
					WERANGELEGT
				) values (
					'$nPartyID',
					'$nLoginID',
					'0',
					now(),
					'$nLoginID'
				)
			";
			$res = DB::query($sql);
		}
		
		// prüfen ob noch eine offene Bestellung vorliegt
		$sql = "select count(*) as vorhanden
			from acc_bestellung b,
			     acc_ticket_typ t
			where t.partyId = $aktuellePartyID
			and b.ticketTypId = t.typId
			and b.status = ".ACC_STATUS_OFFEN."
			and b.bestellerUserId = '$nLoginID'
			and b.partyId = ".$aktuellePartyID;
		
		$res = DB::query($sql);
		$rowTemp = $res->fetch_array();
		if ($rowTemp['vorhanden'] > 0 && $action == "order") {
			// es existiert noch eine offene Bestellung für diese Party
			echo "<p class=\"fehler\">$str[acc_err_bestoffen]</p>";
			echo "<p><a class=\"arrow\" href=\"".$_SERVER['REQUEST_URI']."&action=bill\">".$str['acc_rechnungzeigen']."</a><br>";
			echo "<a class=\"arrow\" href=\"".$_SERVER['REQUEST_URI']."&action=edit\">".$str['acc_edit_order']."</a></p>";
		} elseif ($rowTemp['vorhanden'] < 1 && $action == "edit") {
			// Keine Bestellung, also auch nicht änderbar
			echo "<p class=\"fehler\">Du hast keine offene Bestellung, die Du &auml;ndern kannst.</p>";
			echo "<p><a class=\"arrow\" href=\"".$_SERVER['REQUEST_URI']."&action=order\">".$str['bestaufgeben']."</a></p>";
			
		} elseif (CFG::getMandantConfig("TICKETVERKAUF_OFFEN", $nPartyID) != "J" && $callFromAdmin != 1 && !(CFG::getMandantConfig("TICKETVERKAUF_SUPPORTER", $nPartyID) == "J" && User::isSupporter($nLoginID, $aktuellePartyID) == 1) ) {
			// Ist der Ticketverkauf überhaupt eröffnet?
			echo "<p class=\"fehler\">$str[acc_err_verkaufnichtoffen]</p>";
			
		} else {			
			// Bestellvorgang
			if (isset($_POST['posted']) && $_POST['posted']== "yes") {
				// Bestellung abgeschickt, prüfen und bestätigen/ neu zeigen
				// Tickets in Array
				$sql = "select *
					from acc_ticket_typ
					where partyId = '$aktuellePartyID'
				";
				$res = DB::query($sql);
				// durchloopen und jeweils prüfen
				$counter = 0;
				$nichtVerfuegbar = 0;
				$zuViele         = 0;
				while ($rowTemp = $res->fetch_array()) {
					$bestellAnzahl = $_POST["anzId".$rowTemp['typId']];
					$counter = $counter + $bestellAnzahl;	
					
					// bei Verfügbarkeitsprüfung vorhandene Ticket berücksichtigen
					if (verfuegbareTickets($rowTemp['typId'], $aktuellePartyID) - $bestellAnzahl + $AlteBestellAnzahl[$rowTemp['typId']] < 0) {
						$nichtVerfuegbar = 1;
					}
					
					if ($maxTickets < $bestellAnzahl) {
						$zuViele = 1;
					}
				}
			
				if ($zuViele == 1) {
					// Mehr als die Maximale Bestellanzahl eines jeweiligen Postens
					echo "<p class=\"fehler\">$str[acc_err_maxtickets1] $maxTickets $str[acc_err_maxtickets2]</p>";
					showBestellschein();
				} elseif ($nichtVerfuegbar == 1) {
					// nicht für alle Positionen genug Artikel/ Tickets da
					echo "<p class=\"fehler\">$str[acc_err_nichtgenug]</p>";
					showBestellschein();
				} elseif ($counter == 0 && action == "order") {
					// Bestellschein nicht ausgefüllt
					echo "<p class=\"fehler\">$str[acc_err_bestausfuellen]</p>";
					showBestellschein();
				} else {
					// Bestellung ok
					
					// Bestellung in Datenbank einfügen
					$sql = "select *
						from acc_ticket_typ
						where partyId = $aktuellePartyID
					";
					$res = DB::query($sql);
					
					$newBestellId = 0;
					
					if ($action == "edit") {
						// bestellid aus DB ermitteln(!)
						$sql = "select b.bestellId
							from acc_bestellung b
							where b.status = ".ACC_STATUS_OFFEN."
							and b.bestellerUserId = '$nLoginID'
							and b.partyId = ".$aktuellePartyID;
						$resAnz = DB::query($sql);
						$rowTempId = $resAnz->fetch_array();
						$newBestellId = $rowTempId['bestellId'];
					}
					
					// durchloopen und wenn Artikel gewählt in bestellung einfügen / updaten
					while ($rowTemp = $res->fetch_array()) {
						$bestellAnzahl = $_POST["anzId".$rowTemp['typId']];
						
						if ($action == "order" && $bestellAnzahl > 0) {
							// Es kann im seltenen Fall passieren, dass die bestellid doppelt vergeben
							// wird. In diesem Fall gibt es einen DB-Fehler
							do {								
								// Bestellid holen, wenn noch nicht gesetzt oder ein DB-Fehler vorliegt
								if ($newBestellId < 1 || DB::$link->errno == 1062) {
									$sql = "select max(bestellId) as bestellId
										from acc_bestellung
										where partyId = ".$aktuellePartyID;
									$resId = DB::query($sql);
									$rowId = $resId->fetch_array();
									$bestellId = $rowId['bestellId'];
									$newBestellId = $bestellId + 1;
								}
								
								// Insert probieren
								$sql = "insert into acc_bestellung (
									partyId,
									bestellId,
									bestellerUserId,
									ticketTypId,
									anzahl,
									preis,
									mwstSatz,
									status,
									remoteIp,
									werAngelegt,
									wannAngelegt,
									werGeaendert
									) values (
									'$aktuellePartyID',
									'$newBestellId',
									'$nLoginID',
									'".$rowTemp['typId']."',
									'$bestellAnzahl',
									'".$rowTemp['preis']."',
									'".CFG::getMandantConfig("MWST-SATZ", $nPartyID)."',
									'".ACC_STATUS_OFFEN."',
									'".$_SERVER['REMOTE_ADDR']."',
									'$nLoginID',
									now(),
									'$nLoginID'
									)
								";
								$resId = DB::query($sql);
							} while (DB::$link->errno == 1062);
							
							// Die zugehörigen Ticket in der Tickettabelle anlegen
							// NEU: Bei Bestellung anlegen werden die Tickets nicht mehr mit angelegt!

						} elseif ($action == "edit") {
							// Andere Funktionen bei Bestellung editieren
							// Feststellen ob Position schon in der Datenbank und dann ID lesen
							$sql = "select b.bestellId,
									b.anzahl
								from acc_bestellung b,
								     acc_ticket_typ t
								where t.partyId = $aktuellePartyID
								and t.typId = '".$rowTemp['typId']."'
								and b.ticketTypId = t.typId
								and b.status = ".ACC_STATUS_OFFEN."
								and b.bestellerUserId = '$nLoginID'
								and b.partyId = ".$aktuellePartyID;
							$resAnz = DB::query($sql);
							if ($resAnz->num_rows > 0) {
								// Datensatz vorhanden
								if ($bestellAnzahl > 0) {
									// Datensatz aktualisieren mit UPDATE
									$sql = "update acc_bestellung b,
										     acc_ticket_typ t
										set b.anzahl = '$bestellAnzahl'
										where t.partyId = $aktuellePartyID
										and t.typId = '".$rowTemp['typId']."'
										and b.ticketTypId = t.typId
										and b.status = ".ACC_STATUS_OFFEN."
										and b.bestellerUserId = '$nLoginID'
										and b.partyId = ".$aktuellePartyID;
									$resNone = DB::query($sql);
								} else {
									// Datensatz löschen mit DELETE
									$sql = "delete from acc_bestellung
										where partyId = $aktuellePartyID
										and ticketTypId = '".$rowTemp['typId']."'
										and status = ".ACC_STATUS_OFFEN."
										and bestellerUserId = '$nLoginID'
										and partyId = ".$aktuellePartyID;
									$resNone = DB::query($sql);
								}
							} elseif ($bestellAnzahl > 0) {
								// bestellanzahl > 0, aber noch kein Datensatz für diesen Typ vorhanden, insert!
								// Insert in DB
								$sql = "insert into acc_bestellung (
									partyId,
									bestellId,
									bestellerUserId,
									ticketTypId,
									anzahl,
									preis,
									mwstSatz,
									status,
									remoteIp,
									werAngelegt,
									wannAngelegt,
									werGeaendert
									) values (
									'$aktuellePartyID',
									'$newBestellId',
									'$nLoginID',
									'".$rowTemp['typId']."',
									'$bestellAnzahl',
									'".$rowTemp['preis']."',
									'".CFG::getMandantConfig("MWST-SATZ", $nPartyID)."',
									'".ACC_STATUS_OFFEN."',
									'".$_SERVER['REMOTE_ADDR']."',
									'$nLoginID',
									now(),
									'$nLoginID'
									)
								";
								$resId = DB::query($sql);
							}
						}
					}
				
					if ($counter == 0 && $action == "edit") {
						// Orde gelöscht
						echo "<p align=\"justify\">Deine Bestellung wurde gel&ouml;scht.</p>";
					} else {
						// Bestätigungen bildschirm/ mail
						echo "<p align=\"justify\">$str[acc_dankebestellung] ".CFG::getMandantConfig("TICKETVERKAUF_AUTOSTORNO", $nPartyID)." $str[acc_dankebestellung2]</p>";
						sendeBestellBestaetigung($aktuellePartyID, $newBestellId, 0);
						
						// ############### WICHTIG, nur NorthCon 2010: Google-Conversion-Tracking!
						if ($aktuellePartyID == -1) {
						
							?>

<!-- Google Code for Reservierung NCW2010 Conversion Page - experimental -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1039741354;
var google_conversion_language = "de";
var google_conversion_format = "1";
var google_conversion_color = "ffffff";
var google_conversion_label = "mjG7CML48AEQquPk7wM";
var google_conversion_value = 0;
if (39) {
  google_conversion_value = 39;
}
/* ]]> */
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1039741354/?value=39&amp;label=mjG7CML48AEQquPk7wM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

							<?php
						
						}
						
						// #################### Google ende
						
					}
					
					if ($callFromAdmin == 1) {
						// Vom Admin aufgerufen und dann Link zum Freischalten angeben
						echo "<p><a class=\"arrow\" href=\"tickets_bestellungen.php?iId=$nLoginID\">Status der Bestellung &auml;ndern</a></p>";
					} else {
						// Normal im Frontend aufgerufen, Link zur Rechnung zeigen
						echo "<p><a class=\"arrow\" href=\"".$_SERVER['REQUEST_URI']."&action=bill\">$str[acc_rg_hinweise]</a></p>";
					}
					
				}
				
			} else {
				// Erster Aufruf, Bestellschein zeigen
				showBestellschein();
			}
		}
	} else {
	// Übersicht anzeigen

		// Tickets
		$sql = "select 
			  t.ticketId,
			  t.sitzReihe,
			  t.sitzPlatz,
			  s.beschreibung as StatusText,
			  s.statusId,
			  y.kurzbeschreibung,
			  y.beschreibung,
			  u.LOGIN,
			  u.USERID
			from 
			  acc_tickets t,
			  acc_ticket_bestellung_status s,
			  acc_ticket_typ y,
			  USER u
			where 
			  u.USERID   = t.userId and
			  t.ownerId  = '$nLoginID' and
			  t.partyId  = '$aktuellePartyID' and
			  t.statusId = s.statusId and
			  t.typId    = y.typId
			order by
			  t.ticketId
			";
		$resTickets = DB::query($sql);

		// Am Ende die einem zugeordneten Tickets anzeigen
		$sql = "select
			  t.ticketId,
			  t.sitzReihe,
			  t.sitzPlatz,
			  u.LOGIN,
			  u.USERID,
			  y.kurzbeschreibung,
			  y.beschreibung,
			  s.beschreibung as StatusText
			from
			  acc_tickets t,
			  acc_ticket_typ y,
			  acc_ticket_bestellung_status s,
			  party p,
			  USER u
			where
			  t.partyId   = p.partyId and
			  p.aktiv     = 'J' and
			  t.userId    = '$nLoginID' and
			  t.statusId  = '".ACC_STATUS_BEZAHLT."' and
			  p.mandantId = '$nPartyID' and
			  u.USERID    = t.ownerId and
			  y.typId     = t.typId and
			  s.statusId  = t.statusId and
			  t.ownerId   != '$nLoginID'
		";
		$resTicketsZugeordnet = DB::query($sql);
		
		if ($resTickets->num_rows > 0 || $resTicketsZugeordnet->num_rows > 0) {
			$bTicketsVorhanden = 1;
		} else {
			$bTicketsVorhanden = 0;
		}
		
		// prüfen ob eine Bestellung vorliegt
		$sql = "select count(*) as vorhanden
			from acc_bestellung b,
			     acc_ticket_typ t
			where t.partyId = $aktuellePartyID
			and b.ticketTypId = t.typId
			and b.bestellerUserId = '$nLoginID'
			and b.partyId = ".$aktuellePartyID;
		$res = DB::query($sql);
		$rowTemp = $res->fetch_array();
		if ($rowTemp['vorhanden'] > 0) {
			$bBestellungVorhanden = 1;
		} else {
			$bBestellungVorhanden = 0;
		}
		// prüfen ob eine offene Bestellung vorliegt
		$sql = "select count(*) as vorhanden
			from acc_bestellung b,
			     acc_ticket_typ t
			where t.partyId = $aktuellePartyID
			and b.ticketTypId = t.typId
			and b.bestellerUserId = '$nLoginID'
			and b.status = '".ACC_STATUS_OFFEN."'
			and b.partyId = ".$aktuellePartyID;
		$res = DB::query($sql);
		$rowTemp = $res->fetch_array();
		if ($rowTemp['vorhanden'] > 0) {
			$bOffeneBestellungVorhanden = 1;
		} else {
			$bOffeneBestellungVorhanden = 0;
		}
		
		// Verschiedene Infotexte für:
		if ($bOffeneBestellungVorhanden == 0 && $bTicketsVorhanden == 0) {
			// Keine Bestellung vorhanden		
			echo "<p align=\"justify\">".$str['acc_info_notickets']."</p>";
		
		} elseif ($bOffeneBestellungVorhanden == 1 && $bTicketsVorhanden == 0) {
			// Bestellung vorhanden aber keine Tickets, bes. Infotext und schon Tabelle anzeigen wo später Tickets reinkommen
			echo "<p align=\"justify\">".$str['acc_info_paynow']."</p>";
			
			echo "<p><table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width=\"99%\">\n
				<tr><td class=\"header\">$str[acc_ticketnr]</td><td class=\"header\">$str[acc_beschr]</td><td class=\"header\">$str[status]</td><td class=\"header\">$str[acc_zuordnung]</td><td class=\"header\">$str[sitzplatz]</td><td class=\"header\">&nbsp;</td></tr>\n";
			$color   = "hblau";
			echo "<tr><td colspan=\"6\" class=\"$color\"><a style=\"color:#DD0000; text-decoration:none;\" href=\"?page=6&action=bill\">".$str['acc_infovorhanden1']."</a><br><small>".$str['acc_infovorhanden2']."</small></td></tr>";
			echo "</table></p>";
		} elseif ($bBestellungVorhanden == 1 || $bTicketsVorhanden == 1) {
			// Bestellung oder (zugeordnete) Tickets vorhanden, Liste zeigen

			echo "<p align=\"justify\">$str[acc_info_kartenuebersicht]</p>";



			echo "<p><table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width=\"99%\">\n
				<tr><td class=\"header\">$str[acc_ticketnr]</td><td class=\"header\">$str[acc_beschr]</td><td class=\"header\">$str[status]</td><td class=\"header\">$str[acc_zuordnung]</td><td class=\"header\">$str[sitzplatz]</td><td class=\"header\">&nbsp;</td></tr>\n";
			$color   = "hblau";
			$counter = 0;
			while ($row = $resTickets->fetch_array()) {
				echo "<tr><td class=\"$color\">".PELAS::formatTicketNr($row['ticketId'])."</td>";
				echo "<td class=\"$color\">".db2display($row['kurzbeschreibung'])."<br><small>".db2display($row['beschreibung'])."</small></td>";
				echo "<td class=\"$color\">";

				if ($row['statusId'] == ACC_STATUS_BEZAHLT) {
					echo db2display($row['StatusText']);
				} else {
					echo "<a style=\"color:#DD0000; text-decoration:none;\" href=\"?page=6&action=bill\">".db2display($row['StatusText'])." *</a>";
				}

				echo "</td>";

				echo "<td class=\"$color\">";
				// Nur die Zuordnung zeigen, wenn Ticket auch den status bezahlt hat
				if ($row['statusId'] == ACC_STATUS_BEZAHLT) {
					echo "<a href=\"?page=4&nUserID=".$row['USERID']."\" class=\"inlink\">".db2display($row['LOGIN'])."</a>";
					echo " <a href=\"?page=6&action=reassign&ticketId=".$row['ticketId']."\"><img src=\"".PELASHOST."gfx/action_refresh.gif\" border=\"0\" align=\"top\" alt=\"Zuordnung &auml;ndern\" title=\"Zuordnung &auml;ndern\"></a>";
				} else {
					echo "<a style=\"color:#DD0000; text-decoration:none;\" href=\"?page=6&action=bill\">$str[acc_nichtmoeglich] *</a>";
				}
				echo "</td>";
				echo "<td class=\"$color\">";
				if ($row['sitzReihe'] > 0) {
					// Ebene rausfinden
					$sql = "select 
						  EBENE
						from 
						  SITZDEF
						where 
						  MANDANTID ='$nPartyID' and
						  REIHE     = '".$row['sitzReihe']."'";
					$resTemp = DB::query($sql);
					$rowTemp = $resTemp->fetch_array();
					$ebene   = $rowTemp['EBENE'];
					echo "<a href=\"?page=13?ebene=$ebene&iTicket=".$row['ticketId']."&locateUser=".$row['USERID']."\">";
					echo $row['sitzReihe']."-".$row['sitzPlatz'];
					echo "</a>";
				} else {
					if ($row['statusId'] == ACC_STATUS_BEZAHLT) {
			  echo " </td><td class=\"$color\"><a href=\"?	page=7&action=printTickets2&ticketId=".$row['ticketId']."&iPartyId=".$aktuellePartyID."\"><img src=\"".PELASHOST."gfx/action_print.gif\" border=\"0\" align=\"top\" alt=\"Ticket drucken\" title=\"Ticket drucken\"></a>";
					} else {
						echo "<i>(</i>$str[kein]<i>)</i>";
					}
				}	
				
//Gritzi QR COde		
//		if ($row['statusId'] == ACC_STATUS_BEZAHLT) {
// echo '</td><td class="' . $color . '">';
// echo '<img src="/qr.php?text=' . urlencode($row['ticketId']) . '" '
//    . 'alt="QR Code Ticket ' . $row['ticketId'] . '" '
//    . 'title="QR Code Ticket ' . $row['ticketId'] . '" '
//     . 'width="100" height="100">';
//
//				} else {
//					echo "</td><td class=\"$color\">&nbsp;";
//				}

				echo "</td></tr>";
				
				if ($color == "dblau") {
					$color="hblau";
				} else {
					$color="dblau";
				}
				$counter++;
			}

			$infotext = 0;
			while ($row = $resTicketsZugeordnet->fetch_array()) {
				echo "<tr><td class=\"$color\">".PELAS::formatTicketNr($row['ticketId'])." *</td>";
				echo "<td class=\"$color\">".db2display($row['kurzbeschreibung'])."<br><small>".db2display($row['beschreibung'])."</small></td>";
				echo "<td class=\"$color\">".db2display($row['StatusText'])."</td>";

				echo "<td class=\"$color\"><small>$str[von]:</small> ";
					echo "<a href=\"?page=4&nUserID=".$row['USERID']."\" class=\"inlink\">".db2display($row['LOGIN'])."</a>";
				echo "</td>";
				echo "<td class=\"$color\">";
				if ($row['sitzReihe'] > 0) {
					echo $row['sitzReihe']."-".$row['sitzPlatz'];
				} else {
					echo "<i>(</i>$str[kein]<i>)</i>";
				}			
			  echo " </td><td class=\"$color\"><a href=\"?page=7&action=printTickets2&ticketId=".$row['ticketId']."&iPartyId=".$aktuellePartyID."\"><img src=\"".PELASHOST."gfx/action_print.gif\" border=\"0\" align=\"top\" alt=\"Ticket drucken\" title=\"Ticket drucken\"></a>";

	      		echo "</td></tr>";
				$counter++;
				if ($color == "dblau") {
					$color="hblau";
				} else {
					$color="dblau";
				}
				$infotext = 1;
			}


			// Wenn noch eine offene Ticketbestellung vorliegt, dann in rot daraufhinweisen
			if ($bOffeneBestellungVorhanden == 1) {
				echo "<tr><td colspan=\"6\" class=\"$color\"><a style=\"color:#DD0000; text-decoration:none;\" href=\"?page=6&action=bill\">".$str['acc_infovorhanden1']."</a><br><small>".$str['acc_infovorhanden2']."</small></td></tr>";
			} else {
				// Nichts anzeigen
			}
			


			if ($counter == 0) {
				echo "<tr><td class=\"$color\" colspan=\"5\">$str[acc_keinetickets]</td></tr>";
			}
			echo "</table>";

			if ($infotext) {
				echo "<small>$str[acc_info_zuordnungnote]</small></p>";
			} else {
				echo "</p>";
			}

		} else {
			// Unbekannter Status
			echo "<p class=\"fehler\">Fehler: Unbekannter Status der Bestellung!</p>";
		}


		// Artikel anzeigen, nur wenn was vorhanden!
		$sql = "select 
			  b.bestellId,
			  b.anzahl,
			  s.beschreibung as StatusText,
			  y.kurzbeschreibung,
			  y.beschreibung,
			  b.status,
			  y.translation
			from 
			  acc_ticket_bestellung_status s,
			  acc_ticket_typ y,
			  acc_bestellung b
			where 
			  (y.translation is NULL or y.translation = 0 or y.translation = ".$STATUS_ZUORDBAR." or y.translation = '".$STATUS_BEZAHLT_SUPPORTERPASS."') and
			  y.partyId         = '$aktuellePartyID' and
			  b.status          = s.statusId and
			  b.ticketTypId     = y.typId and
			  b.bestellerUserId = '$nLoginID' and
			  b.partyId         = '$aktuellePartyID'
			order by
			  b.wannBezahlt
			";
		$resArtikel = DB::query($sql);
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		
		if ($resArtikel->num_rows > 0) {
			// Artikeltabelle nur anzeigen wenn welche vorhanden
			echo "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" >\n	
				<tr><td class=\"header\">$str[anzahl]</td><td class=\"header\">$str[acc_artikel]</td><td class=\"header\">$str[status], $str[acc_zuordnung]</td></tr>\n";
			$color   = "hblau";
			$counter = 0;
			while ($row = $resArtikel->fetch_array()) {
				echo "<tr><td class=\"$color\">".$row['anzahl']."</td>";
				echo "<td class=\"$color\">".db2display($row['kurzbeschreibung'])."<br><small>".db2display($row['beschreibung'])."</small></td>";
				echo "<td class=\"$color\">";
				if ($row['status'] == ACC_STATUS_OFFEN) {
					echo "<a style=\"color: #DD0000; text-decoration: none;\" href=\"?page=6&action=bill\">".db2display($row['StatusText'])." *</a>\n";
				} else {
					
					if ($row['translation'] == $STATUS_ZUORDBAR && $row['status'] == ACC_STATUS_BEZAHLT) {
						
						// Anzahl der bestellten Artikel via lfdNr berücksichtigen
						for ($i = 1; $i <= $row['anzahl']; $i++) {
							
							echo '<form method="post" action="?page=6&action=select" name="data'.$row['bestellId'].$i.'">';
							echo csrf_field() . "\n";
							
							// Zuordnungen
							$sql = "select 
				  				z.ticketId,
				  				z.lfdNr
							from 
				  				acc_extrazuordnung z
							where 
				  				z.partyId         = '$aktuellePartyID' and
								z.bestellId	      = '".$row['bestellId']."' and
								z.lfdNr           = '$i'
							";
							
							$resTemp = DB::query($sql);
							$rowTemp = $resTemp->fetch_array();
							
							// Verfügbare Tickets
							$sql = "select 
				  				t.ticketId
							from 
				  				acc_tickets t
							where 
				  				t.partyId = '$aktuellePartyID' and
								(t.ownerId = '$nLoginID' or t.userId = '$nLoginID') and
								t.statusId = '".ACC_STATUS_BEZAHLT."'
							";
							$resTempTickets = DB::query($sql);
							
							echo "<nobr>Ticket ";
							echo '<select name="ticketId">\n';
							echo "<option value='-1' selected>(-)</option>\n";	
							while ($rowTempTickets = $resTempTickets->fetch_array()) {
								echo "<option value='".$rowTempTickets['ticketId']."'";
								if ($rowTemp['ticketId'] == $rowTempTickets['ticketId']) {
									echo "selected";
								}
								echo ">".PELAS::formatTicketNr($rowTempTickets['ticketId'])."</option>\n";
							}
							echo "</select>\n";
							
							echo '<input type="hidden" name="lfdNr" value="'.$i.'">';
							echo '<input type="hidden" name="bestellId" value="'.$row['bestellId'].'">';
							
							echo " <a href='javascript: document.data".$row['bestellId'].$i.".submit();'><img border='0' align='top' src='".PELASHOST."/gfx/action_refresh.gif'></a>\n";
							
							echo "</form></nobr>\n";
						}
						
					} else {
						echo db2display($row['StatusText']);
					}
				}
				echo "</td></tr>";

				if ($color == "dblau") {
					$color="hblau";
				} else {
					$color="dblau";
				}
				$counter++;
			}
			if ($counter == 0) {
				echo "<tr><td class=\"$color\" colspan=\"3\">$str[acc_keineartikel]</td></tr>";
			}
			echo "</table>";
		}
		
		// Ende Tickets und Artikel zeigen
		
		echo "<p>\n";
		echo '<a class="arrow" href="?page=6&action=order">' . $str['acc_order_tickets'] . "</a><br>\n";
		echo '<a class="arrow" href="?page=6&action=bill">' . $str['acc_rechnungzeigen'] . "</a><br>\n";
		echo '<a class="arrow" href="?page=6&action=edit">' . $str['acc_edit_order'] . "</a><br>\n";
		echo '<a class="arrow" href="?page=6&action=ticketfaq">Ticket-FAQ</a><br>' . "\n";
		
		/* Neuer Ticketdruck kann nur 1 pro Aufruf
		if ($bTicketsVorhanden == 1) {
			printf('<a class="arrow" href="?page=7&action=printTickets&iPartyId=%s">%s</a>' . "\n",
				$aktuellePartyID, $str[acc_print_tickets]);
		} */
		echo "</p>\n";
	}
}

?>
