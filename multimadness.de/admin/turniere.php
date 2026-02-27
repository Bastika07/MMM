<?php
/* Turniere */
require('controller.php');
require_once('dblib.php');
$iRecht = 'TURNIERADMIN';
require('checkrights.php');
include_once('admin/vorspann.php');


echo "<h1>Turnieradmin</h1>";

# Wieviele Coins kann ein Turnier maximal benötigen?
define('TOURNAMENT_MAX_COINS', 20);
define('TOURNAMENT_MAX_GROUP_SIZE', 15);

$iMandantID = (int) $_REQUEST['iMandantID'];

function pow2($exp) {
    return pow(2, $exp);
}

function show_form() {
    global $MandantID, $iCoins, $TURNIERTYPE, $iStandardRundenzeit, $TurnierTypen, $TurnierID, $iSTARTZEIT, $Action, $iNAME, $iTURN_TEXT, $iBILDKL, $iBILDGR, $iANZAHL_TEILNEHMER, $iGRUPPENSPIEL, $iTYP, $iART, $iAB18, $preis_platz1, $preis_platz2, $preis_platz3, $ircChannel;
?>

<form method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>" name="data">
  <input type="hidden" name="Action" value="<?= $Action ?>"/>
  <input type="hidden" name="TurnierID" value="<?= $TurnierID ?>"/>
  <input type="hidden" name="MandantID" value="<?= $MandantID ?>"/>
<table cellspacing="1" class="outer">
  <tr class="row-0">
    <td>Titel:</td>
    <td><input type="text" name="iNAME" size=20 maxlength=80 value="<?= $iNAME ?>"/> *</td>
  </tr>
  <tr class="row-1">
    <td>Startzeit:</td>
    <td><input type="text" name="iSTARTZEIT" size=20 maxlength=50 value="<?= $iSTARTZEIT ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>Kleines Bild:</td>
    <td><input type="text" name="iBILDKL" size=40 maxlength=50 value="<?= $iBILDKL ?>"/></td>
  </tr>
  <tr class="row-1">
    <td>Großes Bild:</td>
    <td><input type="text" name="iBILDGR" size=40 maxlength=50 value="<?= $iBILDGR ?>"/></td>
  </tr>
  <tr class="row-0">
    <td>Slots:</td>
    <td>
      <select name="iANZAHL_TEILNEHMER">
<?php
    $team_sizes = array_map('pow2', range(2, 9));
    foreach ($team_sizes as $i) {
        printf('        <option value="%s"%s>%s</option>' . "\n",
	    $i, (($iANZAHL_TEILNEHMER == $i) ? ' selected="selected"' : ''), $i);
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Gruppengröße:</td>
    <td>
      <select name="iGRUPPENSPIEL">
<?php
    for ($i = 1; $i <= TOURNAMENT_MAX_GROUP_SIZE; $i++) {
	echo "        <option value=\"$i\"" . ($iGRUPPENSPIEL == $i ? ' selected="selected"' : '') . '>' . $i . "</option>\n";
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-0">
    <td>Turniertyp:</td>
    <td>
      <select name="iTYP">
<?php
    foreach($TURNIERTYPE as $key => $sOutput) {
	echo "        <option value=\"$key\"".($key == $iTYP ? ' selected="selected"' : '') . '>' . db2display($sOutput) . "</option>\n";;
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Liga/Art:</td>
    <td>
      <select name="iART">
<?php	
    $field_info = DB::getRow('SHOW FIELDS FROM TURNIERLISTE LIKE "ART"');
    $arten = explode("','", substr($field_info['Type'], 5, -2));
    foreach ($arten as $art) {
        $selected = ($art == $iART);
	echo "      <option value=\"$art\"" . ($selected ? ' selected="selected"' : '') . '>' . $art . "</option>\n";;
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-0">
    <td>Ab 18?</td>
    <td>
      <select name="iAB18">
        <option value="J"<?= ($iAB18 == 'J' ? ' selected="selected"' : '') ?>>ja</option>
        <option value="N"<?= ($iAB18 == 'N' ? ' selected="selected"' : '') ?>>nein</option>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Kosten (Coins):</td>
    <td>
      <select name="iCoins">
<?php
    for ($i = 0; $i <= TOURNAMENT_MAX_COINS; $i++) {
	echo "        <option value=\"$i\"".($i == $iCoins ? ' selected="selected"' : '') . '>' . $i . "</option>\n";
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-0">
    <td valign="top">Preis 1. Platz:</td>
    <td><input type="text" name="preis_platz1" size="60" maxlenght="200" value="<?= $preis_platz1 ?>"/></td>
  </tr>
  <tr class="row-1">
    <td valign="top">Preis 2. Platz:</td>
    <td><input type="text" name="preis_platz2" size="60" maxlenght="200" value="<?= $preis_platz2 ?>"/></td>
  </tr>
  <tr class="row-0">
    <td valign="top">Preis 3. Platz:</td>
    <td><input type="text" name="preis_platz3" size="60" maxlenght="200" value="<?= $preis_platz3 ?>"/></td>
  </tr>
  <tr class="row-1">
    <td valign="top">IRC-Channel:</td>
    <td>#<input type="text" name="ircChannel" size="30" maxlenght="50" value="<?= $ircChannel ?>"/> (Intranet)</td>
  </tr>
  <tr class="row-0">
    <td valign="top">Text/Regeln:</td>
    <td class="hblau"><textarea name="iTURN_TEXT" cols="65" rows="25"><?= $iTURN_TEXT ?></textarea> *</td>
  </tr>
  <tr class="row-1">
    <td>&nbsp;</td>
    <td><input type="submit" value="speichern"/> <input type="reset" value="zurücksetzen"/>
  </tr>
</table>
</form>
<?php
}

if (($Action == 'new') or ($Action == 'edit')) {
	# Anlegen oder bearbeiten.
	if (($Action == 'edit') and ! isset($_POST['iNAME'])) {
		$row = DB::getRow('
			SELECT *
			FROM TURNIERLISTE
			WHERE TURNIERID = ?
			  AND MANDANTID = ?
			', $TurnierID, $MandantID);
		$iNAME = $row['NAME'];
		$iTURN_TEXT = $row['REGELN'];
		$iBILDKL = $row['BILDKL'];
		$iBILDGR = $row['BILDGR'];
		$iANZAHL_TEILNEHMER = $row['ANZAHL_TEILNEHMER'];
		$iGRUPPENSPIEL = $row['GRUPPENGROESSE'];
		$iTYP = $row['TYPE'];
		$iSTARTZEIT = $row['STARTZEIT'];
		$iART = $row['ART'];
		$iCoins  = $row['COINS'];
		$iAB18 = $row['AB18'];
		$preis_platz1 = $row['PREIS_PLATZ1'];
		$preis_platz2 = $row['PREIS_PLATZ2'];
		$preis_platz3 = $row['PREIS_PLATZ3'];
		$ircChannel = $row['IRC_CHANNEL'];
		##$iStandardRundenzeit = $row[STANDARD_RUNDENZEIT];

		echo "<h2>Turnierdaten ändern: $iNAME</h2>";
	} else {
		echo "<h2>Neue Turnierdaten erfassen</h2>";
	}

	if (! isset($_POST['iNAME']) ) {
		show_form();
	} else {
		if (empty($_POST['iNAME']) or empty($_POST['iTURN_TEXT'])) {
			echo "<p class='fehler'>Bitte alle Felder ausfüllen!</p>";
			show_form();
		} elseif (($iTYP == 6) and ($iANZAHL_TEILNEHMER < 4)) {
			echo "<p class='fehler'>An einem Doppel-KO-Spiel müssen mindestens 4 Teams/Spieler teilnehmen!</p>";
			show_form();
		} else {
			# Alles klar, INSERT oder UPDATE.
			$iTYP = $iTYP * 1;
			$iANZAHL_TEILNEHMER = $iANZAHL_TEILNEHMER * 1;
			# Schweinegatter aus $ircChannel entfernen.
			if ($ircChannel{0} == '#')
			  $ircChannel = substr($ircChannel, 1);
			if ($Action == 'edit') {
				DB::query("
					UPDATE TURNIERLISTE
					SET STARTZEIT='$iSTARTZEIT', NAME='$iNAME', ANZAHL_TEILNEHMER=$iANZAHL_TEILNEHMER , GRUPPENGROESSE=$iGRUPPENSPIEL , TYPE=$iTYP , BILDKL='$iBILDKL', BILDGR='$iBILDGR', REGELN='$iTURN_TEXT', ART='$iART' , COINS='$iCoins' , AB18='$iAB18', PREIS_PLATZ1='$preis_platz1', PREIS_PLATZ2='$preis_platz2', PREIS_PLATZ3='$preis_platz3', IRC_CHANNEL='$ircChannel'
					WHERE TURNIERID = $TurnierID
					  AND MANDANTID = '$MandantID'
				");
				if (mysql_errno() == 0) {
					echo "<p>Neue Turnierdaten gespeichert.</p>";
					echo "<p><a href=\"turniere.php\">Zum Turnieradmin</a></p>";
				} else {
					echo "DB-Fehler";
				}

			} else {
				DB::query("
					INSERT INTO TURNIERLISTE (MANDANTID, STANDARD_RUNDENZEIT, STARTZEIT, NAME, ANZAHL_TEILNEHMER, GRUPPENGROESSE, TYPE, BILDKL, BILDGR, REGELN, ART, COINS, AB18, IRC_CHANNEL)
					VALUES ('$MandantID', '$iStandardRundenzeit', '$iSTARTZEIT', '$_POST[iNAME]', $iANZAHL_TEILNEHMER, $iGRUPPENSPIEL, $iTYP, '$iBILDKL', '$iBILDGR', '$_POST[iTURN_TEXT]','$iART', '$iCoins', '$iAB18', '$ircChannel')
				");
				if (mysql_errno() == 0) {
					echo "<p>Neues Turnier gespeichert.</p>";
					echo "<p><a href=\"turniere.php\">Zum Turnieradmin</a></p>";
				} else {
					echo "DB-Fehler";
				}
			}
		}
	}
} else {
    require_once('admin/helpers.php');
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('TURNIERADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');

    if ($iMandantID) {
        $rowMandant = DB::getRow('
            SELECT MANDANTID, BESCHREIBUNG, REFERER
	    FROM MANDANT
	    WHERE MANDANTID = ?
	    ', $iMandantID);

	# Gesamtcoins herausfinden.
	$maxCoins = DB::getOne('
	    SELECT STRINGWERT
	    FROM CONFIG
	    WHERE PARAMETER = "COINS_GESAMT"
	      AND MANDANTID = ?
	    ', $rowMandant['MANDANTID']);
?>

<h2><?= db2display($rowMandant['BESCHREIBUNG']) ?></h2>

<p>
  Gesamtanzahl Coins: <strong><?= $maxCoins ?></strong>
  &middot;
  <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?MandantID=<?= $rowMandant['MANDANTID'] ?>&amp;Action=new">Turnier anlegen</a>
</p>

<table cellspacing="1" class="outer" width="780">
  <tr>
    <th>Turnier</th>
    <th>Slots</th>
    <th>Coins</th>
    <th>Liga</th>
    <th>Typ</th>
    <th>Startzeit</th>
  </tr>
<?php
        $rows = DB::getRows('
	    SELECT *
	    FROM TURNIERLISTE
	    WHERE MANDANTID = ?
	    ORDER BY NAME
	    ', $rowMandant['MANDANTID']);
        $row_idx = 0;
	foreach ($rows as $row) {
	    $icon = $rowMandant['REFERER'] . $row['BILDKL'];
	    $attendee_count = DB::getOne("
	        SELECT COUNT(*)
	        FROM TURNIERTEILNEHMER
	        WHERE TURNIERID = $row[TURNIERID]
	          AND MANDANTID='$rowMandant[MANDANTID]'
	        ");
?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td>
      <div style="background: url('<?= $icon ?>') no-repeat; padding-left: 21px;">
        <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?TurnierID=<?= $row['TURNIERID'] ?>&amp;MandantID=<?= $rowMandant['MANDANTID'] ?>&amp;Action=edit"><?= $row['NAME'] ?></a>
      </div>
    </td>
    <td style="text-align: center;"><?= $attendee_count ?>/<?= $row['ANZAHL_TEILNEHMER'] ?></td>
    <td style="text-align: center;"><?= $row['COINS'] ?></td>
    <td style="text-align: center;"><?= $row['ART'] ?></td>
    <td><?= $TURNIERTYPE[$row['TYPE']] ?></td>
    <td>
<?php
	    if (isset($row['AKTUELLE_RUNDE']) and ($row['AKTUELLE_RUNDE'] > 0)) {
	        echo 'Gestartet';
	    } else {
	        echo $row['STARTZEIT'];
	    }
?>
    </td>
  </tr>
<?php
	}
?>
</table>

<?php
    }
}
	
include('admin/nachspann.php');
?>
