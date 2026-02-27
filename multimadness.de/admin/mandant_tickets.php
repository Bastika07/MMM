<?php
require('controller.php');
require_once('dblib.php');
$iRecht = 'MANDANTADMIN';
require('checkrights.php');
include('admin/helpers.php');
include('admin/vorspann.php');

$dbh = DB::connect();

# Dirty hacks
if (isset($_POST['iTypID'])) $_GET['iTypID'] = $_POST['iTypID'];
if (isset($_POST['iPartyID'])) $_GET['iPartyID'] = $_POST['iPartyID'];
if (isset($_POST['action'])) $_GET['action'] = $_POST['action'];

function show_form_ticket_aendern() {
	global $loginID, $dbh, $dbname;
?>
<p>Die Translation für einen normalen Platz ist &quot;bezahlt&quot;, für die Loge &quot;Bezahlt Loge&quot;. Wenn die Webseite zwei Sprachen unterst&uuml;tzt, bitte ins Feld Beschreibung die englische Bezeichnung eingeben.</p>

<form method="post" action="mandant_tickets.php?iMandantID=<?= intval($_GET['iMandantID']) ?>&iPartyID=<?= intval($_GET['iPartyID']) ?>&iTypID=<?= intval($_GET['iTypID']) ?>&action=<?= htmlspecialchars($_GET['action'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
  <input type="hidden" name="iPosted" value="yes"/>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Informationen zum Ticket/Artikel eingeben</td>
  </tr>
  <tr class="row-0">
    <td>Party</td>
    <td>
<?php
	$result = DB::query("select partyId, beschreibung, terminVon from party where mandantId = '".intval($_GET['iMandantID'])."' and partyId = '".intval($_GET['iPartyID'])."' order by terminVon desc");
	$row = $result->fetch_array();
	echo db2display($row['beschreibung']) . ' - ' . dateDisplay2Short($row['terminVon']);
	echo "</td></tr>\n";
	echo "<tr class=\"row-1\"><td>Ticket/Artikel</td><td><input type=\"text\" name=\"iKurzbeschreibung\" size=\"40\" maxlength=\"250\" value=\"".htmlspecialchars($_POST['iKurzbeschreibung'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\"/> *</td></tr>\n";
	echo "<tr class=\"row-0\"><td>Englisch oder Beschreibung</td><td><input type=\"text\" name=\"iBeschreibung\" size=\"60\" maxlength=\"1500\" value=\"".htmlspecialchars($_POST['iBeschreibung'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\"/></td></tr>\n";
	echo "<tr class=\"row-1\"><td>Preis</td><td><input type=\"text\" name=\"iPreis\" size=\"30\" maxlength=\"10\" value=\"".htmlspecialchars($_POST['iPreis'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\"/> *</td></tr>\n";
	echo "<tr class=\"row-0\"><td>Anzahl</td><td><input type=\"text\" name=\"iAnzahl\" size=\"30\" maxlength=\"10\" value=\"".htmlspecialchars($_POST['iAnzahl'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\"/> *</td></tr>\n";
	echo "<tr class=\"row-1\"><td>Translation</td><td><select name=\"iTranslation\">";
	echo "<option value=\"NULL\">(keine)</option>";
	$result= DB::query('SELECT STATUSID, BESCHREIBUNG FROM STATUS order by STATUSID');
	while ($row = $result->fetch_array()) {
		echo '  <option value="' . $row['STATUSID'] . '"';
		if (($_POST['iTranslation'] == $row['STATUSID'])) {
		    echo ' selected="selected"';
		}
		echo '>' . db2display($row['BESCHREIBUNG']);
	}
?>	
      </select>
    </td>
  </tr>
  <tr class="row-0">
    <td>&nbsp;</td>
    <td><input type="submit" value="speichern"/></td>
  </tr>
</table>
</form>
<?php
}


// *****************************************************************

if ($_GET['iMandantID'] < 1) {
    # Mandant nicht gewählt.
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('MANDANTADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.

    # Sicherheitsabfrage
    if (! BenutzerHatRechtMandant('MANDANTADMIN', $_GET['iMandantID'])) {
	echo '<p class="fehler">Keine Berechtigung für ausgewählten Mandanten!</p>' . "\n";
	exit;
    }

    if ($_GET['action'] == 'delete') {
	# Ticket löschen.
	echo "<h1>Ticket löschen</h1>\n";
	if (! BenutzerHatRechtMandant('MANDANTADMIN', $_GET['iMandantID'])) {
	    echo '<p class="fehler">Keine Berechtigung!</p>' . "\n";
	} else {
	    # TODO: Löschen implementieren.
	    echo '<p class="fehler">Löschen noch nicht implementiert.</p>' . "\n";
	}
    } elseif (($_GET['action'] == 'add') or ($_GET['action'] == 'edit')) {
	# Ticket anlegen oder ändern.
	echo "<h1>Ticket anlegen/ändern</h1>\n";
	if ($_POST['iPosted'] != 'yes') {
	    if ($_GET['action'] == 'edit') {
		# Wenn Ã„nderungsmodus, dann Variablen füllen.
		$result= DB::query("SELECT * FROM acc_ticket_typ WHERE typID = '".intval($_GET['iTypID'])."'");
		$row = $result->fetch_array();
		$_POST['iKurzbeschreibung'] = $row['kurzbeschreibung'];
		$_POST['iBeschreibung'] = $row['beschreibung'];
		$_POST['iPreis'] = $row['preis'];
		$_POST['iAnzahl'] = $row['anzahlVorhanden'];
		$_POST['iTranslation'] = $row['translation'];
	    }
	    if (($_GET['action'] == 'edit') and ($_GET['iTypID'] < 1)) {
		echo '<p class="fehler">Kein Ticket ausgewählt.</p>' . "\n";
	    } else {
		show_form_ticket_aendern();
	    }
	} elseif (empty($_POST['iKurzbeschreibung']) or empty($_POST['iPreis']) ) {
	    echo '<p class="fehler">Bitte alle mit * gekennzeichneten Felder ausfüllen!</p>' . "\n";
	    show_form_ticket_aendern();
	} elseif (! BenutzerHatRechtMandant('MANDANTADMIN', $_GET['iMandantID'])) {
	    echo '<p class="fehler">Kein Recht für diesen Mandanten vorhanden!</p>' . "\n";
	    show_form_ticket_aendern();
	} else {
	    if ($_GET['action'] == 'add') {
		# Datensatz anlegen.
		$sSql = "
			INSERT INTO acc_ticket_typ (
				mandantId,
				partyId,
				kurzbeschreibung,
				beschreibung,
				preis,
				anzahlVorhanden,
				translation,
				werAngelegt,
				wannAngelegt,
				werGeaendert
			) VALUES (
				'".intval($_GET['iMandantID'])."',
				'".intval($_GET['iPartyID'])."',
				'".safe($_POST['iKurzbeschreibung'])."',
				'".safe($_POST['iBeschreibung'])."',
				'".floatval($_POST['iPreis'])."',
				'".floatval($_POST['iAnzahl'])."',
				'".intval($_POST['iTranslation'])."',
				'$loginID',
				NOW(),
				'$loginID'
			)";
	    } else {
		# Datensatz aktualisieren.
		$sSql = "
			UPDATE acc_ticket_typ SET 
				kurzbeschreibung = '".safe($_POST['iKurzbeschreibung'])."',
				beschreibung = '".safe($_POST['iBeschreibung'])."',
				preis = '".floatval($_POST['iPreis'])."',
				anzahlVorhanden = '".floatval($_POST['iAnzahl'])."',
				translation = '".$_POST['iTranslation']."',
				werGeaendert = '$loginID'
			WHERE typId = '".intval($_GET['iTypID'])."'
			";
	    }
	    $tempVar = DB::query($sSql);
	    if (DB::$link->errno > 0) {
		echo 'Fehler: '.DB::$link->errno.': '.DB::$link->error." beim einf&uuml;gen/ &auml;ndern in Tabelle acc_ticket_typ - Abbruch!<BR>";
		die;
	    }
	    echo "<p>Ticket/ Artikel angelegt/ ge&auml;ndert.</p>";
	    echo "<p><a href=\"mandant_tickets.php?iMandantID=".intval($_GET['iMandantID'])."\">Zur &Uuml;bersicht</a></p>";
	}
    } else {
	# Tickets pro Party anzeigen.
	echo "<h1>Tickets und Artikel anzeigen</h1>\n";
	$result = DB::query("SELECT * FROM party WHERE mandantId = '".intval($_GET['iMandantID'])."' ORDER BY terminBis DESC");
	while ($rowParty = $result->fetch_array()) {
		
      echo "<form name=\"partyselect\" method=\"post\" action=\"mandant_tickets.php?iMandantID=".intval($_GET['iMandantID'])."&iPartyID=".$rowParty[partyId]."\">";
	    echo "<p><table cellspacing=\"0\" cellpadding=\"0\" width=\"1000\">\n";
	    echo "<tr><td class=\"navbar\">\n";
	    echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\">\n";
	    echo "<tr><td class=\"navbar\" colspan=\"7\"><b>".db2display($rowParty['beschreibung'])." - ".dateDisplay2Short($rowParty['terminVon'])." bis ".dateDisplay2Short($rowParty['terminBis'])."</b></td></tr>\n";
	    echo "<tr><td class=\"dblau\"><b>Sel.</b></td><td class=\"dblau\"><b>ID</b></td><td class=\"dblau\"><b>Artikel</b></td><td class=\"dblau\"><b>Beschreibung</b></td><td class=\"dblau\"><b>Preis</b></td><td class=\"dblau\"><b>Anzahl</b></td><td class=\"dblau\"><b>Translation</b></td></tr>\n";

	    $result2 = DB::query("SELECT * FROM acc_ticket_typ WHERE partyId = '$rowParty[partyId]' ORDER BY translation DESC");
	    $sKlasse = 'dblau';
	    $counter = 0;
	    while ($row = $result2->fetch_array()) {
		if ($sKlasse == 'hblau') {
		    $sKlasse = 'dblau';
		} else {
		    $sKlasse = 'hblau';
		}
		if ($row[aktiv] == 'J') {
		    $sKlasse = 'red';
		}
		echo "<tr><td class=\"$sKlasse\"><input type=\"radio\" name=\"iTypID\" value=\"$row[typId]\"></td>
		  <td class=\"$sKlasse\">$row[typId]</td>
		  <td class=\"$sKlasse\">$row[kurzbeschreibung]</td>
		  <td class=\"$sKlasse\">$row[beschreibung]</td>
		  <td class=\"$sKlasse\">$row[preis]</td>
		  <td class=\"$sKlasse\">$row[anzahlVorhanden]</td>
		  <td class=\"$sKlasse\">";
		if ($row['translation'] > 0) {
		    $resultTemp= DB::query("SELECT BESCHREIBUNG FROM STATUS WHERE STATUSID = $row[translation]");
		    $rowTemp = $resultTemp->fetch_array();
		    echo db2display($rowTemp['BESCHREIBUNG']);
		} else {
		    echo '(keine)';
		}
		echo "</td>
		</tr>\n";
		$counter++;
	    }
	    if ($counter == 0) {
		echo "<tr><td class=\"hblau\" colspan=\"7\">keine Einträge vorhanden</td></tr>\n";
	    }
?>
  <tr>
    <td class="dblau" colspan="7"> 
      <select name="action">
	<option value="edit">Ticket/Artikel bearbeiten</option>
	<option value="delete">Ticket/Artikel löschen</option>
      </select>
      <input type="submit" value="=&gt;"/>
      <p><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?action=add&amp;iMandantID=<?= intval($_GET['iMandantID']); ?>&amp;iPartyID=<?= $rowParty['partyId'] ?>">Ticket/Artikel anlegen</a></p>
  </table>
    </td>
  </tr>
</table>
</p>
</form>
<?php
	}
    }
}

include('admin/nachspann.php');
?>
