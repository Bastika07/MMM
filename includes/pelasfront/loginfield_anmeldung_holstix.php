<?php
include_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "pelasfunctions.php";

$selected = 1;

if ($nLoginID > 0) {
	$selected = 2;
}

//feststellen, ob noch nicht angemeldet
if ($nLoginID < 1) {
  $selected = 1;
} else {
  $result = DB::query("select STATUS from ASTATUS where USERID='$nLoginID' and MANDANTID='$nPartyID'");
  $row = $result->fetch_array();
  if ($row[STATUS] == 1) {
	  $selected = 3; 
  } elseif (($row[STATUS] == 2) || ($row[STATUS] == 3)) {
	  $selected = 4;
  }
}

?>

<table cellspacing="0" cellpadding="1" border="0">
<tr><td>

<?php

//select 1 = einloggen
if ($selected == 1) {
	echo "<b>1. <a class=\"navlink\" href=\"/login.php\"><b>$str[lh_einloggen]</b></a></b><br>".
	"2. $str[lh_anmelden]<br>".
	"3. $str[lh_bezahlen]<br>".
	"4. $str[lh_reservieren]<br>";
} elseif ($selected == 2) {
//select 2 = anmelden
	echo "1. $str[lh_einloggen]<br>".
	"<b>2. <a class=\"navlink\" href=\"/teilnahme.php\"><b>$str[lh_anmelden]</b></a></b><br>".
	"3. $str[lh_bezahlen]<br>".
	"4. $str[lh_reservieren]<br>";
} elseif ($selected == 3) {
//select 3 = bezahlen
	echo "1. $str[lh_einloggen]<br>".
	"2. $str[lh_anmelden]<br>".
	"<b>3. <a class=\"navlink\" href=\"/teilnahme.php\"><b>$str[lh_bezahlen]</b></a></b><br>".
	"4. $str[lh_reservieren]<br>";
} elseif ($selected == 4) {
//select 4 = setzen
	echo "1. $str[lh_einloggen]<br>".
	"2. $str[lh_anmelden]<br>".
	"3. $str[lh_bezahlen]<br>".
	"<b>4. <a class=\"navlink\" href=\"/sitzplan.php\"><b>$str[lh_reservieren]</b></a></b><br>";
}

if ($nLoginID > 1) {
  ?>
	<center>
	<small><i>(</i><a href="/login.php?Action=logout">Ausloggen</a><i>)</i></small>
	</center>
  <?php
}
?>

</td></tr>
</table>
<?php


?>