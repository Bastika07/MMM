<?php
require('controller.php');
include_once "dblib.php";
$iRecht = "USERADMIN";
include_once "checkrights.php";

header('Content-type: plain/text');


$dbh = DB::connect();
$mandantId = 3;

$sql = "SELECT
					EBENE, REIHE, LAENGE
				FROM
					SITZDEF
				WHERE
					MANDANTID = $mandantId";
$res = DB::query($sql);
while ($row = mysql_fetch_assoc($res)) {
	for ($i = 1; $i <= $row['LAENGE']; $i++)
		$ebenen[$row['EBENE']][$row['REIHE']][$i] = '[frei]';
}

$sql = "SELECT 
  				sd.EBENE, sd.REIHE, st.PLATZ, u.LOGIN
				FROM 
  				SITZDEF sd, SITZ st
				LEFT JOIN 
  				SITZ s ON s.REIHE = sd.REIHE AND s.PLATZ = st.PLATZ AND s.MANDANTID = $mandantId
				LEFT JOIN 
  				USER u ON u.USERID = s.USERID
				WHERE 
			  sd.MANDANTID = $mandantId AND 
			  st.MANDANTID = $mandantId AND 
			  st.REIHE = sd.REIHE
			  and st.RESTYP=1";
$res = DB::query($sql);
echo mysql_error();
while ($row = mysql_fetch_assoc($res)) {
	$ebenen[$row['EBENE']][$row['REIHE']][$row['PLATZ']] = $row['LOGIN'];
}  
		
echo "ebene;reihe;platz;login\n";
		
foreach ($ebenen as $ebeneNr => $ebene)
	foreach ($ebene as $reiheNr => $reihe)
		foreach ($reihe as $platzNr => $login)
			echo "$ebeneNr;$reiheNr;$platzNr;$login\n";
	

?>
