<?php
/* Formatierung von Daten */

##include_once('pelas_bot.php');

#
# Sendet eine Textmail mit PHPMailer
#
function sende_mail_text ($empfaenger_mail, $subject, $body, $replyto_mail = "", $replyto_name = "" ) {

	global $str, $nPartyID;

	$mail = new PHPMailer;

	// $mail->IsSendmail(); // telling the class to use Sendmail -> Benötigt für 1und1
	$mail->IsSMTP();
	//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->Host       = MAIL_HOST; // sets the SMTP server
	$mail->Port       = 25; // set the SMTP port
	$mail->Username   = MAIL_USERNAME; // SMTP account username
	$mail->Password   = MAIL_PASSWORD;        // SMTP account password
	$mail->SetFrom(MAIL_ABSENDER, MAIL_ABSENDER_NAME);

	

	$mail->AddAddress($empfaenger_mail); 	//Set who the message is to be sent to
	$mail->Subject = $subject; 	//Set the subject line
	$mail->CharSet = 'UTF-8';
	
	if ($replyto_mail != "") # Abweichende Antwortadresse setzen
		$mail->AddReplyTo($replyto_mail, $replyto_name);
	
	
	// Footer mit Impressum immer zufügen!
	// Ermittlung der Benutzer-, Partydaten
	$sql = "select
				m.BESCHREIBUNG,
				m.EMAIL,
				m.REFERER,
				m.FIRMA,
				m.STRASSE,
				m.PLZ,
				m.ORT,
				m.TELEFON,
				m.FAX,
				m.HANDELSREGISTER
			from 
				MANDANT m
			where
				m.MANDANTID = ".intval($nPartyID);
	$result_mandant = DB::query($sql);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$rowMandant = $result_mandant->fetch_array();

	if ($rowMandant && count($rowMandant > 0))
	{
		$body .= "\n";
		$body .= "\n";
		$body .= $str['acc_vielegruesse'].",\n";
		$body .= "\n";
		$body .= $str['acc_dein']." ".$rowMandant['BESCHREIBUNG']."-Team\n";
		$body .= "\n";
		$body .= "--\n";
		$body .= "\n";
		$body .= $rowMandant['FIRMA']."\n";
		$body .= $rowMandant['STRASSE']."\n";
		$body .= $rowMandant['PLZ']." ".$rowMandant['ORT']."\n";
		$body .= $str[acc_telefon].": ".$rowMandant['TELEFON']."\n";
		$body .= $str[acc_telefax].": ".$rowMandant['FAX']."\n";
		$body .= "Mail   : ".$rowMandant['EMAIL']."\n";
		if (!empty($rowMandant['HANDELSREGISTER'])) {
			$body .= $rowMandant['HANDELSREGISTER']."\n";
		}
	}

	// Body mit Impressum übergeben
	$mail->Body = $body;

	if(!$mail->Send()) {
		PELAS::logging( "Mailer Error sending at ".$empfaenger_mail." subject '".$subject."': " . $mail->ErrorInfo);
	} else {
		return true;
	}

}

#
# Sendet eine HTML-Mail mit PHPMailer
#
function sende_mail_html ($empfaenger_mail, $subject, $body, $altbody = "", $replyto_mail = "", $replyto_name = "" ) {

	$mail = new PHPMailer;

	// $mail->IsSendmail(); // telling the class to use Sendmail -> Benötigt für 1und1
	$mail->isSMTP();
	// $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->Host       = MAIL_HOST; // sets the SMTP server
	$mail->Port       = 25;                    // set the SMTP port
	$mail->Username   = MAIL_USERNAME; // SMTP account username
	$mail->Password   = MAIL_PASSWORD;        // SMTP account password
	$mail->SetFrom(MAIL_ABSENDER, MAIL_ABSENDER_NAME);

	$mail->AddAddress($empfaenger_mail); 	//Set who the message is to be sent to
	$mail->Subject = $subject; 	//Set the subject line
	$mail->CharSet = 'UTF-8';
	
	if ($replyto_mail != "") # Abweichende Antwortadresse setzen
		$mail->AddReplyTo($replyto_mail, $replyto_name);
	
	$mail->MsgHTML($body);
	
	if ($altbody != "")
		$mail->AltBody = $altbody;

	if(!$mail->Send()) {
		PELAS::logging( "Mailer Error: " . $mail->ErrorInfo);
	} else {
		return true;
	}

}

#
# Auf den > NEWSLETTER < angepasster Versand
#
# Format: txt oder html
#
function sende_mail_newsletter($empfaenger_mail, $subject, $body, $format = "html", $altbody = "") {

	try
	{
		$mail = new PHPMailer;
	
		// $mail->IsSendmail(); // telling the class to use Sendmail -> Benötigt für 1und1
		$mail->isSMTP();
		// $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->Host       = MAIL_HOST_NEWSLETTER; // sets the SMTP server
		$mail->Port       = 25;                    // set the SMTP port
		$mail->Username   = MAIL_USERNAME_NEWSLETTER; // SMTP account username
		$mail->Password   = MAIL_PASSWORD_NEWSLETTER;        // SMTP account password
		$mail->SetFrom(MAIL_ABSENDER_NEWSLETTER, MAIL_ABSENDER_NAME_NEWSLETTER);
	
		$mail->AddAddress($empfaenger_mail); 	//Set who the message is to be sent to
		$mail->Subject = $subject; 	//Set the subject line
		$mail->CharSet = 'UTF-8';
		
		if ($format == "html") {
			$mail->MsgHTML($body);
			if ($altbody != "")
				$mail->AltBody = $altbody;		
		} else
			$mail->Body = $body;
		
		$mail->Send();
	
		return true;
	
	}
	catch (phpmailerException $e)
	{
  		// echo $e->errorMessage(); //Pretty error messages from PHPMailer
		// Deaktiviert für Newsletter!
		PELAS::logging( "Mailer Error: " . $mail->ErrorInfo);
		return false;
	}
}



function date2displayShort($rdat) {
    return substr($rdat,6,2).".".substr($rdat,4,2).".".substr($rdat,0,4);
}

/* Filtere HTML-Tags aus einem String, damit sie gefahrlos (Stichwort XSS)
 * angezeigt werden können.
 */
function db2display($formdata) {
    $result = stripslashes($formdata);
    return check_text($result, True);
}

function simpleHTML($result) {
    $array = array(
        '[bold]' => '<b>',
        '[/bold]' => '</b>',
        '[italic]' => '<i>',
        '[/italic]' => '</i>',
        '[zitat]' => '<div class="zitat"><i>',
        '[/zitat]' => '</i></div>');	             
	  
    foreach ($array as $key => $val) {
        $result = str_replace($key, $val, $result);
    }
    return $result;
}

function dateDisplay($sDatum) {
    return substr($sDatum, 6, 2) . '.' . substr($sDatum, 4, 2) . '.' . substr($sDatum, 0, 4)
        . ' ' . substr($sDatum, 8, 2) . ':' . substr($sDatum, 10, 2);
}

function dateDisplay2($sDatum) {
    return substr($sDatum, 8, 2) . '.' . substr($sDatum, 5, 2) . '.' . substr($sDatum, 0, 4)
        . ' ' . substr($sDatum, 11, 2) . ':' . substr($sDatum, 14, 2);
}

function dateDisplay2Short($sDatum) {
    return substr($sDatum, 8, 2) . '.' . substr($sDatum, 5, 2) . '.' . substr($sDatum, 0, 4);
}

function db2displayForum($str) {
    $result = stripslashes($str);
    $result = check_text($result, True);
    $result = simpleHTML($result);  

    # Smilies
    $result = insertSmilies($result);
	  
    # Added by muffi, automatischer Umbruch
    $wrap_at = 50;
    $result = preg_replace('/([^\s\<\>]{'.$wrap_at.','.$wrap_at.'})/', '\1 ', $result);
    //$result = preg_replace('%(\s*)([^>]{'.$wrap_at.',})(<|$)%e', "'\\1'.wordwrap('\\2', '".$wrap_at."', ' ', 1).'\\3'", $result);
  
    return $result;
}

/*
 * Die folgenden 3 Funktionen wurden am 10.11.2013 aus der dblib.php hierher migriert
 */
function displayAdjustedPicture($file, $maxWidth, $maxHeight) {
    if (file_exists(PELASDIR . $file) and ($imgSize = @GetImageSize(PELASDIR . $file)) !== False) {
        # Zu breit oder zu hoch?
        $width = ($imgSize[0] > $maxWidth) ? $maxWidth : $imgSize[0];
        $height = ($imgSize[1] > $maxHeight) ? $maxHeight : $imgSize[1];
        printf('<img src="%s?time=%d" width="%d" height="%d" border="1" vspace="3" hspace="3"/>',
            PELASHOST . $file, time(), $width, $height);
        return true;
    } else {
		return false;
    }
}

function displayUserPic($nGesichtID, $groessex = 110, $groessey = 150) {
    $file = 'userbild/' . $nGesichtID . '.jpg';
    if (!displayAdjustedPicture($file, $groessex, $groessey)) {
	    printf('<img src="%s" width="%d" height="%d" border="0" vspace="3" hspace="3"/>',
            PELASHOST . 'gfx/userbild_none.jpg', 110, 150);    	
    }
}

function displayClanPic($nClanID, $nMandantID) {
    $file = 'clanlogo/' . $nMandantID . '_' . $nClanID . '.jpg';
    return displayAdjustedPicture($file, 220, 60);
}


# Diese Funktion interpretiert Smilie-Code
function insertSmilies($result) {
    include('forum_vars.php');
    for ($i = 1; $i <= $smilies_count; $i++) {
        $result = str_replace($SmiliesArray[$i][1],
            '<img src="' . PELASHOST . 'gfx/' . $SmiliesArray[$i][0] . '"> ', $result);
    }
    $result = str_replace(':-lamer','<img src="' . PELASHOST . 'gfx/smx1.gif">', $result);
    $result = str_replace(':-shout','<img src="' . PELASHOST . 'gfx/smx2.gif">', $result);
    $result = str_replace(':-wayne','<img src="' . PELASHOST . 'gfx/smx3.gif">', $result);
    return $result;
}

function db2displayNews($str) {
    $result = stripslashes($str);
    $result = check_text($result, True);
    $result = simpleHTML($result);  
	
    # [img]image_url_here[/img] code.
    $result = preg_replace("/\[img\](.*?)\[\/img\]/si",
        "<img src=\"\\1\" border=\"0\" hspace=\"5\">", $result);

    # [imgright]image_url_here[/imgright] code.
    $result = preg_replace("/\[imgright\](.*?)\[\/imgright\]/si",
        "<img align=\"right\" src=\"\\1\" border=\"0\" hspace=\"5\">", $result);

    # [imgleft]image_url_here[/imgleft] code.
    $result = preg_replace("/\[imgleft\](.*?)\[\/imgleft\]/si",
        "<img align=\"left\" src=\"\\1\" border=\"0\" hspace=\"5\">", $result);  

    # Patterns and replacements for URL and email tags.
    $patterns = array();
    $replacements = array();

    # [url]xxxx://www.phpbb.com[/url] code.
    $patterns[0] = "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
    $replacements[0] = '<a href="\1\2" target="_blank">\1\2</A>';
		
    # [urlintern]xxxx://www.phpbb.com[/urlintern] code.
    # opens URL in same window
    $patterns[1] = "#\[urlintern\]([a-z]+?://){1}(.*?)\[/urlintern\]#si";
    $replacements[1] = '<a href="\1\2">\1\2</A>';

    # [url]www.phpbb.com[/url] code. (no xxxx:// prefix).
    $patterns[2] = "#\[url\](.*?)\[/url\]#si";
    $replacements[2] = '<a href="\1" target="_blank">\1</A>';
		
    # [urlintern]www.phpbb.com[/urlintern] code. (no xxxx:// prefix).
    # opens URL in same window
    $patterns[3] = "#\[urlintern\](.*?)\[/urlintern\]#si";
    $replacements[3] = '<a href="\1">\1</A>';

    # [url=xxxx://www.phpbb.com]phpBB[/url] code.
    $patterns[4] = "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
    $replacements[4] = '<a href="\1\2" target="_blank">\3</A>';
		
    # [urlintern=xxxx://www.phpbb.com]phpBB[/urlintern] code.
    # opens URL in same window
    $patterns[5] = "#\[urlintern=([a-z]+?://){1}(.*?)\](.*?)\[/urlintern\]#si";
    $replacements[5] = '<a href="\1\2">\3</A>';

    # [url=www.phpbb.com]phpBB[/url] code. (no xxxx:// prefix).
    $patterns[6] = "#\[url=(.*?)\](.*?)\[/url\]#si";
    $replacements[6] = '<a href="\1" target="_blank">\2</A>';
		
    # [urlintern=www.phpbb.com]phpBB[/urlintern] code. (no xxxx:// prefix).
    # opens URL in same window
    $patterns[7] = "#\[urlintern=(.*?)\](.*?)\[/urlintern\]#si";
    $replacements[7] = '<a href="\1">\2</A>';

    # [email]user@domain.tld[/email] code.
    $patterns[8] = "#\[email\](.*?)\[/email\]#si";
    $replacements[8] = '<a href="mailto:\1">\1</A>';

    $result = preg_replace($patterns, $replacements, $result);

    # Smilies parsen
    $result = insertSmilies($result);
    return $result;
}

function check_text($text, $hasbr=false) {
    $itext = $text;
    $alltags = '<br/>;';
    $alltags = preg_replace('/;$/', '', $alltags);

    $i_tags = explode(';', $alltags);
    while (list($ikey, $ival) = each($i_tags)) {
        if ($ival != '') {
            $itext = str_ireplace($ival, "### $ikey ###", $itext);
        }
    }

    $itext = str_replace('<', '&lt;', $itext);
    $itext = str_replace('>', '&gt;', $itext);
    $itext = str_replace("'", '&acute;', $itext);
    $itext = preg_replace('/<[^<>]*>/', '', $itext);
    $itext = str_replace('"', '&quot;', $itext);
  
    $i_tags = explode(';', $alltags);
    while(list($ikey, $ival) = each($i_tags)) {
        if ($ival != '') {
            $itext = str_ireplace("### $ikey ###",$ival, $itext);
        }
    }

    $itext = str_replace("\r\n", '<br/>', $itext);
    $itext = str_replace("\n", '<br/>', $itext);
    $itext = str_replace("\r", '<br/>', $itext);
    $itext = str_replace("\n", '<br/>', $itext);

    return $itext;
}

function displayInhalte($nKategorieID, $nParentID) {
  global $dbname, $nPartyID, $nLoginID, $KATEGORIE_NEWSCOMMENT, $KATEGORIE_FORUM, $KATEGORIE_NEWS;

	$nParentID = intval($nParentID);
	$nKategorieID = intval($nKategorieID);
			
  DB::connect();
		
  if ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
    $sSql = "select INHALTID, DERINHALT, AUTOR, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and PARENTID=$nParentID and KATEGORIEID=$nKategorieID order by WANNANGELEGT asc";
  } else {
    $sSql = "select INHALTID, DERINHALT, AUTOR, AUTORNAME, WANNANGELEGT from INHALT where MANDANTID=$nPartyID and (PARENTID=$nParentID or INHALTID=$nParentID) and KATEGORIEID=$nKategorieID order by WANNANGELEGT asc";
  }
  $result = DB::query($sSql);

  # Titel ermitteln.
  if ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
    $nTempKat = $KATEGORIE_NEWS;
  } else {
    $nTempKat = $nKategorieID;
  }
		
  $result_titel = DB::query("select TITEL from INHALT where INHALTID = $nParentID and KATEGORIEID = $nTempKat and MANDANTID = $nPartyID");
	$row_titel = $result_titel->fetch_array();
		
  # ID des letzten Posts holen.
  $sql = "select
            INHALTID
          from
            INHALT
          where
            PARENTID = $nParentID
          order by
            INHALTID desc
          limit 1";		        
  $result_last = DB::query($sql);
  $row_last = $result_last->fetch_row();
  $last_post = $row_last[0];

  echo "<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\">";
  echo "<tr><td class=\"forum_titel\"><img src=\"/gfx/lgif.gif\" width=\"115\" height=\"0\"></td><td class=\"forum_titel\" width=\"100%\"><b>".db2display($row_titel[TITEL])."</b></td></tr>";

  while ($row = $result->fetch_array()) {
    echo "<tr><td class='forum_bg1' valign='top'>";
    $tempAutorID = $row['AUTOR'];
    if ($tempAutorID > 0) {
      echo "<a href=\"benutzerdetails.php?nUserID=$tempAutorID\">";
    }
    echo "<b>".db2display($row['AUTORNAME']);
    if ($tempAutorID > 0) {
      echo "</a>";
    }

    echo "</b><br><span class='kleinertext'>";
			
    if ($tempAutorID > 0) {
      if (User::hatRecht("GASTADMIN", $tempAutorID, $nPartyID)) {
        echo "innovaLAN Gastadmin";
      } elseif (User::hatRecht("TEAMMEMBER2", $tempAutorID, $nPartyID)) {
        echo "innovaLAN Admin";
      } elseif (User::hatRecht("TEAMMEMBER", $tempAutorID, $nPartyID)) {
        echo "innovaLAN Trainee";
      } else {
        echo "Registrierter Benutzer";
      }
    } else {
      echo "Anonymer Benutzer";
    }
			
    echo "</span><br>";
			
    # Userbild
    if ($tempAutorID >= 1) {
        displayUserPic($tempAutorID);
    }
		
    echo "</td>";
    echo "<td class='forum_bg2' valign='top'>\n";
    echo "<table width=\"100%\"><tr><td><span class='kleinertext'><img src='".PELASHOST."gfx/forum_posticon.gif'> ".dateDisplay2($row['WANNANGELEGT'])."</span></td>";
			
		$last_post = (isset($last_post) ? $last_post : $nParentID);
			
    if ($row['INHALTID'] == $last_post && $row['AUTOR'] == $nLoginID) {
      echo "<td align=\"right\"><a href=\"forum.php?Action=edit&Inhalt=$row[INHALTID]\"><img src=\"".PELASHOST."gfx/forum_edit.gif\" border=\"0\"></a></td>";
    }
    echo "</tr></table><hr>";
    echo db2displayForum($row['DERINHALT']);
    echo "<br><br><br></td></tr>\n";
  }
  
  echo "</table>";
		
  if ($nKategorieID == $KATEGORIE_FORUM) {
    echo "<p><table cellpadding='3' cellspacing='5' border='0'><tr><td class='forum_titel'><a href='forum.php?iAction=add&Parent=$nParentID' class=\"forumlink\">Beitrag schreiben</a></td><td class='forum_titel'><a href='forum.php' class=\"forumlink\">Zu den Themen</a></td></tr></table></p>";
	} else if ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
    echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href='news.php?iAction=add&nInhaltID=$nParentID' class=\"forumlink\">Kommentar abgeben</a></td><td class='forum_titel'><a href='news.php' class=\"forumlink\">Zu den News</a></td></tr></table></p>";			
    if ($iAction != "add") {
      echo "<p align=\"right\"><a href=\"news.php?iAction=add&nInhaltID=$nParentID\">$str[abgeben]</a></p>";
    }
  }
}


function InhaltAnlegen($nKategorieID, $nParentID) {
    global $dbname, $nPartyID, $KATEGORIE_NEWS, $KATEGORIE_NEWSCOMMENT, $KATEGORIE_FORUM, $nLoginID, $sLogin;

		$nParentID = intval($nParentID);
		$nKategorieID = intval($nKategorieID);

    # Inhalt anlegen
    DB::connect();

    if ($nParentID == '') {
        $nParentID == -1;
    }

    # Titel ermitteln
    if ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
	$nTempKat = $KATEGORIE_NEWS;
    } else {
        $nTempKat = $nKategorieID;
    }
    $result_titel = DB::query('
        SELECT TITEL
	FROM INHALT
	WHERE INHALTID = ' . $nParentID . '
	  AND KATEGORIEID = ' . $nTempKat . '
	  AND MANDANTID = ' . $nPartyID
    );
    $row_titel = $result_titel->fetch_array();

    if ($_GET['Parent'] == -1) {
        $wasneu = 'Thema';
    } else {
        $wasneu = 'Beitrag';
    }

	if ($nLoginID == '') {
		echo "<p>Nicht autorisiert: Nur eingeloggte Benutzer d&uuml;rfen ein Thema erstellen oder Antworten verfassen.</p>";
		echo "<p><img src=\"/gfx/headline_pfeil.gif\" border=\"0\"> <a href=\"forum.php\">Zum Forum</a></p>";
	} else {

		echo "<p>$wasneu erstellen</p>\n";

		function show_form ($nParentID,$row_titel) {
			global $iBeitrag, $iBeitragstitel, $iNickname, $ComeFrom, $sLogin, $nLoginID;

			if ($nParentID != -1) { 
				echo "<p><table cellspacing=\"1\" cellpadding=\"3\" border=\"0\"><tr><td class='forum_titel'>&nbsp;</td><td class='forum_titel'> Thema: <b>$row_titel[TITEL]</b></td>"; 
			} else {
				echo "<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\"><tr><td class='forum_titel'>&nbsp;</td><td class='forum_titel'> <b>Neues Thema</b></td>";
			}

			$sPostTo = getenv('PATH_INFO');
			if ($sPostTo == '/newscomment.php') {
				# Bei Newscomment Scriptname Frontend != Backend
				$sPostTo = '/news.php';
			}

			?>			

			<form method="post" action="<?php echo $sPostTo; ?>?iAction=add&nInhaltID=<?php echo $nParentID; ?>&Parent=<?php echo $nParentID; ?>" name="data">
			<?= csrf_field() ?>
			<input type="hidden" name="iParent" value="<?php echo $nParentID; ?>">
			<input type="hidden" name="iNick" value="go">
			
				<tr><td class='forum_bg1'>Nickname:</td><td class='forum_bg2'>
				<?php
				if ($nLoginID > 0) {
					echo $sLogin;
					echo "<input type=\"hidden\" name=\"iNickname\" value=\"$sLogin\">";
				} else {
					echo "<input type=\"text\" name=\"iNickname\" size=\"20\" maxlength=\"30\" value=\"$iNickname\">";
				}
				?>
				</td></tr>

				<?php
				if ($nParentID != -1) {
					echo "<input type='hidden' name='iBeitragstitel' value='following thread'>";
				} else {
					echo "<tr><td class='forum_bg1'>Titel:</td><tD class='forum_bg2'><input type='text' name='iBeitragstitel' size='40' maxlength='40' value='$iBeitragstitel'></td></tr>";
				}
				?>

				<tr><td valign="top" class='forum_bg1'>Beitrag:</td><td class='forum_bg2'><textarea name="iBeitrag" wrap="virtual" cols="45" rows="10" maxlength="3000"><?php echo $iBeitrag;?></textarea></td></tr>

				<tr><td colspan="2" height="40" class='forum_bg1'><input type="submit" value="Speichern" name="knopf">&nbsp;<input type="reset">
				<tr><td class="forum_titel" colspan="2"><b>Smilies</b></td></tr>
				<tr><td colspan="2" class="forum_bg1">
					<?php
					include('forum_vars.php');
					$counter = 0;
					for ($i = 1; $i <= $smilies_count; $i++) {
						echo '<img src="' . PELASHOST . 'gfx/' . $SmiliesArray[$i][0] . "\" border=\"0\" onclick=\"javascript:ShowInfo('" . $SmiliesArray[$i][1] . "');\">&nbsp;";
						if ($counter > ($smilies_count / 2)) {
							echo '<br/>';
							$counter = 0;
						}
						$counter++;
					}
					?>
				</td></tr>
			</table>
			</p>
			</form>

			<script type="text/javascript">
			<!--
			function ShowInfo(NewSmilie) {
				document.data.iBeitrag.value=document.data.iBeitrag.value+NewSmilie;
				document.data.iBeitrag.focus();
			}
			//-->
			</script>

			<?php
		}

		if (! isset($_POST['iNick']) ) {
		    show_form($nParentID, $row_titel);
		} else {
		    if (empty($_POST['iNickname']) or empty($_POST['iBeitragstitel']) or empty($_POST['iBeitrag']) ) {
			echo "<p class='fehler'>Du musst alle Felder ausfüllen.</p>";
			show_form($nParentID, $row_titel);
		    } elseif (strlen($_POST['iBeitrag']) > 3000) {
			echo '<p class="fehler">Der Beitrag darf maximal 3000 Zeichen lang sein.</p>' . "\n";
			show_form($nParentID, $row_titel);
		    } elseif (($loginID == '') and ($row['USER_USERID'] != '')) {
			echo '<p class="fehler">Dieser Nickname ist registriert. Du kannst mit Ihm nur Beiträge verfassen, wenn du eingeloggt bist.</p>' . "\n";
			show_form($nParentID, $row_titel);
		    } else {
			if ($nLoginID != '') {
			    $Nick2db = $sLogin;
			    $Userid2db = $nLoginID;
			} else {
			    $Nick2db = $_POST['iNickname'];
			    $Userid2db = -1;
			}
			$result = DB::query(
			    'INSERT INTO INHALT (
			        MANDANTID, PARENTID, KATEGORIEID, AKTIV,
			        TITEL, AUTOR, AUTORNAME, DERINHALT, DATE1,
				WANNANGELEGT, WERANGELEGT, WERGEAENDERT
			    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)',
			    (int)$nPartyID, (int)$_POST['iParent'], (int)$nKategorieID, 'J',
			    $_POST['iBeitragstitel'], (int)$Userid2db, $Nick2db, $_POST['iBeitrag'],
			    (int)$Userid2db, (int)$Userid2db
			);
			if ($_POST['iParent'] > 0) {
			    # Letzen Beitrag in den Parentbeitrag schreiben.
			    $result = DB::query(
			        'UPDATE INHALT SET DATE1 = NOW()
				WHERE INHALTID = ?
				  AND KATEGORIEID = ?
				  AND MANDANTID = ?',
			        (int)$_POST['iParent'], (int)$nKategorieID, (int)$nPartyID
			    );
			}
			# Zurückleiten
			if ($nKategorieID == $KATEGORIE_FORUM) {
			    if ($nParentID == -1) {
			        $NewAction = '';
			    } else {
			        $NewAction = 'view';
			    }
			    $sDest = "forum.php?Action=$NewAction&Parent=$nParentID";
			} elseif ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
			    $sDest = "news.php?iAction=comment&nInhaltID=$nParentID";
			}
			echo "<p>Du wirst umgehend zu Deinem neu erfassten Beitrag <a href=\"$sDest\">weitergeleitet</a>.</p>";
			echo "<meta http-equiv='Refresh' content='4; URL=$sDest'>";
		    }
		}
	    }
}

function InhaltEdit($nKategorieID, $nParentID) {
    global $dbname, $nPartyID, $KATEGORIE_NEWS, $KATEGORIE_NEWSCOMMENT, $KATEGORIE_FORUM, $nLoginID, $sLogin;

		$nParentID = intval($nParentID);
		$nKategorieID = intval($nKategorieID);

    #Inhalt anlegen
    DB::connect();

    # Titel ermitteln
    if ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
	$nTempKat = $KATEGORIE_NEWS;
    } else {
	$nTempKat = $nKategorieID;
    }
		
    $sql = 'SELECT PARENTID, DERINHALT, KATEGORIEID
            FROM INHALT
            WHERE INHALTID = ?';
    $res = DB::query($sql, (int)$_GET['Inhalt']);
    if ($res->num_rows != 1) {
        PELAS::fehler('Ungültige BeitragsID');
    } else {
        $row = $res->fetch_row();
        $nParentID = ($row[0] == -1) ? (int)$_GET['Inhalt'] : $row[0];
        $inhalt = $row[1];      
        $nKategorieID = $row[2];
		
        $sql = 'SELECT TITEL
		FROM INHALT 
		WHERE INHALTID = ?';
	$result = DB::query($sql, (int)$nParentID);
	$row = $result->fetch_row();
	$titel = $row[0];

	$wasneu = ($_GET['Parent'] == -1) ? 'Thema' : 'Beitrag';

	if ($nLoginID == '') {
	    echo "<p>Nicht autorisiert: Nur eingeloggte Benutzer dürfen ein Thema erstellen oder Antworten verfassen.</p>\n";
	    echo '<p><img src="/gfx/headline_pfeil.gif" border="0"> <a href="forum.php">Zum Forum</a></p>' . "\n";
	} else {
		echo "<p>$wasneu bearbeiten</p>\n";

		function show_form ($nParentID, $inhalt, $titel, $inhaltID) {
			global $iBeitrag, $iBeitragstitel, $iNickname, $ComeFrom, $sLogin, $nLoginID;

			if ($nParentID != -1) { 
				echo "<p><table cellspacing=\"1\" cellpadding=\"3\" border=\"0\"><tr><td class='forum_titel'>&nbsp;</td><td class='forum_titel'> Thema: <b>$titel</b></td>"; 
			} else {
				echo "<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\"><tr><td class='forum_titel'>&nbsp;</td><td class='forum_titel'> <b>Neues Thema</b></td>";
			}

			$sPostTo = getenv('PATH_INFO');
			if ($sPostTo == '/newscomment.php') {
				# Bei Newscomment Scriptname Frontend != Backend
				$sPostTo = '/news.php';
			}
			?>			
			<form method="post" action="<?php echo $sPostTo; ?>?Action=edit&Inhalt=<?php echo $inhaltID?>" name="data">
			<?= csrf_field() ?>
			<input type="hidden" name="iNick" value="go">
			<input type="hidden" name="Action" value="edit">
			<input type="hidden" name="Inhalt" value="<?php echo $row['INHALTID']?>">
			
				<tr><td class='forum_bg1'>Nickname:</td><td class='forum_bg2'>
				<?php
				if ($nLoginID > 0) {
					echo $sLogin;
					echo "<input type=\"hidden\" name=\"iNickname\" value=\"$sLogin\">";
				} else {
					echo "<input type=\"text\" name=\"iNickname\" size=\"20\" maxlength=\"30\" value=\"$iNickname\">";
				}
				?>
				</td></tr>

				<?php
				if ($nParentID != -1) {
					echo "<input type='hidden' name='iBeitragstitel' value='following thread'>";
				} else {
					echo "<tr><td class='forum_bg1'>Titel:</td><td class='forum_bg2'><input type='text' name='iBeitragstitel' size='40' maxlength='40' value='$titel'></td></tr>";
				}				
				?>

				<tr><td valign="top" class='forum_bg1'>Beitrag:</td><td class='forum_bg2'><textarea name="iBeitrag" wrap="virtual" cols="45" rows="10" maxlength="3000"><?php echo $inhalt;?></textarea></td></tr>

				<tr><td colspan="2" height="40" class='forum_bg1'><input type="submit" value="&Auml;ndern" name="knopf">&nbsp;<input type="reset">
				<tr><td class="forum_titel" colspan="2"><b>Smilies</b></td></tr>
				<tr><td colspan="2" class="forum_bg1">
					<?php
					include('forum_vars.php');
					$counter = 0;
					for ($i = 1; $i <= $smilies_count; $i++) {
						echo '<img src="' . PELASHOST . 'gfx/' . $SmiliesArray[$i][0] . "\" border=\"0\" onclick=\"javascript:ShowInfo('" . $SmiliesArray[$i][1] . "');\">&nbsp;";
						if ($counter > ($smilies_count / 2)) {
							echo '<br/>';
							$counter = 0;
						}
						$counter++;
					}
					?>
				</td></tr>
			</table>
			</p>
			</form>

			<script type="text/javascript">
			<!--
			function ShowInfo(NewSmilie) {
				document.data.iBeitrag.value=document.data.iBeitrag.value+NewSmilie;
				document.data.iBeitrag.focus();
			}
			//-->
			</script>

			<?php
		}
		if (! isset($_POST['iNick']) ) {
			show_form($nParentID, $inhalt, $titel, $_GET['Inhalt']);
		} else {
			if (empty($_POST['iNickname']) or empty($_POST['iBeitragstitel']) or empty($_POST['iBeitrag'])) {
				echo '<p class="fehler">Du musst alle Felder ausfüllen.</p>' . "\n";
				show_form($nParentID, $inhalt, $titel, $_GET['Inhalt']);
			} elseif (strlen($_POST['iBeitrag']) > 3000) {
				echo '<p class="fehler">Der Beitrag darf maximal 3000 Zeichen lang sein.</p>' . "\n";
				show_form($nParentID, $inhalt, $titel, $_GET['Inhalt']);
			} elseif (($loginID == '') and ($row['USER_USERID'] != '')) {
				echo '<p class="fehler">Dieser Nickname ist registriert. Du kannst mit Ihm Nur Beiträge verfassen, wenn du eingeloggt bist.</p>' . "\n";
				show_form($nParentID, $inhalt, $titel, $_GET['Inhalt']);
			} else {			  
				if ($nLoginID != '') {
					$Nick2db = $sLogin;
					$Userid2db = $nLoginID;
				} else {
					$Nick2db = $_POST['iNickname'];
					$Userid2db = -1;
				}
				$sql = 'UPDATE INHALT
				        SET DERINHALT = ?
				        WHERE INHALTID = ?';
			        DB::query($sql, $_POST['iBeitrag'], (int)$_GET['Inhalt']);
				
				$sql = "UPDATE INHALT
				        SET DATE1 = NOW()
				        WHERE INHALTID = '$nParentID'";
				DB::query($sql);
				
				# Zurückleiten
				if ($nKategorieID == $KATEGORIE_FORUM) {
					$sDest = "forum.php?Action=view&Parent=$nParentID";
				} elseif ($nKategorieID == $KATEGORIE_NEWSCOMMENT) {
					$sDest = "news.php?iAction=comment&nInhaltID=$nParentID";
				}
				
				echo "<p>Dein <a href=\"$sDest\">Beitrag</a> wurde erfolgreich geändert.</p>";
				echo "<meta http-equiv='Refresh' content='4; URL=$sDest'>";
			}
		}

		}
	}
}
?>
