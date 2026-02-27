<?php
require_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "language.inc.php";

// 1 Nachricht in wieviel Minuten?
define('TIMELIMIT', 5);
define('CHARLIMIT', 4000);

if ($nLoginID < 1) {
  echo "<p class=\"fehler\">".$str['kontakt_nureingeloggt']."</p>";
} else {
  if ($_GET['nUserID'] < 1) { $_GET['nUserID'] = -1;}
  $err = false;
  $q = "SELECT zeit\n"
      ."FROM USERMAIL\n"
      ."WHERE von = ".intval($nLoginID)."\n"
      ."ORDER BY zeit DESC\n"
      ."LIMIT 0, 1\n";
  if ($res = DB::query($q)) {
    $row = mysql_fetch_row($res);
    $timeDif = ceil((time() - $row[0])/60);
  }
  
  $text = isset($_POST['text']) ? $_POST['text'] : '';
  
  if (isset($text) && empty($text)) {
    PELAS::fehler('Du hast keine Nachricht eingegeben!');
    $err = true;
  } else if (isset($text) && strlen($text) > CHARLIMIT) {
    PELAS::fehler('Deine Nachricht darf nicht länger als '.CHARLIMIT.' Zeichen sein!');
    $err = true;
  } else if ($timeDif < TIMELIMIT) {
    PELAS::fehler('Du hast vor '.$timeDif.' Minuten die letzte Nachricht geschickt. Du darfst jedoch nur 1 Nachricht pro '.TIMELIMIT.' Minuten versenden!');
    $err = true;
  }

  if (isset($text) && $_GET['nUserID'] != -1 && !$err) {
    // Mail senden
    sendMail($text, $_GET['nUserID'], $nLoginID);
  } else {
    $result = DB::query("select * from USER where USERID=".intval($_GET['nUserID']));
    $row = mysql_fetch_array($result);

    echo "<form method=\"post\" action=\"?page=17&nUserID=".intval($_GET['nUserID'])."\">\n";
    echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\">\n";
    echo "  <tr><td class=\"pelas_benutzer_titel\" height=\"39\" valign=\"middle\" colspan=\"3\" valign=\"top\">";
    echo "    <b>".$str['kontakt_to'].": ".db2display($row['LOGIN'])."</b>\n";
    echo "  </td></tr>";
    echo "  <tr><td class=\"pelas_benutzer_inhalt\" valign=\"top\"><p>".$str['kontakt_deinenachricht'].":
            <textarea name=\"text\" style=\"width:90%;\" rows=\"8\">".(($err) ? $text : '')."</textarea>
            <br><div align=\"center\"><input type=\"submit\"></div>
            <p>".$str['kontakt_satz1']."
            <ul> 
              <li>".$str['kontakt_satz2']." ".TIMELIMIT." ".$str['kontakt_satz3']."</li>
              <li>".$str['kontakt_satz4']."</li>
              <li>".$str['kontakt_satz5']." ".CHARLIMIT." ".$str['kontakt_satz6']."</li>
            </ul>
            <br>
            </p>        
            </td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
  }
}

function sendMail($text, $to, $from) {
  global $nPartyID;
  $text = str_replace("\r", "", $text);
  $to = intval($to);
  DB::connect();
  $q = "select LOGIN, EMAIL\n"
      ."from USER\n"
      ."where USERID=".$to;
      
  if ($res = mysql_query($q)) {
    $row = mysql_fetch_assoc($res);
    $subject = "Nachricht bezüglich ".PELAS::mandantByID($nPartyID);
    $msg = "Hallo ".$row['LOGIN'].",\n"
          ."".User::name($from)." möchte dir folgende Nachricht zukommen lassen\n"
          ."----------------------------------\n"
          .$text
          ."\n----------------------------------\n"
          ."Die Emailadresse von ".User::name($from)." lautet: ".User::email($from)."\n"
          ."Seine ID ist: ".$from."\n"
          ."Wenn du mit ".User::name($from)." Kontakt aufnehmen willst, kannst "
          ."du einfach auf diese Mail antworten."
          ."\n\n---\nDiese Nachricht wurde über PELAS, das Verwaltungssystem "
          ."von ".PELAS::mandantByID($nPartyID).", verschickt. Bei Fragen wende dich bitte an ".ADMIN_MAIL."\n";

		$erfolg = sende_mail_text($row['EMAIL'], $subject, $msg, User::email($from), User::name($from) );
					
    if ($erfolg) {
      // erfolg      
      DB::query("INSERT INTO USERMAIL (von, an, text, zeit) VALUES (?, ?, ?, ?)", $from, $to, $msg, time());
      echo "<table width=\"90%\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\">\n";
      echo "  <tr><td class=\"pelas_benutzer_titel\" height=\"39\" colspan=\"3\" valign=\"top\">";
      echo "    <table width=\"100%\" height=\"100%\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\">";
      echo "      <tr><td class=\"pelas_benutzer_titel\"><b>Nachricht erfolgreich verschickt!</b></td></tr>";
      echo "    </table>";
      echo "  </td></tr>";
      echo "  <tr><td class=\"pelas_benutzer_inhalt\" valign=\"top\"><p>".nl2br(db2display($msg))."</td></tr>\n";
      echo "</table>\n";
    } else {
      PELAS::fehler('Fehler beim verschicken der Nachricht! Bitte erneut versuchen. 
                     Bei wiederholten Problemen bitte info@innovalan.de kontaktieren!');
    }
  }

}

?>
