<?php
# Include für die Sidebar mit Sponsor-Logos
include_once "dblib.php";
//Reservierung offen?
$sql = "select 
	  STRINGWERT 
	from 
	  CONFIG 
	where 
	  PARAMETER = 'SPONSOREN_AKTIV' AND
	  MANDANTID = $nPartyID";
$result = DB::query($sql);
$row = mysql_fetch_assoc($result);
// checken, ob get-variable on
if ($row['STRINGWERT'] == "J") {
		$sql = "select 
						s.Website, 
						s.Logo_Sidebar
					from 
						sponsoren s
					where 
						s.MandantID = $nPartyID
					Order by Rand()";
		$rows = DB::getRows($sql);
		echo '<center>';
		echo '<br>';
		foreach ($rows as $key => $row) {
			echo '<a href="'.$row['Website'].'" target="_blank"><img src="'.$row['Logo_Sidebar'].'" style="width:150px; margin:15px 0px 15px 0px"></a>';
		}
	}
?>
    
  