<?php
require('controller.php');
require_once('dblib.php');
require_once('format.php');
$iRecht = 'MANDANTADMIN';
require_once('checkrights.php');
include('admin/vorspann.php');




function show_form_party_anlegen() {
	global $loginID;
?>
<form method="post">
  <input type="hidden" name="iPosted" value="yes"/>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Informationen zur Party</th>
  </tr>
  <tr class="row-0">
    <td>Mandant:</td>
    <td>
      <select name="iMandantID">
<?php
	$q = 'SELECT m.MANDANTID, m.BESCHREIBUNG
              FROM MANDANT m
	        INNER JOIN RECHTZUORDNUNG r USING (MANDANTID)
              WHERE r.RECHTID = "MANDANTADMIN"
                AND r.USERID = ' . intval($loginID);
	foreach (DB::getRows($q) as $mandant) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $mandant['MANDANTID'],
		(($_GET['iMandantID'] == $mandant['MANDANTID']) ? ' selected="selected"' : ''),
		db2display($mandant['BESCHREIBUNG']));
	}
?>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Name (mit Zusatz):</td>
    <td><input type="text" name="iName" size="30" maxlength="150" value="<?= htmlspecialchars($_POST['iName'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-0">
    <td>Location:</td>
    <td><input type="text" name="iLocation" size="30" maxlength="150" value="<?= htmlspecialchars($_POST['iLocation'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Stra&szlig;e:</td>
    <td><input type="text" name="iLocationStrasse" size="30" maxlength="100" value="<?= htmlspecialchars($_POST['iLocationStrasse'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>PLZ:</td>
    <td><input type="text" name="iLocationPLZ" size="10" maxlength="5" value="<?= htmlspecialchars($_POST['iLocationPLZ'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Ort:</td>
    <td><input type="text" name="iLocationOrt" size="30" maxlength="100" value="<?= htmlspecialchars($_POST['iLocationOrt'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>Teilnehmer:</td>
    <td><input type="text" name="iTeilnehmer" size="30" maxlength="20" value="<?= htmlspecialchars($_POST['iTeilnehmer'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> *</td>
  </tr>
  
  <tr class="row-0">
    <td>Begleitereintritt:</td>
    <td><input type="text" name="iBegleitereintritt" size="10" maxlength="6" value="<?= htmlspecialchars($_POST['iBegleitereintritt'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> Euro</td>
  </tr>
  
  <tr class="row-1">
    <td>Beginn am:</td>
    <td>
      <select name="iBeginnTag">
<?php
	foreach (range(1, 31) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iBeginnTag']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select>.
      <select name="iBeginnMonat">
<?php
	foreach (range(1, 12) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iBeginnMonat']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select>.
      <select name="iBeginnJahr">
<?php
	foreach (range(1995, date('Y') + 5) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iBeginnJahr']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select> &nbsp; 
      <select name="iBeginnStunde">
<?php
	foreach (range(0, 24) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iBeginnStunde']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select> : 
      <select name="iBeginnMinute">
<?php
	foreach (range(0, 24) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iBeginnMinute']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select> Uhr  
    </td>
  </tr>
  <tr class="row-0">
    <td>Ende am:</td>
    <td>
      <select name="iEndeTag">
<?php	
	foreach (range(1, 31) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iEndeTag']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select>.
      <select name="iEndeMonat">
<?php
	foreach (range(1, 12) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iEndeMonat']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select>.
      <select name="iEndeJahr">
<?php
	foreach (range(1995, date('Y') + 5) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iEndeJahr']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select> &nbsp; 
      <select name="iEndeStunde">
<?php
	foreach (range(0, 24) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_POST['iEndeStunde']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select> : 
      <select name="iEndeMinute">
<?php
	foreach (range(0, 24) as $i) {
	    printf('<option value="%s"%s>%s</option>' . "\n",
	        $i, (($i == $_PSOT['iEndeMinute']) ? ' selected="selected"' : ''), $i);
	}
?>
      </select> Uhr
    </td>
  </tr>
  <tr class="row-1">
    <td>Mindestalter:</td>
    <td>
      <select name="iMindestalter">
<?php
	    printf('<option value="%s"%s>%s Jahre</option>' . "\n",
	        0, ((0 == $_POST['iMindestalter']) ? ' selected="selected"' : ''), 0);
	    printf('<option value="%s"%s>%s Jahre</option>' . "\n",
	        16, ((16 == $_POST['iMindestalter']) ? ' selected="selected"' : ''), 16);
	    printf('<option value="%s"%s>%s Jahre</option>' . "\n",
	        18, ((18 == $_POST['iMindestalter']) ? ' selected="selected"' : ''), 18);
?>
      </select>
    </td>
  </tr>
  <tr class="row-0">
    <td>SupporterPass-Bild klein:</td>
    <td><input type="text" name="iSupporterPassPicSmall" size="30" maxlength="250" value="<?= htmlspecialchars($_POST['iSupporterPassPicSmall'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> URL wenn vorhanden</td>
  </tr>
  <tr class="row-1">
    <td>SupporterPass-Bild Gro&szlig;:</td>
    <td><input type="text" name="iSupporterPassPicBig" size="30" maxlength="250" value="<?= htmlspecialchars($_POST['iSupporterPassPicBig'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"/> URL wenn vorhanden</td>
  </tr>
  <tr class="row-0">
    <td>&nbsp;</td>
    <td><input type="submit" value="Party speichern"/></td>
  </tr>
</table>
</form>
<?php
}

# ---------------------------------------------------------------- #

if ($_GET['action'] == 'activate') {
    # Party aktivieren.
    echo "<h1>Party aktivieren</h1>";
    if (! BenutzerHatRechtMandant('MANDANTADMIN', $_GET['iMandantID'])) {
	echo '<p class="fehler">Keine Berechtigung!</p>' . "\n";
    } else {
	# Datum prüfen.
	$q = "SELECT NOW() > terminBis
	      FROM party
	      WHERE partyId = '".intval($_GET['iPartyID'])."'";
	if (DB::getOne($q)) {
	    echo '<p class="fehler">Die Party ist bereits vorüber, die Aktivierung ist nicht möglich!</p>' . "\n";
	} else {
	    # Alle deaktivieren.
	    $q = "UPDATE party
	          SET aktiv = 'N'
	          WHERE mandantId = '".intval($_GET['iMandantID'])."'";
	    DB::query($q);

	    # Gewünschte aktivieren.
	    $q = "UPDATE party
	          SET aktiv = 'J'
	          WHERE mandantId = '".intval($_GET['iMandantID'])."'
		    AND partyId = '".intval($_GET['iPartyID'])."'";
	    DB::query($q);

	    echo '<p class="confirm">Die Party wurde aktiviert.</p>' . "\n";
	    echo '<p><a href="' . $_SERVER['SCRIPT_NAME'] . '">Zur Ãœbersicht</a></p>' . "\n";
	}
    }
} elseif (($_GET['action'] == 'add') or ($_GET['action'] == 'edit')) {
    # Party anlegen oder ändern.
?>
<h1>Party anlegen/ändern</h1>
<p><em>Hinweis</em>: Die Party kann über die Aktivierungsfunktion in der <a href="<?= $_SERVER['SCRIPT_NAME'] ?>">Übersicht</a> aktiviert werden.</p>
<?php	
    # Für Zahlencheck Teilnehmer
    $match1 = '';
    for ($i = 1; $i <= strlen ($_POST['iTeilnehmer']); $i++) {
        $match1 .= '[0-9]';
    }

    if ($_POST['iPosted'] != 'yes') {
        if ($_GET['action'] == 'edit') {
	    # Wenn Ã„nderungsmodus, dann Variablen füllen.
	    $q = 'SELECT beschreibung, teilnehmer, begleitereintritt, location, locationStrasse, locationPLZ, locationOrt, mindestalter, terminVon, terminBis, supporterPassPicSmall, supporterPassPicBig
	          FROM party
		  WHERE partyId = ' . intval($_GET['iPartyID']);
	    $party = DB::getRow($q);
	    $_POST['iName']        = $party['beschreibung']; 
	    $_POST['iTeilnehmer']  = $party['teilnehmer'];
	    $_POST['iBegleitereintritt'] = $party['begleitereintritt'];
	    $_POST['iLocation']    = $party['location']; 
	    $_POST['iLocationStrasse'] = $party['locationStrasse'];
	    $_POST['iLocationPLZ'] = $party['locationPLZ'];
	    $_POST['iLocationOrt'] = $party['locationOrt'];
	    $_POST['iMindestalter'] = $party['mindestalter'];
	    $_POST['iBeginnJahr']  = substr($party['terminVon'], 0, 4);
	    $_POST['iBeginnMonat'] = substr($party['terminVon'], 5, 2);
	    $_POST['iBeginnTag']   = substr($party['terminVon'], 8, 2);
	    $_POST['iBeginnStunde'] = substr($party['terminVon'], 11, 2);
	    $_POST['iBeginnMinute'] = substr($party['terminVon'], 14, 2);
	    $_POST['iEndeJahr']    = substr($party['terminBis'], 0, 4);
	    $_POST['iEndeMonat']   = substr($party['terminBis'], 5, 2);
	    $_POST['iEndeTag']     = substr($party['terminBis'], 8, 2);
	    $_POST['iEndeStunde'] = substr($party['terminBis'], 11, 2);
	    $_POST['iEndeMinute'] = substr($party['terminBis'], 14, 2);
	    $_POST['iSupporterPassPicSmall'] = $party['supporterPassPicSmall'];
	    $_POST['iSupporterPassPicBig'] = $party['supporterPassPicBig'];
        }
	if (($_GET['action'] == 'edit') and ($_GET['iPartyID'] < 1)) {
	    echo '<p class="fehler">Keine Party ausgewählt.</p>' . "\n";
	} else {
	    show_form_party_anlegen();
	}
    } elseif (empty($_POST['iMandantID']) or empty($_POST['iName']) or empty($_POST['iLocation']) or empty($_POST['iLocationPLZ']) or empty($_POST['iTeilnehmer']) ) {
	echo '<p class="fehler">Bitte alle mit * gekennzeichneten Felder ausfüllen!</p>' . "\n";
	show_form_party_anlegen();
    } elseif (! checkdate ($_POST['iBeginnMonat'], $_POST['iBeginnTag'], $_POST['iBeginnJahr'])) {
	echo '<p class="fehler">Ungültiges Datum: <tt>' . implode(', ', array(intval($_POST['iBeginnTag']), intval($_POST['iBeginnMonat']), intval($_POST['iBeginnJahr']))) . "</tt></p>\n";
	show_form_party_anlegen();
    } elseif (! checkdate ($_POST['iEndeMonat'], $_POST['iEndeTag'], $_POST['iEndeJahr'])) {
	echo '<p class="fehler">Ungültiges Datum: <tt>' . implode(', ', array(intval($_POST['iEndeTag']), intval($_POST['iEndeMonat']), intval($_POST['iEndeJahr']))) . "</tt></p>\n";
	show_form_party_anlegen();
    } elseif (preg_match('/^' . $match1 . '$/', $_POST['iTeilnehmer']) != 1) {
	echo '<p class="fehler">Im Feld Teilnehmer ist keine Zahl!</p>' . "\n";
	show_form_party_anlegen();
    } elseif (! BenutzerHatRechtMandant('MANDANTADMIN', $_POST['iMandantID'])) {
	echo '<p class="fehler">Kein Recht für diesen Mandanten vorhanden!</p>' . "\n";
	show_form_party_anlegen();
    } else {
	if ($_GET['action'] == 'add') {
	    # Datensatz in Party-Tabelle anlegen.
	    $q = "INSERT INTO party (
		    mandantId, beschreibung, teilnehmer, begleitereintritt, location, locationStrasse, locationPLZ, locationOrt, mindestalter,
		    terminVon, terminBis, supporterPassPicSmall, supporterPassPicBig, werAngelegt, wannAngelegt, werGeaendert
	          ) VALUES (
		    '".safe($_POST['iMandantID'])."', 
				'".safe($_POST['iName'])."', 
				'".safe($_POST['iTeilnehmer'])."', 
				'".safe($_POST['iBegleitereintritt'])."', 
				'".safe($_POST['iLocation'])."',
				'".safe($_POST['iLocationStrasse'])."', 
				'".safe($_POST['iLocationPLZ'])."',
				'".safe($_POST['iLocationOrt'])."',
		    '".safe($_POST['iMindestalter'])."',
				'".$_POST['iBeginnJahr']."-".$_POST['iBeginnMonat']."-".$_POST['iBeginnTag']." ".$_POST['iBeginnStunde'].":".$_POST['iBeginnMinute']."', 
				'".$_POST['iEndeJahr']."-".$_POST['iEndeMonat']."-".$_POST['iEndeTag']." ".$_POST['iEndeStunde'].":".$_POST['iEndeMinute']."',
		    '".safe($_POST['iSupporterPassPicSmall'])."',
				'".safe($_POST['iSupporterPassPicBig'])."',
				'".intval($loginID)."', 
				NOW(), 
				'".intval($loginID)."'
	      )";
	} else {
	    # Datensatz in Party-Tabelle aktualisieren.
	    $q = "UPDATE party SET 
		    beschreibung = '".safe($_POST['iName'])."',
		    teilnehmer = '".safe($_POST['iTeilnehmer'])."',
		    begleitereintritt = '".safe($_POST['iBegleitereintritt'])."',
		    location = '".safe($_POST['iLocation'])."',
		    locationStrasse = '".safe($_POST['iLocationStrasse'])."',
		    locationPLZ = '".safe($_POST['iLocationPLZ'])."',
		    locationOrt = '".safe($_POST['iLocationOrt'])."',
		    mindestalter = '".safe($_POST['iMindestalter'])."',
		    terminVon = '".$_POST['iBeginnJahr']."-".$_POST['iBeginnMonat']."-".$_POST['iBeginnTag']." ".$_POST['iBeginnStunde'].":".$_POST['iBeginnMinute']."', 
		    terminBis = '".$_POST['iEndeJahr']."-".$_POST['iEndeMonat']."-".$_POST['iEndeTag']." ".$_POST['iEndeStunde'].":".$_POST['iEndeMinute']."',
				supporterPassPicSmall = '".safe($_POST['iSupporterPassPicSmall'])."',
				supporterPassPicBig = '".safe($_POST['iSupporterPassPicBig'])."',
		    werGeaendert = '".intval($loginID)."'
		  WHERE partyId = '".intval($_GET['iPartyID'])."'";
	}
	DB::query($q);
	if (mysql_errno()) {
	    exit('Fehler: ' . mysql_errno() . ': ' . mysql_error() . ' beim Einfügen/Ã„ndern in Party-Tabelle - Abbruch!<br/>');
	}
	echo '<p class="confirm">Party gespeichert.</p>' . "\n";
	echo '<p><a href="' . $_SERVER['SCRIPT_NAME'] . '">Zur Ãœbersicht</a></p>' . "\n";
    }
} else {
    # Party in Liste zeigen.
?>
<h1>Partys verwalten</h1>
<p>Die jeweils aktive Party ist rot hinterlegt.</p>
<?php
    foreach (PELAS::mandantArray(False) as $mandantID => $mandantName) {
        if (! BenutzerHatRechtMandant('MANDANTADMIN', $mandantID)) {
            continue;
	}
	$q = 'SELECT partyId, beschreibung, aktiv, terminVon, terminBis, teilnehmer, location
	      FROM party
	      WHERE mandantId = ' . $mandantID . '
	      ORDER BY terminVon';
	$parties = DB::getRows($q);
	$raw_action_url = sprintf('%s?iMandantID=%s&amp;action=',
	    $_SERVER['SCRIPT_NAME'], $mandantID);
?>
<h2><?= $mandantNname ?></h2>

<table cellspacing="1" class="outer">
  <tr>
    <th width="25">ID</th>
    <th width="180">Name</th>
    <th width="160">Location</th>
    <th width="40">Plätze</th>
    <th width="145">Zeitraum</th>
    <th width="140">Aktionen</th>
  </tr>
<?php $row_idx = 0; ?>
<?php foreach ($parties as $party): ?>
  <tr class="row-<?= ($party['aktiv'] == 'J') ? 'active' : ($row_idx++ % 2); ?>">
    <td><?= $party['partyId'] ?></td>
    <td><?= $party['beschreibung'] ?></td>
    <td><?= $party['location'] ?></td>
    <td style="text-align: right;"><?= $party['teilnehmer'] ?></td>
    <td><?= dateDisplay2Short($party['terminVon']) ?> - <?= dateDisplay2Short($party['terminBis']) ?></td>
    <td>
      <a href="<?= $raw_action_url ?>edit&amp;iPartyID=<?= $party['partyId'] ?>">bearbeiten</a>
      &middot;
      <a href="<?= $raw_action_url ?>activate&amp;iPartyID=<?= $party['partyId'] ?>">aktivieren</a>
    </td>
  </tr>
<?php endforeach; ?>
  <tr class="row-<?= $row_idx % 2; ?>">
    <td colspan="5">&nbsp;</td>
    <td>
      <a href="<?= $raw_action_url ?>add">neue Party anlegen</a>
    </td>
  </tr>
</table>
<?php
    }
}

include('admin/nachspann.php');
?>
