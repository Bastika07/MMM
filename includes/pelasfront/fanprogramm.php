<?php
include_once "dblib.php";
include_once "format.php";
include_once "language.inc.php";

// Anzahl der Sperrminuten für Aufrufe von der gleichen IP
$sperrMinuten = 300; // 5h Sperre
$remoteIp     = $_SERVER['REMOTE_ADDR'];

if (!isset($dbh))
	$dbh = DB::connect();

// Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

if (isset($linkus)) {
	// Zählen, danach weiterleiten auf News
	$linkus = intval($linkus);
	
	// Alte Sperrdatensätze löschen
	$sql = "delete from CLAN_FANCOUNTER_LOCK where DATE_ADD(time, INTERVAL $sperrMinuten MINUTE) <= NOW()";
	mysql_query($sql);
	
	// Gucken ob Sperrdatensatz vorhanden.
	$sql = "select remoteIp from CLAN_FANCOUNTER_LOCK where partyId='$aktuellePartyID' and clanId='$linkus' and remoteIp='$remoteIp'";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		// Vorhanden, nicht zählen
		//echo "IP noch gesperrt";
	} else {
		// Datensatz schreiben und zählen, vorher gucken ob für diese ID das Programm aktiv ist
		$sql = "select FANPROGRAMM from CLAN where CLANID='$linkus' and MANDANTID='$nPartyID'";
		$result = DB::getOne($sql);
		if ($result[0] == "J") {
			// Nur Zähldatensatz einfügen wenn Feature in der Clanverwaltun aktiviert
			$sql = "insert into CLAN_FANCOUNTER_LOCK (partyId, clanId, remoteIp) VALUE ('$aktuellePartyID', '$linkus', '$remoteIp')";
			mysql_query($sql);
			// Hochzählen, vorher gucken ob DS schon vorhanden
			$sql = "INSERT INTO CLAN_FANCOUNTER (partyId, clanId, stand, wannAngelegt) VALUES ('$aktuellePartyID', '$linkus', '1', NOW()) ON DUPLICATE KEY UPDATE stand=stand+1";
			$result = mysql_query($sql);
		}
	}
	header ("Location: news.php");
} else {

?>

<p align="justify">Die hier gelisteten Clans und Websites nehmen am Fanprogramm &quot;Link us&quot; teil. 
Mit dieser Aktion möchten wir uns bei allen Gästen bedanken, welche die NorthCon tatkräftig mit Werbung auf der eigenen Homepage unterstützen.
Informationen zur Teilnahme am Fanprogramm findet ihr in eurer <img src="gfx/headline_pfeil.png" border="0"> <a href="/clanverwaltung.php">Clanverwaltung</a>.</p>


<table class="rahmen_allg" cellpadding='2' cellspacing='1' border='0' width="530">

<tr><td class="header">Platz</td><td class="header">Website/ Clan (Klicks)</td><td class="header">Clanlogo</td></tr>

<?php

	$sql = "select 
				c.CLANID,
				c.NAME,
				cf.stand
			from
				CLAN c,
				CLAN_FANCOUNTER cf
			where 
				c.MANDANTID = '$nPartyID' and
				cf.partyId  = '$aktuellePartyID' and
				cf.clanId   = c.CLANID
			order by cf.stand desc
	";
	$result = mysql_query($sql);
	
	//echo mysql_errno().": ".mysql_error()."<BR>";
	
	$zaehl = 1;
	
	while ($row = mysql_fetch_array($result)) {
		if ($zaehl == 1) {
			$size = 8;
		} elseif ($zaehl > 1 and $zaehl <=10) {
			$size = 6;
		} else {
			$size = 4;
		}
		
		echo "<TR>";
		
		echo "<TD width=\"40\" class='TNListeTDB' align=\"center\"><font size=\"$size\">".$zaehl."</font></td>";
		
		echo "<TD class='TNListeTDA'>";

		echo "<a href=\"clandetails.php?nClanID=$row[CLANID]\">".db2display($row['NAME'])."</a>";
		echo " (".$row['stand'].")";
		
		echo "</td>";
		
		echo "<TD class='TNListeTDB'>";
		
		$inClan =$row['CLANID'];
				
		displayClanPic($inClan,$nPartyID);
		
		echo "</td></tr>\n";
		
		$zaehl++;
	}

echo "</table>";
}
?>
