<?php
require_once "dblib.php";
include_once "format.php";
include "language.inc.php";

$AnzahlProSeite = intval($AnzahlProSeite);
$AktSeite = intval($AktSeite);

if ($AnzahlProSeite < 20 || $AnzahlProSeite > 100) {
	$AnzahlProSeite = 30;
}

//suchen angewaehlt?
if ($limitListe != "") {
	$limitString = " and LOGIN like '%$limitListe%'";
} else {
	$limitString = "";
}

function ShowBlaettern()
{
	global $iSortierung, $sAddQuery, $limitListe, $str, $AnzahlDS_blaettern, $AnzahlProSeite, $AktSeite;
	echo "<tr><td colspan=\"5\" align=\"right\" class=\"TNListe\">";
	$counter=0;
	for ($i=0;$i<= (ceil($AnzahlDS_blaettern/$AnzahlProSeite)-1);$i++) {
		$counter++;
		if ($i!= 0) { echo "&nbsp;|&nbsp;"; }
		echo "<a href=\"teilnehmer.php?AktSeite=".$i."&limitListe=$limitListe$sAddQuery&iSortierung=$iSortierung\" class=\"TNLink\">";
		if ($i==$AktSeite) { echo "<b>"; }
		echo ($i*$AnzahlProSeite+1)."-".($i*$AnzahlProSeite+$AnzahlProSeite)."</a>";
		if ($i==$AktSeite) { echo "</b>"; }
		if ($counter>=6) {
			$counter=0;
			echo "<br>";
		}
	}
	echo "&nbsp;|&nbsp;<a href=\"teilnehmer.php?AktSeite=-1&limitListe=$limitListe$sAddQuery&iSortierung=$iSortierung\" class=\"TNLink\">";
	if ($AktSeite== -1) { echo "<b>"; }
	echo "$str[alle]</a> ";
	if ($AktSeite== -1) { echo "</b>"; }
	echo "</td></tr>";
}


// Anmeldung offen?
$row = @mysql_fetch_array(DB::query("select STRINGWERT from CONFIG where PARAMETER = 'VORANMELDUNG_OFFEN' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
// checken, ob get-variable on
if ($voranmeldung == "true" && $row['STRINGWERT'] == "J") {
	$bVoranmeld = 1;
	$sAddQuery="&voranmeldung=true";
} else {
	$bVoranmeld = 0;
	$sAddQuery="";
}
$row = @mysql_fetch_array(DB::query("select STRINGWERT from CONFIG where PARAMETER = 'ANMELDUNG_OFFEN' and MANDANTID = $nPartyID"), MYSQL_ASSOC);
if ($row['STRINGWERT'] == "J") {
	$bAnmeld = 1;
} else {
	$bAnmeld = 0;
}


//Vorarbeit fuers Blaettern
if ($AktSeite == "") {
	$AktSeite = 0;
}

$sql = "select 
          count(*) 
        from 
          ASTATUS a, USER u 
        where 
          a.STATUS IN ($STATUS_ANGEMELDET, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, 
	                     $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, $STATUS_COMFORT_8PERS, $STATUS_PREMIUM_8PERS,
	                     $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS, $STATUS_VIP_4PERS)          
        and 
          a.MANDANTID = $nPartyID and 
          a.USERID=u.USERID 
        $limitString";
$result = DB::query($sql);
//echo mysql_errno().": ".mysql_error()."<BR>";
$row = mysql_fetch_array($result);
$AnzahlDS_blaettern = $row[0];
if ($AnzahlDS_blaettern == 0) {
	$AnzahlDS_blaettern = 1;
}

$sql = "select 
          count(*) 
        from 
          ASTATUS a, USER u 
        where 
          a.STATUS IN ($STATUS_ANGEMELDET, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, 
	                     $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, $STATUS_COMFORT_8PERS, 
	                     $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS, $STATUS_VIP_4PERS)          
        and 
          a.MANDANTID = $nPartyID and 
          a.USERID=u.USERID";
//"select count(*) from ASTATUS a, USER u where (a.STATUS=$STATUS_ANGEMELDET or a.STATUS=$STATUS_BEZAHLT or a.STATUS=$STATUS_BEZAHLT_LOGE) and a.MANDANTID = $nPartyID and a.USERID=u.USERID"

$result = DB::query($sql);
//echo mysql_errno().": ".mysql_error()."<BR>";
$row = mysql_fetch_array($result);
$AnzahlDS = $row[0];
if ($AnzahlDS == 0) {
	$AnzahlDS = 0;
}
//Ende Vorarbeit
//Wie viele bezahlt?

	if (BUNGALOWLAN) {
		$sql = "(select 
							count(u.USERID)
		        from 
		          USER u, bungalow2user b2u 
		        where 
		          b2u.userID = u.USERID and
		          b2u.mandantID = $nPartyID)
		        UNION
		        (select 
		          count(u.USERID)
		        from 
		          USER u, bungalows b 
		        where 
		          b.bookedBy = u.USERID and
		          b.mandantID = $nPartyID)
		";

	} else {
		$sql = "select 
          count(*) 
        from 
          ASTATUS 
        where STATUS IN ($STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, 
	                     $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, $STATUS_COMFORT_8PERS, 
	                     $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS, $STATUS_VIP_4PERS) and 
        MANDANTID = $nPartyID";
	}

$result = DB::query($sql);

//echo mysql_errno().": ".mysql_error()."<BR>";

$AnzahlDSbezahlt = 0;
while ($row = mysql_fetch_array($result)) {
	$AnzahlDSbezahlt = $AnzahlDSbezahlt + $row[0];
}

$result = DB::query("select count(*) from ASTATUS where (STATUS=$STATUS_BEZAHLT_LOGE) and MANDANTID = $nPartyID");
$row = mysql_fetch_array($result);
$AnzahlDSbezahltLoge = $row[0];
//Wie viele Plaetze insgesamt?
$result = DB::query("select STRINGWERT from CONFIG where MANDANTID=$nPartyID and PARAMETER='TEILNEHMER'");
$row = mysql_fetch_array($result);
$partyPlaetze = $row[STRINGWERT];
?>

<p>
<table cellspacing="0" cellpadding="2" border="0" width="100%">
<tr><td valign="top">
	<p><?=$str[spieler1]?> <?php echo $AnzahlDS; ?> <?=$str[spieler2]?><br>
	<?php echo $AnzahlDSbezahlt; ?> <?=$str[spieler3]?>

	<?php
	if ($AnzahlDSbezahltLoge > 0) {
		echo ", $str[spieler5] $AnzahlDSbezahltLoge $str[spieler6]";
	} else {
		echo ".";
	}
	echo "<br>";
	echo $partyPlaetze-$AnzahlDSbezahlt; ?> <?=$str[spieler4]?>.</p>
</td><td align="right" valign="top">
<?php 
if (!BUNGALOWLAN) {
?>
	<form method="get" action="teilnehmer.php">
	<p><input type="text" name="limitListe" size="20" maxlength="20" value="<?=$limitListe?>"> <input type="submit" value="<?=$str[TN_Suchen]?>"></p>
	</form>
<?php 
}
?>
</td></tr>
</table>
</p>

<table class="rahmen_allg" cellpadding='1' cellspacing='1' border='0' width="100%">
<?php

	ShowBlaettern();

	?>
	
	<tr><td class="TNListe" width="14"><img src="lgif.gif" border="0" height="1" width="14"></td>
	<td class="TNListe" width="35%"><b><a href="teilnehmer.php?AktSeite=<?=$AktSeite?>&limitListe=<?=$limitListe?><?=$sAddQuery?>&iSortierung=nick" class="TNLink">Nickname</a></b></td>
	<?php
	if (BUNGALOWLAN === true)
	  echo '<td class="TNListe" width="28%"><b>Bungalow</b></td>';
	else
	  echo '<td class="TNListe" width="28%"><b>Clan</b></td>';
	?>
	<td class="TNListe" width="32%"><b>Spiele</b></td><td class="TNListe" width="13"><img src="lgif.gif" border="0" height="1" width="13"></td></tr>
	
	<tr><td class="Header_Separator" colspan="5"><img src="/gfx/lgif.gif" width="1" height="1"></td></tr>
	
	<?php

	// Sortierung
	if ($iSortierung == "nick") {
		$sAddSort = "order by LOGIN";
	} else if (!BUNGALOWLAN) {
		$sAddSort = "order by a.WANNANGEMELDET";
	} else {
		$sAddSort = "";
	}

	if ($AktSeite == "-1") {
		$sAddWhere = "";
	} else {
		$sAddWhere = "limit ".($AktSeite*$AnzahlProSeite).",".$AnzahlProSeite;
	}
	if (BUNGALOWLAN) {
		$sql = "(select 
		          u.USERID, u.LOGIN, u.KOMMENTAR_PUBLIC, u.HOMEPAGE, u.LAND, b2u.bungalow
		        from 
		          USER u, bungalow2user b2u 
		        where 
		          b2u.userID = u.USERID and
		          b2u.mandantID = $nPartyID)
		        UNION
		        (select 
		          u.USERID, u.LOGIN, u.KOMMENTAR_PUBLIC, u.HOMEPAGE, u.LAND, b.ID as bungalow
		        from 
		          USER u, bungalows b 
		        where 
		          b.bookedBy = u.USERID and
		          b.mandantID = $nPartyID) 
		        $limitString 
		        $sAddSort $sAddWhere";
	} else {
		$sql = "select 
		          u.USERID, u.LOGIN, u.KOMMENTAR_PUBLIC, u.HOMEPAGE, u.LAND, a.STATUS 
		        from 
		          USER u, ASTATUS a 
		        where 
		          a.USERID = u.USERID and 
		          a.STATUS IN ($STATUS_ANGEMELDET, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE, 
		                       $STATUS_COMFORT_4PERS, $STATUS_COMFORT_6PERS, $STATUS_COMFORT_8PERS, 
		                       $STATUS_PREMIUM_4PERS, $STATUS_PREMIUM_6PERS, $STATUS_ZUGEORDNET, $STATUS_VIP_2PERS, $STATUS_VIP_4PERS) and          
		          a.MANDANTID = $nPartyID 
		        $limitString 
		        $sAddSort $sAddWhere";
	}
	        
	$result = DB::query($sql);
	
	//echo mysql_errno().": ".mysql_error()."<BR>";
	
	while ($row = mysql_fetch_array($result)) {
		$tempULogin = $row['LOGIN'];
		if (strlen($tempULogin) > 23 ) {
			$Anzeige_Name = db2display(substr( $tempULogin, 0, 23)."...");
		} else {
			$Anzeige_Name = db2display($tempULogin);
		}
		$tempUSpiele = $row['KOMMENTAR_PUBLIC'];
		if (strlen($tempUSpiele) > 24 ) {
					$Anzeige_Spiele = db2display(substr( $tempUSpiele, 0, 24)."...");
				} else {
					$Anzeige_Spiele = db2display($tempUSpiele);
		}
		
		echo "<TR><TD class='TNListeTDA' align=\"center\"><a href=\"benutzerdetails.php?nUserID=$row[USERID]\"><img src=\"gfx/userinfo.gif\" border=\"0\"></a></TD>";
		echo "<TD class='TNListeTDB'><img src=\"".PELASHOST."gfx/flags/".db2display(strtolower($row['LAND'])).".png\" border=\"0\"> $Anzeige_Name</TD>";
		
		
		echo "<TD class='TNListeTDA'>";		
		if (BUNGALOWLAN === true) {
		  echo "<a href=\"/bungalows.php?action=detail&bungalow=".$row['bungalow']."\">Nr. ".$row['bungalow']."</a>";
		} else {
		  // Clan raussuchen
		  $result2 = DB::query("select c.CLANID, c.NAME from CLAN c, USER_CLAN uc where c.CLANID = uc.CLANID and uc.USERID=$row[USERID] and uc.MANDANTID=$nPartyID and uc.AUFNAHMESTATUS='$AUFNAHMESTATUS_OK'");
  		$row2    = mysql_fetch_array($result2);
	  	$sClan   = db2display($row2['NAME']);
		  $nClanID = $row2['CLANID'];
  		if (strlen($sClan) > 22 ) {
	  		$sClan = substr( $sClan, 0, 22)."...";
		  }
				  		
	  	if ($nClanID > 0) {
		  	echo "<a href=\"clandetails.php?nClanID=$nClanID\" class=\"inlink\">$sClan</a>";
  		} else {
			  echo "&nbsp;";
  		}	  	
	  }
	  echo "</TD>";
		
		
		echo "<td class='TNListeTDB'>$Anzeige_Spiele&nbsp;</td><td class='TNListeTDA' align=\"center\">";
				
	                       				
		if ($row['STATUS'] == $STATUS_BEZAHLT_LOGE) { 
			echo "<img src=\"/gfx/te_lg.gif\">"; 
		} else if ($row['STATUS'] == $STATUS_BEZAHLT || 
		           $row['STATUS'] == $STATUS_COMFORT_4PERS || 
		           $row['STATUS'] == $STATUS_COMFORT_6PERS ||  
		           $row['STATUS'] == $STATUS_COMFORT_8PERS ||  
		           $row['STATUS'] == $STATUS_PREMIUM_4PERS ||  
		           $row['STATUS'] == $STATUS_PREMIUM_6PERS || 
		           $row['STATUS'] == $STATUS_VIP_2PERS || 
		           $row['STATUS'] == $STATUS_VIP_4PERS || 
		           $row['STATUS'] == $STATUS_ZUGEORDNET) {
			echo "<img src=\"/gfx/te_bz.gif\">"; 
		} else {
			echo "<img src=\"/gfx/te_an.gif\">"; 
		}
		
		echo "</td></TR>\n";
	}

ShowBlaettern();
	
echo "</table>";
echo "<p><b>$str[legende]:</b>&nbsp; <img src=\"/gfx/te_bz.gif\"> $str[bezahlt] &nbsp; <img src=\"/gfx/te_lg.gif\"> $str[bezahltloge] &nbsp; <img src=\"/gfx/te_an.gif\"> $str[angemeldet]</p>";

?>
