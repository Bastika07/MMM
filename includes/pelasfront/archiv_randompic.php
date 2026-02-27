<?
include_once "dblib.php";
include_once "format.php";
include_once "pelasfunctions.php";

if (isset($minPartyID) && is_numeric($minPartyID) && $minPartyID > 0) {
	$addWhere = " AND a.PARTYID >= '$minPartyID'";
} else {
	$addWhere = "";
}

// Neues Archiv auslesen
$sql = "SELECT
          a.ARCHIVID, h.beschreibung as NAME, a.TYP
        FROM
          ARCHIV AS a, party AS h
        WHERE 
          a.LOCKED ='no' AND
          a.TYP = 'img' AND
          a.MANDANTID = '$nPartyID' AND
          a.PARTYID = h.partyId".$addWhere;
$q = DB::query($sql);
$pics = array();
while ($row = mysql_fetch_assoc($q)) {
  $archivId = $row['ARCHIVID'];
  $ausgaben[$archivId] = $row['NAME'];
  $typ = $row['TYP'];
  addArchivToArray($archivId, &$pics, $typ);
}


#altes Archiv auslassen! Geht so nicht mehr gut!
/*
$sql = "SELECT
          a.ARCHIVID, h.NAME, a.TYP
        FROM
          ARCHIV_INFO AS a, PARTYHISTORIE AS h
        WHERE 
          a.LOCKED ='no' AND
          a.TYP = 7 AND
          a.PARTYID = '$nPartyID' AND
          a.PARTYID = h.MANDANTID AND
          a.LFDNR = h.LFDNR";
$q = DB::query($sql);
$pics = array();
while ($row = mysql_fetch_assoc($q)) {
  $archivId = $row['ARCHIVID'];
  $ausgaben[$archivId] = $row['NAME'];
  $typ = $row['TYP'];
  addArchivToArray($archivId, &$pics, $typ);
}
*/


$picnum = rand(0, count($pics)-1);
$archivId = $pics[$picnum]['archivId'];
$webdir = $sPelasHost."archiv/_img/".$archivId;
$thumb = $webdir.'/'.$pics[$picnum]['thumb'];
$pic = $webdir.'/'.substr($pics[$picnum]['thumb'], 3);
$archivLink = "/archiv.php?selectPartyID=$nPartyID&selectTyp=img&archivID=$archivId";


echo "<a href=\"$pic\"><img src=\"$thumb\" vspace=\"3\" border=\"0\"></a><BR>";
echo db2display($ausgaben[$archivId])."\n<br>";
echo "<img src=\"/gfx/pfeil.gif\" border=\"0\"> <a class=\"navlink\" href=\"$archivLink\">$str[galerie_ansehen]</a><br>\n";


function addArchivToArray($archivId, &$array, $typ) {
  $dirname = PELASDIR."archiv/_".$typ."/".$archivId;
  if (is_dir($dirname)) {
    $glob = glob($dirname."/tn*");
    foreach ($glob as $val) {
      array_push($array, array('thumb' => basename($val), 'archivId' => $archivId));
    }
  }
}
?>
