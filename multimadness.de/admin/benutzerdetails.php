<?php
/* Benutzerdetails bearbeiten */
require('controller.php');

$iRecht = array('TEAMMEMBER');
require_once('checkrights.php');

if ($_GET ["userid"] != $loginID)
{
	$iRecht = array('USERADMIN', 'USERADMIN_READONLY');
}
require('format.php');
require('admin/vorspann.php');
?>

<h1>Benutzerdetails v2.0</h1>

<?php
function show_form() {
    global $PHP_SELF, $id, $loginID, $formVars, $oldData, $dbh, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE;
?>
<script type="text/javascript">
<!--
function openAstatus(Mandant) {
    Formname = "Astatus" + Mandant;
    Oldstatus = document.forms[Formname].OldStatus.value;
    theURL = "benutzerstatus.php?nMandantID=" + Mandant + "&nOldStatus=" + Oldstatus + "&id=<?= intval($_GET['id']) ?>";
    detail = window.open(theURL, "Astatus", "width=420,height=320,locationbar=false,resize=false");
    detail.focus();
}
//-->
</script>

<form method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>?id=<?= intval($_GET['id']); ?>" name="details">
	<input type="hidden" name="oldData" value="<?php echo $oldData; ?>">
  <input type="hidden" name="actionID" value="daten"/>
  <input type="hidden" name="formVars[alter_userid]" value="<?= $formVars['userid'] ?>"/>
<table cellspacing="0" cellpadding="0" border="0" width="500">
  <tr>
    <td class="navbar">
      <table width="100%" cellspacing="1" cellpadding="3" border="0">
        <tr>
	  <td class="navbar" colspan="2"><b>Benutzerdaten <?= $formVars['login'] ?></b></td>
	</tr>

    <?php
    if (empty($_GET['id'])) {
        # Bei Benutzer-Anlegen die Mandanten zeigen, damit ein Datensatz in ASTATUS angelegt werden kann.
        echo '<tr><td class="dblau">Anlegen für Mandant</td><td class="hblau">'."\n";
        echo '<select name="formVars[mandantzuordnung]">'."\n";
        $sql = '
	    SELECT m.MANDANTID, m.BESCHREIBUNG
	    FROM MANDANT m, RECHTZUORDNUNG r
            WHERE r.USERID = ' . $loginID . '
	      AND r.MANDANTID = m.MANDANTID
	      AND r.RECHTID = "USERADMIN"';
        $rows = DB::getRows($sql);
	foreach ($rows as $rowMandant) {
            echo '<option value="' . $rowMandant['MANDANTID'] . '" ' . (($rowMandant['MANDANTID'] == $formVars['mandantzuordnung']) ? ' selected="selected">' : '>') . db2display($rowMandant['BESCHREIBUNG']) . "</option>\n";
        }
        echo "\n</select></td></tr>\n";
    } else {
      # Logindaten
      echo '<tr><td class="dblau">UserID:</td><td class="dblau"><input type="text" name="formVars[userid]" size="35" maxlength="120" value="'.$formVars['userid'].'">'."\n";
      echo '<input type="submit" value="suchen"></td></tr>'."\n";
    }
    echo '<tr><td class="dblau">Login:</td><td class="hblau"><input type="text" name="formVars[login]" size="40" maxlength="120" value="'.$formVars['login'].'">*</td></tr>'."\n";
    echo '<tr><td class="dblau">Kennwort:</td><td class="hblau"><input type="password" name="formVars[password1]" size="40" maxlength="120" value="'.$formVars['password1'].'">*</td></tr>'."\n";
    echo '<tr><td class="dblau">Bestätigung:</td><td class="hblau"><input type="password" name="formVars[password2]" size="40" maxlength="120" value="'.$formVars['password2'].'">*</td></tr>'."\n";

    # Benutzerdaten
    echo '<tr><td class="dblau" colspan="2"><b>Persönliche Daten</b></td></tr>';
    echo '<tr><td class="dblau">Vorname:</td><td class="hblau"><input type="text" name="formVars[name]" size="40" maxlength="35" value="'.$formVars['name'].'">*</td></tr>'."\n";
    echo '<tr><td class="dblau">Nachname:</td><td class="hblau"><input type="text" name="formVars[nachname]" size="40" maxlength="35" value="'.$formVars['nachname'].'">*</td></tr>'."\n";
    echo '<tr><td class="dblau">URL (mit http://):</td><td class="hblau"><input type="text" name="formVars[homepage]" size="40" maxlength="120" value="'.$formVars['homepage'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">Email-Adresse:</td><td class="hblau"><input type="text" name="formVars[email]" size="40" maxlength="120" value="'.$formVars['email'].'">*</td></tr>'."\n";
    echo '<tr><td class="dblau">Strasse:</td><td class="hblau"><input type="text" name="formVars[strasse]" size="40" maxlength="50" value="'.$formVars['strasse'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">PLZ:</td><td class="hblau"><input type="text" name="formVars[plz]" size="10" maxlength="10" value="'.$formVars['plz'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">Ort:</td><td class="hblau"><input type="text" name="formVars[ort]" size="40" maxlength="50" value="'.$formVars['ort'].'"></td></tr>'."\n";

    # Zusätze
     echo '<tr><td class="dblau">Skype:</td><td class="hblau"><input type="text" name="formVars[skype]" size="40" maxlength="120" value="'.$formVars['skype'].'"></td></tr>'."\n";
	echo '<tr><td class="dblau">Personalausweis-Nr:</td><td class="hblau"><input type="text" name="formVars[personr]" size="11" maxlength="11" value="'.$formVars['personr'].'"></td></tr>'."\n";
   	echo '<tr><td class="dblau">WWCL ID Single:</td><td class="hblau"><input type="text" name="formVars[wwcl_single]" size="10" maxlength="10" value="'.$formVars['wwcl_single'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">WWCL ID Team:</td><td class="hblau"><input type="text" name="formVars[wwcl_team]" size="10" maxlength="10" value="'.$formVars['wwcl_team'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">NGL ID Single:</td><td class="hblau"><input type="text" name="formVars[ngl_single]" size="10" maxlength="10" value="'.$formVars['ngl_single'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">NGL ID Team:</td><td class="hblau"><input type="text" name="formVars[ngl_team]" size="10" maxlength="10" value="'.$formVars['ngl_team'].'"></td></tr>'."\n";

    # Von Mailing ausschliessen Feld
    echo '<tr><td class="dblau">Keine Mailings:</td><td class="hblau"><input type="checkbox" name="formVars[kein_mailing]" value="J"'.(($formVars['kein_mailing'] == "J") ? ' checked>' : '>').'</td></tr>'."\n";

    # Kommentare
    echo '<tr><td class="dblau">Kommentar:</td><td class="hblau"><input type="text" name="formVars[komment]" size="40" maxlength="200" value="'.$formVars['komment'].'"></td></tr>'."\n";
    echo '<tr><td class="dblau">Kommentar (intern):</td><td class="hblau"><input type="text" name="formVars[komment_intern]" size="40" maxlength="200" value="'.$formVars['komment_intern'].'"></td></tr>'."\n";

    if (($_GET['id'] > 0) and User::hatRecht('TEAMMEMBER', $_GET['id'])) {
?>
  <tr>
    <td class="dblau" colspan="2"><b>Team-Daten</b></td>
  </tr>
  <tr>
    <td class="dblau">Telefon:</td>
    <td class="hblau"><input type="text" name="formVars[team_telefon]" size="20" maxlength="15" value="<?= $formVars['team_telefon'] ?>"/></td>
  </tr>
  <tr>
    <td class="dblau">Mobil:</td>
    <td class="hblau"><input type="text" name="formVars[team_mobil]" size="20" maxlength="15" value="<?= $formVars['team_mobil'] ?>"/></td>
  </tr>
  <tr>
    <td class="dblau">Teilteam:</td>
    <td class="hblau">
	<?php
        echo '<select name="formVars[team_teilteam]">'."\n";
        echo '<option value="0">(Kein)</option>\n';
        $sql = '
	    SELECT id, description
	    FROM user_ext_team';
	
        $rows = DB::getRows($sql);
	foreach ($rows as $rowTeilteam) {
            echo '<option value="' . $rowTeilteam['id'] . '" ' . (($rowTeilteam['id'] == $formVars['team_teilteam']) ? ' selected="selected">' : '>') . db2display($rowTeilteam['description']) . "</option>\n";
        }
        echo "\n</select>";
        ?>
    </td>
  </tr>
  <tr>
    <td class="dblau">Aufgabe:</td>
    <td class="hblau"><input type="text" name="formVars[team_aufgabe]" size="40" maxlength="150" value="<?= $formVars['team_aufgabe'] ?>"/></td>
  </tr>
  <tr>
    <td class="dblau">Status:</td>
    <td class="hblau"><label><input type="checkbox" name="formVars[team_inaktiv]" value="1"<?= ($formVars['team_inaktiv'] ? ' checked="checked"' : '') ?>/> inaktiv</label></td>
  </tr>
  <tr>
    <td class="dblau">Geburtstag:</td>
    <td class="hblau">
<?php
        echo '<select name="formVars[team_Tag]">' . "\n";
        for ($i= 1; $i <= 31; $i++) {
            echo '<option value="' . $i . '"' . (($i == $formVars['team_Tag']) ? ' selected>' : '>') . $i . "</option>\n";
	}
        echo "\n" . '</select> <select name="formVars[team_Monat]">' . "\n";
        for ($i = 1; $i <= 12; $i++) {
            echo '<option value="' . $i . '"' . (($i == $formVars['team_Monat']) ? ' selected>' : '>') . $i . "</option>\n";
	}
        echo "\n" . '</select> <select name="formVars[team_Jahr]">' . "\n";
        for ($i = 1960; $i <= 2010; $i++) {
            echo '<option value="' . $i . '"' . (($i == $formVars['team_Jahr']) ? ' selected>' : '>') . $i . "</option>\n";
	}
        echo "\n" . '</select></td></tr>' . "\n";
?>
  <tr>
    <td class="dblau">Kommentar:</td>
    <td class="hblau"><input type="text" name="formVars[team_kommentar]" size="40" maxlength="250" value="<?= $formVars['team_kommentar'] ?>"/></td>
  </tr>
  <tr>
<?php
    }
?>
    <td class="dblau">&nbsp;</td>
    <td class="dblau">
      <input type="submit" value="Daten speichern"/>
      <input type="button" value="Zurück" onclick="window.history.back();"/>
    </td>
  </tr>
</table>
    </td>
  </tr>
</table>
</form>
<?php
    $hatBezahlt = 0;
    if ($_GET['id'] > 0) {
        echo '<table cellspacing="0" cellpadding="0" border="0" width="500">'."\n";
        echo '<tr><td class="navbar">'."\n";
        echo '<table width="100%" cellspacing="1" cellpadding="3" border="0">'."\n";
        echo '<tr><td class="navbar" colspan="2"><b>Anmeldestatus</b></td></tr>'."\n";

        # Welche Mandanten dürfen bearbeitet werden?
        $sql = '
	    SELECT m.MANDANTID, m.BESCHREIBUNG
            FROM MANDANT m, RECHTZUORDNUNG r
            WHERE r.USERID = ' . $loginID . '
	      AND r.MANDANTID = m.MANDANTID
	      AND r.RECHTID = "USERADMIN"';
        $resMandant = DB::query($sql);

        $vorhanden = 0;
        while ($rowMandant = $resMandant->fetch_array()) {
            # Welchen Status hat der Teilnehmer in diesem Mandanten?
            $sql = "SELECT
                        a.STATUS,
                        a.WANNGEAENDERT,
                        a.WERGEAENDERT,
                        a.WANNANGEMELDET,
                        a.WANNBEZAHLT,
                           b.BESCHREIBUNG
                    FROM
                        ASTATUS a,
                        STATUS b
                    WHERE
                        a.USERID = '".intval($_GET['id'])."' AND
                        a.MANDANTID = " . $rowMandant['MANDANTID'] . " AND
                        a.STATUS = b.STATUSID";
            $resAstatus = DB::query($sql);

            echo '<form name="Astatus' . $rowMandant['MANDANTID'] . '">' . "\n";
            echo '<tr><td class="dblau">' . $rowMandant['BESCHREIBUNG'] . ":</td>\n";

            $rowAstatus = $resAstatus->fetch_array();
            if ($rowAstatus == '') {
                   $rowAstatus = array('BESCHREIBUNG' => 'nicht angemeldet');
	    }

            echo '<td class="hblau"><input type="text" readonly name="AstatusBeschreibung" value="'.$rowAstatus['BESCHREIBUNG'].'">';
            echo '<input type="hidden" name="OldStatus" value="0'.$rowAstatus['STATUS'].'"/>'."\n";
            echo '<input type="button" onclick="javascript:openAstatus('.$rowMandant['MANDANTID'].');" value="ändern"/>'."\n";

/* quick hack by razzor
            echo '<hr/><table width="100%" cellspacing="1" cellpadding="0" border="0">'."\n";
            echo '<tr><td class="hblau">Geaendert am:</td><td class="hblau">'.$rowAstatus['WANNGEAENDERT'].'</td></tr>'."\n";
            echo '<tr><td class="hblau">Geaendert von:</td><td class="hblau">'.$rowAstatus['WERGEAENDERT'].'</td></tr>'."\n";
            echo '<tr><td class="hblau">Angemeldet am:</td><td class="hblau">'.$rowAstatus['WANNANGEMELDET'].'</td></tr>'."\n";
            echo '<tr><td class="hblau">Bezahlt am:</td><td class="hblau">'.$rowAstatus['WANNBEZAHLT'].'</td></tr>'."\n";
            echo '</table>'."\n";
*/
            # Platzreservierung
            if (($rowAstatus['STATUS'] == $STATUS_BEZAHLT) or ($rowAstatus['STATUS'] == $STATUS_BEZAHLT_LOGE)) {
                $hatBezahlt = 1;
	    }
            $vorhanden = 1;

            echo "</td></tr></form>\n";
        }

	if ($vorhanden == 0) {
            echo '<tr><td class="dblau" width="50" colspan="3" align="center"><b>Keine Berechtigung<b></td></tr>'."\n";
	}
        echo '</table></td></tr></table><br>'."\n";
    }

    # Sitzplatzreservierung
    if (($_GET['id'] > 0) and ($hatBezahlt == 1)) {
        echo '<table cellspacing="0" cellpadding="0" border="0" width="600">'."\n";
        echo '<tr><td class="navbar">'."\n";
        echo '<table cellspacing="1" cellpadding="3" border="0" width="100%">'."\n";
        echo '<tr><td class="navbar" colspan="10"><b>Sitzplatz-Reservierung</b></td></tr>'."\n";

        # Auf welchen Parties darf man reservieren?
        $sql = "SELECT m.MANDANTID, m.BESCHREIBUNG
                FROM MANDANT m, RECHTZUORDNUNG r
                WHERE
                    r.USERID = $loginID AND
                    m.MANDANTID = r.MANDANTID AND
                    r.RECHTID = 'USERADMIN'";
        $resMandant = DB::query($sql);

        while ($rowMandant = $resMandant->fetch_array()) {
            echo '<tr><td class="dblau" colspan="5">'."\n";
            echo '<table width="100%" border="0"><tr>'."\n";
            echo '<td align="left"><b>'.$rowMandant['BESCHREIBUNG'].'</b></td>'."\n";
            echo '<td align="right">'."\n";

            # Welche Ebenen gibt es auf den Parties?
            $sql = "SELECT DISTINCT s.EBENE
                    FROM SITZDEF s, ASTATUS a
                    WHERE
                        s.MANDANTID='".$rowMandant['MANDANTID']."' AND
                        s.MANDANTID=a.MANDANTID AND
                        a.USERID = '".intval($_GET['id'])."' AND
                        a.STATUS > 0
                    ORDER BY s.EBENE";
            $resEbene = DB::query($sql);
            while ($row = $resEbene->fetch_array()) {
                echo '<a href="sitzplan.php?nPartyID='.$rowMandant['MANDANTID'].'&ebene='.$row['EBENE'].'&userID='.intval($_GET['id']).'" target="_blank">Ebene '.$row['EBENE'].'</a> '."\n";
	    }

            # Für neuen Sitzplan - DEAKTIVIERT
            /*
            if (istSitzplanAdmin($loginID)){
              $liste = sitzBlockListe($rowMandant['MANDANTID'], $_GET['id']);
              echo "</td><td>Ebenen nach neuem System: $liste\n";
            }
            */
              echo "</td></tr></table></td></tr>\n";

            # Welche Plätze sind dem User zugewiesen, und wer hat reserviert?
            $sql = "SELECT DISTINCT
                        u1.USERID USERID1,
                        u1.LOGIN LOGIN1,
                        u2.USERID USERID2,
                        u2.LOGIN LOGIN2,
                        CONCAT(s.REIHE, '-', s.PLATZ) PLATZ,
                        s.WANNRESERVIERT,
                        s.RESTYP
                    FROM
                        SITZ s,
                        MANDANT m2
                        LEFT JOIN USER u1 ON (s.USERID = u1.USERID)
                        LEFT JOIN USER u2 ON (s.WERGEAENDERT = u2.USERID)
                    WHERE
                        s.MANDANTID = " . $rowMandant['MANDANTID'] . " AND
                        u1.USERID = '".intval($_GET['id'])."'";
            $resPlatz = DB::query($sql);
            while ($row = $resPlatz->fetch_array()) {
                echo '<tr><td class="hblau">Gesetzt von: ';
                if (isset($row['USERID2'])) {
                    echo '<a href="' . $PHP_SELF . '?id=' . $row['USERID2'] . '">' . $row['LOGIN2'] . "</a></td>\n";
                } else {
                    echo '<a href="' . $PHP_SELF . '?id=' . $row['USERID1'] . '">' . $row['LOGIN1'] . "</a></td>\n";
		}
                echo '<td class="hblau" width="100" align="center">Platz: ' . $row['PLATZ'] . '</td>';
                $date = $row['WANNRESERVIERT'];
                echo '<td class="hblau" width="140" align="center">' . db2display($date) . '</td>';
                echo '<td class="hblau" width="100" align="center">';
                switch($row['RESTYP']) {
                    case 1:
                    case 3:
                            echo 'Reservierung';
                            break;
                    case 2:
                            echo 'Vormerkung';
                            break;
                     default:
                            echo 'unknown';
                            break;
                }
                echo "</td></tr>\n";
               }
        }
        echo "</table></td></tr></table><br/>\n";
    }

    # Logbuch: Letzte Actions aus der Logging-Tabelle
    if ($_GET['id'] > 0) {
        echo '<table cellspacing="0" cellpadding="0" border="0" width="600">'."\n";
        echo '<tr><td class="navbar">'."\n";
        echo '<table width="100%" cellspacing="1" cellpadding="3" border="0">'."\n";
        echo '<tr><td class="navbar"><b>Logbuch-Eintrag</b></td><td class="navbar"><b>Datum</b></td><td class="navbar"><b>Kategorie</b></td></tr>'."\n";
    
        $sTBG = 'dblau';
        $sql = "
	    SELECT *
            FROM logging
            WHERE userID = '".intval($_GET['id'])."'
            ORDER BY time DESC
            LIMIT 6";
        $res = DB::query($sql);
        while ($row = $res->fetch_array()) {
            echo '<tr><td class="'.$sTBG.'">'.db2display($row['msg']).'</td><td class="'.$sTBG.'">'.dateDisplay2($row['time']).'</td><td class="'.$sTBG.'">'.$row['cat'].'</td></tr>'."\n";
            if ($sTBG == 'dblau') {
              $sTBG = 'hblau';
            } else {
              $sTBG = 'dblau';
            }
        }
        echo '</table></td></tr></table></form><br>'."\n";
    }

    # Rechtetabelle
    if ($_GET['id'] > 0) {
        echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.intval($_GET['id']).'" name="details">'."\n";
        echo '<input type="hidden" name="actionID" value="rechte">'."\n";
        echo '<table cellspacing="0" cellpadding="0" border="0">'."\n";
        echo '<tr><td class="navbar">'."\n";
        echo '<table cellspacing="1" cellpadding="3" border="0">'."\n";
        echo '<tr><td class="navbar" colspan="10"><b>Rechte</b></td></tr>'."\n";

        # Welche Mandanten dürfen editiert werden?
        $sql = "SELECT DISTINCT m.MANDANTID, m.BESCHREIBUNG
                FROM MANDANT m, RECHTZUORDNUNG r
                WHERE
                    r.USERID = '".intval($loginID)."' AND
                    r.RECHTID = 'MANDANTADMIN' AND
                    r.MANDANTID = m.MANDANTID";
        $res = DB::query($sql);
        $r_mandanten = array();
        while ($row = $res->fetch_assoc()) {
            array_push($r_mandanten, $row);
	}

        # Welche Rechte gibt es im System?
        $res = DB::query('SELECT RECHTID FROM RECHT');
        $r_rechte = array();
        while ($row = $res->fetch_assoc())
            array_push($r_rechte, $row);

        # Welche Rechte, in welchem Mandanten hat das Opfer?
        $sql = "SELECT RECHTID, MANDANTID
                FROM RECHTZUORDNUNG
                WHERE USERID = '".intval($_GET['id'])."'";
        $res = DB::query($sql);
        $flags = array();
        while ($row = $res->fetch_assoc()) {
            array_push($flags, $row);
	}

        echo '<tr><td width="100" align="center" class="dblau">Rechte</td>'."\n";
        foreach ($r_mandanten as $mandant) {
            echo '<td width="100" align="center" class="dblau">'.$mandant['BESCHREIBUNG'].'</td>'."\n";
	}
        echo '</tr>'."\n";

        foreach ($r_rechte as $recht) {
            echo '<tr><td width="100" class="dblau">'.$recht['RECHTID'].'</td>'."\n";
            foreach ($r_mandanten as $mandant) {
                $found = '';
                foreach ($flags as $lala) {
                    if (($lala['RECHTID'] == $recht['RECHTID']) and ($lala['MANDANTID'] == $mandant['MANDANTID'])) {
                        $found = ' checked="checked"';
                        break;
                    }
                }
                echo '<td width="100" class="hblau" align="center">'."\n";
                echo '<input type="checkbox" value="X" name="recht['.$recht['RECHTID'].']['.$mandant['MANDANTID'].']"'.$found.'></td>'."\n";
            }
            echo '</tr>';
        }
        echo '<tr><td colspan="10" class="dblau" height="50" align="center">'."\n";
        echo '<input type="submit" value="Daten speichern"></td></tr>'."\n";
        echo '</td></tr></table></tr></table></form>'."\n";
    }
}

# Bei den Meinzelmännchen reinschauen...
$dbh = DB::connect();

$form = $_POST['formVars'];
if ($form['alter_userid'] != $form['userid']) {
    # Neue ID eingetippt, danach in der DB suchen.
    $sql = "SELECT USERID
           FROM USER
           WHERE USERID = '$form[userid]'";
    $res = DB::query($sql);
    $row = $res->fetch_array();
    if ($row) {
         $_GET['id'] = $row['USERID'];
    } else {
          PELAS::fehler("Kein User mit der ID $form[userid] in Datenbank gefunden!");
        $_GET['id'] = 0;
    }
    unset($_POST['actionID']);
}

if (!isset($_GET['id'])) {
    # Wenn ohne ID aufgerufen, nichts machen.
    unset($_POST['actionID']);
} elseif (!isset($_POST['actionID']) or ($_POST['actionID'] != 'daten')) {
    # wenn wir eine ID haben, aber keine actionID, dann daten zu ID holen
    # (wir wurden das erstemal aufgerufen, ohne ?id=xx)
    if ($_GET['id'] > 0) {
        # Daten zu ID holen.
        $sql = "select *
                from USER
                where USERID = '".intval($_GET['id'])."'";
        $res = DB::query($sql);
        $row = $res->fetch_array();

        $formVars['userid']            = $row['USERID'];
        $formVars['alter_userid']    = $row['USERID'];
        $formVars['login']            = $row['LOGIN'];

        $formVars['email']            = $row['EMAIL'];
        $formVars['name']            = $row['NAME'];
        $formVars['nachname']        = $row['NACHNAME'];
        $formVars['strasse']        = $row['STRASSE'];
        $formVars['plz']            = $row['PLZ'];
        $formVars['ort']            = $row['ORT'];

        $formVars['komment']        = $row['KOMMENTAR_PUBLIC'];
        $formVars['komment_intern']    = $row['KOMMENTAR_INTERN'];
        $formVars['homepage']        = $row['HOMEPAGE'];
        $formVars['personr']        = $row['PERSONR'];
		$formVars['skype']        = $row['SKYPE'];
        $formVars['wwcl_single']    = $row['WWCL_SINGLE'];
        $formVars['wwcl_team']        = $row['WWCL_TEAM'];
        $formVars['kein_mailing']    = $row['KEIN_MAILING'];
        $formVars['ngl_single']        = $row['NGL_SINGLE'];
        $formVars['ngl_team']        = $row['NGL_TEAM'];

				$oldData = $formVars[login]." ".$formVars[name]." ".$formVars[nachname]." ".$formVars[plz]." ".$formVars[email];

    } else {
        unset($formVars);
    }

    if (User::hatRecht('TEAMMEMBER', $_GET['id'])) {
        # Wenn Teammember, dann auch die Teamdaten holen.
        $sql = 'SELECT *
                FROM USER_EXT
                WHERE USERID = ' . intval($_GET['id']);
	$row = DB::getRow($sql);

        $formVars['team_telefon'] = $row['TELEFON'];
        $formVars['team_mobil'] = $row['MOBIL'];
        $formVars['team_teilteam'] = $row['TEILTEAMID'];
        $formVars['team_aufgabe'] = $row['AUFGABE'];
        $formVars['team_kommentar'] = $row['KOMMENTAR'];
        $formVars['team_geburtstag'] = $row['GEBURTSTAG'];
        $formVars['team_inaktiv'] = $row['INAKTIV'];

        $eArray = explode('-', $formVars['team_geburtstag']);
        $formVars['team_Jahr'] = $eArray[0];
        $formVars['team_Monat'] = $eArray[1];
        $formVars['team_Tag'] = $eArray[2];
    }

}

if ($_POST['actionID'] == 'daten') {
    $formVars = $_POST['formVars'];

    # OK, Daten geändert/erstellt.
	require_once('checkrights.php');
	
	if (! User::hatRecht('USERADMIN')) {
		if ( $_GET["userid"] == $loginID ) {
			if ( $iRecht != 'TEAMMEMBER' ) 
			{
				PELAS::fehler('Sie haben nur die Berechtigung Daten zu lesen, nicht zu ändern!');
			}
		}
	} elseif (empty($formVars['login']) or
        empty($formVars['email']) or
        empty($formVars['name']) or
        empty($formVars['nachname'])) {
        PELAS::fehler('Bitte alle Mussfelder ausfüllen!');
    } elseif ($formVars['password1'] != $formVars['password2']) {
        PELAS::fehler('Du hast das Passwort nicht richtig bestätigt!');
    } elseif (strstr($formVars['login'], ';') != '') {
        PELAS::fehler('Ein Semikolon im Login ist nicht erlaubt!');
    } elseif ((strstr($formVars['login'], chr(92)) != '') or (strstr($formVars['password1'], chr(92)) != '')) {
        PELAS::fehler('Es befindet sich ein unerlaubtes Zeichen im Login oder Passwort!');
    } elseif ($vorhanden != '') {
        # FIXME: WTF?
        PELAS::fehler('Dieser Login/Nickname ist bereits registriert!');
    } else {
		#check if INAKTIV is empty
		if(empty($formVars[team_inaktiv])){
			$formVars[team_inaktiv]=0;
		}
		
        # Alles klar, INSERT oder UPDATE.
        if (empty($_GET['id'])) {
					$sql = "INSERT INTO USER
                    SET
                        LOGIN = '$formVars[login]',
                        EMAIL = '$formVars[email]',
                        NAME = '$formVars[name]',
                        NACHNAME = '$formVars[nachname]',
                        STRASSE = '$formVars[strasse]',
                        PLZ = '$formVars[plz]',
                        ORT = '$formVars[ort]',
                        KOMMENTAR_PUBLIC = '$formVars[komment]',
                        KOMMENTAR_INTERN = '$formVars[komment_intern]',
                        HOMEPAGE = '$formVars[homepage]',
                        PERSONR = '$formVars[personr]',
						SKYPE = '$formVars[skype]',
                        WWCL_SINGLE = '$formVars[wwcl_single]',
                        WWCL_TEAM = '$formVars[wwcl_team]',
                        NGL_SINGLE = '$formVars[ngl_single]',
                        NGL_TEAM = '$formVars[ngl_team]',
                        KEIN_MAILING = '$formVars[kein_mailing]',
                        WERANGELEGT = '$loginID',
                        WANNANGELEGT = NOW(),
                        WERGEAENDERT = '$loginID'";
            ## echo "<p>$sql</p>\n";
            DB::query($sql);
            
            # User-ID rausfinden.
            $sql = '
	        SELECT USERID
           	FROM USER
           	WHERE LOGIN = "' . $formVars['login'] . '"';
            $theLoginID = DB::getOne($sql);
            
            # Datensatz in ASTATUS anlegen.
            $sql = "INSERT INTO ASTATUS
                    SET
                         MANDANTID = '$formVars[mandantzuordnung]',
                         USERID = $theLoginID,
                         STATUS = 0,
                         WERANGELEGT = '$loginID',
                         WANNANGELEGT = NOW(),
                         WERGEAENDERT = '$loginID'
            ";
            DB::query($sql);

						// Hash des eingegebenen Passwortes suchen
						$pwHash = PELAS::HashPassword ($formVars[password1], $theLoginID);
						$sql = "UPDATE USER set PASSWORD_HASH = '$pwHash' where USERID = '$theLoginID'";
						DB::query($sql);

            PELAS::confirm('Benutzer wurde mit der Benutzer-ID ' . $theLoginID . ' angelegt.');
	    $_GET['id'] = $theLoginID;
        } else {
            $sql = "UPDATE USER
                  SET
                    LOGIN = '$formVars[login]',
                    EMAIL = '$formVars[email]',
                    NAME = '$formVars[name]',
                    NACHNAME = '$formVars[nachname]',
                    STRASSE = '$formVars[strasse]',
                    PLZ = '$formVars[plz]',
                    ORT = '$formVars[ort]',
                    KOMMENTAR_PUBLIC = '$formVars[komment]',
                    KOMMENTAR_INTERN = '$formVars[komment_intern]',
                    HOMEPAGE = '$formVars[homepage]',
                    PERSONR = '$formVars[personr]',
                    SKYPE = '$formVars[skype]',
					WWCL_SINGLE = '$formVars[wwcl_single]',
                    WWCL_TEAM = '$formVars[wwcl_team]',
                    NGL_SINGLE = '$formVars[ngl_single]',
                    NGL_TEAM = '$formVars[ngl_team]',
                    KEIN_MAILING = '$formVars[kein_mailing]',
                    WERGEAENDERT = '$loginID'
                  WHERE USERID = '".intval($_GET['id'])."'";
            
            DB::query($sql);

						// logging wenn Name oder PLZ geändert
						$newData = $formVars[login]." ".$formVars[name]." ".$formVars[nachname]." ".$formVars[plz]." ".$formVars[email];
						if ($oldData != $newData) {
							PELAS::logging("Userdata changed from $oldData to $newData", "admin", $loginID);
						}

						// Wenn Passwort eingegeben wurde, nur dann auch dieses aktualisieren
						if (strlen($formVars[password1]) >= 3) {
							$pwHash = PELAS::HashPassword ($formVars[password1], $_GET['id']);
							$sql = "UPDATE USER set PASSWORD_HASH = '$pwHash' where USERID = '".$_GET['id']."'";
							DB::query($sql);
						} else {
							echo "<p>Hinweis: Passwort wird nicht aktualisiert.</p>";
						}


            if (User::hatRecht('TEAMMEMBER', $_GET['id'])) {
                $geburtstag = $formVars['team_Jahr'] . '-' . $formVars['team_Monat'] . '-' . $formVars['team_Tag'];

                $sql = 'SELECT * FROM USER_EXT WHERE USERID = ' . $_GET['id'];
                $res = DB::query($sql);
                if ($res and ($res->num_rows == 0)) {
                    # Kein Datensatz vorhanden, einfügen.
                    $sql = "
		        INSERT INTO USER_EXT
                        SET
                             USERID = '".$_GET['id']."',
                             TELEFON = '$formVars[team_telefon]',
                             MOBIL = '$formVars[team_mobil]',
                             TEILTEAMID = '$formVars[team_teilteam]',
                             AUFGABE = '$formVars[team_aufgabe]',
                             KOMMENTAR = '$formVars[team_kommentar]',
                             GEBURTSTAG = '$geburtstag',
                             INAKTIV = '$formVars[team_inaktiv]'
			";
                } else {
                    # Aktualisieren.
                    $sql = "
		        UPDATE USER_EXT
                        SET
                             TELEFON = '$formVars[team_telefon]',
                             MOBIL = '$formVars[team_mobil]',
                             TEILTEAMID = '$formVars[team_teilteam]',
                             AUFGABE = '$formVars[team_aufgabe]',
                             KOMMENTAR = '$formVars[team_kommentar]',
                             GEBURTSTAG = '$geburtstag',
                             INAKTIV = '$formVars[team_inaktiv]'
                        WHERE USERID = '".intval($_GET['id'])."'";
                }
                DB::query($sql);
                PELAS::confirm('Daten gespeichert.');
            }
        }
        echo '<p><a href="benutzerverwaltung.php">Zurück zur Benutzerverwaltung</a></p>' . "\n";
    }

} elseif (($_POST['actionID'] == 'rechte') and User::hatRecht('MANDANTADMIN')) {
    # Welche Rechte, in welchem Mandanten hat das Opfer?
    $sql = "SELECT RECHTID, MANDANTID
            FROM RECHTZUORDNUNG
            WHERE USERID = '".intval($_GET['id'])."'";
    $res = DB::query($sql);

    $flags = array();
    while ($row = $res->fetch_assoc()) {
        array_push($flags, $row);
    }

    # Welche Mandanten dürfen wir überhaupt verändern?
    $sql = "SELECT MANDANTID
            FROM RECHTZUORDNUNG
            WHERE USERID = '{$loginID}'
	      AND RECHTID = 'MANDANTADMIN'";
    $res = DB::query($sql);
    $allowed = array();
    while ($row = $res->fetch_assoc()) {
        $allowed[$row['MANDANTID']] = True;
    }

    # IST(db) mit SOLL(form) vergleichen.
    foreach ($flags as $flag) {
	if (! isset($allowed[$flag['MANDANTID']]))
		continue;

        if (! isset($_POST['recht'][$flag['RECHTID']][$flag['MANDANTID']])) {
            # Das Recht ist in der DB aber nicht im Form -> Recht entziehen.
            $sql = "DELETE FROM RECHTZUORDNUNG
                    WHERE USERID = '".$_GET['id']."'
                      AND MANDANTID = '".$flag['MANDANTID']."'
                      AND RECHTID = '".$flag['RECHTID']."'";
            DB::query($sql);
        }
    }

    # SOLL(form) mit IST(db) vergleichen -> Recht hinzufügen.
    foreach ($_POST['recht'] as $recht => $tmp1) {
        foreach ($tmp1 as $mandant => $tmp2) {
            if (! isset($allowed[$mandant])) {
                continue;
	    }
            $found = 0;
            foreach ($flags as $flag) {
                if ($flag['MANDANTID'] == $mandant && $flag['RECHTID'] == $recht) {
                    $found= 1;
                    break;
                }
            }
            if ($found == 0) {
                $sql = "INSERT INTO
                            RECHTZUORDNUNG (USERID, MANDANTID, RECHTID, WERANGELEGT, WANNANGELEGT)
                          VALUES
                              ('".$_GET['id']."', '".$mandant."', '".$recht."', ".$loginID.", NOW())";
                DB::query($sql);
            }
        }
    }
    PELAS::confirm('Rechte gespeichert.');
}

show_form();
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/

require('admin/nachspann.php');
?>
