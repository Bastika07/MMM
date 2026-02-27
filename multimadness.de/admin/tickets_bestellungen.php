<?php
require('controller.php');
require_once "dblib.php";
$iRecht = array("ACCOUNTINGADMIN");
include "checkrights.php";
include_once "format.php";
include_once "pelasfunctions.php";
include_once "PHPMailer/PHPMailerAutoload.php";
if (isset($_GET['action']) && $_GET['action'] == "detail")
	$menu_deactivate = true;
include "admin/vorspann.php";

if ($_GET['action'] == "detail") {
	// Detailansicht Bestellung
	?>
	
	<script language="JavaScript">
	<!--
	function relocate() {
		// Bezahlt setzen
		document.forms.statusSelect.iStatus.selectedIndex = 1;
		
		// Formular abschicken
		document.forms.statusSelect.submit();
	}
	//-->
	</script>

	<?php
	echo "<h1>Bestellung Nr. ".PELAS::formatBestellNr(intval($_GET['iPartyId']), intval($_GET['iBestellId']))."</h1>";

	// Essentielle Daten der Bestellung lesen
	$sql = "select
		  u.USERID,
		  u.LOGIN,
		  u.NAME,
		  u.NACHNAME,
		  u.STRASSE,
		  u.PLZ,
		  u.ORT,
		  u.LAND,
		  b.status,
		  b.zahlungsweiseId,
		  b.delivered,
		  t.mandantId
		from
		  USER u,
		  acc_bestellung b,
		  acc_ticket_typ t
		where
		  b.bestellId = '".intval($_GET['iBestellId'])."' and
		  u.USERID    = b.bestellerUserId and
		  t.typId     = b.ticketTypId and
		  b.partyId   = '".intval($_GET['iPartyId'])."'
		";
	$result = DB::query($sql);
	$row = $result->fetch_array();

	$_POST['bestellStatus'] = $row['status'];
	if (!isset($_POST['iZahlungsweise'])) {
		$iZahlungsweise = $row['zahlungsweiseId'];
	}

	if ($_GET['iBestellId'] < 1 || !BenutzerHatRechtMandant ("ACCOUNTINGADMIN", $row['mandantId'])) {
		// Ungültige Nr. oder kein Recht vorhanden
		echo "<p class=\fehler\">Ung&uuml;ltige Bestell-Nr. oder keine Rechte vorhanden.</p>";
	} else {
		// ok
		
		if ($_GET['detailaction'] == "status") {
			// Status ändern und meldung anzeigen
			if ($_POST['iStatus'] == ACC_STATUS_BEZAHLT && ($_POST['iStatus'] != $_POST['iOldStatus'])) {
				// neuer status bezahlt
				// Bestellung updaten mit Standard-Funktion
				BestellungFreischalten($_GET['iPartyId'], $_GET['iBestellId']);
				
			} elseif ($_POST['iStatus'] == ACC_STATUS_STORNIERT || $_POST['iStatus'] == ACC_STATUS_OFFEN) {
				// Platz löschen wenn neuer Status = storniert oder offen - Status der Tickets geht immer auf Storniert
				$sql = "update
					  acc_tickets
					set
					  sitzReihe = NULL,
					  sitzPlatz = NULL,
					  statusId = '".ACC_STATUS_STORNIERT."'
					where
					  bestellId = '".intval($_GET['iBestellId'])."' and
					  partyId   = '".intval($_GET['iPartyId'])."'
				";
				$result = DB::query($sql);
				
				// Falls Supporterpässe in der Bestellung vorhanden, ebenfalls stornieren - Status der Pässe geht immer auf Storniert
				$sql = "update
					  acc_supporterpass
					set
					  statusId = '".ACC_STATUS_STORNIERT."'
					where
					  bestellId = '".intval($_GET['iBestellId'])."' and
					  partyId   = '".intval($_GET['iPartyId'])."'
				";
				$result = DB::query($sql);

			}
			
			// Weitere Updateinfos in die Bestellung schreiben
			$sql = "update
				  acc_bestellung
				set
				  status	  = '".intval($_POST['iStatus'])."',
				  paymentMethod   = '&Uuml;berweisung',
				  delivered       = '".safe($_POST['iVerschickt'])."',
				  zahlungsweiseId = '".safe($_POST['iZahlungsweise'])."'
				where
				  bestellId = '".intval($_GET['iBestellId'])."' and
				  partyId   = '".intval($_GET['iPartyId'])."'
			";
			$result = DB::query($sql);
			
			
			// Ã„nderung des Status per Mail schicken?
			if ($_POST['iSendeMail'] == "yes") {
				sendeBestellBestaetigung(intval($_GET['iPartyId']), intval($_GET['iBestellId']), 1, $_POST['iStatus']);
			}
			
			echo "<p>Der Status der Bestellung wurde ge&auml;ndert.</p>";
			
			if ($_GET['close'] == "yes") {
			// Eigenes Fenster schlieÃŸen
			?>
				<script language="JavaScript">
				<!--
				opener.document.forms['filter'].submit();
				self.close();
				//-->
				</script>
			<?php
			}
		} else {
			// Rechnungsdaten anzeigen
			showOpenBill($_GET['iBestellId'], 0, $row['mandantId'], (isset($_GET['iPartyId']) ? $_GET['iPartyId'] : false));

			echo "<form name=\"statusSelect\" action=\"tickets_bestellungen.php?action=detail&iPartyId=".intval($_GET['iPartyId'])."&iBestellId=".intval($_GET['iBestellId'])."&detailaction=status&close=yes\" method=\"post\"><table><tr>";
			echo csrf_field() . "\n";
			if ($_POST['bestellStatus'] != ACC_STATUS_BEZAHLT) {
				echo "<td valign=\"top\" width=\"140\"><input type=\"button\" value=\"Geldeingang\" OnClick=\"javascript:relocate();\"></td>";
			}
			echo "<td valign=\"top\">Status ändern in</td>\n";
			echo "<td valign=\"top\"><select name=\"iStatus\">\n";

			if ($_POST['iStatus'] < 1) {
				$_POST['iStatus'] = $_POST['bestellStatus'];
			}
			$_POST['iOldStatus'] = $_POST['iStatus'];
			$result = DB::query("select statusId, beschreibung from acc_ticket_bestellung_status");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			while ($rowTemp = $result->fetch_array()) {
				echo "<option value=\"$rowTemp[statusId]\"";
				if (isset($_POST['iStatus']) && $_POST['iStatus'] == $rowTemp['statusId']) {echo " selected";}
				echo ">$rowTemp[beschreibung]\n";
			}

			echo "<input type=\"hidden\" name=\"iOldStatus\" value=\"".intval($_POST['iOldStatus'])."\">";

			echo "</select></td>";
			echo "<td valign=\"top\" width=\"50\"><input type=\"submit\" value=\"go\"></td>";
			echo "<td><table>";
			
				echo "<tr><td><input type=\"checkbox\" name=\"iSendeMail\" value=\"yes\" checked> User informieren</td></tr>";
				echo "<tr><td><input type=\"checkbox\" name=\"iVerschickt\" value=\"J\"";
				if ($row['delivered'] == "J") {
					echo " checked";
				}
				echo "> Bestellung verschickt</td></tr>";
			
				echo "<tr><td valign=\"top\">Zahlungsweise <select name=\"iZahlungsweise\">\n";

				if ($_POST['iZahlungsweise'] < 1) {
					if (LOCATION == "intranet") {
						$_POST['iZahlungsweise'] = ACC_ZAHLUNGSWEISE_BAR;
					} else {
						$_POST['iZahlungsweise'] = ACC_ZAHLUNGSWEISE_UEBERWEISUNG;
					}
				}
				$result = DB::query("select zahlungsweiseId, desc_german from acc_zahlungsweise");
				//echo DB::$link->errno.": ".DB::$link->error."<BR>";
				while ($rowTemp = $result->fetch_array()) {
					echo "<option value=\"$rowTemp[zahlungsweiseId]\"";
					if (isset($_POST['iZahlungsweise']) && $_POST['iZahlungsweise'] == $rowTemp['zahlungsweiseId']) {echo " selected";}
					echo ">$rowTemp[desc_german]\n";
				}

				echo "</select></td></tr>";
			
			echo "</table></td></tr></table>";
			echo "</form>";
		}
	}
	
} else {

	// Wenn wir eine Vorgabe für die User-ID bekommen, dann das Feld vorbelegen
	if (isset($_GET['Id']) && !isset($_POST['iId'])) {
		$_POST['iId'] = $_GET['Id'];
	}
	
	if (empty($_POST['sortierung'])) {
	  $_POST['sortierung'] = "WANNANGELEGT desc";
	}
	?>

	<script language="JavaScript">
	<!--
	function openBestellung(PartyId, BestellId) {
	    detail = window.open("tickets_bestellungen.php?action=detail&iPartyId="+PartyId+"&iBestellId="+BestellId,"Bestellung","width=620,height=520,locationbar=false,resize=false");
	    detail.focus();
	}
	//-->
	</script>

	<h1>Accounting: Bestellungen</h1>

	<table cellspacing="0" cellpadding="0" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">

	<form method="post" name="filter" action="tickets_bestellungen.php">
	<?= csrf_field() ?>

	<input type="hidden" name="iGo" value="yes">

	<tr><td class="navbar" colspan="6"><b>Filtereinstellungen</b></td></tr>
	<tr>
		<td class="dblau" width="70">Bestell-Nr. </td><td width="170" class="hblau"><input type="text" name="iBestellId" size=7 maxlength=14 value="<?= htmlspecialchars($_POST['iBestellId'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
		<td class="dblau" width="70">Vorname </td><td width="170" class="hblau"><input type="text" name="iName" size=25 maxlength=35 value="<?= htmlspecialchars($_POST['iName'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
		<td class="dblau">Nachname </td><td class="hblau"><input type="text" name="iNachname" size=25 maxlength=35 value="<?= htmlspecialchars($_POST['iNachname'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
	</tr><tr>
		<td class="dblau" width="60">User-ID </td><td width="170" class="hblau"><input type="text" name="iId" size=5 maxlength=10 value="<?= intval($_POST['iId'] ?? 0) ?: '' ?>"></td>
		<td class="dblau">Nur aktive Partys</td><td class="hblau">
		  <input type="radio" name="iAktive" value="J"
		    <?php
		      if ($_POST['iAktive'] != "N") {
		        echo "checked";
		      }
		    ?>
		  >Ja
		  <input type="radio" name="iAktive" value="N"
		    <?php
		      if ($_POST['iAktive'] == "N") {
		        echo "checked";
		      }
		    ?>
		  >Nein
		</td>
		<td class="dblau">Login</td><td class="hblau"><input type="text" name="iLogin" size=25 maxlength=35 value="<?= htmlspecialchars($_POST['iLogin'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
	</tr><tr>
		<td class="dblau">Status </td>
		<td class="hblau"><select name="iStatus">
		<option value="-1">Alle
		<?php
			if ($iStatus == 0) {
				$iStatus = ACC_STATUS_OFFEN;
			}
			$result = DB::query("select statusId, beschreibung from acc_ticket_bestellung_status");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			while ($row = $result->fetch_array()) {
				echo "<option value=\"$row[statusId]\"";
				if (isset($_POST['iStatus']) && $_POST['iStatus'] == $row['statusId']) {echo " selected";}
				echo ">$row[beschreibung]";
			}
		?>
		</select></td>

		<td class="dblau">Mandant</td>
		<td class="hblau"><select name="iMandant">
		<option value="-1"
		<?php
			if (isset($_POST['iMandant']) && $_POST['iMandant'] == -1) {echo " selected";}
		?>
		>Alle
		<?php
			$result = DB::query("select m.MANDANTID, m.BESCHREIBUNG from MANDANT m, RECHTZUORDNUNG r where r.MANDANTID=m.MANDANTID and r.USERID=".intval($loginID)." and r.RECHTID='ACCOUNTINGADMIN'");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			while ($row = $result->fetch_array()) {
				echo "<option value=\"$row[MANDANTID]\"";
				if (isset($_POST['iMandant']) && $_POST['iMandant'] == $row['MANDANTID']) {echo " selected";}
				echo ">$row[BESCHREIBUNG]";
			}
		?>
		</select></td>
		<td class="dblau">Sortierung </td>
		<td class="hblau"><select name="sortierung">
		<option value="bestellId" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "bestellId") {echo "selected";} ?>>Bestell-Nr.
		<option value="userId" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "userId") {echo "selected";} ?>>User-ID.		
		<option value="login" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "login") {echo "selected";} ?>>Login
		<option value="NAME" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "NAME") {echo "selected";} ?>>Nachname
		<option value="WANNANGELEGT desc" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "WANNANGELEGT desc") {echo "selected";} ?>>Bestelldatum
		</select></td>
	</tr><tr>
		<td colspan="6" class="dblau" align="center"><input type="submit" value="Bestellungen auflisten"></td>
	</tr>
	</form>
	</table></td></tr></table>

	<?php 

	if (isset($_POST['iGo']) && $_POST['iGo'] == 'yes') {

	?>
	<br>
	<table cellspacing="0" cellpadding="0" width="98%" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">
	<tr>
		<td class="navbar"><b>Best.-Nr.</b></td>
		<td class="navbar"><b>User-ID</a></b></td>
		<td class="navbar"><b>Login</a></b></td>
		<td class="navbar"><b>Vor-/Nachname</b></td>
		<td class="navbar"><b>Party</b></td>
		<td class="navbar"><b>Best.-Datum</b></td>
		<td class="navbar"><b>Status</b></td>
	</tr>
	<?php


	$sAddWhere = '';

	// Filter und default-Sortierung
	if (!empty($_POST['iLogin'])) {
	  $sAddWhere = "$sAddWhere and u.LOGIN like '%".safe($_POST['iLogin'])."%'";
	} 
	if (!empty($_POST['iName'])) {
	  $sAddWhere = "$sAddWhere and u.NAME like '%".safe($_POST['iName'])."%'";
	} 
	if (!empty($_POST['iNachname'])) {
	  $sAddWhere = "$sAddWhere and u.NACHNAME like '%".safe($_POST['iNachname'])."%'";
	} 
	if (!empty($_POST['iBestellId'])) {
	  $sAddWhere = "$sAddWhere and b.bestellId = '".PELAS::getBestellIdFromBestNr($_POST['iBestellId'])."' and b.partyId = '".PELAS::getPartyIdFromBestNr($_POST['iBestellId'])."'";
	} 
	if (!empty($_POST['iId'])) {
	  $sAddWhere = "$sAddWhere and u.USERID = '".intval($_POST['iId'])."'";
	} 
	if ($_POST['iMandant'] > 0) {
	  $sAddWhere = "$sAddWhere and t.mandantId = '".$_POST['iMandant']."'";
	}
	if ($_POST['iStatus'] >= 0) {
	  $sAddWhere = "$sAddWhere and b.status = '".$_POST['iStatus']."'";
	}
	if ($_POST['iAktive'] == "J") {
	  $sAddWhere = "$sAddWhere and (p.aktiv = 'J' OR p.terminVon > (SELECT terminVon FROM party WHERE aktiv = 'J' AND mandantId = r.MANDANTID)) ";
	}

	$sWhere = "select 
		     p.partyId,
		     b.bestellId,
		     u.USERID, 
		     u.LOGIN, 
		     u.NAME, 
		     u.NACHNAME, 
		     b.wannAngelegt,
		     b.paymentMethod,
		     b.delivered,
		     b.status,
		     b.zahlungsweiseId,
		     s.beschreibung,
		     p.beschreibung as partyname
		   from 
		     USER u,
		     RECHTZUORDNUNG r,
		     acc_bestellung b,
		     acc_ticket_bestellung_status s,
		     acc_ticket_typ t,
		     party p
		   where 
		     b.bestellerUserId = u.USERID and
		     t.typId = b.ticketTypId and
		     t.mandantId = r.MANDANTID and
		     r.RECHTID = 'ACCOUNTINGADMIN' and
		     r.USERID = '".intval($loginID)."' and
		     s.statusId = b.status and
		     p.mandantId = t.mandantId and
		     p.partyId = t.partyId and
		     b.partyId = p.partyId
		     $sAddWhere
		     $sAddWhereMandant

		   order by 
		     ".safe($_POST['sortierung']);

	$result = DB::query($sWhere);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";

	$bgc = 'hblau';

	while ($row = $result->fetch_array()) {
		echo "<tr>";
		echo "<td class=\"$bgc\"><a href=\"javascript:openBestellung('".$row['partyId']."', '".$row['bestellId']."');\">".PELAS::formatBestellNr($row['partyId'], $row['bestellId'])."</a>&nbsp;</td>";
		echo "<td class=\"$bgc\"><a href=\"benutzerdetails.php?id=".$row['USERID']."\">$row[USERID]&nbsp;</a></td>";
		echo "<td class=\"$bgc\">".db2display($row['LOGIN'])."&nbsp;</td>";
		echo "<td class=\"$bgc\">",db2display($row["NAME"])." ".db2display($row["NACHNAME"])."&nbsp;</td>";
		echo "<td class=\"$bgc\">".db2display($row['partyname'])."&nbsp;</td>";
		echo "<td class=\"$bgc\"><nobr>".dateDisplay2Short($row['wannAngelegt'])."</nobr></td>";
		echo "<td class=\"$bgc\">";
		if ($row['status'] == ACC_STATUS_BEZAHLT) {
			$sql = "select desc_german
				from acc_zahlungsweise
				where zahlungsweiseId = '".$row['zahlungsweiseId']."'";
			$resultTemp = DB::query($sql);
			$rowTemp = $resultTemp->fetch_array();
			
			echo "<a href=\"tickets_rechnung.php?iPartyId=".$row['partyId']."&iBestellId=".$row['bestellId']."\" target=\"blank\">".db2display($row['beschreibung'])."</a>";
			echo " <small>(".db2display($rowTemp['desc_german']);
			if ($row['delivered'] == "J") {
				echo ", verschickt";
			}
			echo ")</small>";
		} else {
			echo db2display($row['beschreibung']);
		}
		
		echo "&nbsp;</td>";
		echo "</tr>\n";
		if ($bgc == 'hblau')
		  $bgc = 'dblau';
		else 
		  $bgc = 'hblau';
	}

	}

	?>

	</table></td></tr></table>

	<?php

}

include "admin/nachspann.php";
?>
