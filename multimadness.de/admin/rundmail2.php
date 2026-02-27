<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "MAILINGADMIN";
include "checkrights.php";
include_once "PHPMailer/PHPMailerAutoload.php";
include "format.php";
include "admin/vorspann.php";

//#################################
//maximale Anzahl pro Seite
$maxCount = 50;
//##################################

// Text auslesen
$dbh = DB::connect();
$sql = "select TITEL, DERINHALT from INHALT where INHALTID = ".intval($_GET['nInhaltID']);
$res2 = DB::query($sql);
$row = mysql_fetch_array($res2);
//echo mysql_errno().": ".mysql_error()."<BR>";
$sTitel   = $row['TITEL'];
$sTheText = $row['DERINHALT'];

// Absenderliste erstellen
$sql = "select m.MAILANTWORTADRESSE, m.BESCHREIBUNG 
  from MANDANT m, RECHTZUORDNUNG r 
  where r.USERID = '".intval($loginID)."'
  and m.MANDANTID = r.MANDANTID 
  and r.RECHTID = 'MAILINGADMIN'";
$result = mysql_query($sql);

# WICHTIG: Absender sind derzeit FEST in constants.php gesetzt!
/* while ($row = mysql_fetch_array($result)) {
    $absender[] = array (
    	'EMAIL' => $row['MAILANTWORTADRESSE'],
    	'BESCHREIBUNG' => $row['BESCHREIBUNG']
    );
} */
$absender[] = array (
	'EMAIL' => MAIL_ABSENDER_NEWSLETTER,
	'BESCHREIBUNG' => MAIL_ABSENDER_NAME_NEWSLETTER
);


function sendeEmail($email, $sTitel, $sEmpfaengerNick, $sTheText, $nUserID, $mailingid, $format)
{
	global $absenderForm, $absender;
	if (strpos ($email, "@") > 0) {
		//ungueltige Zeichen ausser Email rausnehmen
		$email = preg_replace('/,/', '.', $email);

		// \r aus dem zu mailenden String rausnehmen (WICHTIG für GMX!)
		$sTheText = str_replace("\r", "", $sTheText);

		// Platzhalter Nickname
		$sTitel = str_replace("%nick%", $sEmpfaengerNick, $sTitel);
		$sTheText = str_replace("%nick%", $sEmpfaengerNick, $sTheText);

		# Abmeldelink bauen und automatisch am Ende einfügen, wenn kein Cancel im Newsletter	
		$rand = mt_rand(10000, 10000000);
		$abmelde_code = sha1("schubvidu".$rand."kghkl".$nUserID);
		$cancel_url = BASE_URL."?page=11&action=unsubscribe&id=".intval($nUserID)."&code=".$abmelde_code;
		if (strpos($sTheText, "%cancel%") <= 0)
			$sTheText .= $cancel_url;
		else 
			$sTheText = str_replace("%cancel%", $cancel_url, $sTheText);

		// Platzhalter Newsletter Abo Bestätigung (einmalig nach Umstellung!)
		$abo_url = BASE_URL."?page=11&action=subscribeRM&id=".intval($nUserID)."&code=".$abmelde_code;
		$sTheText = str_replace("%abo%", $abo_url, $sTheText);
		
		# Datensatz in Newsletter execute schreiben
		$sql = "INSERT INTO mailing_execute SET 
							mailing_id = '".intval($mailingid)."',
							user_id = '".intval($nUserID)."',
							email = '".safe($email)."',
							code = '".safe($abmelde_code)."',
							format = '".(($format == "text") ? "text" : "html")."',
							betreff = '".safe($sTitel)."',
							body = '".safe($sTheText)."',
							wann_angelegt = NOW()";
							
		return mysql_query($sql);
		
		
		# New Mailer! -> OFF!
		if ($format == "html") {
			// sende_mail_newsletter($email, $sTitel, $sTheText, "html", $nUserID);
		} else {
			// sende_mail_newsletter($email, $sTitel, $sTheText, "txt", $nUserID);
		}
		
	} else {
		echo "<p>Ung&uuml;tige Emailadresse - skipping</p>";
	}
}

?>

<h1>Mailing</h1>

<?php
if (isset($_POST['startat']) && $_POST['startat'] > 0) {
	$_GET['counter'] = $_POST['startat'];
}
if (isset($_POST['iGo']) && $_POST['iGo'] == "yes") {

  // TODO: wirklich nötig??
//  mysql_connect($dbhost, $dbuser, $dbpass) OR DIE("Couldn't connect to server!");
//  mysql_select_db($dbname) OR DIE("Couldn't select database!");

   if ($_POST['wer'] == "bezahlt") $sAddWhere = " and (a.STATUS = $STATUS_BEZAHLT OR a.STATUS = $STATUS_BEZAHLT_LOGE)";
   elseif ($_POST['wer'] == "unbezahlt") $sAddWhere = " and a.STATUS = $STATUS_ANGEMELDET";
   elseif ($_POST['wer'] == "abgemeldet") $sAddWhere = " and a.STATUS = $STATUS_ABGEMELDET";
   elseif ($_POST['wer'] == "unangemeldet") $sAddWhere = " and a.STATUS = $STATUS_NICHTANGEMELDET";
   elseif ($_POST['wer'] == "alle") $sAddWhere = "";
   
   //Rechte auf Mandanten beachten und besondere Anweisung für Archiv!
   if ($_POST['wer'] == "archiv_bezahlt") {
	if ($_POST['iMandant'] < 1) {
		$sWhere = "";
		echo "<p class=\"fehler\">F&uuml;r Archiv-Abfragen bitte immer einen Mandanten ausw&auml;hlen.</p>";
	} else {
		$sWhere = "select distinct u.USERID,u.LOGIN,u.NAME,u.EMAIL,u.WANNGEAENDERT from USER u, ASTATUSHISTORIE a where a.USERID = u.USERID and a.MANDANTID=".intval($_POST['iMandant'])." and u.NEWSLETTER = 1 and (a.STATUS = $STATUS_BEZAHLT OR a.STATUS = $STATUS_BEZAHLT_LOGE)";
	}
   } elseif ($_POST['wer'] == "tickets") {
   	// Neues Ticketsystem, immer mit Mandant
   	$aktuelleParty = PELAS::mandantAktuelleParty($_POST['iMandant']);
   	$sWhere = "select distinct 
   			u.USERID,
   			u.LOGIN,
   			u.NAME,
   			u.EMAIL,
   			u.WANNGEAENDERT 
   		from USER u, 
   			acc_tickets t
   		where (t.userId = u.USERID or t.ownerId = u.USERID)
   			and t.partyId = '".intval($aktuelleParty)."'
   			and t.statusId = '".ACC_STATUS_BEZAHLT."'
			and u.NEWSLETTER = 1
			and u.KEIN_MAILING = 'N'";
   } elseif ($_POST['wer'] == "ticketsoffen") {
   	// Neues Ticketsystem, immer mit Mandant
   	$aktuelleParty = PELAS::mandantAktuelleParty($_POST['iMandant']);
   	$sWhere = "select distinct 
   			u.USERID,
   			u.LOGIN,
   			u.NAME,
   			u.EMAIL,
   			u.WANNGEAENDERT 
   		from USER u, 
   			acc_bestellung b
   		where (b.bestellerUserId = u.USERID)
   			and b.partyId = '".intval($aktuelleParty)."'
   			and b.status = '".ACC_STATUS_OFFEN."'
			and u.NEWSLETTER = 1
			and u.KEIN_MAILING = 'N'";
	} else {
	if ($_POST['iMandant'] > 0) {
		$sWhere = "select distinct 
						u.USERID,
						u.LOGIN,
						u.NAME,
						u.EMAIL,
						u.WANNGEAENDERT
				   from
					   USER u, 
					   ASTATUS a
					where 
						a.USERID = u.USERID 
						and a.MANDANTID=".intval($_POST['iMandant'])." 
						and u.NEWSLETTER = 1 
						and u.KEIN_MAILING = 'N'
						$sAddWhere";
	} else {
		//alle Mandanten, wo berechtigt
		$sWhere = "select distinct 
					u.USERID,
					u.LOGIN,
					u.NAME,
					u.EMAIL,
					u.WANNGEAENDERT 
				   from 
					   USER u, 
					   ASTATUS a, 
					   RECHTZUORDNUNG r 
					where 
						a.USERID = u.USERID 
						and a.MANDANTID=r.MANDANTID 
						and r.USERID=".intval($loginID)." 
						and u.NEWSLETTER = 1 
						and u.KEIN_MAILING = 'N'
						$sAddWhere";
	}
   }
   if ($_POST['wer'] == "test")
   {
	   // TEstbetrieb
		$sEmpfaengerNick = "Testbetrieb";
		$nUserID = 2;
		//Testbetrieb mit Testempfänger
		$email = $_POST['zusatz'];
		$status = sendeEmail($email, $sTitel, $sEmpfaengerNick, $sTheText, $nUserID, $_GET['nInhaltID'], $_POST['format']);
		if ($status == true)
				echo "<p>Testmail verschickt.</p>";
			else 
				echo "<p>Testmail konnte nicht eingereiht werden. Bitte beachten, dass nur eine Testmail pro Mailing und Empfänger verschickt werden kann: </p>".htmlspecialchars(mysql_error());
		echo "<p><a href=\"rundmail2.php?nKategorieID=$KATEGORIE_MAILING&nInhaltID=".intval($_GET['nInhaltID'])."\">Zur&uuml;ck zur Auswahl</a></p>";
   } 
   else
   {
		// ATTENTION! Adressen auslesen

		$result = mysql_query("$sWhere limit ".intval($_GET['counter']).", $maxCount");
		//echo mysql_errno().": ".mysql_error()."<BR>";

		$counter2 = 0;
		while ($row = mysql_fetch_array($result)) {
			//echo htmlentities("mail($email, $betreff, $inhalt, \"From: MultiMadness <mailer@multimadness.de>\nReply-To: MultiMadness Team <team@multimadness.de>\nX-Mailer: PHP/\".phpversion().\"\nX-Priority: 3\nReturn-Path: <team@multimadness.de>\");")."<br><br>";
			$email           = $row['EMAIL'];
			$sEmpfaengerNick = $row['LOGIN'];
			$nUserID		 = $row['USERID'];
			$status = sendeEmail($email, $sTitel, $sEmpfaengerNick, $sTheText, $nUserID, $_GET['nInhaltID'], $_POST['format']);
			if ($status == true)
				echo "<script language=\"JavaScript\">parent.output.document.write('OK (".intval($_GET['counter']).") ".db2display($row['LOGIN'])."<br>');</script>";
			else 
				echo "<script language=\"JavaScript\">parent.output.document.write('FAIL (".intval($_GET['counter']).") ".db2display($row['LOGIN'])."<br>');</script>";
			$_GET['counter']++;
			$counter2++;
		}
		if ($counter2 >= $maxCount) {
			echo "<form name=\"weiter\" action=\"rundmail2.php?counter=".intval($_GET['counter'])."&nInhaltID=".intval($_GET['nInhaltID'])."\" method=post>";
			echo "<input type=\"hidden\" name=\"iGo\" value=\"yes\">";
			echo "<input type=\"hidden\" name=\"wer\" value=\"".htmlspecialchars($_POST['wer'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\">";
			echo "<input type=\"hidden\" name=\"iMandant\" value=\"".intval($_POST['iMandant'] ?? 0)."\">";
			echo "<input type=\"hidden\" name=\"format\" value=\"".htmlspecialchars($_POST['format'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\">";
			echo "<input type=\"hidden\" name=\"absenderForm\" value=\"".htmlspecialchars($_POST['absenderForm'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\">";
			
			echo "<input type=\"submit\" value=\"Weiter...\">";
			echo "</form>";
			echo "<script language=\"JavaScript\">document.forms.weiter.submit();</script>";
		} else {
			echo "<p>Alle Emails verschickt.</p>";
			echo "<p><a href=\"redaktionsverwaltung.php?nKategorieID=$KATEGORIE_MAILING&nInhaltID=".intval($_GET['nInhaltID'])."\" target=\"_parent\">Zur&uuml;ck zur Verwaltung</a></p>";
			$row = mysql_query("update INHALT set AKTIV='J', DATE1=NOW() where INHALTID = ".intval($_GET['nInhaltID']));
			//echo mysql_errno().": ".mysql_error()."<BR>";
		}
   }
   //echo "<meta http-equiv='Refresh' content='0; URL=rundmail.php3?counter=$counter&iGo=yes&wer=$wer'>";   
   //echo "<p><a href=\"rundmail.php3?counter=$counter&iGo=yes&wer=$wer\">Weiter</a></p>";
}
else
{

	?>
	<p><u>Info</u>: Alle User, die im Profil den Newsletterversand nicht aktiviert oder den Versand nicht per Bestätigungslink freigeschaltet haben, werden keine Email erhalten.</p>
	<table>
	<form action="rundmail2.php?counter=0&nInhaltID=<?= intval($_GET['nInhaltID']); ?>" method=post>
	<input type=hidden name=iGo value=yes>

	<tr>
	<td width='100'>Absender</td>
	<td><select name='absenderForm'>

	<?php
	$i = 0;
	foreach ($absender as $value) {
		printf("<option value='%s'>%s / %s</option>\n", $i, $value['BESCHREIBUNG'], $value['EMAIL']);
		$i++;
	}
	?>
	</select></td>
	</tr>

	<tr><td width=100>Empf&auml;nger</td><td>
	<select name=wer size=1>
	<option value="test">Test only *</option>
	<option value="alle">Alle Benutzer</option>
	<option value="tickets">Ticketinhaber und Benutzer</option>
	<option value="ticketsoffen">Benutzer mit offener Bestellung</option>
	<?php
	/*
	<option value="bezahlt">Alle bezahlten (altes System)</option>
	<option value="unbezahlt">Alle angemeldeten  (altes System)</option>
	<option value="abgemeldet">Alle abgemeldeten  (altes System)</option>
	<option value="unangemeldet">Alle ungemeldeten  (altes System)</option>
	<option value="archiv_bezahlt">Aus Archiv: bezahlte  (altes System)</option>
	*/
	?>
	</select>
	</td></tr>
	<tr><td>
		Testempf&auml;nger
	</td><td>
		<input type="text" name="zusatz"> (Nur im Modus Test only)
	</td></tr>
	<tr><td>Format</td><td>
		<select name="format" size=1>
		<option value="html" selected>HTML</option>
		<option value="text">Text</option>
		</select>
	</td></tr>
	<tr><td width=100>Mandant</td><td>
	<select name=iMandant size=1>
	<?php
			$result= mysql_db_query ($dbname, "select m.MANDANTID, m.BESCHREIBUNG from MANDANT m, RECHTZUORDNUNG r where r.MANDANTID=m.MANDANTID and r.USERID=".intval($loginID)." and r.RECHTID='MAILINGADMIN'",$dbh);
			//echo mysql_errno().": ".mysql_error()."<BR>";
			while ($row = mysql_fetch_array($result)) {
				echo "<option value=\"$row[MANDANTID]\"";
				if (isset($_POST['iMandant']) && $_POST['iMandant'] == $row['MANDANTID']) {echo " selected";}
				echo ">$row[BESCHREIBUNG]";
			}
			echo "<option value=\"-1\">Alle";
	?>
	</select>
	</td></tr>
	<tr><td>Betreff</td><td><?php echo db2display($sTitel); ?></td></tr>
	<tr><td>Preview<br>(&Auml;nderung nicht m&ouml;glich)
	</td><td><textarea name=inhalt cols=50 rows=16 wrap=physical><?=$sTheText?></textarea></td></tr>
	<tr><td>Start bei Nr.</td><td><input type=text name=startat></td></tr>
	<tr><td colspan=2 align=center><input type=submit value="Mailing abschicken"></td></tr>
	</form>
	</table>
	<?php
}

include "admin/nachspann.php";
?>
