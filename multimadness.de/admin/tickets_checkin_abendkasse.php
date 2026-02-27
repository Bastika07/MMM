<?php
/* Checkin und Abendkasse */
require('controller.php');
$iRecht = 'EINLASSADMIN';
require('checkrights.php');
require_once('admin/helpers.php');
require_once('format.php');

$aktuellePartyID = PELAS::mandantAktuelleParty($_GET['iMandantID']);

if ($_POST['barcode'] and $_GET['iMandantID']) {
    # Barcode eingegeben; parsen und weiterleiten.
    $userID = (int) substr($_POST['barcode'], 0, 7);
    $ticketID = (int) substr($_POST['barcode'], 7);
    header(sprintf('Location: %s?iMandantID=%d&iUserId=%d&ticketID=%d',
	$_SERVER['SCRIPT_NAME'], $_GET['iMandantID'], $userID, $ticketID));
    exit;
}

include('admin/vorspann.php');

# Quick-Hack: Get-IDs in Posts umwandeln
if (isset($_GET['iUserId'])) $_POST['iUserId'] = intval($_GET['iUserId']);
if (isset($_GET['ticketID'])) $_POST['ticketID'] = intval($_GET['ticketID']);


if ($_GET['iMandantID'] < 1) {
    # Mandant nicht gewählt.
    echo "<h1>Checkin &amp; Abendkasse</h1>\n";
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('EINLASSADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.
    $aktuellePartyID = PELAS::mandantAktuelleParty($_GET['iMandantID']);
	$naechstePartyID = PELAS::mandantNextParty($_GET['iMandantID'], $aktuellePartyID);
    $nPartyID = intval($_GET['iMandantID']);
    $maxTickets = 10;

    $sql = "SELECT p.beschreibung, p.terminVon,
		   DATE_SUB(CURDATE(), INTERVAL 18 YEAR) minimumGeburtstag
	    FROM party p
	    WHERE mandantId = '".intval($_GET['iMandantID'])."'
	      AND aktiv = 'J'";
    $party = DB::getRow($sql);
    printf("<h1>Checkin für <em>%s</em> (%s)</h1>\n",
	db2display($party['beschreibung']), datedisplay2short($party['terminVon']));

    if (($_POST['formButtonEinchecken'] == 'einchecken [e]') and ($_POST['iTickets'] > 0)) {
	# Gewähltes Ticket einchecken.

	# Nur einchecken wenn Ticket bezahlt.
	$sql = "SELECT statusId
		FROM acc_tickets
		WHERE partyId = '".intval($aktuellePartyID)."'
		  AND ticketId = '".safe($_POST['iTickets'])."'";
	$res = DB::query($sql);
	$row = mysql_fetch_array($res);
	if ($row['statusId'] != ACC_STATUS_BEZAHLT) {
	    echo '<p class="fehler">Gew&auml;hltes Ticket ist nicht bezahlt.</p>' . "\n";
	} else {
	    $sql = "UPDATE acc_tickets t, party p
		    SET t.eingecheckt = 'J'
		    WHERE t.ticketId = '".intval($_POST['iTickets'])."'
		      AND p.partyId = t.partyId
		      AND p.mandantId = '".intval($_GET['iMandantID'])."'
		      AND p.aktiv = 'J'";
	    $res = DB::query($sql);
	}
	
    } elseif (($_POST['formButtonZuordnen'] == 'neu zuordnen') and ($_POST['iTickets'] > 0) and ($_POST['iZuordnung_UserId'] > 0)) {
			
	# Gewähltes Ticket neu zuordnen.

	# Nur wenn Ticket bezahlt.
	$sql = "SELECT statusId
		FROM acc_tickets
		WHERE partyId = ".intval($aktuellePartyID)."
		AND ticketId = '".intval($_POST['iTickets'])."'";
	$res = DB::query($sql);
	$rowBez = mysql_fetch_array($res);

	# Gültigkeit der UserId prüfen.
	$sql = "SELECT USERID
		FROM USER
		WHERE USERID = '".intval($_POST['iZuordnung_UserId'])."'";
	$resUser = DB::query($sql);

	# Prüfen, ob UserId schon irgendwo zugeordnet.
	$sql = "SELECT t.userId, t.ticketId
		FROM acc_tickets t, party p
		WHERE t.userId = '".intval($_POST['iZuordnung_UserId'])."'
		  AND (t.userId != t.ownerId
		    AND t.userId != '".intval($_POST['iUserId'])."')
		  AND p.partyId = t.partyId
		  AND p.mandantId = '".intval($_GET['iMandantID'])."'
		  AND p.aktiv = 'J'";
	$resZuordnung = DB::query($sql);
	if (mysql_num_rows($resUser) < 1) {
	    echo '<p class="fehler">Zuzuordnende Benutzer-ID ' . intval($_POST['iZuordnung_UserId']) . " unbekannt.</p>\n";
	} elseif (mysql_num_rows($resZuordnung) > 0) {
	    $row = mysql_fetch_array($resZuordnung);
	    echo '<p class="fehler">Benutzer-ID ' . intval($_POST['iZuordnung_UserId']) . ' ist bereits dem Ticket Nr. ' . PELAS::FormatTicketNr($row['ticketId']) . " zugeordnet.</p>\n";
	} elseif ($rowBez['statusId'] != ACC_STATUS_BEZAHLT) {
	    echo '<p class="fehler">Gew&auml;hltes Ticket ist nicht bezahlt.</p>' . "\n";
	} else {
	    # Mit Update gleichzeitig sicherstellen, dass der Updatende auch der Inhaber
	    # ist, weil nur dieser eine Neuzuordnung veranlassen darf.
	    $sql = "UPDATE acc_tickets t, party p
		    SET t.userId = '".intval($_POST['iZuordnung_UserId'])."'
		    WHERE t.ticketId = '".intval($_POST['iTickets'])."'
		      AND p.partyId = t.partyId
		      AND p.mandantId = '".intval($_GET['iMandantID'])."'
		      AND p.aktiv = 'J'
		      AND t.ownerId = '".intval($_POST['iUserId'])."'";
	    $res = DB::query($sql);
	}
    }
?>

<script type="text/javascript" src="tickets_checkin_abendkasse.js"></script>

<form name="ticketForm" action="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= intval($_GET['iMandantID']); ?>" method="post">
  <table cellspacing="1" class="outer">
    <tr>
      <th colspan="2">Teilnehmer</th>
    </tr>
    <tr class="row-0">
      <td>Barcode:</td>
      <td><input type="text" id="barcode" name="barcode" size="14" maxlength="14"/></td>
    </tr>
    <tr class="row-1">
      <td>Benutzer-ID:</td>
      <td>
        <input type="text" name="iUserId" value="<?= (isset($_POST['iUserId']) && $_POST['iUserId'] !== '') ? intval($_POST['iUserId']) : '' ?>" size="6" maxlength="10"/>
        <input type="submit" value="Tickets auflisten"/>
        <input type="button" value="User-ID suchen" onclick="openSearch1();"/>
		<input type="button" value="Abendkasse" onclick="openAbendkasse(<?= intval($_GET['iMandantID']) ?>);"/>
		<input type="button" value="Extrazuordnung" onclick="openExtrazuordnung(<?= intval($_GET['iMandantID']) ?>);"/>
		
		<?php if ($naechstePartyID > 0): ?>
			<input type="button" value="Vorverkauf" onclick="openAbendkasse(<?= intval($_GET['iMandantID']) ?>, <?= $naechstePartyID; ?>);"/>
		<?php endif; ?>
      </td>
    </tr>
    <tr class="row-0">
      <td style="vertical-align: top;">Name suchen:</td>
      <td>
        <input type="text" name="nameInput" style="width: 480px;" onkeyup="xmlhttpPost(this.value)"/><br/>
        <select size="10" id="userSelect" style="width: 480px;" onclick="iUserId.value = this.value"></select>
      </td>
    </tr>
    <tr class="row-1">
      <td style="vertical-align: top;">Tickets:</td>
      <td>
        <select name="iTickets" size="10" style="width: 580px;" onclick="document.forms.ticketForm.formButtonEinchecken.disabled=false;">
<?php
    $sql = "SELECT t.*, u.NAME, u.NACHNAME, u.LOGIN, u.SHIRTSIZE, s.sizeCode, s.sizeDesc
	    FROM acc_tickets t, party p, USER u, acc_tshirt s
	    WHERE u.USERID = t.userId
	      AND (t.ownerId = '".intval($_POST['iUserId'])."'
		OR t.userId = '".intval($_POST['iUserId'])."')
	      AND t.partyId = p.partyId
	      AND p.mandantId = '".intval($_GET['iMandantID'])."'
	      AND p.aktiv = 'J'
	      AND t.statusId != " . ACC_STATUS_STORNIERT . "
		  AND u.SHIRTSIZE = s.sizeCode
	    ORDER by t.ticketId";
    $tickets = DB::getRows($sql);
    foreach ($tickets as $ticket) {
	$addOut = "";
	$note = '';
	if ($ticket['ownerId'] != $_POST['iUserId']) {
	    $note .= '*';
	}
	if ($ticket['statusId'] != ACC_STATUS_BEZAHLT) {
	    $note .= '**';
	}
	
	// Zuordbare Extras suchen.
	$sql = "select 
				y.kurzbeschreibung,
				e.bestellId
			from
				acc_extrazuordnung e,
				acc_ticket_typ y,
				acc_bestellung b,
				party p
			where
				e.partyId = '".intval($aktuellePartyID)."' and
				e.ticketId = '".$ticket['ticketId']."' and
				
				b.bestellId = e.bestellId and
				y.translation = '".$STATUS_ZUORDBAR."' and
				
				b.ticketTypId  = y.typId and

				b.partyId  = p.partyId and
				p.aktiv = 'J' and
				p.mandantId = '".intval($_GET['iMandantID'])."'
		";
	$resTemp = DB::query($sql);
	$rowTemp = mysql_fetch_array($resTemp);
	if ($rowTemp['bestellId'] > 0) {
		$addOut = "(".substr($rowTemp['kurzbeschreibung'], 0, 24).")";
	}
	
	//$debugout.= "<pre>$sql</pre>";

	printf('<option value="%s"%s>Nr. %s%s, %s %s, %s, UID %s, Platz %s-%s, T-Shirt: %s %s %s</option>' . "\n",
	    $ticket['ticketId'],
	    (($ticketID == $ticket['ticketId'])? ' selected="selected"' : ''),
	    PELAS::FormatTicketNr($ticket['ticketId']), $note,
	    db2display($ticket['NAME']), db2display($ticket['NACHNAME']),
	    db2display($ticket['LOGIN']), $ticket['userId'],
	    $ticket['sitzReihe'], $ticket['sitzPlatz'],
		$ticket['sizeDesc'],
	    (($ticket['eingecheckt'] == 'J') ? ', (eingecheckt)' : ''), 
	    $addOut
	    );
	}
?>
        </select>
        
        
        <br/>* Dieses Ticket wurde dem Benutzer zugeordnet. Die Zuordnung kann nur vom Ticketbesitzer geändert werden.
        <br/>** Dieses Ticket wurde noch nicht bezahlt.
      </td>
    </tr>
    <tr class="row-0">
      <td>&nbsp;</td>
      <td>
	<em>Achtung:</em> Der Teilnehmer muss spätestens am <strong><?= datedisplay2short($party['minimumGeburtstag']) ?></strong> geboren sein!
        <input type="submit" name="formButtonEinchecken" value="einchecken [e]" accesskey="e"<?php if (! $ticketID) { echo ' disabled="disabled"'; } ?>/>
      </td>
    </tr>
    <tr class="row-1">
      <td>&nbsp;</td>
      <td>
        <input type="submit" name="formButtonZuordnen" value="neu zuordnen"/> auf Benutzer-ID 
        <input type="text" onchange="document.forms.ticketForm.formButtonZuordnen.disabled=false;" name="iZuordnung_UserId" value="<?= (isset($_POST['iZuordnung_UserId']) && $_POST['iZuordnung_UserId'] !== '') ? intval($_POST['iZuordnung_UserId']) : '' ?>" size="6" maxlength="10"/>
        <input type="button" value="User-ID suchen" onclick="openSearch2();"/>
      </td>
    </tr>
  </table>
<script type="text/javascript">document.getElementById('barcode').focus();</script>
</form>

<hr>

<h1>Begleiter</h1>

<form name="begleiterForm" action="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= intval($_GET['iMandantID']); ?>" method="post">
  <table cellspacing="1" class="outer">
    <tr>
      <th colspan="2">Name und Adresse</th>
    </tr>
    <tr class="row-0">
      <td>Vor- und Nachname:</td>
      <td><input name="name" type="text" size="40"/></td>
    </tr>
    <tr class="row-1">
      <td>Straße, Hausnr.:</td>
      <td><input name="address" type="text" size="40"/></td>
    </tr>
    <tr class="row-0">
      <td>PLZ:</td>
      <td><input name="plz" type="text" size="5"/></td>
    </tr>
    <tr class="row-1">
      <td>&nbsp;</td>
      <td><input type="submit" name="begleiterButton" value="einchecken"/></td>
    </tr>
  </table>
</form>

<?php
    if ($_POST['begleiterButton'] == 'einchecken') {
	# Begleiter einchecken.
	$sql = "INSERT INTO acc_gast_checkin (partyId, name, plz, address, checkedin_at)
		VALUES ('".intval($aktuellePartyID)."', '".safe($_POST['name'])."', '".safe($_POST['plz'])."', '".safe($_POST['address'])."', unix_timestamp())";
	$res = DB::query($sql);
	echo '<p>Begleiter ' . db2display($name) . " eingecheckt.</p>\n";
    }
?>

<hr/>

<?php
    # Eingecheckte
    $sql = 'SELECT COUNT(*)
	    FROM acc_tickets t
	    WHERE t.partyId = ' . intval($aktuellePartyID) . '
	      AND t.eingecheckt = "J"';
    $anzahlCheckins = DB::getOne($sql);

    # Begleiter
    $sql = 'SELECT COUNT(*)
	    FROM acc_gast_checkin
	    WHERE partyId = ' . intval($aktuellePartyID);
    $anzahlGastCheckins = DB::getOne($sql);
?>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Statistik</th>
  </tr>
  <tr class="row-0">
    <td>Eingecheckte:</td>
    <td><?= $anzahlCheckins ?></td>
  </tr>
  <tr class="row-1">
    <td>Begleiter:</td>
    <td><?= $anzahlGastCheckins ?></td>
  </tr>
</table>

<?php
}

include('admin/nachspann.php');
?>
