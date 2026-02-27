<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "EINLASSADMIN";
include "checkrights.php";

header('Content-type: text/xml; charset=ISO-8859-1');

$query = mysql_real_escape_string($_GET['query']);

if (empty($query)) {
  // Wenn keine Einschränkung übergeben wurde, dann leeres Ergebnis zurückliefern
  echo "<users/>\n";
} else {
  $sql = "select
            LOGIN, USERID, NAME, NACHNAME
          from
            USER
          where
            CONCAT(CONCAT(NAME, ' '), NACHNAME) LIKE '%$query%' OR
            LOGIN LIKE '%$query%'";
  $result = DB::query($sql);
  
  echo "<users>\n";
  while ($row = mysql_fetch_assoc($result)) {
    echo "  <user id=\"{$row['USERID']}\">".xmlentities($row['LOGIN'])." (".xmlentities($row['NAME'])." ".xmlentities($row['NACHNAME']).", ID:".$row['USERID'].")</user>\n";
  }
  echo "</users>";
}
function xmlentities($string) {
   return str_replace ( array ( '&', '"', "'", '<', '>', '?' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string );
}

?>
