<?php
require_once('dblib.php');
require_once('format.php');
require('language.inc.php');

// Details der eingeloggten Sessiondaten lesen und zählen.
$sql = "
  SELECT u.USERID, u.LOGIN
  FROM USER u, php_session s
  WHERE UNIX_TIMESTAMP(s.session_time) >= UNIX_TIMESTAMP() - " . USER_ONLINE_TIMEOUT . "
    AND s.userId = u.USERID
    AND s.mandantId = '".intval($nPartyID)."'
  ORDER BY s.session_time DESC
";

echo "<!-- $sql -->";

$result = DB::query($sql);
$loggedUserOnline = $result->num_rows;

// Alle Sessiondatensätze lesen.
$sql = "
  SELECT COUNT(*)
  FROM php_session s
  WHERE UNIX_TIMESTAMP(s.session_time) >= UNIX_TIMESTAMP() - " . USER_ONLINE_TIMEOUT . "
    AND s.mandantId = '".intval($nPartyID)."'
";
$userOnlineGesamt = DB::getOne($sql);
  
//if (isset($sStyle) && $sStyle == "d6") {
  // Output D6-Style (neben Forum mit Forum-CSS)
//  echo '<table cellspacing="1" cellpadding="3" border="0" width="100%">' . "\n";
//  echo '  <tr><td class="forum_titel">Wer ist online? (' . $userOnline . ")</td></tr>\n";
//  echo '  <tr><td class="dblau">' . "\n";
//  $count = 0;
//  while ($row = $result->fetch_array()) {
//   echo '    <a href="?page=4&nUserID=' . $row['USERID'] . '">' . db2display($row['LOGIN']) . "</a><br/>\n";
//    $count++;
//  }
//  echo "  </td></tr>\n";
 // echo "</table>\n";
//} else {
  echo '<div class="users-online">' . "\n";
  echo '  <p>' . $userOnlineGesamt . ' Benutzer '.(($userOnlineGesamt == 1) ? "ist" : "sind" ).' online. Davon '.(($loggedUserOnline == 1) ? 			"ist" : "sind" ).' ' . $loggedUserOnline . ' eingeloggt:</p>';
  echo "  <ul>\n";
  while ($row = $result->fetch_array()) {
    echo '    <li><i class="fa fa-user"></i> <a href="?page=4&nUserID=' . $row['USERID'] . '">' . db2display($row['LOGIN']) . "</a></li>\n";
  }
  echo "  </ul>\n";
  echo "</div>\n";
//}

//Aufräumen alles was älter als min ist. Gritzi 19.6.2022
$sql    = "DELETE FROM php_session WHERE session_time < now() - INTERVAL 7 DAY"; 
DB::query($sql);

?>
