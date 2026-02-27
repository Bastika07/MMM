<?php
include_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "language.inc.php";

if (isset($_GET['ebene'])) {
	$ebene = $_GET['ebene'];
	if (!is_numeric($ebene) || $ebene < 1 || ($nPartyID == 5 && $ebene != 1)) {
		$ebene = 1;
	}
} else {
	$ebene = 1;
}

$reihe    = intval($_GET['reihe']    ?? 0);
$tisch    = intval($_GET['tisch']    ?? 0);
$iAction  = intval($_GET['iAction']  ?? 0);
$clanMate = intval($_GET['clanMate'] ?? 0);

if (!isset($dbh))
	$dbh = DB::connect();

include_once "sitzlib.php";

if ($nPartyID < 1) {
	echo "<p>Es wurden nicht gen&uuml;gend Daten geliefert, um den Sitzplan anzuzeigen.</p>";
	exit;
}

// Reservierung offen?
$sql = "select 
	  STRINGWERT 
	from 
	  CONFIG 
	where 
	  PARAMETER = 'SITZPLATZRES_OFFEN' AND
	  MANDANTID = $nPartyID";
$result = DB::query($sql);
$row = mysql_fetch_assoc($result);
// checken, ob get-variable on
if ($row['STRINGWERT'] == "N") {
	$sql = "select 
		  STRINGWERT 
		from 
		  CONFIG 
		where 
		  PARAMETER = 'SITZPLATZRES_OFFEN_AB' AND 
		  MANDANTID = $nPartyID";
	$result = DB::query($sql);
	$row = mysql_fetch_assoc($result);
	$bResOffen = 0;
	$sResOffenAb = $row['STRINGWERT'];
} else {
	$bResOffen = 1;
}
// ################# ok #############################


if ($iAction >= 1 && $nLoginID >= 1) {
	// Anmeldung offen?
	if ($bResOffen == 0) {
		// Sitzplatzreservierung noch nicht offen
		echo "<p>Die Sitzplatzreservierung wird am $sResOffenAb er&ouml;ffnet.</p>";
	} else {
		//Checken, ob Auswahl fuer Clanmates!
		$clanMate = intval($clanMate);
		if ($clanMate >= 1) {
			$sql = "select 
				  CLANID 
				from 
				  USER_CLAN 
				where 
				  MANDANTID = $nPartyID AND 
				  USERID = '$clanMate'";
			$result = DB::query($sql);
			$row = mysql_fetch_assoc($result);
			$derClan = $row['CLANID'];
			
			if ($derClan > 0) {
				//checken, ob eingeloggter User auch gleicher Clan
				$sql = "select 
					  CLANID 
					from 
					  USER_CLAN 
					where 
					  MANDANTID = $nPartyID AND 
					  USERID = $nLoginID";
				$result = DB::query($sql);
				$row = mysql_fetch_assoc($result);
				if ($row['CLANID'] == $derClan) {
					//ist Clanmate auch bezahlt?
//					$row = mysql_fetch_assoc(DB::query("select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = '$clanMate'"));
//					//echo mysql_errno().": ".mysql_error()."<BR>";
//					//Eingeloggter User bezahlt?
//					$row2 = mysql_fetch_assoc(DB::connect("select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID"));
//					if (($row['STATUS'] == $STATUS_BEZAHLT || $row['STATUS'] == $STATUS_BEZAHLT_LOGE) and ($row2['STATUS'] == $STATUS_BEZAHLT || $row2['STATUS'] == $STATUS_BEZAHLT_LOGE)) {

					// eingeloggter User braucht nicht bezahlt haben. zu reservierender User schon
					if (User::hatBezahlt($clanMate, $nPartyID)) {
						//##### TODO: CHecken, ob sauber genug
						// $nLoginID retten
						// Wir fuschen einfach munter weiter
						$realLoginID = $nLoginID;
						//einfach die Loginid auf den Mate umsetzen						
						$nLoginID = $clanMate;				
					} else {
						echo "<p>Dein Clanmate hat nicht bezahlt.</p>";
					}
				}
			} else {
				echo "<p>Nicht autorisiert.</p>";
			}
		} else
		  $realLoginID = $nLoginID;

		$row[1] = mysql_fetch_array(mysql_db_query($dbname, "select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID", $dbh), MYSQL_ASSOC);
		$row[2] = mysql_fetch_array(mysql_db_query($dbname, "select ISTLOGE from SITZDEF where MANDANTID = $nPartyID and REIHE = '$reihe' and LAENGE >= '$tisch'", $dbh), MYSQL_ASSOC);
		$row[3] = mysql_fetch_array(mysql_db_query($dbname, "select USERID, RESTYP from SITZ where MANDANTID = $nPartyID and REIHE = '$reihe' and PLATZ = '$tisch'", $dbh), MYSQL_ASSOC);
		$result = mysql_db_query($dbname, "select REIHE, PLATZ from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1", $dbh);
		$res[1] = mysql_num_rows($result);
		$row[4] = mysql_fetch_array($result);
		$res[2] = mysql_num_rows(mysql_db_query($dbname, "select REIHE, PLATZ from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 2", $dbh));
		
		// Alte Ebene raussuchen
		$resTemp = mysql_fetch_array(mysql_db_query($dbname, "select sd.EBENE from SITZDEF sd, SITZ s where sd.MANDANTID = '$nPartyID' and s.MANDANTID = '$nPartyID' and s.USERID = '$nLoginID' and s.REIHE=sd.REIHE", $dbh), MYSQL_ASSOC);
		if ($resTemp['EBENE'] > 0) {
			$nAlteEbene = $resTemp['EBENE'];
		}
		
		//echo mysql_errno().": ".mysql_error()."<BR>";
		if ($row[1][STATUS] == 3 && $row[2][ISTLOGE] == 1) // Platz = Loge und Reservierer = Loge
		{
			if ($iAction == 1) // Reservieren
			{
				if ($res[1] == 1) // Bereits ein Platz reserviert
				{
					if ($row[3][USERID] <= 1) // Platz leer
					{
					// Abteilung für Loge umsetzen - FUNKTIONIERT
						mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = '$realLoginID' where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
						$generate = TRUE;
						PELAS::logging ("seat change ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
					elseif ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 1) // Eigener Platz & reserviert
					{
					// Abteilung für Loge reservierung aufheben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
						$generate = TRUE;
						PELAS::logging ("seat delete ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
					else
					{
						if ($row[3][RESTYP] == 2) // Platz vorgemerkt
						{
						// Abteilung für vormerkung überschreiben - FUNKTIONIERT
							mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
							mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = $realLoginID where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
							$generate = TRUE;
							PELAS::logging ("seat reservation ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
						}
					}
				}
				else // Noch kein Platz reserviert
				{
					if ($row[3][RESTYP] == 2) // Platz vorgemerkt
					{
					// Abteilung für Loge vormerkung überschreiben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 1, $realLoginID)", $dbh);
						$generate = TRUE;
						PELAS::logging ("seat reservation ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
					elseif ($row[3][RESTYPE] != 1) // Platz frei
					{
					// Abteilung für Loge reservieren - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, '$reihe', '$tisch', $nLoginID, 1, '$realLoginID')", $dbh);
						$generate = TRUE;
						PELAS::logging ("seat reservation ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
				}
			}
			elseif ($iAction == 2) // Vormerken
			{
				if ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 2) // Eigene Vormerkung
				{
				// Abteilung für Loge vormerkung aufheben - FUNKTIONIERT
					mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
					$generate = TRUE;
				}
				elseif ($res[2] < 2) // Weniger als 2 Vormerkungen
				{
					if ($row[3][USERID] < 1) // Noch frei
					{
					// Abteilung für Loge vormerken - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, '$reihe', '$tisch', $nLoginID, 2, '$realLoginID')", $dbh);
						$generate = TRUE;
					}
				}
				else
				{
					echo "<p class=\"fehler\">Fehler: Du kannst maximal zwei Pl&auml;tze vormerken.</p>";
				}
			}
		}
		elseif ($row[1][STATUS] == 2 && $row[2][ISTLOGE] == 0) // Platz = Normal und Reservierer = Normal
		{
			if ($iAction == 1) // Reservieren
			{
				if ($res[1] == 1) // Bereits ein Platz reserviert
				{
					if ($row[3][USERID] <= 1) // Platz leer
					{
					// Abteilung für Loge umsetzen - FUNKTIONIERT
						mysql_db_query($dbname, "update SITZ set REIHE = '$reihe', PLATZ = '$tisch', WERGEAENDERT = '$realLoginID' where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
						$generate = TRUE;
						PELAS::logging ("seat change ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
					elseif ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 1) // Eigener Platz & reserviert
					{
					// Abteilung für Loge reservierung aufheben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
						$generate = TRUE;
						PELAS::logging ("seat delete ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
					else
					{
						if ($row[3][RESTYP] == 2) // Platz vorgemerkt
						{
						// Abteilung für vormerkung überschreiben - FUNKTIONIERT
							mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
							mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = '$realLoginID' where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
							$generate = TRUE;
							PELAS::logging ("seat reservation ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
						}
					}
				}
				else // Noch kein Platz reserviert
				{
					if ($row[3][RESTYP] == 2) // Platz vorgemerkt
					{
					// Abteilung für Loge vormerkung überschreiben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, '$reihe', '$tisch', $nLoginID, 1, '$realLoginID')", $dbh);
						$generate = TRUE;
						PELAS::logging ("seat reservation ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
					elseif (!$row[3][RESTYPE]) // Platz frei
					{
					// Abteilung für Loge reservieren - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, '$reihe', '$tisch', $nLoginID, 1, '$realLoginID')", $dbh);
						$generate = TRUE;
						PELAS::logging ("seat reservation ".$reihe."-".$tisch." for ".$nLoginID." by ".$realLoginID, "sitzplan", $nLoginID);
					}
				}
			}
			elseif ($iAction == 2) // Vormerken
			{
				if ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 2) // Eigene Vormerkung
				{
				// Abteilung für Loge vormerkung aufheben - FUNKTIONIERT
					mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = '". $reihe."' and PLATZ = '". $tisch."' LIMIT 1", $dbh);
					$generate = TRUE;
				}
				elseif ($res[2] < 2) // Weniger als 2 Vormerkungen
				{
					if ($row[3][USERID] < 1) // Noch frei
					{
					// Abteilung für Loge vormerken - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, '$reihe', '$tisch', $nLoginID, 2, '$realLoginID')", $dbh);
						$generate = TRUE;
					}
				}
				else
				{
					echo "<p class=\"fehler\">Fehler: Du kannst maximal zwei Pl&auml;tze vormerken.</p>";
				}
			}
		} else {
			echo "<p class=\"fehler\">Fehler: Gew&auml;hlte Kategorie nicht gebucht.</p>";
		}

		// Generierung der Ebenen
		if ($generate) {
			include_once("sitzplan_generate.php");
			// feststellen ob die vorherige Ebene auch generiert werden muss
			if (isset($nAlteEbene) && $ebene > 0 && $nAlteEbene != $ebene) {
				GeneriereSitzplan ($nPartyID, $nAlteEbene);
			}
			GeneriereSitzplan ($nPartyID, $ebene);
		}
	}
	header("Location:sitzplan.php?ebene=$ebene");
}

//Grundlagen fuer weiteres Arbeiten
if ($nLoginID < 1) {
	$login = -1;
	$aStatus = -1;
} else {
	$row = mysql_fetch_array(mysql_db_query($dbname, "select LOGIN from USER where USERID = $nLoginID", $dbh), MYSQL_ASSOC);
	$login = db2display($row['LOGIN']);
	$row = mysql_fetch_array(mysql_db_query($dbname, "select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID",$dbh), MYSQL_ASSOC);
	$aStatus = $row['STATUS'];
}
?>

<meta http-equiv="pragma" content="no-cache">

<div id="layer2" style="position:absolute; top:160px; left:0px; width:210px; height:1px; padding:10px; visibility:hide; visibility:hidden; ">wird vor dem sichbarmachen überschrieben</div>
<script language="JavaScript" src="<?=PELASHOST?>sitzplan.js" type="text/javascript"></script>
<script language="JavaScript">init('<?=PELASHOST?>userbild/')</script>



<script language="JavaScript">
<!--
function gores(Reihe,Platz)
{
<?php
 if ($bResOffen == 1) {
  if ($nLoginID >= 1) {
    if ($aStatus  == $STATUS_BEZAHLT || $aStatus  == $STATUS_BEZAHLT_LOGE) {
      ?>
  	if (document.forms.theaction.iAction[0].checked == true) {
  		tempAction = 1;
  	} else {
  		tempAction = 2;
  	}
  	clanMate = document.forms.theaction.clanmate.value;
  	
  	document.location.href="sitzplan.php?ebene=<?= intval($ebene) ?>&reihe="+encodeURIComponent(Reihe)+"&tisch="+encodeURIComponent(Platz)+"&iAction="+encodeURIComponent(tempAction)+"&clanMate="+encodeURIComponent(clanMate);
  <?php
    } else {
  	echo "alert(\"$str[bezahlen]\");\n";
    }
  } else {
    echo "alert(\"Nicht eingeloggt!\");\n";
  }
 } else {
   echo "alert(\"Die Sitzplatzreservierung wurde noch nicht eröffnet!\");\n";
 }
  ?>
}

//-->
</script>



<?php
if ($bResOffen == 0) {
	// Sitzplatzreservierung noch nicht offen
	echo "<p>Die Sitzplatzreservierung wird am $sResOffenAb er&ouml;ffnet.</p>";
} else {
	echo "<p align=\"justify\">$str[infotext]</p>";
	if ($nLoginID >= 1) {
		if ($aStatus  == $STATUS_BEZAHLT || $aStatus  == $STATUS_BEZAHLT_LOGE) {
			echo "<form name=\"theaction\">";
			//#####################################
			//Clanmates?
			$row2 = mysql_fetch_array(mysql_db_query($dbname, "select c.NAME, uc.CLANID from USER_CLAN uc, CLAN c where c.MANDANTID = $nPartyID and c.CLANID = uc.CLANID and uc.USERID = $nLoginID and uc.MANDANTID = $nPartyID and uc.AUFNAHMESTATUS=$AUFNAHMESTATUS_OK", $dbh), MYSQL_ASSOC);
			//echo mysql_errno().": ".mysql_error()."<BR>";
			$inClan   = $row2['CLANID'];
			$clanName = $row2['NAME'];
			if ($inClan > 0) {
				//User hat einen Clan, Memberliste anzeigen
				echo "<p>Auswahl f&uuml;r Clanreservierung &quot;".db2display($clanName)."&quot;: &nbsp; ";
				$result = mysql_db_query ($dbname, "select u.LOGIN, u.USERID from USER u, USER_CLAN uc, ASTATUS a where a.MANDANTID=$nPartyID and a.USERID=u.USERID and (a.STATUS=$STATUS_BEZAHLT or a.STATUS=$STATUS_BEZAHLT_LOGE) and uc.AUFNAHMESTATUS = $AUFNAHMESTATUS_OK and u.USERID = uc.USERID and uc.CLANID = $inClan and uc.MANDANTID = $nPartyID", $dbh);
				echo "<select name=\"clanmate\">";
				while ($row2 = mysql_fetch_array($result)) {
					
					// Sitzplatz raussuchen
					$row_seat_temp = mysql_fetch_array(mysql_db_query($dbname, "select REIHE, PLATZ from SITZ where USERID='$row2[USERID]' and MANDANTID='$nPartyID'", $dbh), MYSQL_ASSOC); 
					$nReihe = $row_seat_temp['REIHE'];
					$nPlatz = $row_seat_temp['PLATZ'];
										
					echo "<option value=\"$row2[USERID]\"";
					if ($nLoginID == $row2[USERID]) {
						echo " selected";
					}
					echo "> ".db2display($row2[LOGIN])."&nbsp;&nbsp;(".$nReihe."-".$nPlatz.")";
				}
				echo "</select></p>";
			} else {
				echo "<input type=\"hidden\" name=\"clanmate\" value=\"-1\">";
			}
			//######################################
			echo "Platz <input type=\"radio\" name=\"iAction\" value=\"1\" checked> reservieren <input type=\"radio\" name=\"iAction\" value=\"2\"> vormerken </form>";
		} elseif ($row['STATUS'] == 1) {
			echo "<p>$str[bezahlen], ".db2display($login).".</p>";
		} else {
			echo "<p>$str[anmelden], ".db2display($login).".</p>";
		}
	} else {
		echo "<p>$str[loginfuerplatz]</p>";
	}
}

$sitzplan_filepath = PELASDIR."sitzbild/sitzplan_html_".$nPartyID."_".$ebene.".txt";
if (is_readable($sitzplan_filepath)) {
	readfile($sitzplan_filepath);
} else {
	echo "Fehler: Sitzplan für die Ebene '".$ebene."' dieser Party wurde nicht gefunden.";
}

if (isset($_GET['locateUser'])) {
  $locateUser = intval($_GET['locateUser']);
  echo "<p align=\"center\"><img src=\"".PELASHOST."/sitzplan_bild.php?nPartyID=$nPartyID&ebene=$ebene&locateUser=$locateUser\" usemap=\"#mmm_map\" border=\"0\"></p>";
} else {
  echo "<p align=\"center\"><img src=\"".PELASHOST."/sitzplan_bild.php?nPartyID=$nPartyID&ebene=$ebene&time=".time()."\" usemap=\"#mmm_map\" border=\"0\"></p>";
}

//echo "<p align=\"center\"><img src=\"".PELASHOST."sitzbild/sitzplan_bild_".$nPartyID."_".$ebene.".png?time=".time()."\" usemap=\"#mmm_map\" border=\"0\"></p>";

?>

<p>
<table><tr>
<td><b><?=$str['legende']?>:</b></td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_frei.gif"> <?=$str['frei']?> &nbsp; </td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_vorgemerkt.gif"> <?=$str['vorgemerkt']?> &nbsp; </td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_besetzt.gif"> <?=$str['besetzt']?> &nbsp; </td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_loge.gif"> <?=$str['loge']?> &nbsp; </td>
</tr></table>
</p>

