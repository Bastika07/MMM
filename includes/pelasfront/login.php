<?php

include_once "dblib.php";
include_once "format.php";
include_once "getsession.php";
include_once "language.inc.php";

if (!isset($dbh))
	$dbh = DB::connect();

function encrypt($string, $key) {
  $result = '';
  for($i=0; $i<strlen ($string); $i++) {
    $char = substr($string, $i, 1);
    $keychar = substr($key, ($i % strlen($key))-1, 1);
    $char = chr(ord($char)+ord($keychar));
    $result.=$char;
  }

  return base64_encode($result);
}

function decrypt($string, $key) {
  $result = '';
  $string = base64_decode($string);

  for($i=0; $i<strlen($string); $i++) {
    $char = substr($string, $i, 1);
    $keychar = substr($key, ($i % strlen($key))-1, 1);
    $char = chr(ord($char)-ord($keychar));
    $result.=$char;
  }

  return $result;
}

function ShowForm ($login = '', $password = '') {
	global $str, $returnTo, $pelasHost, $nPartyID, $iAction, $nInhaltID, $iNick, $iKommentar;
	
	echo "\n<form method=\"post\" name=\"login\">\n";
	echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";

	echo "<tr><td>Login oder Email</td><td><input type=\"text\" name=\"iLogin\" size=\"25\" maxlength=\"50\" value=\"";
	if (isset($_POST['iLogin'])) {
		echo $_POST['iLogin'];
	}
	echo "\"></td></tr>\n";
	echo "<tr><td>Passwort</td><td><input type=\"password\" name=\"iPassword\" size=\"25\" maxlength=\"50\"></td></tr>\n";

	echo "<tr><td colspan=\"2\"><input type=\"checkbox\" name=\"saveLogin\" value=\"1\"> ".$str['saveLogin']."</td></tr>";

	echo "<tr><td valign=\"top\" colspan=\"2\"><input type=\"submit\" value=\"Einloggen\"></td></tr>\n";
	
	echo "</table>";
	echo "</form>";

	echo '<p><img src="gfx/headline_pfeil.png" border="0"> <a href="?page=11">Login erstellen</a><br>';
	
	if (LOCATION != "intranet") {
		if (strpos($_SERVER['REQUEST_URI'], "?"))
			echo '<img src="gfx/headline_pfeil.png" border="0"> <a href="'.$_SERVER['REQUEST_URI'].'&action=passwordreset">Passwort vergessen</a></p>';
		else
			echo '<img src="gfx/headline_pfeil.png" border="0"> <a href="'.$_SERVER['REQUEST_URI'].'?action=passwordreset">Passwort vergessen</a></p>';
	}
	
}

function ShowFormPasswordreset () {
	global $str;
	echo "<p>$str[gibemail]</p>";
	echo "\n<form method=\"post\" name=\"login\">\n";
	echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
	
	echo "<tr><td>$str[emailadresse]</td><td><input type=\"text\" name=\"iEmail\" size=\"25\" maxlength=\"50\" value=\"$iLogin\"></td></tr>\n";
	
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td colspan='2'>Sicherheitsabfrage: Bitte den Text aus dem Bild in das nebenstehende Feld eintragen</td></tr>\n";

		echo "<tr><td colspan='2' align='left'><table><tr><td>";

		// Captcha anzeigen und erstellen

		echo "<img src=\"".PELASHOST."showCaptcha.php?sid=".$useSessionId."\" width=\"230\" height=\"60\">";

		echo " </td><td> &nbsp; <input type='text' size='10' maxlength='10' name='captcha' value='".$_POST['captcha']."'> *</td></tr></table>";
		echo "</td></tr>";

	
	echo "<tr><td valign=\"top\" colspan=\"2\"><input type=\"submit\"></td></tr>\n";
	
	echo "</table>";
	echo "</form>";
	
	echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=11\">$str[erstellen]</a></p>";
}

function showFormNewPW () {
	global $str, $_SERVER;

	echo "<p>Bitte setze jetzt dein neues Passwort:</p>";
	echo "\n<form method=\"post\" action=\"".$_SERVER['REQUEST_URI']."\" name=\"login\">\n";
	echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
	
	echo "<tr><td>$str[passwort]</td><td><input type=\"password\" name=\"newP1\" size=\"25\" maxlength=\"50\" value=\"$newP1\"></td></tr>\n";
	echo "<tr><td>$str[pwbest]</td><td><input type=\"password\" name=\"newP2\" size=\"25\" maxlength=\"50\" value=\"$newP2\"></td></tr>\n";
	echo "<tr><td valign=\"top\" colspan=\"2\"><input type=\"submit\"></td></tr>\n";
	
	echo "</table>";
	echo "</form>";
	
}


if (isset($_GET['action']) && $_GET['action'] == "passwordreset") {
//Passwort vergessen
	if (isset($_GET['pwrcode']) && isset($_GET['pwruid'])) {
		// Code checken zum Passwort neu setzen
		$sql = "
			select userId
			from userActionHash
			where
				userId = '".intval($_GET['pwruid'])."' and
				hash   = '".mysql_real_escape_string($_GET['pwrcode'])."' and
				action = 'pwreset'
		";
		$workUID = DB::getOne($sql);

		if ($workUID == $_GET['pwruid']) {
			// Der Hash und die UserID stimmen überein, es darf ein neues Passwort eingegeben werden

			if (!isset($_POST['newP1']) && !isset($_POST['newP2'])) {
				// Das Formular wurde noch nicht ausgefüllt
				showFormNewPW();
			} elseif ($_POST['newP1'] != $_POST['newP2']) {
				echo "<p class=\"fehler\">$str[pwwrong]</p>";
				showFormNewPW();
			} elseif (strlen($_POST['newP1']) < 5) {
				echo "<p class=\"fehler\">Bitte mindestens fünf Zeichen eingeben.</p>";
				showFormNewPW();
			} elseif ( strstr($_POST['newP1'],chr(92)) != ""  ) {
				echo "<p class=\"fehler\">".$str[unerlaubt]."</p>";
				showFormNewPW();
			} else {
				// ActionHash korrekt, neues Passwort iO, GO: PW in DB schreiben und actionHash-Eintrag löschen
				$newPassHash = PELAS::HashPassword ($_POST['newP1'], $_GET['pwruid']);
				$sql = "update USER
					set PASSWORD_HASH = '".$newPassHash."'
					where USERID = '".intval($_GET['pwruid'])."'
				";
				DB::query($sql);

				$sql = "
					delete from userActionHash
					where
						userId = '".intval($_GET['pwruid'])."' and
						hash   = '".mysql_real_escape_string($_GET['pwrcode'])."' 
				";
				DB::query($sql);

				echo "<p>Dein Passwort wurde erfolgreich neu gesetzt. Du kannst dich ab sofort mit dem gewählten Passwort einloggen.</p><p><a href=\"?page=5\" class=\"arrow\">Einloggen</a></p>";
			}

		} else {
			echo "<p class='fehler'>Die gelieferten Daten für den Passwort-Reset sind falsch oder veraltet.</p><p>Bitte beachte, dass du nur ein mal ein neues Passwort mit 
				dem Link aus der Email setzen kannst. Wenn du dein Passwort noch einmal setzen möchtest, fordere bitte eine neue Email an.</p>";
			echo '<p><a href="?page=5" class="arrow">Einloggen</a><br>';
			echo '<a href="?page=5&action=passwordreset" class="arrow">Email für Passwort-Reset erneut anfordern</a></p>';
		}	

	} elseif (!isset($_POST['iEmail'])) {
		ShowFormPasswordreset ();
	} else {

		// Captcha-Checken
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
		if(mysql_num_rows($result) > 0) {
			$captchaCheck = "1";
		} else {
			$captchaCheck = "-1";
		}

		if ($captchaCheck == "1") {
			
			$email_lowered = strtolower($_POST['iEmail']);
			$stmt = DB::$link->prepare("select USERID, LOGIN, EMAIL from USER where LCASE(EMAIL) = ?");
			$stmt->bind_param('s', $email_lowered);
			$stmt->execute();
			$result = $stmt->get_result();
			//echo mysql_errno().": ".mysql_error()."<BR>";
			$row = mysql_fetch_array($result);
			$resultemail_lowered = strtolower($row['EMAIL']);
	
			$result2 = mysql_query("select BESCHREIBUNG from MANDANT where MANDANTID= '".intval($nPartyID)."'");
			//echo mysql_errno().": ".mysql_error()."<BR>";
			$row2 = mysql_fetch_array($result2);
			$sMandant = $row2['BESCHREIBUNG'];
	
			if ($resultemail_lowered == $email_lowered and $email_lowered != "" ) {
				// Hasu für die spätere Zulassung erstellen
				$actionHash = sha1($row['USERID'].$row['LOGIN'].mt_rand());
	
				$linkReset  = BASE_URL."?page=5&action=passwordreset&pwrcode=".$actionHash."&pwruid=".$row['USERID'];
	
				$sql = "
					replace into userActionHash 
						(userId,
						action,
						hash,
						wannAngelegt,
						werAngelegt)
					values
						('".$row['USERID']."',
						'pwreset',
						'$actionHash',
						NOW(),
						1)
				";
				DB::query($sql); 
	
				$mailText = "Hallo $row[LOGIN],\n\num ein neues Passwort für ".$row2['BESCHREIBUNG']." zu setzen, klicke bitte auf den nachfolgenden Link\n\n".$linkReset."\n\nWICHTIG: Du kannst über diesen Link nur einmal ein Passwort setzen. Sollte etwas nicht funktionieren, lasse Dir auf unserer Website eine neue Email zuschicken.\n\nSolltest Du diese Mail nicht angefordert haben, bitte den Link ignorieren, Dein Passwort bleibt unberührt. Sicherheitshalber kannst Du die Organisatoren darauf hinweisen.\n\nDiese Mail wurde automatisch generiert - Bitte nicht antworten";
	
				/* mail("$email_lowered", "$sMandant: Passwort-Reset", $mailText,
				"From: <".MAIL_ABSENDER.">\nReply-To: ".MAIL_ABSENDER."\nX-Mailer:". phpversion()); */
	
				$erfolg = sende_mail_text($email_lowered, $sMandant.": Passwort-Reset", $mailText);
	
				if ($erfolg)
					echo "<p>$str[unterwegs]</p>";
				else
					echo "<p class='fehler'>Es gab ein technisches Problem beim Versenden der Mail für den Passwort-Reset. Bitte wende Dich direkt an uns.</p>\n";
	
			} else {
				echo "<p class='fehler'>$str[nichtvorhanden]</p>";
				ShowFormPasswordreset();
			}
		} else {
			echo "<p class='fehler'>Du hast den Sicherheitscode falsch eingegeben.</p>";
			ShowFormPasswordreset();
		}
	}
} elseif (isset($_REQUEST['Action']) && $_REQUEST['Action'] == 'logout') {
  // logout
  if (session_destroy()) {

		// Wenn vorhanden, dann globalLogin Token in der DB löschen
		$sql = "delete from 
							userGlobalLogin
						where
							userID = '".intval($nLoginID)."' and
							mandantId = '".intval($nPartyID)."'
		";
		DB::query($sql);

		// Cookie löschen
		setcookie("pelasGlobalLogin", FALSE);

    $nLoginID = "";
    $sLogin   = "";

		if (strpos($_SERVER['REQUEST_URI'], "?"))
			header ("Location: ".$_SERVER['REQUEST_URI']."&Action=logoutok");
		else
			header ("Location: ".$_SERVER['REQUEST_URI']."?Action=logoutok");
		
  } else {
    echo "<p>Fehler beim Logout.</p>\n";
  }
} elseif (isset($_REQUEST['Action']) && $_REQUEST['Action'] == "logoutok") {
	// Meldung zum ausloggen
  echo "<p>Du wurdest erfolgreich ausgeloggt.</p>\n";
} elseif (isset($_REQUEST['Action']) && $_REQUEST['Action'] == "loginok") {
	//Erfolgreichen login ausgeben
  echo "<p>Hallo ".db2display($sLogin).", du bist nun mit der ID ".$nLoginID." eingeloggt.</p>\n";
		// Altes System
		$result4 = mysql_query("select STATUS from ASTATUS where USERID='".intval($nLoginID)."' and MANDANTID='".intval($nPartyID)."'");
		$row4 = mysql_fetch_array($result4);

		if (mysql_num_rows($result4) < 1) {
			// kein Datensatz in Astatus vorhanden > einfügen!
			$resultTemp = mysql_query("insert into ASTATUS (MANDANTID, USERID, STATUS, WANNANGELEGT, WERANGELEGT, WERGEAENDERT) values ('".intval($nPartyID)."', '".intval($nLoginID)."', '".$STATUS_NICHTANGEMELDET."', now(), '".intval($nLoginID)."', now())");
		}

		if (ACCOUNTING == "NEW") {
			// Neues System
			if (LOCATION != 'intranet') {
				echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=6\"> $str[zuranmeldung]</a>";
			}
			echo "<br><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"?page=10\"> $str[zumforum]</a></p>";

			// Link zum Bezahlformular für Friends
			if ($nPartyID == 11 && !User::hatbezahlt($nLoginID)) {
				echo "<p>Zum Formular mit den Zahlungsinformationen geht es <img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href='?page=6&action=bill'>hier</a>.</p>";
			}

		} elseif ($row4['STATUS'] < 1) {
			// Meldung Altes System
			echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"teilnahme.php\"> $str[zuranmeldung]</a>";
			echo "<br><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"forum.php\"> $str[zumforum]</a></p>";
		}

} elseif (isset($_SESSION['MMMSESSION']['nLoginID']) && !empty($_SESSION['MMMSESSION']['nLoginID'])) {
  // ist schon angemeldet
  echo "<p>Du bist als ".htmlspecialchars($_SESSION['MMMSESSION']['sLogin'])." mit der ID ".$_SESSION['MMMSESSION']['nLoginID']." eingeloggt.</p>\n";
  echo "<form method=\"post\">\n";
  echo "<input type=\"hidden\" name=\"Action\" value=\"logout\">\n";
  echo "<input type=\"submit\" value=\"ausloggen\">\n";
  echo "</form>\n";
} elseif (isset($_POST['iLogin']) && isset($_POST['iPassword'])) {
  // formular abgesendet
  if (!empty($_POST['iLogin']) && !empty($_POST['iPassword'])) {
    // beide Felder angegeben
	
	// Jeweils nach Login und Email suchen in der Datenbank
	$result = mysql_query("select USERID, LOGIN from USER where BINARY LOGIN='".mysql_real_escape_string($_POST['iLogin'])."'");
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$rowLogin = mysql_fetch_array($result);

	$result = mysql_query("select USERID, LOGIN from USER where EMAIL='".mysql_real_escape_string($_POST['iLogin'])."'");
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$rowEmail = mysql_fetch_array($result);

	if ($rowLogin['USERID'] > 0) {
		$theUser  = $rowLogin['USERID'];
		$theLogin = $rowLogin['LOGIN'];
	} elseif ($rowEmail['USERID'] > 0) {
		$theUser  = $rowEmail['USERID'];
		$theLogin = $rowEmail['LOGIN'];
	}

	// Hash des eingegebenen Passwortes suchen
	$pwHash = PELAS::HashPassword ($_POST['iPassword'], $theUser);

	// Und nun den Hashwert in der Datenbank checken
	$result = mysql_query("select PASSWORD_HASH from USER where USERID='".$theUser."'");
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$rowPwHash = mysql_fetch_array($result);

  if ($theUser > 0 && $rowPwHash['PASSWORD_HASH'] == $pwHash) {
      // login ok

			// Ist dauerhaftes einloggen gefordert?
			if ($_POST['saveLogin'] == 1) {

				// Bla-Hash generieren, Cookie setzen
				$theToken = hash('md5', mt_rand());
				setcookie("pelasGlobalLogin", $theUser.';'.$theToken, time()+45240000);

				// Daten in die Tabelle userGlobalLogin schreiben
				$sql = "replace into userGlobalLogin
									(mandantId, userId, token, wannAngelegt)
								values
									('$nPartyID', '$theUser', '$theToken', now())";
  	    if (!DB::query($sql)) {
	        $str = "could not save permanent login";
  	      PELAS::logging($str, 'login', $theUser);
    	  }
			}

			$_SESSION['MMMSESSION']["nLoginID"] = $theUser;
			//$_SESSION['MMMSESSION']["sLogin"] = htmlentities($theLogin);
			$_SESSION['MMMSESSION']["sLogin"] = $theLogin;
			$nLoginID = $theUser;
			$sLogin   = $theLogin;
			
			// Im Intranet die IP nochmal in einer extra Tabelle speichern
			if (LOCATION == 'intranet') {
				$sql = "REPLACE INTO 
                USER2IP
              (userid, ip)
                VALUES ($theUser, '$_SERVER[REMOTE_ADDR]')";
				if (!DB::query($sql)) {
					$str = "could not save ip";
				  PELAS::logging($str, 'login', $nLoginID);
				  }
			} else {
				$token = encrypt($_SERVER[REMOTE_ADDR], "MMM4ever");
				//$rtoken = decrypt($token, "MMM4ever");
				$sql = "INSERT INTO 
                USERLogin
              (UserID, Token, LetzterLogin)
                VALUES ($theUser, '$token ', now()) ON DUPLICATE KEY UPDATE Token='$token ', LetzterLogin=now()";
				DB::query($sql);
			}
			
			header ("Location: ?page=5&Action=loginok");

    } else {
      // login fehlgeschlagen
      PELAS::fehler("Login fehlgeschlagen! Passwort oder Login falsch.");
      PELAS::logging("Login failed: $_REQUEST[iLogin]", 'login', $nLoginID);
      ShowForm($_REQUEST['iLogin'], $_REQUEST['iPassword']);
    }
    
  } else {
    // mindestens ein Feld leer
    echo "<p class='fehler'>";
    if (empty($_REQUEST['iLogin'])) {
      echo "Kein Login angegeben!\n";
    }
    if (empty($_REQUEST['iPassword'])) {
      echo "Kein Password angegeben!\n";
    }
    echo "</p>";
    ShowForm($_REQUEST['iLogin'], $_REQUEST['iPassword']);
  }
} else {
  // login-form anzeigen
  ShowForm();
}
?>
