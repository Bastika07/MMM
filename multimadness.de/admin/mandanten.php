<?php
require('controller.php');
require_once('dblib.php');
$iRecht = 'MANDANTADMIN';
require_once('checkrights.php');
include('admin/vorspann.php');


function show_form_neu() {
?>
<form method="post" action="mandanten.php?iAction=<?= htmlspecialchars($_GET['iAction'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>&amp;nMandantID=<?= intval($_GET['nMandantID']) ?>">
  <input type="hidden" name="iPosted" value="yes"/>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Mandant anlegen/bearbeiten</th>
  </tr>
  <tr class="row-0">
    <td>Interner Name/Beschreibung:</td>
    <td><input type="text" name="iBeschreibung" size="30" maxlength="80" value="<?= htmlspecialchars($_POST['iBeschreibung'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Firma:</td>
    <td><input type="text" name="iFirma" size="30" maxlength="80" value="<?= htmlspecialchars($_POST['iFirma'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>Kontaktperson:</td>
    <td><input type="text" name="iKontaktperson" size="30" maxlength="80" value="<?= htmlspecialchars($_POST['iKontaktperson'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>StraÃŸe:</td>
    <td><input type="text" name="iStrasse" size="30" maxlength="80" value="<?= htmlspecialchars($_POST['iStrasse'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-0">
    <td>PLZ:</td>
    <td><input type="text" name="iPLZ" size="11" maxlength="10" value="<?= htmlspecialchars($_POST['iPLZ'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Ort:</td>
    <td><input type="text" name="iOrt" size="30" maxlength="80" value="<?= htmlspecialchars($_POST['iOrt'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-0">
    <td>Telefon:</td>
    <td><input type="text" name="iTelefon" size="30" maxlength="40" value="<?= htmlspecialchars($_POST['iTelefon'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Telefon 2:</td>
    <td><input type="text" name="iTelefon2" size="30" maxlength="40" value="<?= htmlspecialchars($_POST['iTelefon2'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>Fax:</td>
    <td><input type="text" name="iFax" size="30" maxlength="40" value="<?= htmlspecialchars($_POST['iFax'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-1">
    <td>E-Mail:</td>
    <td><input type="text" name="iEmail" size="30" maxlength="100" value="<?= htmlspecialchars($_POST['iEmail'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Newsletter Antwortadresse:</td>
    <td><input type="text" name="iAntwort" size="30" maxlength="100" value="<?= htmlspecialchars($_POST['iAntwort'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-0">
    <td>Referer (Domain inkl. http://):</td>
    <td><input type="text" name="iReferer" size="30" maxlength="80" value="<?= htmlspecialchars($_POST['iReferer'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Steuernummer:</td>
    <td><input type="text" name="iSteuernummer" size="20" maxlength="20" value="<?= htmlspecialchars($_POST['iSteuernummer'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>Handelsregister:</td>
    <td><input type="text" name="iHandelsregister" size="30" maxlength="50" value="<?= htmlspecialchars($_POST['iHandelsregister'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-1">
    <td>IRC-Channel:</td>
    <td><input type="text" name="iIRC" size="30" maxlength="255" value="<?= htmlspecialchars($_POST['iIRC'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-0">
    <td>Interner Kommentar:</td>
    <td><input type="text" name="iKommentar" size="30" maxlength="200" value="<?= htmlspecialchars($_POST['iKommentar'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-1">
    <td>&nbsp;</td>
    <td><input type="submit" value="Mandant speichern"/></td>
  </tr>
</table>
</form>
<?php
}

if (($_GET['iAction'] == 'neu') or ($_GET['iAction'] == 'edit')) {

	$dbh = DB::connect();

	if ($_GET['iAction'] == 'neu') {
		echo "<h1>Mandant anlegen</h1>";
	} else {
		echo "<h1>Mandant bearbeiten</h1>";
		if ($_POST['iPosted'] != 'yes') {
			$result= DB::query('SELECT * FROM MANDANT WHERE MANDANTID = ' . intval($_GET['nMandantID']));
			$row = $result->fetch_array();
			$_POST['iBeschreibung']    = $row['BESCHREIBUNG'];
			$_POST['iFirma']	          = $row['FIRMA'];
			$_POST['iKontaktperson']   = $row['KONTAKTPERSON'];
			$_POST['iStrasse']         = $row['STRASSE'];
			$_POST['iPLZ']             = $row['PLZ'];
			$_POST['iOrt']             = $row['ORT'];
			$_POST['iTelefon']         = $row['TELEFON'];
			$_POST['iTelefon2']        = $row['TELEFON2'];
			$_POST['iFax']             = $row['FAX'];
			$_POST['iEmail']           = $row['EMAIL'];
			$_POST['iAntwort']		  = $row['MAILANTWORTADRESSE'];
			$_POST['iReferer']         = $row['REFERER'];
			$_POST['iIRC']		  = $row['IRC'];
			$_POST['iKommentar']       = $row['KOMMENTAR_INTERN'];
			$_POST['iSteuernummer']    = $row['STEUERNUMMER'];
			$_POST['iHandelsregister'] = $row['HANDELSREGISTER'];
		}
	}
	
	if ($_POST['iPosted'] != 'yes') {
		show_form_neu();
	} elseif ( empty($_POST['iBeschreibung']) or empty($_POST['iKontaktperson']) or empty($_POST['iStrasse']) or empty($_POST['iPLZ']) or empty($_POST['iOrt']) or empty($_POST['iTelefon']) or empty($_POST['iEmail']) or empty($_POST['iReferer']) ) {
		echo "<p class=\"fehler\">Bitte alle mit * gekennzeichneten Felder ausfüllen!</p>\n";
		show_form_neu();
	} else {
		if ($_GET['iAction'] == 'neu') {
			$result= DB::query("INSERT INTO MANDANT 
					(BESCHREIBUNG, FIRMA, KONTAKTPERSON, STRASSE, PLZ, ORT, TELEFON, TELEFON2, FAX, EMAIL, REFERER, IRC, KOMMENTAR_INTERN, STEUERNUMMER, HANDELSREGISTER, MAILANTWORTADRESSE, WANNANGELEGT, WERANGELEGT, WERGEAENDERT) 
					VALUES (
					'".safe($_POST['iBeschreibung'])."', 
					'".safe($_POST['iFirma'])."',
					 '".safe($_POST['iKontaktperson'])."', 
					  '".safe($_POST['iStrasse'])."', 
						 '".safe($_POST['iPLZ'])."', 
						  '".safe($_POST['iOrt'])."', 
							 '".safe($_POST['iTelefon'])."', 
							 '".safe($_POST['iTelefon2'])."', 
							  '".safe($_POST['iFax'])."', 
								 '".safe($_POST['iEmail'])."', 
								  '".safe($_POST['iReferer'])."', 
									 '".safe($_POST['iIRC'])."', 
									  '".safe($_POST['iKommentar'])."', 
										 '".safe($_POST['iSteuernummer'])."', 
										  '".safe($_POST['iHandelsregister'])."', 
											 '".safe($_POST['iAntwort'])."', 
											  NOW(), $loginID, $loginID) ");
			echo "<p>Mandant angelegt.</p>\n";
		} else {
			$result= DB::query("UPDATE MANDANT SET 
				BESCHREIBUNG='".safe($_POST['iBeschreibung'])."', 
				FIRMA='".safe($_POST['iFirma'])."',   
				KONTAKTPERSON='".safe($_POST['iKontaktperson'])."',   
				STRASSE='".safe($_POST['iStrasse'])."',  
				PLZ='".safe($_POST['iPLZ'])."',  
				ORT='".safe($_POST['iOrt'])."',  
				TELEFON='".safe($_POST['iTelefon'])."',  
				TELEFON2='".safe($_POST['iTelefon2'])."',  
				FAX='".safe($_POST['iFax'])."', 
				EMAIL='".safe($_POST['iEmail'])."', 
				REFERER='".safe($_POST['iReferer'])."', 
				IRC='".safe($_POST['iIRC'])."',  
				KOMMENTAR_INTERN='".safe($_POST['iKommentar'])."',  
				STEUERNUMMER='".safe($_POST['iSteuernummer'])."',  
				HANDELSREGISTER='".safe($_POST['iHandelsregister'])."', 
				MAILANTWORTADRESSE='".safe($_POST['iAntwort'])."', 
				WERGEAENDERT='".intval($loginID)."' 
			where MANDANTID='".intval($_GET['nMandantID'])."'");
			echo "<p>Mandant geändert.</p>\n";
			## echo DB::$link->errno . ': ' . DB::$link->error . '<br/>';
		}
	}
} else {
    # Mandanten auflisten.
    $q = 'SELECT MANDANTID id, BESCHREIBUNG name
          FROM MANDANT';
    $mandanten = DB::getRows($q);
?>
<h1>Mandanten verwalten</h1>
<p><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?iAction=neu">Mandant hinzufügen</a></p>

<table cellspacing="1" class="outer">
  <tr>
    <th>ID</th>
    <th>Interner Name</th>
    <th>Aktionen</th>
  </tr>
<?php $row_idx = 0; ?>
<?php foreach ($mandanten as $mandant): ?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td><?= $mandant['id'] ?></td>
    <td><?= $mandant['name'] ?></td>
    <td><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?iAction=edit&amp;nMandantID=<?= $mandant['id'] ?>">ändern</a></td>
  </tr>
<?php endforeach; ?>
</table>
<?php
}

include('admin/nachspann.php');
?>
