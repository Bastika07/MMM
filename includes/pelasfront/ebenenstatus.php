<?php
include_once "dblib.php";

$ebene = intval($ebene);


function getEbenenStatus($ebene)
{  
  global $dbname, $dbh, $nPartyID, $SITZ_RESERVIERT;
  
  if ($ebene == -1) {
    // alle Plätze
    $addSql = "";
  } else {
    // auf Ebene beschränken
    $addSql = "d.ebene = '$ebene' and";
  }
  
  $sql = "select
            count(*) as zahl
          from
            sitzplan_def d
          where 
            mandantID = $nPartyID and
            $addSql
            (type = 'platz' or type = 'logenplatz')";
  $res = DB::query($sql);
  $row = $res->fetch_array();  
  $sitzeEbene = $row['zahl'];
  
  $sql = "select
            count(*) as zahl
          from
            SITZ s,
            sitzplan_def d
          where 
            s.MANDANTID = $nPartyID and
            d.mandantID = $nPartyID and
            $addSql
            (type = 'platz' or type = 'logenplatz') and
            s.REIHE = d.reihe and
            s.PLATZ = d.platz and
            s.RESTYP = '$SITZ_RESERVIERT'";
  $res = DB::query($sql);
  $row = $res->fetch_array();
  $belegungEbene = $row['zahl'];
  
  echo "$belegungEbene/$sitzeEbene";

//Funktion Ende
}

?>
