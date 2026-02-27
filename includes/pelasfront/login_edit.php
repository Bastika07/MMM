<?php
echo '<style type="text/css">.modal {
display:none!important;
}</style>';
?>
<?php


/*
    Changelog
    =========
    10.03.2004 - dne :  Überprüfung ob nur ein Leerzeichen hintereinande im Login ist:
                        " " erlaubt, "  " oder "^ " oder " $" sind verboten
    22.04.2004 - mgi :  Bestätigung für AGB eingebaut
	28.05.2008 - mgi :  Geburtsdatum eingebaut
	10.11.2013 - azi :  Geburtsdatum seit langem deaktiviert, daher entfernt
						PHPBB-Anbindung seit langem deaktiviert, daher entfernt
	19.11.2013 - azi :  Beben-bezogener Code entfernt
	20.11.2013 - azi :  fixed security issues (XSS)
 */

require_once "dblib.php";
include_once "format.php";
include_once "pelasfunctions.php";
include_once "session.php";
include_once "language.inc.php";

################################################################
# Neu: Newsletter-Abmeldung passiert über dieses Script
if (isset($_GET['action']) && $_GET['action'] == "unsubscribe" && isset($_GET['code']) && isset($_GET['id'])) {
	
	# Abmelde-Code verifizieren
	$sql = "SELECT user_id
					FROM mailing_execute
					WHERE user_id = '".intval($_GET['id'])."'
						AND code = '".safe($_GET['code'])."'
						AND sent IS NOT NULL";
	$uns_id = DB::getOne($sql);
	$erfolg = false;
	if (is_numeric($uns_id)) {
		# OK, abmelde-Flag setzen
		$sql = "UPDATE 
					USER
				SET 
					KEIN_MAILING = 'J',
					NEWSLETTER = 0, 
					NEWSLETTER_ABO_DATE = NULL, 
					NEWSLETTER_ABO_CODE='', 
					NEWSLETTER_ABO_EMAIL=NULL
				WHERE 
					USERID = '".intval($uns_id)."'";
		$erfolg = DB::query($sql);
	}
	# Erfolgsmeldung ausgeben
	if ($erfolg == true)
		echo "<p>Die Abmeldung von unserem Mailing-System war erfolgreich.<br>Du kannst Deinen Mailing-Versand zu jeder Zeit unter Persönliche Daten wieder aktivieren.</p>";
	else
		echo "<p>Es gab ein Problem bei der Abmeldung von unserem Mailing.<br>Wende Dich bitte direkt per E-Mail an uns und wir melden Dich per Hand ab.</p>\n";
	
	goto ende;
	
}
####### Ende: Newsletter-Abmeldung #############################

################################################################
# Neu: Newsletter-Anmeldung passiert über dieses Script
if (isset($_GET['action']) && $_GET['action'] == "subscribe" && isset($_GET['code']) && isset($_GET['id'])) {
	
	# Anmelde-Code verifizieren
	$sql = "SELECT USERID
					FROM USER
					WHERE USERID = '".intval($_GET['id'])."'
						AND NEWSLETTER_ABO_CODE = '".safe($_GET['code'])."'";
	$uns_id = DB::getOne($sql);
	$erfolg = false;

	if ($uns_id == 0) {
		echo "<p>Du bist bereits für den MultiMadness-Newsletter angemeldet oder benutzt einen ungültigen Code.</p>";
		echo '<p>Prüfe Deinen Status bitte in Deinen <a href="?page=11" class="arrow">Personlichen daten</a></p>';
		goto ende;
	}
	
	if (is_numeric($uns_id)) {
		# OK, anmelde-Flag setzen
		# ACHTUNG: Immer KEIN_MAILING = N sowie NEWSLETTER = 1 setzen!
		$sql = "UPDATE 
					USER
				SET 
					KEIN_MAILING = 'N',
					NEWSLETTER = 1, 
					NEWSLETTER_ABO_DATE = NOW(), 
					NEWSLETTER_ABO_CODE=NULL, 
					NEWSLETTER_ABO_EMAIL=NULL
				WHERE 
					USERID = '".intval($uns_id)."'";
		$erfolg = DB::query($sql);
	}
	# Erfolgsmeldung ausgeben
	if ($erfolg == true)
		echo "<p>Vielen Dank! Deine Anmeldung zum MultiMadness-Newsletter war erfolgreich.</p>";
	else
		echo "<p>Es gab ein Problem bei der Anmeldung zum MultiMadness-Newsletter.<br>Wende Dich bitte direkt per E-Mail an uns.</p>\n";
	
	goto ende;
	
}
####### Ende: Newsletter-Anmeldung #############################

################################################################
# Neu: Newsletter-Anmeldung via Rundmail
if (isset($_GET['action']) && $_GET['action'] == "subscribeRM" && isset($_GET['code']) && isset($_GET['id'])) {
	
	# Anmelde-Code verifizieren
	$sql = "SELECT user_id
					FROM mailing_execute
					WHERE user_id = '".intval($_GET['id'])."'
						AND code = '".safe($_GET['code'])."'
						AND sent IS NOT NULL";
	$uns_id = DB::getOne($sql);
	$erfolg = false;
	
		if ($uns_id == 0) {
		echo "<p>Du bist bereits für den MultiMadness-Newsletter angemeldet oder benutzt einen ungültigen Code.</p>";
		goto ende;
	}
	
	if (is_numeric($uns_id)) {
		# OK, abmelde-Flag setzen
		$sql = "UPDATE 
					USER
				SET
					KEIN_MAILING = 'N',
					NEWSLETTER = 1, 
					NEWSLETTER_ABO_DATE = NOW(), 
					NEWSLETTER_ABO_CODE=NULL, 
					NEWSLETTER_ABO_EMAIL=NULL, 
					POPUP_DISPLAY = 0
				WHERE 
					USERID = '".intval($uns_id)."'";
		$erfolg = DB::query($sql);
	}
	# Erfolgsmeldung ausgeben
	if ($erfolg == true)
		echo "<p>Vielen Dank! Deine Anmeldung zum MultiMadness-Newsletter war erfolgreich.</p>";
	else
		echo "<p>Es gab ein Problem bei der Anmeldung bei unserem Mailing.<br>Wende Dich bitte direkt per E-Mail an uns.</p>\n";
	
	goto ende;
	
}
####### Ende: Newsletter-Anmeldung #############################

$useSessionId = session_id();

if ($nLoginID == "") {
	$actionID = 1;
} else {
	$actionID = 2;
	if (!isset($_POST['iLogin'])) {

		$result = DB::query("select * from USER where USERID = '".safe($nLoginID)."'");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$row = $result->fetch_array();

		$_POST['iLogin'] = $row['LOGIN'];
		$_POST['iAlterLogin']  = $row['LOGIN'];
		$_POST['iEmail']       = $row['EMAIL'];
		$_POST['iAlterEmail']  = $row['EMAIL'];
		$_POST['iName']        = $row['NAME'];

		$_POST['iNachName']    = $row['NACHNAME'];

		$_POST['iStrasse']     = $row['STRASSE'];
		$_POST['iPLZ']         = $row['PLZ'];
		$_POST['iOrt']         = $row['ORT'];
		$_POST['iLand']        = strtolower($row['LAND']);
		$_POST['iHomepage']    = $row['HOMEPAGE'];

		$_POST['tshirt']					= $row['SHIRTSIZE'];

		$_POST['iKommentar']   = $row['KOMMENTAR_PUBLIC'];

		$_POST['iPersoNr']     = $row['PERSONR'];

		$_POST['iNewsletterAbo'] = $row['NEWSLETTER'];
		$_POST['oldNewsletterAbo'] = $row['NEWSLETTER'];
		//$_POST['NewsletterAboEMailDate'] = $row['NEWSLETTER_ABO_EMAIL'];
		if($row['NEWSLETTER_ABO_EMAIL'] != '0000-00-00 00:00:00') 
		{ 
			$_POST['NewsletterAboEMailDate'] = $row['NEWSLETTER_ABO_EMAIL'];
		};
		
		$_POST['oldData']     = db2display($row['LOGIN']." ".$row['NAME']." ".$row['NACHNAME']." ".$row['PLZ']." ".$row['EMAIL']);

		// AGB und Datenschutz anchecken wenn schonmal eingeloggt
		$_POST['iAGB']         = "ok";
		$_POST['iDS']         = "ok";
	}
}
if (!isset($_POST['action'])) {
	$_POST['action'] = '';
}
if ($_POST['action'] == 'upload' && $nLoginID > 0) {
	// Bild-Upload verarbeiten
	if (isset($_FILES['iUserbild'])) {
		$size = getimagesize($_FILES['iUserbild']['tmp_name']);
	}
    if ($_FILES['iUserbild']['size'] > 51200) {
      // Größe prüfen
      echo "<p style=\"color: red\">$str[zugross]</p>";
    } else if ($size[0] > 110 && $size[1] > 150) {
      echo "<p style=\"color: red\">$str[bildZuHochBreit]</p>";
    } else if ($size[0] > 110) {
      echo "<p style=\"color: red\">$str[bildZuBreit]</p>";
    } else if ($size[1] > 150) {
      echo "<p style=\"color: red\">$str[bildZuHoch]</p>";
    } else {
		$newfile = PELASDIR."userbild/".intval($nLoginID).".jpg";
		// Falls die Datei nicht mehr den passenden Eigentümer (www-data) hat, löschen wir sie zuerst.
		// Das geht, da der Ordner www-data gehört.
		if (file_exists($file) && !is_writeable($newfile)) {
			unlink($file);
		}
		if (!move_uploaded_file($_FILES['iUserbild']['tmp_name'], $newfile)) {
			echo "<p style=\"color: red\">$str[interrror]</p>";
		} else {
		  chmod($newfile, 0664);
		  DB::query("UPDATE USER set BILD_VORHANDEN='J' where USERID=".intval($nLoginID));
			$res = DB::query("select MANDANTID from ASTATUS where USERID=".intval($nLoginID));
			while ($row = $res->fetch_assoc()) {
				$result2 = DB::query("UPDATE CONFIG set STRINGWERT='".intval($nLoginID)."' where PARAMETER='NEUESTES_GESICHT' and MANDANTID=$row[MANDANTID]");
				//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			}
		}
	}
}


// Formular für die Dateineingabe anzeigen
function show_form ()
{
	global 	$useSessionId, $captcha, $sLang, $oldData, $iAGB, $str, $buMeldung, $nPartyID, $PELASSESSID, $sMeldung, $i_userbild, $nLoginID, $iLogin, $actionID, $iAlterLogin, $iPassword1, $iPassword2, $iEmail, $iName, $iNachName, $iStrasse, $iPLZ, $iOrt, $iLand, $iHomepage, $tshirt, $iKommentar, $iPersoNr, $iDS, $iNewsletterAbo;

	if ($actionID == 1) {
		echo "<p class=\"hervorgehoben\"><b>".$str['erstellen']."</b></p>";
	} else {
		printf ('<p class=\"hervorgehoben\"> <b>%s</b>
					&nbsp;<a class="arrow" href="?page=4&nUserID=%s">Eigenes Profil ansehen</a>
					&nbsp;<a class="arrow" href="?page=6">Ticketverwaltung</a></p>',
				$str['datenaendern'], $nLoginID);
	}
	echo $sMeldung."\n";

	// echo "<p align=\"justify\">".$str['einleitung']."</p>";

	?>

	<form method="post" action="">
	<?= csrf_field() ?>

	<input type="hidden" name="oldData" value="<?php echo htmlspecialchars($oldData); ?>">

	<TABLE>
		<?php if ($actionID != 1) {
			echo "<TR><TD>".$str['deineid'].":</TD><TD>".htmlspecialchars($nLoginID)."</TD></TR>\n";
		} ?>
		<TR><TD>Login/ Nickname:</TD><TD><input type="text" name="iLogin" size="25" maxlength="30" value="<?php echo htmlspecialchars($_POST['iLogin']); ?>" autocomplete="new-password"> *</TD></TR>
		<TR><TD><?=$str['passwort']?>:</TD><TD><input type="password" name="iPassword1" size="25" maxlength="30" autocomplete="new-password">
		<?php
		if ($actionID != 1) {
			echo "<small>".$str['pwhinweis']."</small>";
		} else {
			echo "*";
		}
		?>
		</TD></TR>
		<TR><TD><?=$str['pwbest']?>:</TD><TD><input type="password" name="iPassword2" size="25" maxlength="30" autocomplete="new-password">
		<?php
		if ($actionID == 1) {
			echo "*";
		}
		?>
		</TD></TR>
		<tr><td colspan="2">&nbsp;</td></tr>
			<TR><TD><?=$str['email']?>:</TD><TD><input type="text" NAME="iEmail" size=25 maxlength=40 value="<?php echo htmlspecialchars($_POST['iEmail']); ?>"> *</TD></TR>
			<TR><TD><?=$str['name']?>:</TD><TD><input type="text" NAME="iName" size=25 maxlength=35 value="<?php echo htmlspecialchars($_POST['iName']); ?>"> *</TD></TR>

			<TR><TD><?=$str['nachname']?>:</TD><TD><input type="text" NAME="iNachName" size=25 maxlength=35 value="<?php echo htmlspecialchars($_POST['iNachName']); ?>"> *</TD></TR>

			<TR><TD><?=$str['strasse']?>:</TD><TD><input type="text" NAME="iStrasse" size=25 maxlength=40 value="<?php echo htmlspecialchars($_POST['iStrasse']); ?>"></TD></TR>
			<TR><TD><?=$str['plz']?>:</TD><TD><input type="text" NAME="iPLZ" size=6 maxlength=10 value="<?php echo htmlspecialchars($_POST['iPLZ']); ?>"> *</TD></TR>
			<TR><TD><?=$str['ort']?>:</TD><TD><input type="text" NAME="iOrt" size=25 maxlength=40 value="<?php echo htmlspecialchars($_POST['iOrt']); ?>"></TD></TR>
			<TR><TD><?=$str['land']?>:</TD><TD><select NAME="iLand">
			<?php
				// Welches selektiert?
				$bKeinLand = 0;
				if ($_POST['iLand'] == "") {
					// noch keines Selektiert, ISO-Code aus Browser lesen
					$iLandArray = explode(",", strtolower(getenv("HTTP_ACCEPT_LANGUAGE")));
					$_POST['iLand'] = $iLandArray[0];
					$bKeinLand  = 1;
				}
				// Länder aus DB holen
				$sql = "select
					isoCode,
					isoLanguageCode,
					descGerman,
					descEnglish
					from
					acc_flags
				";
				$result = DB::query($sql);
				if ($sLang == "en") {
					$feld = "descEnglish";
				} else {
					$feld = "descGerman";
				}
				while ($row = $result->fetch_array()) {
					echo "<option value=\"".db2display(strtolower($row['isoCode']))."\"";

					if ($bKeinLand == 1 && strlen($_POST['iLand']) <= 2) {
						// Wenn die Sprache des Browsers 2 zeichen lang ist, dann den isoLanguageCode vergleichen
						if ($_POST['iLand'] == strtolower($row['isoLanguageCode'])) {
							echo " selected";
						}
					} elseif ($bKeinLand == 1) {
						// Wenn die Sprache des Brwoser mehr als 2 Zeichen lang ist, die letzten 2 Stellen mit dem isoCode vergleichen
						if (substr($_POST['iLand'], strlen($_POST['iLand'])-2, 2) == strtolower($row['isoCode'])) {
							echo " selected";
						}
					} else {
						// Wenn die Sprache des Brwoser mehr als 2 Zeichen lang ist, die letzten 2 Stellen mit dem isoCode vergleichen
						if ($_POST['iLand'] == strtolower($row['isoCode'])) {
							echo " selected";
						}
					}
					echo ">".db2display($row[$feld])."</option>\n";
				}
			?>
			</select> *</TD></TR>

			<tr><td colspan="2">&nbsp;</td></tr>

			<tr><td><?=$str['shirtsize']?></td>
			<td>
				<select name="tshirt">
	<?php
					// Shirtgrößen aus DB holen
					$sql = "select
						sizeCode,
							sizeDesc
						from
						acc_tshirt
						order by
							sizeCode
					";
					$result = DB::query($sql);
					while ($row = $result->fetch_array()) {
						echo "<option value=\"".db2display($row['sizeCode'])."\"";
						if ($_POST['tshirt'] == $row['sizeCode']) {
							echo " selected";
						}
						echo ">".db2display($row['sizeDesc'])."</option>\n";
					}
	?>
				</select>
			</TD></TR>

			<TR><TD><?=$str['website']?>: (mit http://)</TD><TD><input type="text" NAME="iHomepage" size=25 maxlength=55 value="<?php echo htmlspecialchars($_POST['iHomepage']); ?>"></TD></TR>
			<TR><TD>Games: </TD><TD><input type="text" NAME="iKommentar" size=25 maxlength=55 value="<?php echo htmlspecialchars($_POST['iKommentar']); ?>"></TD></TR>

			<tr><td>&nbsp;</td></tr>


		
<?php	// Wenn bereits eine E-Mail zur Newsletterbestätigung verschickt wurde, dann hier anzeigen	
		if ($_POST['NewsletterAboEMailDate'])
			echo  '<tr><td colspan="2"><p class="fehler">'.$str['newsletter_abo_open'].date('d.m.Y H:i', strtotime($_POST['NewsletterAboEMailDate'])).'</p></td></tr>'; ?>
				

		<TR><TD colspan="2"><input type="checkbox" value="1" NAME="iNewsletterAbo"
			<?php	if ($_POST['iNewsletterAbo'] == "1") { echo " checked";} ?>> <?=$str['newsletter_abo']?>
			<input type="hidden" name="oldNewsletterAbo" value="<?= $_POST['oldNewsletterAbo']; ?>">
		</TD></TR>

<?php
	if ($actionID == 1)
	{	
		// Datenschutz und AGB-Boxen anhaken bei Anlage User
		?>
		<tr><td colspan="2"><input type="checkbox" value="ok" name="iAGB"
		<?php
		if ($_POST['iAGB'] == "ok") {
			echo "checked";
		};
		?>
		> <?=$str['agbakzeptiert']?>
		</td></tr>
		<tr><td colspan="2"><input type="checkbox" value="ok" name="iDS"
		<?php
		if ($_POST['iDS'] == "ok") {
			echo "checked";
		};
		?>
		> <?=$str['dsakzeptiert']?>
		</td></tr>
<?php
	}
	else
	{	?>
		<input type="hidden" name="iAGB" value="ok">
		<input type="hidden" name="iDS" value="ok">
<?php
	}	?>

	<?php
		// Captcha anzeigen bei Accounterstellung
		if ($actionID != 2) {

			echo "<tr><td>&nbsp;</td></tr>\n";
			echo "<tr><td colspan='2'>Sicherheitsabfrage: Bitte den Text aus dem Bild in das nebenstehende Feld eintragen</td></tr>\n";

			echo "<tr><td colspan='2' align='left'><table><tr><td>";

			// Captcha anzeigen und erstellen

			echo "<img src=\"".PELASHOST."showCaptcha.php?sid=".$useSessionId."\" width=\"230\" height=\"60\">";

			echo " </td><td> &nbsp; <input type='text' size='10' maxlength='10' name='captcha' value='".$_POST['captcha']."'> *</td></tr></table>";
			echo "</td></tr>";



		}
	?>

		<tr><td colspan="2" valign="bottom"><input name="speichern" type="submit" value="<?=$str['speichern']?>">
		<tr><td colspan="2"><hr></td></tr>
		</form>
		<tr><td valign="top"><?=$str['aktbild']?>:</td>
		<td>
		<?php
		if ($nLoginID == "") {
			echo "$str[bildnachlogin]";
		} else {
			echo "<table><tr><td valign=\"top\">";

			displayUserPic($nLoginID);

			echo "</td><td valign=\"top\">$str[voraussetzungen]: <br><li>JPEG-Format <li>$str[breite] 110 Px <li>$str[maxhoehe] 150 Px <li>$str[dateigroesse] 20 KB</td></tr></table></td></tr>";
			echo "<form method=\"post\" enctype=\"multipart/form-data\" name=\"bildupload\" action=\"?page=11\"><tr><td>$str[neuesbild]:</td><td><input type=\"file\" name=\"iUserbild\" size=\"25\">";
			echo csrf_field() . "\n";
			echo "</td></tr>\n";
			echo "<input type='hidden' name='action' value='upload'>\n";
			echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"$str[upload]\"></td></tr></form>\n";
		}
		?>
		</td></tr>
	</TABLE>

<?php
}



if (!isset($_POST['speichern']) ) {
	if (LOCATION == 'intranet') {
		echo "<p class='fehler'>Bitte im Intranet keine Accountänderungen vornehmen, da diese nicht auf den Internetserver zurück kopiert werden.</p>\n";
		goto ende;
	}
	show_form();
} else {
	if ($_POST['iLogin'] != $sLogin) {
		//DB nach Login durchstoebern
		$result = DB::query("select LOGIN from USER where LCASE(LOGIN) = LCASE('".safe($_POST[iLogin])."')");
		$vorhanden= "";
		while ($row = $result->fetch_array()) {
			$vorhanden = $row['LOGIN'];
		}
	} else {
		$vorhanden = "";
	}

	//##############################
	// Email checken (darf nicht doppelt vorkommen)
	$iEmail = strtolower($iEmail);
	$sql = "select LOGIN from USER where LCASE(EMAIL) = LCASE('".safe($_POST['iEmail'])."') and USERID!='".intval($nLoginID)."'";
	$result = DB::query($sql);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$e_vorhanden= "";
	if ($result->num_rows) $e_vorhanden = 1;
	//##############################
	// Email checken Ende

if ($actionID != 2) {
	// Bei Accounterstellung Captcha checken
$sql = 'SELECT
            *
        FROM
            `captcha`
        WHERE
            `captcha_phpsessid` = \''.$useSessionId.'\' and
            `captcha_captcha` = \''.safe($_POST['captcha']).'\' 
        LIMIT
            1';
	$result = DB::query($sql);
	if($result->num_rows > 0) {
		$captchaCheck = "1";
	} else {
		$captchaCheck = "-1";
	}
} else {
	$captchaCheck = "1";
}

	if ($captchaCheck != 1) {
		$sMeldung = "<p class='fehler'>Der Sicherheitscode ist falsch.</p>";
		show_form();
	} elseif ( empty($_POST['iLogin']) || empty($_POST['iEmail']) || empty($_POST['iName']) || empty($_POST['iNachName']) || empty($_POST['iPLZ']) ) {
		$sMeldung = "<p class='fehler'>$str[ausfuellen]</p>";
		show_form();
	} elseif ( empty($_POST['iPassword1']) && $actionID == 1) {
		$sMeldung = "<p class='fehler'>$str[ausfuellen]</p>";
		show_form();
	} elseif ( strlen($_POST['iLogin']) < 3) {
		$sMeldung = "<p class='fehler'>$str[login2short]</p>";
		show_form();
	} elseif ($_POST['iPassword1'] != $_POST['iPassword2']) {
		$sMeldung = "<p class='fehler'>$str[pwwrong]</p>";
		show_form();
        } elseif (strstr ($_POST['iLogin'],";") != "" ) {
		$sMeldung =  "<p class='fehler'>$str[unerlaubt]</p>";
		show_form();
	} elseif (strstr ($_POST['iLogin'],chr(92)) != "" || strstr ($_POST['iPassword1'],chr(92)) != "" ) {
		$sMeldung =  "<p class='fehler'>$str[unerlaubt]</p>";
		show_form();
	} elseif (!validEmail($_POST['iEmail'])) {
		$sMeldung = "<p class='fehler'>".$str['mail_ungueltig']."</p>";
		show_form();
	} elseif ($vorhanden != "" ) {
		$sMeldung =  "<p class='fehler'>$str[registriert]</p>";
		show_form();
	} elseif ($e_vorhanden != "") {
		$sMeldung =  "<p class='fehler'>$str[emailbenutzt]</p>";
		show_form();
 	} elseif ( preg_match('/  /', $iLogin) || preg_match('/^ /', $iLogin) || preg_match('/ $/', $iLogin) ) {
   	// hier ist was innem login was da nicht rein gehört! (zwei leerzeichen)
    $sMeldung =  "<p class='fehler'>Bitte nur ein Leerzeichen verwenden. Auch an erster und letzter Stelle darf kein Leerzeichen stehen.</p>";
    show_form();
	} elseif ($_POST['iAGB'] != "ok") {
    $sMeldung =  "<p class='fehler'>".$str['fehler_agb']."</p>";
    show_form();
	} elseif ($_POST['iDS'] != "ok") {
    $sMeldung =  "<p class='fehler'>".$str['fehler_ds']."</p>";
    show_form();
	} else {
		//alles klar, insert oder update
		if ($actionID == 1)
		{
			// ANLEGEN eines neuen Users
			$sql = "insert into USER (
					LOGIN, 
					EMAIL, 
					NAME, 
					NACHNAME, 
					STRASSE, 
					PLZ, 
					ORT, 
					LAND, 
					HOMEPAGE, 
					SHIRTSIZE, 
					KOMMENTAR_PUBLIC , 
					PERSONR,
					WANNANGELEGT, 
					WERANGELEGT, 
					WERGEAENDERT,
					PASSWORD_HASH
				) values (
					'".safe($_POST['iLogin'])."', 
					'".safe($_POST['iEmail'])."',
					'".safe($_POST['iName'])."', 
					'".safe($_POST['iNachName'])."', 
					'".safe($_POST['iStrasse'])."', 
					'".safe($_POST['iPLZ'])."', 
					'".safe($_POST['iOrt'])."', 
					'".safe($_POST['iLand'])."',
					'".safe($_POST['iHomepage'])."', 
					'".safe($_POST['tshirt'])."', 
					'".safe($iKommentar)."', 
					'".safe($iPersoNr)."', 
					NOW(), 
					-1, 
					-1,
					'platzhalter'
				)";
			$result = DB::query($sql);

			//Eintrag in ASTATUS und Passwort hashen
			$result = DB::query("select USERID from USER where LOGIN = '".safe($_POST[iLogin])."'");
			$row    = $result->fetch_array();
			if ($row['USERID'] > 0 ) {
				$newUserid = $row['USERID'];
				DB::query("insert into ASTATUS (MANDANTID, USERID, STATUS, WANNANGELEGT) values (".intval($nPartyID).", ".intval($newUserid).", ".intval($STATUS_NICHTANGEMELDET).", now())");
				// Passwort hashen und eintragen
				$pwHash = PELAS::HashPassword ($_POST['iPassword1'], $newUserid);
				$result = DB::query("update USER set PASSWORD_HASH = '$pwHash' where USERID='".intval($newUserid)."'");

				echo '<p class="hervorgehoben">'.$str['erstellt'].'</p><p>'.$str['jetzteinloggen'].'</p>';
				echo '<p><img src="gfx/headline_pfeil.png" border="0"> <a href="?page=5">'.$str['loginjetzt'].'</a></p>';

				$tmpUserId = $newUserid;
			}
			else
			{	
				echo '<p class="fehler">Failed to create your login data. Please contact us and report this error!</p>';
				$tmpUserId = -1;
			}
		
		}
		else
		{
			// UPDATE eines bestehenden Users
			$result = DB::query("UPDATE USER set 
					LOGIN='".safe($_POST['iLogin'])."', 
					EMAIL='".safe($_POST['iEmail'])."', 
					NAME='".safe($_POST['iName'])."', 
					NACHNAME='".safe($_POST['iNachName'])."',
					STRASSE='".safe($_POST['iStrasse'])."', 
					PLZ='".safe($_POST['iPLZ'])."',
					ORT='".safe($_POST['iOrt'])."',
					LAND='".safe($_POST['iLand'])."',
					HOMEPAGE='".safe($_POST['iHomepage'])."',
					SHIRTSIZE='".safe($_POST['tshirt'])."',
					KOMMENTAR_PUBLIC ='".safe($_POST['iKommentar'])."',
					PERSONR='".safe($_POST['iPersoNr'])."',
					WERGEAENDERT=".intval($nLoginID )."
				where USERID=$nLoginID");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			if (DB::$link->errno <> 0)
			{
				// FAILED!
				echo '<p class="error">Database-error. Please contact us and report this error!</p>';
				$tmpUserId = -1;
			} else {
				if ($_POST['iPassword1'] <> "" && ($_POST['iPassword1'] = $_POST['iPassword2'])) {
					// Passwort hashen und eintragen
					$pwHash = PELAS::HashPassword ($_POST['iPassword1'], $nLoginID);
					$result = DB::query("UPDATE USER set PASSWORD_HASH='$pwHash', WERGEAENDERT=".intval($nLoginID)." where USERID=".intval($nLoginID));
				}
				// logging wenn Name oder PLZ geändert
				$newData = $_POST['iLogin']." ".$_POST['iName']." ".$_POST['iNachName']." ".$_POST['iPLZ']." ".$_POST[iEmail];
				if ($oldData != $newData) {
					PELAS::logging("Userdata changed from ".safe($oldData)." to ".safe($newData), "accounting", $nLoginID);
				}
				
				// Erfolgreich!
				echo '<p class="hervorgehoben">'.$str['gespeichert'].'</p><p>'.$str['wenngeaendert'].'</p>';
				
				$tmpUserId = $nLoginID;
			}
		}


		// Newsletter-Verarbeitung sowohl für Update als auch Insert
		// Der Haken muss vorher nicht gesetzt gewesen sein, nur dann wird eine E-Mail verschickt!
		if (safe($_POST['iNewsletterAbo']) == "1" && $_POST['iNewsletterAbo'] != $_POST['oldNewsletterAbo'])
		{
			// Wenn sich der Button Newsletter von Nein auf Ja geändert hat, dann bestätigen lassen!
			$rand = mt_rand(10000, 10000000);
			$newsletter_abo_code = sha1("schubvidu".$rand."kghkl".$tmpUserId);	
			
			$sql = "UPDATE USER set 
						KEIN_MAILING = 'J',
						NEWSLETTER = 1,
						NEWSLETTER_ABO_CODE='".$newsletter_abo_code."',
						NEWSLETTER_ABO_EMAIL=NOW()
						where USERID=".intval($tmpUserId);
			$result = DB::query($sql);

			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			if (DB::$link->errno == 0) {
				if (safe($_POST['iNewsletterAbo']) == "1")
				{
					// Bestätigungs-E-Mail versenden:
					$subject = "MultiMadness-Newsletter: Freischaltung";
					$body = "Hallo ".htmlspecialchars($_POST['iName']).",\n\n";
					$body .= "Du möchtest Dich für den MultiMadness-Newsletter anmelden? Dann klicke bitte für Deine Freischaltung auf den folgenden Link:\n\n";
					$body .= BASE_URL."?page=11&action=subscribe&id=".intval($tmpUserId)."&code=".$newsletter_abo_code."\n";
					sende_mail_text ($_POST['iEmail'], $subject, $body);
				}
			}
			else
				echo '<p class="error">An error ocurred with your newsletter-registration. Please contact us and report this error!</p>';
			
			######## Ende Newsletter-Registrierung
		}
		else if (intval($_POST['iNewsletterAbo']) == 0)
		{
			// Newsletter zurücksetzen / abmelden etc. -> Alle Felder auf NULL
			$result = DB::query("UPDATE USER set 
						KEIN_MAILING = 'J',
						NEWSLETTER = 0,
						NEWSLETTER_ABO_DATE=NULL,
						NEWSLETTER_ABO_CODE=NULL,
						NEWSLETTER_ABO_EMAIL=NULL
						where USERID=".intval($tmpUserId) );
		}

	}
}

ende:
