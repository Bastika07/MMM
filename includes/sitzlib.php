<?php
$ebene = intval($ebene);

if (!isset($dbh))
  $dbh_spg = DB::connect();
else 
  $dbh_spg = $dbh;

$result = DB::query("select * from SITZDEF where EBENE='$ebene'");
//echo DB::$link->errno.": ".DB::$link->error."<BR>";
//$PlatzArray = Array ( $row[REIHE] => Array ($row[LAENGE], $row[XCORD], $row[YCORD], $row[AUSRICHTUNG], $row[ISTLOGE] ));
$PlatzArray = "";
while ($row = $result->fetch_array()) {
	//Kommentar ausgeben
	$PlatzArray[$row['REIHE']][0] = $row['LAENGE'];
	$PlatzArray[$row['REIHE']][1] = $row['XCORD'];
	$PlatzArray[$row['REIHE']][2] = $row['YCORD'];
	$PlatzArray[$row['REIHE']][3] = $row['AUSRICHTUNG'];
	$PlatzArray[$row['REIHE']][4] = $row['ISTLOGE'];
}

//groessenkonstanten
$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='SITZTIEFE'");
$row = $result->fetch_array();
$tTempTiefe = $row['STRINGWERT'];
if ($tTempTiefe > 0) {
	$tbreite = $tTempTiefe;
} else {
	$tbreite = 13;
}
$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='SITZBREITE'");
$row = $result->fetch_array();
$tTempBreite = $row['STRINGWERT'];
if ($tTempBreite > 0) {
	$tlaenge = $tTempBreite;
} else {
	$tlaenge = 13;
}

//Breite der Loge
$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='LOGE_SITZBREITE'");
$row = $result->fetch_array();
$tTempBreite = $row['STRINGWERT'];
if ($tTempBreite > 0) {
	$tLogelaenge = $tTempBreite;
} else {
	$tLogelaenge = 18;
}

//Maximale Reihen
$result = DB::query("select MAX(REIHE) as MAXROW from SITZDEF where EBENE='$ebene'");
//echo DB::$link->errno.": ".DB::$link->error."<BR>";
$row = $result->fetch_array();
$maxreihen = $row['MAXROW'];

//Reihen starten ab
$result = DB::query("select MIN(REIHE) as MINROW from SITZDEF where EBENE='$ebene'");
$row = $result->fetch_array();
$startreihen = $row['MINROW'];

?>
