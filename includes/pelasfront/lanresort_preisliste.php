<?php
include_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "language.inc.php";


echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">";

$q = "select * from bungalow_type where mandantID='$nPartyID' order by preis";
$res = DB::query($q);
while ($row = mysql_fetch_array($res)) {
	echo "<tr><td width=\"190\">Bungalow ".$row['bezeichnung']."</td>";
	echo "<td width=\"90\">".$row['size']." Personen</td>";
	echo "<td>".$row['preis']." EUR</td>";
	echo "</tr>";
};

echo "</table>";


?>