<?php
/* Ausstellereingang */
require('controller.php');
require_once('dblib.php');
$iRecht = 'EINLASSADMIN';
require('checkrights.php');
require_once('admin/helpers.php');

$iMandantID = $_REQUEST['iMandantID'];
$aktuellePartyID = PELAS::mandantAktuelleParty($iMandantID);

######## Prüfen ob actions gewählt sind
if ($action == "eintragen") {
	if (empty($_POST['vorname']) || empty($_POST['nachname']) || empty($_POST['organisation']) ) {
		echo "<p class='fehler'>Bitte alle Felder ausf&uuml;llen.</p>";
	} else {
		$sql = "INSERT into aussteller
			(partyId, name, nachname, organisation, typId, werAngelegt, wannAngelegt, werGeaendert)
			values
			($aktuellePartyID, '".safe($_POST['vorname'])."', '".safe($_POST['nachname'])."', '".safe($_POST['organisation'])."', '".intval($_POST['typ'])."', '".intval($loginID)."', NOW(), '".intval($loginID)."')
		";
		DB::query($sql);
		header("Location: ausstellereingang.php?iMandantID=".intval($iMandantID));
		exit;
	}
} else {
	$_POST['vorname'] = "";
	$_POST['nachname'] = "";
	$_POST['organisation'] ="";
	$_POST['typ'] = 00;
}

if ($action == 'checkinAussteller' && $ausstellerId > 0) {
	// Aussteller einchecken
	$sql = "
		UPDATE aussteller
		SET checkedIn = 'J', werGeaendert = '$loginID', wannGeaendert = now()
		WHERE ausstellerId = '$ausstellerId' and
		partyId = '$aktuellePartyID';	
	";
	DB::query($sql);
	echo '<p class="confirm">Aussteller eingecheckt</p>';
}

if ($action == 'checkinTicket' && $ticketId > 0) {
	// Spieler mit Ticket einchecken
	$sql = "
		UPDATE acc_tickets
		SET eingecheckt = 'J', werGeaendert = '$loginID', wannGeaendert = now()
		WHERE ticketId = '$ticketId' and
		partyId = '$aktuellePartyID';
	";
	DB::query($sql);
	echo '<p class="confirm">Turnierspieler eingecheckt</p>';
}
######## Ende Aktionseinheit

include('vorspann.php');

if ($iMandantID < 1) {
    # Mandant nicht gewählt.
    echo "<h1>Ausstellereingang</h1>\n";
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('EINLASSADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.
    $aktuellePartyID = PELAS::mandantAktuelleParty($iMandantID);
    $nPartyID = $iMandantID;

		 $sql = "SELECT p.beschreibung, p.terminVon,
		   DATE_SUB(CURDATE(), INTERVAL p.mindestAlter YEAR) minimumGeburtstag
	    FROM party p
	    WHERE mandantId = '".intval($iMandantID)."'
	      AND aktiv = 'J'";
    $party = DB::getRow($sql);
    printf("<h1>Ausstellereingang für <em>%s</em> (%s)</h1>\n",
	db2display($party['beschreibung']), datedisplay2short($party['terminVon']));


  # Alle Tickets und Benutzerdaten auslesen, die vom Typ BEZAHLT_TURNIERSPIELER sind

	$sql = "
		SELECT 
			t.ticketId, 
			t.eingecheckt as checkedIn,
			u.NAME,
			u.NACHNAME,
			u.PLZ as organisation,
			'Turnierspieler' as beschreibung,
			t.wannGeaendert
		FROM
			acc_tickets t,
			acc_ticket_typ y,
			USER u
		where
			t.userId = u.USERID and
			t.typId = y.typId and
			y.translation = '".$STATUS_BEZAHLT_TURNIERSPIELER."' and
			t.partyId = '$aktuellePartyID' and
			t.statusId = '".ACC_STATUS_BEZAHLT."'
	";
	$result_tickets = DB::query($sql);

	$gesamtdaten= array();
	while ($row = $result_tickets->fetch_array()) {
		array_push($gesamtdaten, $row);
	}

	$sql = "
		SELECT 
			a.ausstellerId, 
			a.checkedIn as checkedIn,
			a.NAME,
			a.NACHNAME,
			a.organisation,
			t.beschreibung,
			'-1' as ticketId,
			a.wannGeaendert
		FROM
			aussteller a,
			aussteller_typ t
		where
			a.typId = t.typId and
			a.partyId = '$aktuellePartyID'
	";
	$result_aussteller = DB::query($sql);

	while ($row = $result_aussteller->fetch_array()) {
		array_push($gesamtdaten, $row);
	}

?>

		<table cellspacing="0" cellpadding="0" border="0" width="800">
		<tr><td class="navbar">
		<table width="100%" cellspacing="1" cellpadding="3" border="0">
		<tr>
			<td class="navbar"><b>Vorname, Name</b></td>
			<td class="navbar"><b>Personengruppe</b></td>
			<td class="navbar"><b>Organisation oder PLZ</b></td>
			<td class="navbar"><b>Letzte Änderung</b></td>
			<td class="navbar"><b>Einchecken</b></td>
		</tr>

<?php

	$countIt = 0;
	$bgc = 'hblau';
	foreach($gesamtdaten AS $row) {
			echo '<tr>';
			echo '<td class="'.$bgc.'">'.db2display($row['NAME']." ".$row['NACHNAME']).'</td>';
			echo '<td class="'.$bgc.'">'.db2display($row['beschreibung']).'</td>';
			echo '<td class="'.$bgc.'">'.db2display($row['organisation']).'</td>';
			echo '<td class="'.$bgc.'">'.date("d.m.Y", strtotime($row['wannGeaendert'])).'</td>';
			echo '<td class="'.$bgc.'">';

			if ($row['checkedIn'] == 'J') {
				$addString = 'disabled="disabled"';
			} else {
				$addString = '';
			}

			if ($row['ticketId'] <= 0) {
				// Aussteller, keine Ticket-ID
				echo '<input type="button" value="Checkin '.db2display($row['beschreibung']).'" '.$addString.' OnClick="document.location.href=\'ausstellereingang.php?action=checkinAussteller&iMandantID='.$iMandantID.'&ausstellerId='.$row['ausstellerId'].'\';">';
			} else {
				// Turnierspieler mit Ticket-ID
				echo '<input type="button" value="Checkin Ticket '.PELAS::formatTicketNr($row['ticketId']).'" '.$addString.' OnClick="document.location.href=\'ausstellereingang.php?action=checkinTicket&iMandantID='.$iMandantID.'&ticketId='.$row['ticketId'].'\';">';
			}

			echo '</td>';
			echo '</tr>';
			if ($bgc == 'hblau') {
			  $bgc = 'dblau';
			} else {
			  $bgc = 'hblau';
			}
			$countIt++;
	}
	echo "<form name='eintragen' action='ausstellereingang.php?action=eintragen' method='post'>\n";
	echo "<input type='hidden' name='iMandantID' value='".$iMandantID."'>\n";
	echo "<tr><td class='$bgc'><input type='text' style='width:100px' maxlength='100' name='vorname' value='".htmlspecialchars($_POST['vorname'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."'> <input type='text' maxlength='100'  style='width:100px' name='nachname' value='".htmlspecialchars($_POST['nachname'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."'></td>\n";
	echo "<td class='$bgc'><select name='typ' width='100'>\n";
	$sql = "SELECT *
		FROM aussteller_typ
	";
	$result_aussteller_typ = DB::query($sql);
	while ($row_typ = $result_aussteller_typ->fetch_array()) {
		echo "<option value='".$row_typ['typId']."'";
		if ($_POST['typ'] == $row_typ['typId']) echo " selected";
		echo ">".phplspecialchars($row_typ['beschreibung'])."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td class='$bgc'><input type='text'  style='width:150px' name='organisation' maxlength='100' value='".htmlspecialchars($_POST['organisation'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."'></td>\n";
	echo "<td class='$bgc'  colspan='2'><input type='submit' value='Neu eintragen' name='eintragen'></td>\n";
	echo "</td></tr></form>\n";
	echo '<tr><td class="navbar" colspan="4" style="padding:10px; text-align:center;">Summe Personen Ausstellereingang: '.$countIt.'</td></tr>';
?>
	
	</table></td></tr></table>

	<p><em>Achtung:</em> Der Aussteller muss spätestens am <strong><?= datedisplay2short($party['minimumGeburtstag']) ?></strong> geboren sein!</p>
	<p>Grau hinterlegte Checkin-Buttons sind bereits eingecheckt worden.</p>


<?php
}

include('nachspann.php');
?>
