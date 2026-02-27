<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "MANDANTADMIN";

include "checkrights.php";

require('format.php');
require('admin/vorspann.php');
?>

<h1>Mandant Einstellungen</h1>

<p>Konfigurationsvariablen für die Anwendung, aufgeteilt nach Mandanten.</p>

<?php 

if ($_POST['iGo'] == "Yes") {
	$sql = "select * from CONFIG where MANDANTID=".intval($_POST['iMandant']);
	$result = DB::getRows($sql);
	foreach ($result as $key => $row) {
		$sql = "update CONFIG set STRINGWERT = '" . safe($_POST[$row['PARAMETER']]) . "' where PARAMETER = '".$row['PARAMETER']."' and MANDANTID=".intval($_POST['iMandant']);
		$erfolg = DB::getOne($sql);
	}
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	echo "<p>Konfiguration gespeichert.</p>";
} else {
	$sql = "select 
						m.MANDANTID, 
						m.BESCHREIBUNG 
					from 
						RECHTZUORDNUNG r, 
						MANDANT m 
					where 
						r.USERID=".intval($loginID)." 
						and r.RECHTID='MANDANTADMIN' 
						and r.MANDANTID=m.MANDANTID 
						and r.MANDANTID > 0";
	$rows = DB::getRows($sql);
	foreach ($rows as $key => $row) {
		$theMandant = $row['MANDANTID'];
		if (BenutzerHatRecht("MANDANTADMIN",$theMandant)) {
			echo "<table cellspacing=\"0\" cellpadding=\"0\">\n";
			echo "<tr><td class=\"navbar\">\n";
			echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\">\n";
			echo "<tr><td class=\"navbar\" colspan=\"3\"><b>".db2display($row['BESCHREIBUNG'])."</b></td></tr>";
			echo "<form method=\"post\" action=\"config.php\">";
			echo csrf_field() . "\n";
			echo "<input type=\"hidden\" name=\"iMandant\" value=\"".$row['MANDANTID']."\">";
			$sql = "select * from CONFIG where MANDANTID=".intval($theMandant)." order by PARAMETER";
			$resultCDatas= DB::getRows($sql);
			foreach ($resultCDatas as $key => $rowCData) {
				echo "<tr><td class=\"dblau\">".db2display($rowCData['PARAMETER'])."</td><td class=\"hblau\"><input type=text name=\"".db2display($rowCData['PARAMETER'])."\" size=20 maxlength=255 value=\"".$rowCData['STRINGWERT']."\"></td><td class=\"dblau\">".db2display($rowCData['BESCHREIBUNG'])."</tr>\n";
			}
			echo "<input type=\"hidden\" value=\"Yes\" name=\"iGo\">";
			echo "<tr><td class=\"dblau\" align=\"center\" colspan=3><input type=submit value=\"Speichern\"></td></tr>";
			echo "</table></td></tr></table></form>\n";
		}
	}
}

require('admin/nachspann.php');
?>
