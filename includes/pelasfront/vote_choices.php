<?php
require_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "language.inc.php";

//Aktuelle Umfrage ermitteln

$result= mysql_query("select UMFRAGE_UMFRAGEID, UMFRAGE_BESCHREIBUNG,UMFRAGE_AUSWAHL_ANZAHL from UMFRAGE where UMFRAGE_MANDANTID=".intval($nPartyID)." and UMFRAGE_AKTUELL='J'");
//echo mysql_errno().": ".mysql_error()."<BR>";
$row = mysql_fetch_array($result);
$aktuelle_umfrage = $row['UMFRAGE_UMFRAGEID'];
$putout = $row['UMFRAGE_BESCHREIBUNG'];
$anzahl = $row['UMFRAGE_AUSWAHL_ANZAHL'];


// Umfrage sichtbar?
$sql = "select 
	  STRINGWERT 
	from 
	  CONFIG 
	where 
	  PARAMETER = 'UMFRAGE_AKTIV' AND
	  MANDANTID = $nPartyID";
$result = DB::query($sql);
$row = mysql_fetch_assoc($result);
// checken, ob get-variable on
if ($row['STRINGWERT'] == "N") {
	$aktuelle_umfrage = 0;
} 


if ($aktuelle_umfrage < 1) {
	// Keine Umfrage aktiv
	//echo "Umfrage ist inaktiv";
} else {
	//Umfrage zeigen
	echo "<form method=\"post\" action=\"?page=32\">";
	
	echo "<p>$putout</p>";
	if($anzahl>1){
			echo "<p>($anzahl Antworten möglich)</p>";
	}

	//$result = mysql_query("select b.UMFRAGE_BESCHREIBUNG, a.UMFVAUS_VOTENR, a.UMFVAUS_VOTEBESCHREIBUNG from UMFRAGE b, UMFVAUS a where b.UMFRAGE_UMFRAGEID=".intval($aktuelle_umfrage)." and a.UMFVAUS_UMFRAGEID = ".intval($aktuelle_umfrage)." and b.UMFRAGE_MANDANTID=".intval($nPartyID)." order by a.UMFVAUS_VOTEBESCHREIBUNG");
	$result = mysql_query("select b.UMFRAGE_BESCHREIBUNG, a.UMFVAUS_VOTENR, a.UMFVAUS_VOTEBESCHREIBUNG from UMFRAGE b, UMFVAUS a where b.UMFRAGE_UMFRAGEID=".intval($aktuelle_umfrage)." and a.UMFVAUS_UMFRAGEID = ".intval($aktuelle_umfrage)." and b.UMFRAGE_MANDANTID=".intval($nPartyID)." order by a.UMFVAUS_VOTEORDER");
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$antowrten = 0;
	if ($result) {
		if($anzahl == 1){
			while ($row = mysql_fetch_array($result)) {
				$antworten++;
				echo "<input type=\"radio\" class=\"noborder\" name=\"Umfrage\" value=\"$row[UMFVAUS_VOTENR]\"> $row[UMFVAUS_VOTEBESCHREIBUNG]<br>\n";
			}
		} else {
			while ($row = mysql_fetch_array($result)) {
				$antworten++;
			echo "<input type=\"checkbox\" class=\"noborder\" name=\"Umfrage[]\" value=\"$row[UMFVAUS_VOTENR]\"> $row[UMFVAUS_VOTEBESCHREIBUNG]<br>\n";
			}
		}

	}

	echo "<input type=\"hidden\" name=\"UmfrageID\" value=\"$aktuelle_umfrage\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"vote\">";

	if ($antworten > 0) {
		
		// Kleiner Hack für die Startseite: Kleine Buttons
		if (isset($SHOW_SMALL_BTN) && $SHOW_SMALL_BTN == TRUE)
			$add_style = "style='padding: 5px 10px 5px 10px;'";
		else $add_style = "";
		
?>
			<p><input <?=$add_style;?> type="submit" value="Vote!"> <input <?=$add_style;?> class="button" type="button" value="<?=$str['ergebnis']?>" OnClick="document.location.href='?page=32&action=results&UmfrageID=<?php echo $aktuelle_umfrage; ?>'"></p></form>
<?php
	} else {
		// Keine Umfrage aktiv.
		echo "</form>";
	}
}
?>
