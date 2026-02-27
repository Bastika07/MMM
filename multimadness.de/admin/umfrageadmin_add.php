<?php
/* Umfrage hinzufügen */
require('controller.php');
require_once('dblib.php');
$iRecht = 'UMFRAGEADMIN';
require('checkrights.php');
require_once('admin/helpers.php');
include('admin/vorspann.php');


echo "<h1>Umfrage anlegen</h1>\n";

function show_form() {
    global $iBeschreibung, $iName, $iMandantID, $iAuswahlanzahl;
	$iAuswahlanzahl=7;
?>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>?iMandantID=<?= $iMandantID ?>" method="post">
<?= csrf_field() ?>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Umfragedetails</th>
  </tr>
  <tr class="row-0">
    <td>Interner Name:</td>
    <td><input type="text" name="iName" size="30" maxlength="50" value="<?= $iName ?>"/></td>
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
    <td><input type="submit" value="Umfrage anlegen"/></td>
  </tr>
</table>
</form>
<?php
}

$iMandantID = $_REQUEST['iMandantID'];
if ($iMandantID < 1) {
    # Mandant nicht gewählt, User einen Mandanten auswählen lassen.
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('UMFRAGEADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.
    if (! isset($_POST['iName']) ) {
	show_form();
    } else {
	if (empty($_POST['iName']) or empty($_POST['iBeschreibung'])) {
		echo '<p class="fehler">Du musst alle Felder ausfüllen.</p>' . "\n";
		show_form();
	} elseif (strlen($_POST['iBeschreibung']) > 200) {
		echo '<p class="fehler">Die Beschreibung darf maximal 200 Zeichen lang sein.</p>' . "\n";
		show_form();
	} else {
	    DB::query("
	        INSERT INTO UMFRAGE (UMFRAGE_NAME, UMFRAGE_MANDANTID, UMFRAGE_BESCHREIBUNG, UMFRAGE_AUSWAHL_ANZAHL)
		VALUES ('".safe($_POST['iName'])."', ".intval($iMandantID).", '".safe($_POST['iBeschreibung'])."', ".safe($_POST['iAuswahlanzahl']).")
		");
	    if (DB::$link->errno == 0) {
		echo '<p class="confirm">Deine Umfrage wurde erfasst. Du kannst Sie nun bearbeiten und dann freischalten.</p>' . "\n";
		echo "<p><a href=\"umfrageadmin.php?iMandantID=$iMandantID\">Zur Umfrageverwaltung</a></p>\n";
	    } else {
		echo '<p class="fehler">Datenbankfehler beim Anlegen der Umfrage.</p>' . "\n";
	    }
	}
    }
}

include('admin/nachspann.php');
?>
