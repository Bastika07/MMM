<?php
/* Umfragen */
require('controller.php');
require_once('dblib.php');
$iRecht = 'UMFRAGEADMIN';
require('checkrights.php');
require_once('admin/helpers.php');
include('admin/vorspann.php');


echo "<h1>Umfragen verwalten</h1>\n";

$iMandantID = $_REQUEST['iMandantID'];
if ($iMandantID < 1) {
    # Mandant nicht gewählt, User einen Mandanten auswählen lassen.
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('UMFRAGEADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.
    if ($_GET['action'] == 'festlegen') {
	# Als aktuelle Umfrage festlegen.
	echo "<p>Die neue Umfrage wurde als aktuell gekennzeichnet.</p>";
	echo "<p><a href='umfrageadmin.php?iMandantID=" . $iMandantID - "'>Zurück zur Liste</a></p>\n";
	DB::query( '
	    UPDATE UMFRAGE SET UMFRAGE_AKTUELL = "N"
	    WHERE UMFRAGE_MANDANTID = ' . $iMandantID );
	DB::query('
	    UPDATE UMFRAGE SET UMFRAGE_AKTUELL = "J"
	    WHERE UMFRAGE_MANDANTID = ' . $iMandantID . '
	      AND UMFRAGE_UMFRAGEID = ' . intval($_POST['nUmfrage']) );
	
} elseif ($_GET['action'] == 'edit') {
			
	# Antwortmöglichkeiten anpassen.
	function show_form() {
	    global $dbh, $iBeschreibung, $iName, $iUmfrage, $dbname, $iMandantID, $iAuswahlanzahl;
?>
<p><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= $iMandantID ?>">Zurück zur Übersicht</a></p>
<p>Beachte, dass eine Bearbeitung nach der Freigabe eine Verfälschung des Ergebnisses herbeiführt.</p>

<form method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= $iMandantID ?>&amp;action=edit&iUmfrage=<?= intval($_GET['iUmfrage']); ?>" name="Kopf">
<?= csrf_field() ?>
  <input type="hidden" name="Was" value="Kopf"/>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Umfragekopf</td>
  </tr>
  <tr class="row-0">
    <td>Interner Name:</td>
    <td><input type="text" name="iName" size="30" maxlength="100" value="<?= $iName ?>"/></td>
  </tr>
  <tr class="row-1">
    <td>Beschreibung (Frage):</td>
    <td><textarea name="iBeschreibung" wrap="virtual" cols="45" rows="2"><?= $iBeschreibung ?></textarea></td>
  </tr>
  <tr class="row-1">
    <td>Auswahlanzahl</td>
    <td><select name="iAuswahlanzahl" wrap="virtual" cols="45" rows="2">
		<option value="1" <?php if($iAuswahlanzahl==1){echo "selected";} ?>>1</option>
		<option value="2" <?php if($iAuswahlanzahl==2){echo "selected";} ?>>2</option>
		<option value="3" <?php if($iAuswahlanzahl==3){echo "selected";} ?>>3</option>
		<option value="4" <?php if($iAuswahlanzahl==4){echo "selected";} ?>>4</option>
		<option value="5" <?php if($iAuswahlanzahl==5){echo "selected";} ?>>5</option>
	</select></td>
  </tr>
  <tr class="row-0">
    <td>&nbsp;</td>
    <td><input type="submit" value="Umfrage speichern"/></td>
  </tr>
</table>
</form>

<table cellspacing="1" class="outer">
  <tr>
    <th colspan="1">Antwortmöglichkeiten</th>
  </tr>
<?php
	    $q = 'SELECT UMFVAUS_VOTENR votenr,
	                 UMFVAUS_VOTEBESCHREIBUNG beschreibung,
					 UMFVAUS_VOTEORDER orderid
	          FROM UMFVAUS
		  WHERE UMFVAUS_UMFRAGEID = ' . intval($_GET['iUmfrage']) . ' order by UMFVAUS_VOTEORDER';
	    $row_idx = 0;
	    foreach (DB::getRows($q) as $row) {
?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
	<td><?= $row['orderid'] ?></td>
    <td><?= $row['beschreibung'] ?></td>
    <td><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= $iMandantID ?>&amp;action=delete&amp;iUmfrage=<?= intval($_GET['iUmfrage']); ?>&amp;VoteNr=<?= $row['votenr'] ?>">löschen</a></td>
  </tr>
<?php
	    }
?>
</table>

<br/>

<form method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= $iMandantID ?>&amp;action=edit&iUmfrage=<?= intval($_GET['iUmfrage']); ?>" name="Body">
<?= csrf_field() ?>
<table cellspacing="1" class="outer">
  <input type="hidden" name="iName" value="<?= $iName ?>"/>
  <input type="hidden" name="iBeschreibung" value="<?= $iBeschreibung ?>"/>
  <input type="hidden" name="Was" value="Body"/>
  <tr>
    <th colspan="2">Neue Antwortmöglichkeit erstellen</th>
  </tr>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td>Beschreibung:</td>
    <td><input type="text" name="iNewVote" size="30" maxlength="100"/></td>
  </tr>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td>Index:</td>
    <td><input type="number" name="iNewVoteIndex" size="1" maxlength="20"/></td>
  </tr>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td>&nbsp;</td>
    <td><input type="submit" value="hinzufügen"/></td>
  </tr>
</table>
</form>
<?php
        }
	if (! isset($_POST['iName']) ) {
	    $q = 'SELECT *
	          FROM UMFRAGE
		  WHERE UMFRAGE_UMFRAGEID = ' . intval($_GET['iUmfrage']);
	    $row = DB::getRow($q);
	    $iName = $row['UMFRAGE_NAME'];
	    $iBeschreibung = $row['UMFRAGE_BESCHREIBUNG'];
	    $iAuswahlanzahl = $row['UMFRAGE_AUSWAHL_ANZAHL'];
	    show_form();
	} else {
	    if (($_POST['Was'] == 'Kopf') and (empty($_POST['iName']) or empty($_POST['iBeschreibung']))) {
		echo '<p class="fehler">Du musst alle Felder ausfüllen.</p>' . "\n";
		show_form();
	    } elseif (($_POST['Was'] == 'Kopf') and strlen($_POST['iBeschreibung']) > 200) {
		echo '<p class="fehler">Die Beschreibung darf maximal 200 Zeichen lang sein.</p>' . "\n";
		show_form();
	    } elseif (($_POST['Was'] == 'Body') and strlen($_POST['iNewVote']) > 100) {
		echo '<p class="fehler">Die Antowrtmöglichkeit darf maximal 100 Zeichen lang sein.</p>' . "\n";
		show_form();
	    } elseif (($_POST['Was'] == 'Body') and (empty($_POST['iNewVote']))) {
		echo '<p class="fehler">Du musst alle Felder ausfüllen.</p>' . "\n";
		show_form();
	    } else {
		if ($_POST['Was'] == 'Kopf') {
		    DB::query("UPDATE UMFRAGE SET UMFRAGE_AUSWAHL_ANZAHL = ".safe($_POST['iAuswahlanzahl']).", UMFRAGE_NAME = '".safe($_POST['iName'])."', UMFRAGE_BESCHREIBUNG = '".safe($_POST['iBeschreibung'])."' WHERE UMFRAGE_UMFRAGEID = " . intval($_GET['iUmfrage']));
		    echo '<p>Dein Umfragekopf wurde geändert. <a href="' . $_SERVER['SCRIPT_NAME'] . "?iMandantID=$iMandantID&amp;action=edit&amp;iUmfrage=".intval($_GET['iUmfrage'])."\">Weiter</a></p>\n";
		} elseif ($_POST['Was'] == 'Body') {
		    $q = "INSERT INTO UMFVAUS (UMFVAUS_VOTEBESCHREIBUNG, UMFVAUS_MANDANTID, UMFVAUS_UMFRAGEID, UMFVAUS_VOTEORDER)
			  VALUES ('".safe($_POST['iNewVote'])."', $iMandantID, ".intval($_GET['iUmfrage']).",".intval($_POST['iNewVoteIndex']).")";
		    DB::query($q);
	            echo '<p>Antwort wurde gespeichert. <a href="' . $_SERVER['SCRIPT_NAME'] . "?iMandantID=$iMandantID&amp;action=edit&amp;iUmfrage=".intval($_GET['iUmfrage'])."\">Weiter</a></p>";
		}
	    }
	}
    } elseif ($_GET['action'] == 'delete') {
	DB::query('
	    DELETE FROM UMFVAUS
	    WHERE UMFVAUS_UMFRAGEID = ' . intval($_GET['iUmfrage']) . '
	      AND UMFVAUS_VOTENR = ' . intval($_GET['VoteNr']) . '
	      AND UMFVAUS_MANDANTID = ' . intval($iMandantID));
	echo '<p class="confirm">Die Votemöglichkeit wurde gelöscht.</p>' . "\n";
	echo '<p><a href="' . $_SERVER['SCRIPT_NAME'] . '?action=edit&amp;iMandantID=' . $iMandantID . '&amp;iUmfrage=' . intval($_GET['iUmfrage']) . '">Weiter</a></p>' . "\n";
    } else {
        # Umfragenliste zeigen.
?>
<p><a href="umfrageadmin_add.php?iMandantID=<?= $iMandantID ?>">Umfrage für diesen Mandanten anlegen</a></p>

<form action="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= $iMandantID ?>&amp;action=festlegen" method="post">
<?= csrf_field() ?>
<table cellspacing="1" class="outer">
  <tr>
    <th>ID</th>
    <th>Interner Titel</th>
    <th>Beschreibung (Frage)</th>
    <th>Ergebnis</th>
    <th>Aktionen</th>
    <th>Auswahlanzahl</th>
  </tr>
<?php
        $q = 'SELECT u.UMFRAGE_UMFRAGEID id, u.UMFRAGE_NAME name,
                     u.UMFRAGE_BESCHREIBUNG beschreibung, u.UMFRAGE_AKTUELL aktuell ,
					m.REFERER referer, u.UMFRAGE_AUSWAHL_ANZAHL anzahl
	      FROM UMFRAGE u,
					MANDANT m
	      WHERE u.UMFRAGE_MANDANTID = ' . $iMandantID . '
				AND m.MANDANTID = '.intval($iMandantID).'
	      ORDER BY u.UMFRAGE_UMFRAGEID';
        $row_idx = 0;
        foreach (DB::getRows($q) as $umfrage) {
?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td><?= $umfrage['id'] ?></td>
    <td><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?action=edit&amp;iMandantID=<?= $iMandantID ?>&amp;iUmfrage=<?= $umfrage['id'] ?>"><?= $umfrage['name'] ?></a></td>
    <td><?= $umfrage['beschreibung'] ?></td>
    <td><a href="<?= $umfrage['referer']; ?>/?page=32&action=results&UmfrageID=<?= $umfrage['id'] ?>" target="_blank">Ergebnis</a></td>
    <td><input type="radio" name="nUmfrage" value="<?= $umfrage['id'] ?>"<?= ($umfrage['aktuell'] == 'J') ? ' checked="checked"' : '' ?>/></td>
	<td><?= $umfrage['anzahl'] ?></td>
  </tr>
<?php } ?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td colspan="5" style="text-align: right;"><input type="submit" value="als aktuelle Umfrage festlegen"/></td>
  </tr>
</table>
</form>
<?php
    }
}

include('admin/nachspann.php');
?>
