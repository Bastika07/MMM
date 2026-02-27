<?php

/*
    Dieses ist die Version für neues Accounting
*/
?>

<script language="JavaScript">
<!--
function openPELAS(nUserID)
{
	pelas = window.open("/pelas/pelas.php?iUserID="+nUserID,"PELAS","screenX="+(screen.width-280)+",screenY="+(screen.height-379)+",width=270,height=320,locationbar=false,resize=false");
	pelas.focus();
}
-->
</script>

<?php
include_once "dblib.php";
include_once "format.php";
include_once "session.php";
include "language.inc.php";

if (!isset($dbh))
	$dbh = DB::connect();

//Userdaten
if (!isset($_GET['nUserID']) || !is_numeric($_GET['nUserID']) || $_GET['nUserID'] < 0) {
  PELAS::fehler('Ungültige Benutzer-ID!');
} else {
  $result = mysql_db_query ($dbname, "select * from USER where USERID = $nUserID",$dbh);
  if (mysql_num_rows($result) != 1) {
    PELAS::fehler('Kein Benutzer mit dieser ID!');
  } else {

$statusbeschreibung = mysql_db_query ($dbname, "select b.BESCHREIBUNG from STATUS b, ASTATUS a where a.USERID=$nUserID and a.MANDANTID=$nPartyID and b.STATUSID=a.STATUS",$dbh);
//echo mysql_errno().": ".mysql_error()."<BR>";
$sitz = mysql_db_query ($dbname, "select * from SITZ where USERID='$nUserID' and MANDANTID='$nPartyID' AND RESTYP='$SITZ_RESERVIERT'",$dbh);
$besuchteParties = mysql_db_query ($dbname, "
	select m.REFERER, p.NAME, p.BEGINN 
	from MANDANT m, ASTATUSHISTORIE a, PARTYHISTORIE p 
	where m.MANDANTID=p.MANDANTID 
		and a.USERID = '$nUserID' 
		and a.MANDANTID = p.MANDANTID 
		and a.LFDNR=p.LFDNR 
		and (a.STATUS='$STATUS_BEZAHLT' 
			or a.STATUS='$STATUS_BEZAHLT_LOGE' 
			or a.STATUS='$STATUS_COMFORT_4PERS'
			or a.STATUS='$STATUS_COMFORT_6PERS'
			or a.STATUS='$STATUS_COMFORT_8PERS'
			or a.STATUS='$STATUS_PREMIUM_4PERS'
			or a.STATUS='$STATUS_PREMIUM_6PERS'
			or a.STATUS='$STATUS_ZUGEORDNET'
			or a.STATUS='$STATUS_VIP_2PERS'
			or a.STATUS='$STATUS_VIP_4PERS'
		) 
	order by p.BEGINN desc", $dbh);
//echo mysql_errno().": ".mysql_error()."<BR>";

// Clan raussuchen
$result2 = mysql_db_query ($dbname, "select c.CLANID, c.NAME from CLAN c, USER_CLAN uc where c.CLANID = uc.CLANID and uc.USERID='$nUserID' and uc.MANDANTID='$nPartyID' and uc.AUFNAHMESTATUS='$AUFNAHMESTATUS_OK'",$dbh);
$row2    = mysql_fetch_array($result2);
$sClan   = db2display($row2['NAME']);
$nClanID = $row2['CLANID'];

$row = mysql_fetch_array($result);
$rowStat = mysql_fetch_array($statusbeschreibung);

$row_platz = mysql_fetch_array($sitz);

echo "<table class=\"rahmen_allg\" width=\"450\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\"><tr><td class=\"pelas_benutzer_titel\" height=\"39\" colspan=\"3\" valign=\"top\">";
  echo "<table width=\"100%\" height=\"100%\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\"><tr>";
  echo "<td class=\"pelas_benutzer_titel\">";
  echo "<b>".db2display($row['LOGIN'])."</b></td>";
  echo "</tr></table>";
echo "</td></tr>";

echo "<tr><td class=\"pelas_benutzer_prefix\" width=\"80\">$str[homepage]</td><td class=\"pelas_benutzer_inhalt\" width=\"380\">";
if (strpos ($row['HOMEPAGE'], "://") > 0 ) {
	echo "<A HREF=\"$row[HOMEPAGE]\" target=\"_blank\" class=\"inlink\">".db2display($row['HOMEPAGE'])."</A>";
} else {
	echo db2display($row['HOMEPAGE']);
}
echo "</td>";

echo "<td class=\"pelas_benutzer_inhalt\" rowspan=\"11\" valign=\"top\">";

displayUserPic($nUserID);

echo "</td></tr>";

echo "<tr><td class=\"pelas_benutzer_prefix\">$str[ort]</td><td class=\"pelas_benutzer_inhalt\">";
echo PELAS::displayFlag($row['LAND']);
echo " ".db2display($row['PLZ'])." ".db2display($row['ORT'])."</td></tr>";

		
// Clan anzeigen
echo "<tr><td class=\"pelas_benutzer_prefix\">Clan</td><td class=\"pelas_benutzer_inhalt\">";
if ($nClanID > 0) {
	echo "<a href=\"clandetails.php?nClanID=$nClanID\" class=\"inlink\">$sClan</a>";
} else {
	echo "&nbsp;";
}
echo "</TD></tr>";

$tempUSpiele = $row['KOMMENTAR_PUBLIC'];
if (strlen($tempUSpiele) > 61 ) {
			$Anzeige_Spiele = db2display(substr( $tempUSpiele, 0, 61)."...");
		} else {
			$Anzeige_Spiele = db2display($tempUSpiele);
}

echo "<tr><td class=\"pelas_benutzer_prefix\">Spiele</td><td class=\"pelas_benutzer_inhalt\">$Anzeige_Spiele</td></tr>";

// Link zum Kontaktformular
echo "<tr><td class=\"pelas_benutzer_prefix\">Kontakt</td><td class=\"pelas_benutzer_inhalt\">";
if (LOCATION == "intranet") {
	echo "<a href=\"JavaScript:openPELAS(".$row['USERID'].")\">PELAS-Mail</a>";
} else {
	echo "<a href=\"kontaktformular.php?nUserID=".$nUserID."\">Kontaktformular</a>";
}
echo "</td></tr>";
//#########################

echo "<tr><td class=\"pelas_benutzer_prefix\">NGL-ID</td><td class=\"pelas_benutzer_inhalt\">".db2display($row['NGL_SINGLE'])."</td></tr>";
echo "<tr><td class=\"pelas_benutzer_prefix\">WWCL-ID</td><td class=\"pelas_benutzer_inhalt\">".db2display($row['WWCL_SINGLE'])."</td></tr>";


echo "<tr><td class=\"pelas_benutzer_titel\" colspan='2'><b>$str[ticketzuordnung]</b></td></tr>";

// Tickets heraussuchen
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
	  t.userId    = '$nUserID' and
	  t.statusId  = '".ACC_STATUS_BEZAHLT."' and
	  p.mandantId = '$nPartyID'
";
$res = mysql_db_query ($dbname, $sql ,$dbh);

echo "<tr><td class=\"pelas_benutzer_inhalt\" colspan=\"2\">";
$counter = 0;
while ($rowTemp = mysql_fetch_array($res)) {
	if ($counter >= 1) {
		echo "<br>";
	}
	
	echo "Nr. ".PELAS::formatTicketNr($rowTemp['ticketId'])." &nbsp;Platz ";
	
	$sql = "select 
		  EBENE
		from 
		  SITZDEF
		where 
		  MANDANTID ='$nPartyID' and
		  REIHE     = '".$rowTemp['sitzReihe']."'";
	$resTemp2 = DB::query($sql);
	$rowTemp2 = mysql_fetch_array($resTemp2);
	$ebene   = $rowTemp2['EBENE'];
	echo " <a href=\"/sitzplan.php?ebene=$ebene&locateUser=".$rowTemp['userId']."\">";
	echo $rowTemp['sitzReihe']."-".$rowTemp['sitzPlatz'];
	echo "</a>";
	
	$counter++;
}
if ($counter == 0) {
	echo "(".$str[keine].")";
}
echo "</td></tr>";


// TODO: Übersetzen!
echo "<tr><td class=\"pelas_benutzer_titel\" colspan=\"2\"><b>Besuchte Parties</b></td></tr>";

echo "<tr><td class=\"pelas_benutzer_inhalt\" colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"3\" border=\"0\">";

$nCounter = 0;
while ($row=mysql_fetch_array($besuchteParties)) {
	echo " <tr><td> <a href=\"$row[REFERER]\" target=\"_blank\">".db2display($row['NAME'])."</a> <small>(".dateDisplay2Short($row['BEGINN']).")</small></td></tr>";
	$nCounter++;
}
if ($nCounter == 0) {
	echo "<tr><td>keine</td></tr>";
}
echo "</table></td></tr>";

echo "</table>";
}
}
?>
