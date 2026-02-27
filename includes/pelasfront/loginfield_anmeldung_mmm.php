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
  $result = mysql_db_query ($dbname, "select STATUS from ASTATUS where USERID='".intval($nLoginID)."' and MANDANTID='".intval($nPartyID)."'", $dbh);
  $row = mysql_fetch_array($result);
  if ($row[STATUS] == 1) {
	  $selected = 3; 
  } elseif (($row[STATUS] == 2) || ($row[STATUS] == 3)) {
	  $selected = 4;
  }
}

?>

<ul class="status">
<?php
//select 1 = einloggen
if ($selected == 1) {
	echo "<li class='alert'><b><a class=\"navlink\" href=\"?page=5\"><b>$str[lh_einloggen]</b></a></b></li>".
	"<li class='nook'>$str[lh_anmelden]</li>".
	"<li class='nook'>$str[lh_bezahlen]</li>".
	"<li class='nook'>$str[lh_reservieren]</li>";
} elseif ($selected == 2) {
//select 2 = anmelden
	echo "<li class='ok'>$str[lh_einloggen]</li>".
	"<li class='alert'><b><a class=\"navlink\" href=\"?page=6&action=order\"><b>$str[lh_anmelden]</b></a></b></li>".
	"<li class='nook'>$str[lh_bezahlen]</li>".
	"<li class='nook'>$str[lh_reservieren]</li>";
} elseif ($selected == 3) {
//select 3 = bezahlen
	echo "<li class='ok'>$str[lh_einloggen]</li>".
	"<li class='ok'>$str[lh_anmelden]</li>".
	"<li class='alert'><b><a class=\"navlink\" href=\"?page=6&action=bill\"><b>$str[lh_bezahlen]</b></a></b></li>".
	"<li class='nook'>$str[lh_reservieren]</li>";
} elseif ($selected == 4) {
//select 4 = setzen
	echo "<li class='ok'>$str[lh_einloggen]</li>".
	"<li class='ok'>$str[lh_anmelden]</li>".
	"<li class='ok'>$str[lh_bezahlen]</li>".
	"<li class='alert'><b><a class=\"navlink\" href=\"?page=13\"><b>$str[lh_reservieren]</b></a></b></li>";
}
?>
</ul>

<?php


?>