<?php
/*
** clandetails.php
**
** 16/03/2004 ore - Sitzplatz erneut eingebaut
** 16/03/2004 mgi - Script geschrottet
** 16/03/2004 ore - Sitzplatz zu Ausgabe hinzugedichtet
** 11/02/2011 mgi - Sicherheitsabfrage für Input angelegt
*/

// Sicherheitsabfrage der Variablen, die übergeben und in SQLs verarbeitet werden
$nClanID = $_GET['nClanID'];
if (!is_numeric($nClanID)) {
	echo "<p class='fehler'>Es wurden falsche Daten geliefert. Weitere Verarbeitung gestoppt.</p>";
	exit;
}

include_once "dblib.php";
include_once "format.php";
include "language.inc.php";

if (!isset($dbh))
	$dbh = DB::connect();

echo "<table class=\"rahmen_allg\" width=\"450\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\"><tr>\n";
  $clanname = DB::query("select NAME from CLAN where CLANID = '".intval($nClanID)."'")->fetch_array();
  echo "<td colspan=\"2\" class=\"pelas_benutzer_titel\"><b>".db2display($clanname['NAME'])."</b></td>\n";
echo "</tr>\n";

$url = DB::query("select IRC_CHANNEL, URL from CLAN where CLANID = '$nClanID'")->fetch_array();

echo "<tr><td class=\"pelas_benutzer_prefix\" width=\"80\">".db2display($str['homepage'])."</td><td class=\"pelas_benutzer_inhalt\" width=\"380\">";

if (strpos ($url['URL'], "://") > 0 ) {
	echo "<A HREF=\"".db2display($url['URL'])."\" target=\"_blank\" class=\"inlink\">".db2display($url['URL'])."</A>";
} else {
	echo db2display($url['URL']);
}
echo "</td></tr>";

echo "<tr><td class=\"pelas_benutzer_prefix\" width=\"80\">IRC-Channel</td><td class=\"pelas_benutzer_inhalt\" width=\"380\">".db2display($url['IRC_CHANNEL'])."&nbsp;</td></tr>";

echo "<tr><td class=\"pelas_benutzer_prefix\">Logo</td><td class=\"pelas_benutzer_inhalt\">";
displayClanPic($nClanID,$nPartyID);
echo "</td></tr>";

echo "<tr><td class=\"pelas_benutzer_prefix\" height=100% valign=top>$str[members]</td><td class=\"pelas_benutzer_inhalt\" valign=top>";
$sql= "select 
         uc.USERID,
         u.LOGIN
       from 
         USER_CLAN as uc,
         USER as u
       where
         uc.USERID = u.USERID and
         uc.MANDANTID = '".intval($nPartyID)."' and 
         uc.CLANID = '".intval($nClanID)."' and 
         uc.AUFNAHMESTATUS = $AUFNAHMESTATUS_OK";
$result = DB::query($sql);

echo '<table border="0" cellpadding="0" cellspacing="0">'."\n";
while ($row = $result->fetch_array()) {
    echo '<tr><td width="250">'."\n";
    echo "<nobr><a href=\"?page=4&nUserID=$row[USERID]\"><img src=\"gfx/userinfo.gif\" border=\"0\"></a> ".db2display($row['LOGIN'])."</nobr> ";
    $row_status= DB::query("select STATUS from ASTATUS where USERID = $row[USERID] and MANDANTID = ".intval($nPartyID))->fetch_array();
    echo '</td><td width="20">'."\n";

	if (ACCOUNTING == "OLD") {
		// Sitzanzeige altes Accounting
		if ($row_status['STATUS'] == $STATUS_BEZAHLT_LOGE) { 
			echo "<img src=\"/gfx/te_lg.gif\" alt=\"Bezahlt f&uuml;r Loge\">&nbsp"; 
		} else if ( $row_status['STATUS'] == $STATUS_BEZAHLT) {
			echo "<img src=\"/gfx/te_bz.gif\" alt=\"Bezahlt\">&nbsp"; 
		} else if ( $row_status['STATUS'] == $STATUS_ANGEMELDET) {
			echo "<img src=\"/gfx/te_an.gif\" alt=\"Angemeldet\">&nbsp"; 
		}
		echo '</td><td>'."\n";
		$sql= "select s.REIHE, s.PLATZ from SITZ as s where s.USERID = $row[USERID] and s.MANDANTID = '".intval($nPartyID)."' and (s.RESTYP = 1 or s.RESTYP = 3)";
		$row_platz= DB::query($sql)->fetch_array();
		if (isset($row_platz['REIHE']))
		    echo "Platz ".$row_platz['REIHE'].'-'.$row_platz['PLATZ'];
	} else {
		// Sitzanzeige neues Accounting (default)
		$sql = "select
			  t.ticketId,
			  t.sitzReihe,
			  t.sitzPlatz,
			  t.userId
			from
			  acc_tickets t,
			  party p
			where
			  t.partyId   = p.partyId and
			  p.aktiv     = 'J' and
			  t.userId    = '$row[USERID]' and
			  t.statusId  = '".ACC_STATUS_BEZAHLT."' and
			  p.mandantId = '".intval($nPartyID)."'
		";
		$res = DB::query($sql);
		$counter = 0;
		while ($rowTemp = $res->fetch_array()) {
			if ($counter >= 1) {
				echo "&nbsp;";
			}
			echo PELAS::formatTicketNr($rowTemp['ticketId'])."/";
			$sql = "select 
				  EBENE
				from 
				  SITZDEF
				where 
				  MANDANTID ='".intval($nPartyID)."' and
				  REIHE     = '".$rowTemp['sitzReihe']."'";
			$resTemp2 = DB::query($sql);
			$rowTemp2 = $resTemp2->fetch_array();
			$ebene   = $rowTemp2['EBENE'];
			echo "<a href=\"/?page=9&ebene=$ebene&locateUser=".$rowTemp['userId']."\">";
			echo $rowTemp['sitzReihe']."-".$rowTemp['sitzPlatz'];
			echo "</a>";
			$counter++;
		}
		if ($counter == 0) {
			echo "(".$str['keine'].")";
		}
		
	}
        echo '</td></tr>';
}
echo '</table>'."\n";
echo "</td></tr>";
?>
</table>
