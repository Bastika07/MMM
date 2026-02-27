<?php
$ebene = intval($ebene);

if (!isset($dbh))
  $dbh_spg = DB::connect();
else 
  $dbh_spg = $dbh;

$result = mysql_query ("select * from SITZDEF where EBENE='$ebene'", $dbh_spg);
//echo mysql_errno().": ".mysql_error()."<BR>";
//$PlatzArray = Array ( $row[REIHE] => Array ($row[LAENGE], $row[XCORD], $row[YCORD], $row[AUSRICHTUNG], $row[ISTLOGE] ));
$PlatzArray = "";
while ($row = mysql_fetch_array($result)) {
	//Kommentar ausgeben
	$PlatzArray[$row['REIHE']][0] = $row['LAENGE'];
	$PlatzArray[$row['REIHE']][1] = $row['XCORD'];
	$PlatzArray[$row['REIHE']][2] = $row['YCORD'];
	$PlatzArray[$row['REIHE']][3] = $row['AUSRICHTUNG'];
	$PlatzArray[$row['REIHE']][4] = $row['ISTLOGE'];
}

//groessenkonstanten
$result = mysql_query ("select STRINGWERT from CONFIG where PARAMETER='SITZTIEFE'", $dbh_spg);
$row = mysql_fetch_array($result);
$tTempTiefe = $row['STRINGWERT'];
if ($tTempTiefe > 0) {
	$tbreite = $tTempTiefe;
} else {
	$tbreite = 13;
}
$result = mysql_query ("select STRINGWERT from CONFIG where PARAMETER='SITZBREITE'", $dbh_spg);
$row = mysql_fetch_array($result);
$tTempBreite = $row['STRINGWERT'];
if ($tTempBreite > 0) {
	$tlaenge = $tTempBreite;
} else {
	$tlaenge = 13;
}

//Breite der Loge
$result = mysql_query ("select STRINGWERT from CONFIG where PARAMETER='LOGE_SITZBREITE'", $dbh_spg);
$row = mysql_fetch_array($result);
$tTempBreite = $row['STRINGWERT'];
if ($tTempBreite > 0) {
	$tLogelaenge = $tTempBreite;
} else {
	$tLogelaenge = 18;
}

//Maximale Reihen
$result = mysql_query ("select MAX(REIHE) as MAXROW from SITZDEF where EBENE='$ebene'", $dbh_spg);
//echo mysql_errno().": ".mysql_error()."<BR>";
$row = mysql_fetch_array($result);
$maxreihen = $row['MAXROW'];

//Reihen starten ab
$result = mysql_query ("select MIN(REIHE) as MINROW from SITZDEF where EBENE='$ebene'", $dbh_spg);
$row = mysql_fetch_array($result);
$startreihen = $row['MINROW'];

?>
