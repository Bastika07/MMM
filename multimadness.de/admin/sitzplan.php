<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "USERADMIN";
include "checkrights.php";
include "../../includes/admin/vorspann.php";

$dbh = DB::connect();

// Read all user-supplied parameters explicitly with sanitization
$nPartyID = intval($_GET['nPartyID'] ?? 0);
$userID   = intval($_GET['userID']   ?? 0);
$ebene    = intval($_GET['ebene']    ?? 1);
$reihe    = intval($_GET['reihe']    ?? 0);
$tisch    = intval($_GET['tisch']    ?? 0);
$iAction  = intval($_GET['iAction']  ?? 0);
$clanMate = intval($_GET['clanMate'] ?? 0);
if ($ebene < 1) {
	$ebene = 1;
}

// TODO: Wieso ist sitzlib.php nicht im Unterordner libs ?
include_once "sitzlib.php";

// $nLoginID retten
// Wir fuschen einfach munter weiter
$realLoginID = $loginID;

// LoginID mit zu verwaltenden User überschreiben
$nLoginID = $userID;

if ($nPartyID < 1) {
	echo "<p>Es wurden nicht gen&uuml;gend Daten geliefert, um den Sitzplan anzuzeigen.</p>";
	exit;
}

// Reservierung offen?
$row = @mysql_fetch_array(mysql_db_query($dbname, "select STRINGWERT from CONFIG where PARAMETER = 'SITZPLATZRES_OFFEN' and MANDANTID = $nPartyID", $dbh), MYSQL_ASSOC);
// checken, ob get-variable on
if ($row['STRINGWERT'] == "N") {
	$row = @mysql_fetch_array(mysql_db_query($dbname, "select STRINGWERT from CONFIG where PARAMETER = 'SITZPLATZRES_OFFEN_AB' and MANDANTID = $nPartyID", $dbh), MYSQL_ASSOC);
	$bResOffen = 0;
	$sResOffenAb = $row["STRINGWERT"];
} else {
	$bResOffen = 1;
}
// ################# ok #############################

// TODO: Wozu findet hier ein erneuter include der sitzlib.php statt?
include "sitzlib.php";

if ($iAction >= 1 && $nLoginID >= 1) {
	// Anmeldung offen?

		//Checken, ob Auswahl fuer Clanmates!
		if ($clanMate >= 1) {
			$row = mysql_fetch_array(mysql_db_query($dbname, "select CLANID from USER_CLAN where MANDANTID = $nPartyID and USERID = $clanMate", $dbh), MYSQL_ASSOC);
			//echo mysql_errno().": ".mysql_error()."<BR>";
			$derClan = $row[CLANID];
			if ($derClan > 0) {
				//checken, ob eingeloggter User auch gleicher Clan
				$row = mysql_fetch_array(mysql_db_query($dbname, "select CLANID from USER_CLAN where MANDANTID = $nPartyID and USERID = $nLoginID", $dbh), MYSQL_ASSOC);
				if ($row[CLANID] == $derClan) {
					//ist Clanmate auch bezahlt?
					$row = mysql_fetch_array(mysql_db_query($dbname, "select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $clanMate", $dbh), MYSQL_ASSOC);
					//echo mysql_errno().": ".mysql_error()."<BR>";
					//Eingeloggter User bezahlt?
					$row2 = mysql_fetch_array(mysql_db_query($dbname, "select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID", $dbh), MYSQL_ASSOC);
					if (($row[STATUS] == $STATUS_BEZAHLT || $row[STATUS] == $STATUS_BEZAHLT_LOGE) and ($row2[STATUS] == $STATUS_BEZAHLT || $row2[STATUS] == $STATUS_BEZAHLT_LOGE)) {
						//##### TODO: CHecken, ob sauber genug
						//einfach die Loginid auf den Mate umsetzen
						$nLoginID = $clanMate;				
					} else {
						echo "<p>Dein Clanmate hat nicht bezahlt.</p>";
					}
				}
			} else {
				echo "<p>Nicht autorisiert.</p>";
			}
		}

		$row[1] = mysql_fetch_array(mysql_db_query($dbname, "select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID", $dbh), MYSQL_ASSOC);
		$row[2] = mysql_fetch_array(mysql_db_query($dbname, "select ISTLOGE from SITZDEF where MANDANTID = $nPartyID and REIHE = $reihe and LAENGE >= $tisch", $dbh), MYSQL_ASSOC);
		$row[3] = mysql_fetch_array(mysql_db_query($dbname, "select USERID, RESTYP from SITZ where MANDANTID = $nPartyID and REIHE = $reihe and PLATZ = $tisch", $dbh), MYSQL_ASSOC);
		$result = mysql_db_query($dbname, "select REIHE, PLATZ from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1", $dbh);
		$res[1] = mysql_num_rows($result);
		$row[4] = mysql_fetch_array($result);
		$res[2] = mysql_num_rows(mysql_db_query($dbname, "select REIHE, PLATZ from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 2", $dbh));
		//echo mysql_errno().": ".mysql_error()."<BR>";
		
		// Alte Ebene raussuchen
		$resTemp = mysql_fetch_array(mysql_db_query($dbname, "select sd.EBENE from SITZDEF sd, SITZ s where sd.MANDANTID = '$nPartyID' and s.MANDANTID = '$nPartyID' and s.USERID = '$nLoginID' and s.REIHE=sd.REIHE", $dbh), MYSQL_ASSOC);
		if ($resTemp['EBENE'] > 0) {
			$nAlteEbene = $resTemp['EBENE'];
		} else {
			$nAlteEbene = -1;
		}
		
		if ($row[1][STATUS] == 3 && $row[2][ISTLOGE] == 1) // Platz = Loge und Reservierer = Loge
		{
			if ($iAction == 1) // Reservieren
			{
				if ($res[1] == 1) // Bereits ein Platz reserviert
				{
					if ($row[3][USERID] <= 1) // Platz leer
					{
					// Abteilung für Loge umsetzen - FUNKTIONIERT
						mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = $realLoginID where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
						logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
					elseif ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 1) // Eigener Platz & reserviert
					{
					// Abteilung für Loge reservierung aufheben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
						logging($realLoginID, $nLoginID, 1, 'unset', $reihe, $tisch);
						$generate = TRUE;
					}
					else
					{
						if ($row[3][RESTYP] == 2) // Platz vorgemerkt
						{
						// Abteilung für vormerkung überschreiben - FUNKTIONIERT
							mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
							mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = $realLoginID where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
							logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
							$generate = TRUE;
						}
					}
				}
				else // Noch kein Platz reserviert
				{
					if ($row[3][RESTYP] == 2) // Platz vorgemerkt
					{
					// Abteilung für Loge vormerkung überschreiben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 1, $realLoginID)", $dbh);
						logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
					elseif ($row[3][RESTYPE] != 1) // Platz frei
					{
					// Abteilung für Loge reservieren - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 1, $realLoginID)", $dbh);
						logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
				}
			}
			elseif ($iAction == 2) // Vormerken
			{
				if ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 2) // Eigene Vormerkung
				{
				// Abteilung für Loge vormerkung aufheben - FUNKTIONIERT
					mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
					logging($realLoginID, $nLoginID, 2, 'set', $reihe, $tisch);
					$generate = TRUE;
				}
				elseif ($res[2] < 2) // Weniger als 2 Vormerkungen
				{
					if ($row[3][USERID] < 1) // Noch frei
					{
					// Abteilung für Loge vormerken - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 2, $realLoginID)", $dbh);
						logging($realLoginID, $nLoginID, 2, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
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
						mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = $realLoginID where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
						//echo mysql_errno().": ".mysql_error()."<BR>";
						logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
					elseif ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 1) // Eigener Platz & reserviert
					{
					// Abteilung für Loge reservierung aufheben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
						logging($realLoginID, $nLoginID, 1, 'unset', $reihe, $tisch);
						$generate = TRUE;
					}
					else
					{
						if ($row[3][RESTYP] == 2) // Platz vorgemerkt
						{
						// Abteilung für vormerkung überschreiben - FUNKTIONIERT
							mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
							logging(-1, $nLoginID, 2, 'unset', $reihe, $tisch);
							mysql_db_query($dbname, "update SITZ set REIHE = $reihe, PLATZ = $tisch, WERGEAENDERT = $realLoginID where MANDANTID = $nPartyID and USERID = $nLoginID and RESTYP = 1 and REIHE = ". $row[4][REIHE]." and PLATZ = ". $row[4][PLATZ], $dbh);
							logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
							$generate = TRUE;
						}
					}
				}
				else // Noch kein Platz reserviert
				{
					if ($row[3][RESTYP] == 2) // Platz vorgemerkt
					{
					// Abteilung für Loge vormerkung überschreiben - FUNKTIONIERT
						mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 1, $realLoginID)", $dbh);
						logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
					elseif (!$row[3][RESTYPE]) // Platz frei
					{
					// Abteilung für Loge reservieren - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 1, $realLoginID)", $dbh);
						logging($realLoginID, $nLoginID, 1, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
				}
			}
			elseif ($iAction == 2) // Vormerken
			{
				if ($row[3][USERID] == $nLoginID && $row[3][RESTYP] == 2) // Eigene Vormerkung
				{
				// Abteilung für Loge vormerkung aufheben - FUNKTIONIERT
					mysql_db_query($dbname, "delete from SITZ where MANDANTID = $nPartyID and USERID = $nLoginID and REIHE = ". $reihe." and PLATZ = ". $tisch." LIMIT 1", $dbh);
					logging($realLoginID, $nLoginID, 2, 'unset', $reihe, $tisch);
					$generate = TRUE;
				}
				elseif ($res[2] < 2) // Weniger als 2 Vormerkungen
				{
					if ($row[3][USERID] < 1) // Noch frei
					{
					// Abteilung für Loge vormerken - FUNKTIONIERT
						mysql_db_query($dbname, "insert into SITZ (MANDANTID, REIHE, PLATZ, USERID, RESTYP, WERGEAENDERT) values ($nPartyID, $reihe, $tisch, $nLoginID, 2, $realLoginID)", $dbh);
						logging($realLoginID, $nLoginID, 2, 'set', $reihe, $tisch);
						$generate = TRUE;
					}
				}
			}
		} else {
			echo "<p class=\"fehler\">Fehler: Gew&auml;hlte Kategorie nicht gebucht.</p>";
		}

		// Generierung der Ebenen
		if ($generate) {
			include_once("sitzplan_generate.php");
			// feststellen ob die vorherige Ebene auch generiert werden muss
			if ($nAlteEbene > 0 && $ebene > 0 && $nAlteEbene != $ebene) {
				GeneriereSitzplan ($nPartyID, $nAlteEbene);
			}
			GeneriereSitzplan ($nPartyID, $ebene);
		}
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
<script language="JavaScript" src="<?=PELASHOST?>sitzplan.js" type="text/javascript"></script>
<script language="JavaScript">init('<?=PELASHOST?>userbild/')</script>

<script language="JavaScript">
<!--
function gores(Reihe,Platz)
{
<?php
  if ($nLoginID >= 1) {
  
  if ($aStatus  == $STATUS_BEZAHLT || $aStatus  == $STATUS_BEZAHLT_LOGE) {
  
  ?>
  	if (document.forms.theaction.iAction[0].checked == true) {
  		tempAction = 1;
  	} else {
  		tempAction = 2;
  	}
  	clanMate = document.forms.theaction.clanmate.value;
  	
  	document.location.href="sitzplan.php?userID=<?= intval($nLoginID) ?>&nPartyID=<?= intval($nPartyID) ?>&ebene=<?= intval($ebene) ?>&reihe="+encodeURIComponent(Reihe)+"&tisch="+encodeURIComponent(Platz)+"&iAction="+encodeURIComponent(tempAction)+"&clanMate="+encodeURIComponent(clanMate);
  
  <?php
    } else {
  	echo "alert(\"$str[bezahlen]\");\n";
    }
  
  } else {
    echo "alert(\"Nicht eingeloggt!\");\n";
  }

  ?>
}

//-->
</script>




<?php

	echo "<p>$str[infotext]</p>";
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
				while ($row2=mysql_fetch_array($result)) {
					
					// Sitzplatz raussuchen
					$row_seat_temp = mysql_fetch_array(mysql_db_query($dbname, "select REIHE, PLATZ from SITZ where USERID='$row2[USERID]' and MANDANTID='$nPartyID'", $dbh), MYSQL_ASSOC); 
					$nReihe = $row_seat_temp['REIHE'];
					$nPlatz = $row_seat_temp['PLATZ'];
					
					echo "<option value=\"$row2[USERID]\"";
					if ($nLoginID == $row2['USERID']) {
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
		} elseif ($row[STATUS] == 1) {
			echo "<p>$str[bezahlen], ".db2display($login).".</p>";
		} else {
			echo "<p>$str[anmelden], ".db2display($login).".</p>";
		}
	} else {
		echo "<p>$str[loginfuerplatz]</p>";
	}


//include PELASHOST."sitzplan_html.php?nPartyID=$nPartyID&ebene=$ebene";
readfile (PELASDIR."sitzbild/sitzplan_html_".$nPartyID."_".$ebene.".txt");

echo "<p align=\"center\"><img src=\"".PELASHOST."sitzbild/sitzplan_bild_".$nPartyID."_".$ebene.".png?time=".time()."\" usemap=\"#mmm_map\" border=\"0\"></p>";

?>

<p><b><?=$str[legende]?>:</b><br>
<table><tr>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_frei.gif"> <?=$str[frei]?> &nbsp; </td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_vorgemerkt.gif"> <?=$str[vorgemerkt]?> &nbsp; </td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_besetzt.gif"> <?=$str[besetzt]?> &nbsp; </td>
<td><img src="<?=PELASHOST?>/gfx/sitz_leg_loge.gif"> <?=$str[loge]?> &nbsp; </td>
</tr></table>
</p>

<?php

include "../../includes/admin/nachspann.php";

function logging($subject, $object, $restyp, $action, $row, $seat) {
	$msg = $action;
	switch ($restyp) {
		case 1: $msg .= ' reservation'; break; 
		case 2: $msg .= ' earmark'; break; 
	}
  $msg .= " $row-$seat for $object by $subject";
  PELAS::logging($msg, 'sitzplan', $object);
}
?>
