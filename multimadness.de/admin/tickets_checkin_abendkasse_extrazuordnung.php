<?php

ob_start();
require('controller.php');
require_once "dblib.php";
$iRecht = "ACCOUNTINGADMIN";
include "checkrights.php";
include "vorspann.php";

// Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

if ($iId < 1) {
	echo "<p class=\"fehler\">Du musst eine Benutzer-ID im vorhergehenden Formular angeben.</p>";
} else {


	if ($action == "action") {
		// Zuordnung in Datenbank
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

	}

	$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);
	
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


?>
	
	<table cellspacing="1" class="outer" width="600">
    <tr>
    <th colspan="2">Extrazuordnung User <?=$iId;?></th>
    </tr>

<?php

    $class = "row-0";
	
	// Bestellung nach Bestellung, damit die laufende Nummer nachher stimmt!
	$sql = "select 
		b.bestellId,
		b.anzahl,
		t.kurzbeschreibung
	from 
		acc_bestellung b,
		acc_ticket_typ t
	where 
		b.partyId		= '$aktuellePartyID' and
		t.typId			= b.ticketTypId and
		t.translation	= '".$STATUS_ZUORDBAR."' and
		b.bestellerUserId = '".$iId."' and
		b.STATUS		= '".ACC_STATUS_BEZAHLT."'
	";
	
	$resTempBestellung = DB::query($sql);
	
while ($rowBestellung = $resTempBestellung->fetch_array()) {
// Bestellung für Bestellung
	
	for ($i = 1; $i <= $rowBestellung['anzahl']; $i++) {
	
		// Bestellung nach Bestellung, damit die laufende Nummer nachher stimmt!
		$sql = "select 
			z.ticketId,
			z.lfdNr
		from 
			acc_extrazuordnung z
		where 
			z.partyId       = '$aktuellePartyID' and
			z.bestellId		= '".$rowBestellung['bestellId']."' and
			z.lfdnr			= '".$i."'
		";

		$resTemp = DB::query($sql);
		$row = $resTemp->fetch_array();

		echo '<tr class="'.$class.'"><td>';

		echo '<form method="post" action="tickets_checkin_abendkasse_extrazuordnung.php?action=action&iId='.$iId.'&nPartyID='.$nPartyID.'" name="data'.$rowBestellung['bestellId'].$i.'">';
		echo csrf_field() . "\n";

		echo db2display($rowBestellung['kurzbeschreibung']);
		echo "</td><td>";
		echo " <nobr>Ticket ";
		echo '<select name="ticketId">\n';
		echo "<option value='-1' selected>(-)</option>\n";	

		// Verfügbare Tickets
		$sql = "select 
			t.ticketId,
			u.NAME,
			u.NACHNAME
		from 
			acc_tickets t,
			USER u
		where 
			t.partyId  = '$aktuellePartyID' and
			t.statusId = '".ACC_STATUS_BEZAHLT."' and
			u.USERID   = t.userId
		";
		$resTempTickets = DB::query($sql);
		while ($rowTempTickets = $resTempTickets->fetch_array()) {
			echo "<option value='".$rowTempTickets['ticketId']."'";
			if ($row['ticketId'] == $rowTempTickets['ticketId']) {
				echo "selected";
			}
			echo ">".PELAS::formatTicketNr($rowTempTickets['ticketId']);
				$ausgabe = db2display($rowTempTickets['NAME'])." ".db2display($rowTempTickets['NACHNAME']);
			$ausgabe = substr($ausgabe, 0, 20);
				echo " - ".$ausgabe;
				echo "</option>\n";
		}
		echo "</select>\n";
		echo '<input type="hidden" name="lfdNr" value="'.$i.'">';
		echo '<input type="hidden" name="bestellId" value="'.$rowBestellung['bestellId'].'">';
		echo " <a href='javascript: document.data".$rowBestellung['bestellId'].$i.".submit();'><img border='0' align='top' src='".PELASHOST."/gfx/action_refresh.gif'></a>\n";
		echo "</form></nobr>\n";
		echo "</td></tr>\n";

		if ($class == "row-0") {
			$class = "row-1";
		} else {
			$class = "row-0";
		}

	}
	
}

	echo "</table>\n";

}

ob_flush();

include "nachspann.php";
?>
