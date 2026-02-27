<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "TURNIERADMIN";
include "checkrights.php";
include "../../includes/admin/vorspann.php";

$dbh = DB::connect();

echo "<h1>Turniere: Preiseliste ausgeben</h1>";

// Mandant nicht gewählt
if ($iMandantID < 1) {
	echo "<form method=\"post\" action=\"turnierpreisliste.php\">";
	echo csrf_field() . "\n";
	echo "<p>Mandantauswahl: <select name=\"iMandantID\">\n";
		
	$result= DB::query("select m.MANDANTID, m.BESCHREIBUNG from RECHTZUORDNUNG r, MANDANT m where r.USERID=$loginID and r.RECHTID='TURNIERADMIN' and r.MANDANTID=m.MANDANTID and r.MANDANTID > 0");
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	while ($row = $result->fetch_array()) {
		echo "<option value=\"$row[MANDANTID]\">$row[BESCHREIBUNG]\n";
	}
		
	echo "</select> <input type=\"submit\"></p></form>";

} else {
//ok, Mandant ausgewählt

		$result= DB::query("select * from TURNIERLISTE where MANDANTID=$iMandantID order by NAME");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";

		echo "<table width=\"640\" cellspacing=\"1\" cellpadding=\"5\" border=\"1\">\n";
		while ($row = $result->fetch_array()) {
			if ($bgc=="hblau") $bgc="dblau"; else $bgc="hblau";
			echo "<tr><td bgcolor=\"#EEEEEE\" colspan=\"3\"><b>".db2display($row['NAME'])."</b></td></tr>";
      echo "<tr><td>1.Platz</td><td>".db2display($row['PREIS_PLATZ1'])."&nbsp;</td><td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td></tr>";
      echo "<tr><td>2.Platz</td><td>".db2display($row['PREIS_PLATZ2'])."&nbsp;</td><td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td></tr>";
      echo "<tr><td>3.Platz</td><td>".db2display($row['PREIS_PLATZ3'])."&nbsp;</td><td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td></tr>";
		}
    
    echo"</table>";
		
}

include "../../includes/admin/nachspann.php";
?>
