<?php

include_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "language.inc.php";

$turnierid = intval($turnierid);

?>


<script language="JavaScript">
<!--
function ShowInfoClan(Clan)
{
	detail = window.open("clandetails.php?nPartyID=<?php echo $nPartyID; ?>&nClanID="+Clan,"Details","width=450,height=300,locationbar=false,resize=false");
	detail.focus();
}
//-->
</script>

<?php

if ($nLoginID == "") {$nLoginID = -1;}

if (!isset($dbh))
	$dbh = DB::connect();

// Gesamtcoins herausfinden
$result = mysql_db_query ($dbname, "select STRINGWERT from CONFIG where PARAMETER = 'COINS_GESAMT' and MANDANTID='$nPartyID'",$dbh);
$row = mysql_fetch_array($result);
$nMaxCoins = $row['STRINGWERT'];


//Funktionen
function Verbrauchte_Coins($nUserID) {
	global $nPartyID;
	if ($nUserID < 1) {
		return 0;
	}
	$q = "select sum(tl.COINS) as ANZAHL from TURNIERLISTE tl, TURNIERTEILNEHMER t where tl.TURNIERID=t.TURNIERID and t.USERID='$nUserID' and t.MANDANTID='$nPartyID' and tl.MANDANTID='$nPartyID'";
	$res = DB::query($q);
	$rowTemp = mysql_fetch_array($res);
	$anzLeader = $rowTemp['ANZAHL'];
	$q = "select sum(tl.COINS) as ANZAHL from TURNIERLISTE tl, TURNIERGRUPPE t where tl.TURNIERID=t.TURNIERID and t.USERID='$nUserID' and t.MANDANTID='$nPartyID' and tl.MANDANTID='$nPartyID'";
	$res = DB::query($q);
	$rowTemp = mysql_fetch_array($res);
	$anzGruppe = $rowTemp['ANZAHL'];
	
	return $anzLeader + $anzGruppe;
}


if ($turnierid > 0) {
	// Turnierinfos auslesen und in lokalen Variablen ablegen
	$result= mysql_db_query ($dbname, "select t.BILDKL, t.PREIS_PLATZ1, t.PREIS_PLATZ2, t.PREIS_PLATZ3, t.STARTZEIT, t.ANMELDUNGOFFEN, t.TURNIERID, t.GRUPPENGROESSE, t.ANZAHL_TEILNEHMER, t.NAME, t.ART, t.REGELN, t.BILDKL, t.BILDGR, t.COINS from TURNIERLISTE t where t.MANDANTID = $nPartyID and t.TURNIERID='$turnierid'",$dbh);
	$row = mysql_fetch_array($result);
	$Anzahl_Teilnehmer = $row[ANZAHL_TEILNEHMER];
	$TurnierName       = db2display($row[NAME]);
	$GruppenGroesse    = $row[GRUPPENGROESSE];
	$TurnierBildGR     = $row[BILDGR];
	$Regeln            = $row[REGELN];
	$AnmeldungOffen    = $row[ANMELDUNGOFFEN];
	$StartZeit         = $row[STARTZEIT];
	$sArt              = $row[ART];
	$nCoins		        = $row['COINS'];
	$turnier_symbol     = $row[BILDKL];
	$preis_platz1       = db2display($row[PREIS_PLATZ1]);
	$preis_platz2       = db2display($row[PREIS_PLATZ2]);
	$preis_platz3       = db2display($row[PREIS_PLATZ3]);

	// Behilfs-Navbar
	echo "<a href=\"turniere.php\">Turnier&uuml;bersicht</a> &gt; <a href=\"turniere.php?action=detail&turnierid=$turnierid\">$TurnierName</a>";
}
if ($nLoginID > 0) {
	// Wichtige Infos des eingeloggten Users auslesen
	$result= mysql_db_query ($dbname, "select STATUS from ASTATUS where MANDANTID=$nPartyID and USERID=$nLoginID",$dbh);
	$row = mysql_fetch_array($result);
	$UserStatus = $row[STATUS];
	$result= mysql_db_query ($dbname, "select c.NAME, uc.CLANID from CLAN c, USER_CLAN uc where c.MANDANTID=$nPartyID and uc.MANDANTID=$nPartyID and c.CLANID=uc.CLANID and uc.USERID=$nLoginID and uc.AUFNAHMESTATUS=$AUFNAHMESTATUS_OK",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$row = mysql_fetch_array($result);
	$UserClan     = $row[CLANID];
	$UserClanName = $row[NAME];
}

// Ist die Anmeldung auch offen? Wenn nicht nur Listenfunktion zulassen
if ($action == "" || $action == "detail" || $action == "ttn" || $AnmeldungOffen == "J") {

if ($action == "detail") {
//##############################################
// Detailansicht
	
	//echo "<img src=\"/turnierbild/$TurnierBildGR\" border=\"0\" align=\"right\">";

//***** Turnier hat noch nicht begonnen, Teilnahme moeglich *****
	// $result= mysql_db_query ($dbname, "select a.CLANID, a.TURNIERTEILNEHMERID, a.USERID, b.LOGIN, a.TEAMNAME from TURNIERTEILNEHMER a, USER b where b.USERID = a.USERID and a.TURNIERID='$turnierid' and a.MANDANTID=$nPartyID",$dbh);
	$result= mysql_db_query ($dbname, "select a.CLANID, a.TURNIERTEILNEHMERID, a.USERID, b.LOGIN, a.TEAMNAME from TURNIERTEILNEHMER a, USER b where b.USERID = a.USERID and a.TURNIERID='$turnierid' and a.MANDANTID=$nPartyID",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$rc = 1;
	while ($row = mysql_fetch_array($result)) {
		$TurnierTeilnehmerID[$rc] = $row[TURNIERTEILNEHMERID];
		$Teilnehmer[$rc] = $row[LOGIN];
		$Teilnehmer_team[$rc] = $row[TEAMNAME];
		$Teilnehmer_userid[$rc] = $row[USERID];
		$Teilnehmer_clan[$rc] = 0;
		
		if ($row[CLANID] > 0) {
			// Clannamen anzeigen!
			$result2 = mysql_db_query ($dbname, "select CLANID, NAME from CLAN where MANDANTID=$nPartyID and CLANID=$row[CLANID]", $dbh);
			$row2 = mysql_fetch_array($result2);
			$Teilnehmer_team[$rc] = $row2[NAME];
			$Teilnehmer_clan[$rc] = $row2[CLANID];
		}

		$rc ++;
	}
	for ($i = $rc ; $i <= $Anzahl_Teilnehmer; $i++) {
		$Teilnehmer[$i] = "";
	}

// Symbol in Tabelle der Detailsnsicht	
	if ($turnier_symbol != "") {
	    $symbol_string = "<img src=\"$turnier_symbol\" alt=\"$TurnierName\"></img>&nbsp;";
	} else 
	    $symbol_string = "";
	    
// Wenn kein Preis angegeben ist, dann soll dieser String dort erscheinen
    $kein_preis = "Preis noch nicht festgelegt";
    if ($preis_platz1 == "" )
        $preis_platz1 = $kein_preis;
    if ($preis_platz2 == "" )
        $preis_platz2 = $kein_preis;
    if ($preis_platz3 == "" )
        $preis_platz3 = $kein_preis;
        
// Verbleibende Coins ausrechnen
$nVerbleibend = $nMaxCoins - Verbrauchte_Coins($nLoginID);
	    
// Detailansicht eines Tuniers (Header)
    echo "<p>";
    echo    "<table class=\"rahmen_allg\" width=\"580px\" cellpadding=\"2\" cellspacing=\"1\">";
    echo        "<tr>";
    echo            "<td class=\"pelas_benutzer_titel\" colspan=4 width=\"580px\" valign=\"middle\">$symbol_string<strong>$TurnierName : Teilnehmer</strong></td>"; 
    echo        "</tr>";
    echo       	"<tr>"; 
    echo            "<td class=\"dblau\" width=\"90px\">Startzeit</td><td class=\"hblau\" width=\"190px\">$StartZeit</td>";
    echo            "<td class=\"dblau\" width=\"90px\">1. Platz</td><td class=\"hblau\" width=\"190px\">$preis_platz1</td>";
    echo        "</tr>";
    echo        "<tr>"; 
	echo            "<td class=\"dblau\" width=\"90px\">Kosten</td><td class=\"hblau\" width=\"190px\">$nCoins Coins</td>";
    echo            "<td class=\"dblau\" width=\"90px\">2. Platz</td><td class=\"hblau\" width=\"190px\">$preis_platz2</td>"; 
    echo        "</tr>";
    echo        "<tr>"; 
	echo            "<td class=\"dblau\" width=\"90px\">Vorhanden</td><td class=\"hblau\" width=\"190px\">$nVerbleibend Coins</td>";
    echo            "<td class=\"dblau\" width=\"90px\">3. Platz</td><td class=\"hblau\" width=\"190px\">$preis_platz3</td>"; 
    echo        "</tr>";
    echo    "</table>";
    echo "</p>";
	
// Detail Ansicht eines Tunieres (Teilnehmer)	
	echo "<table class=\"rahmen_allg\" width=\"580\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\"><tr>";
	$rc = 1;
	$fc = 2;
	$TableClass = "dblau";
	for ($t = 1; $t <= $Anzahl_Teilnehmer; $t++) {
		if ($fc >= 3) {
			if ($TableClass == "dblau") {
				$TableClass = "hblau";
			} else {
				$TableClass = "dblau";
			}
			$fc = 1;
		}
		$fc++;
		if ($rc >= 3) { 
			echo "</tr><tr>"; 
			$rc = 1; 
		}
		
		if ($Teilnehmer[$t] != "" ) {
			// Bisher im Team gefundene Leute raussuchen
			$result2 = mysql_db_query ($dbname, "select count(*) from TURNIERGRUPPE where MANDANTID=$nPartyID and TURNIERID=$turnierid and TURNIERTEILNEHMERID='$TurnierTeilnehmerID[$t]'", $dbh);
			$row2 = mysql_fetch_array($result2);
			$Anzahl = $row2[0] + 1;
		
			echo "<td class=\"$TableClass\" width=\"50%\">";
			if ( $GruppenGroesse > 1 ) { echo db2display($Teilnehmer_team[$t]); }
			else { echo db2display($Teilnehmer[$t]); }
			
			echo " <i>($Anzahl/$GruppenGroesse)</i> ";
			
			if ($GruppenGroesse > 1) {
				// Teamdetails zeigen
				echo " <a href=\"turniere.php?action=ttn&turnierid=$turnierid&TurnierTeilnehmerID=$TurnierTeilnehmerID[$t]\"><img src=\"/gfx/showinfo.gif\" border=\"0\" alt=\"Teilnehmer Info\"></a>";
			} else {
				// Userdetails zeigen
				echo " <a href=\"benutzerdetails.php?nUserID=$Teilnehmer_userid[$t]\"><img src=\"/gfx/showinfo.gif\" border=\"0\" alt=\"Teilnehmer Info\"></a>";
			}			
			
			if ($Teilnehmer_userid[$t] == $nLoginID) {
				echo " <a href=\"turniere.php?action=abmelden&turnierid=$turnierid&UserID=$Teilnehmer_userid[$t]&TurnierTeilnehmerID=$TurnierTeilnehmerID[$t]\"><img src=\"/gfx/turnier_abmelden.gif\" border=\"0\" alt=\"Vom Turnier abmelden\"></a>";
				if ($UserClan == $Teilnehmer_clan[$t] and $Teilnehmer_clan[$t] > 0) {
					echo " <a href=\"turniere.php?action=clanmates&turnierid=$turnierid&UserID=$row2[USERID]&TurnierTeilnehmerID=$TurnierTeilnehmerID[$t]\"><img src=\"/gfx/turnier_clanmates.gif\" border=\"0\" alt=\"Clanmates anmelden\"></a>";
				}
			}
			
			// Ist der angemeldete User als Mitglied im Team? Wenn ja Austritt ermöglichen
			$result2 = mysql_db_query ($dbname, "select USERID from TURNIERGRUPPE where MANDANTID=$nPartyID and TURNIERID='$turnierid' and TURNIERTEILNEHMERID='$TurnierTeilnehmerID[$t]' and USERID=$nLoginID", $dbh);
			//echo mysql_errno().": ".mysql_error()."<BR>";
			$row2 = mysql_fetch_array($result2);
			if ($row2[USERID] == $nLoginID) {
				$imTeam = 1;
			} else {
				$imTeam = 0;
			}
			if ($imTeam == 1) {
				echo " <a href=\"turniere.php?action=austreten&turnierid=$turnierid&UserID=$row2[USERID]&TurnierTeilnehmerID=$TurnierTeilnehmerID[$t]\"><img src=\"/gfx/turnier_abmelden.gif\" border=\"0\" alt=\"Aus der Gruppe austreten\"></a>";
			} elseif ( $GruppenGroesse > 1 && $Anzahl < $GruppenGroesse && $Teilnehmer_userid[$t] != $nLoginID) {
				// Join?
				echo " <a href=\"turniere.php?action=join&turnierid=$turnierid&TurnierTeilnehmerID=$TurnierTeilnehmerID[$t]\"><img src=\"/gfx/turnier_join.gif\" border=\"0\" alt=\"Diesem Team beitreten\"></a>";
			}
			
			echo "</td>\n";
		} else {
			echo "<td class=\"".$TableClass."\"><a href=\"turniere.php?action=teilnehmen&turnierid=".$turnierid."\">Teilnehmen</a></td>\n";
		}
		$rc ++;
	}
	echo "</table>";

	echo "<p><table><tr><td><img src=\"/gfx/showinfo.gif\"> Details einsehen</td><td><img src=\"/gfx/turnier_abmelden.gif\"> Vom Turnier abmelden</td></tr><tr><td><img src=\"/gfx/turnier_join.gif\"> Dem Team beitreten</td><td><img src=\"/gfx/turnier_clanmates.gif\"> Clanmates hinzuf&uuml;gen</td></tr></table></p>";
	
	echo "<h2>Regeln</h2>";
	
	echo "<p>".db2displayNews($Regeln)."</p>";	
	

} elseif ($action == "abmelden") {
//#############################
// Vom Turnier abmelden

echo "<h2>Vom Turnier ".db2display($TurnierName)." abmelden</h2>";

if ($nLoginID != $UserID) {
	echo "<p>Du bist nicht unter diesem Login angemeldet und kannst Dich deswegen nicht abmelden.</p>";
} elseif ( $rowTurnier[AKTUELLE_RUNDE] >= 1) {
	//Turnier noch nicht gestartet?
	echo "<p>Das Turnier wurde bereits gestartet. Du kannst Dich jetzt nicht mehr abmelden.</p>";
} elseif ($DoIt != "yes") {
	//***** Sicherheitsabfrage *****
	echo "<p>Willst Du Dich ";
	if ($GruppenGroesse > 1) {
		echo "und Dein Team";
	}
	echo " wirklich vom Turnier ".db2display($TurnierName)." abmelden?";
	
	echo "<form method=\"post\" action=\"turniere.php?action=abmelden&turnierid=$turnierid&UserID=$UserID&TurnierTeilnehmerID=$TurnierTeilnehmerID\">";
	echo "<input type=\"hidden\" name=\"DoIt\" value=\"yes\">";
	echo "<input type=\"submit\" value=\" Ja \">";
	echo " <input type=\"button\" value=\" Nein \" OnClick=\"document.location.href='turniere.php?action=detail&turnierid=$turnierid';\">";
	echo "</form>";
} else {
//*** Ok, abmelden
	$result= mysql_db_query ($dbname, "delete from TURNIERTEILNEHMER where TURNIERID=$turnierid and USERID=$UserID and MANDANTID=$nPartyID",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	if ($GruppenGroesse > 1) {
		// Mitglieder auch loeschen
		$result= mysql_db_query ($dbname, "delete from TURNIERGRUPPE where TURNIERID='$turnierid' and MANDANTID=$nPartyID and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
	}
	echo "<p>Du hast Dich soeben vom Turnier $TurnierName abgemeldet.</p>";
}

echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";

} elseif ($action == "teilnehmen") {
//##############################################
// Teilnehmen

function show_form ()
{
global $UserClan, $UserClanName, $iTEAMNAME, $iKOMMENTAR, $turnierid;

?>
<p>Du kannst f&uuml;r dieses Turnier entweder ein Team ohne Clanbindung erstellen oder Deinen Clan anmelden.
Wenn Du ein neues Team erstellst, musst Du einen Namen eingeben.</p>

<form method="post" action="turniere.php?action=teilnehmen&turnierid=<?php echo $turnierid;?>" name="data">
<table class="rahmen_allg" cellspacing=1 cellpadding=2>
	<TR><TD class='dblau'><input type="radio" value=\"name\" name="NimmClan" checked></td><TD class='hblau'>Neues Team erstellen</TD><TD class='hblau'><input type="text" name="iTEAMNAME" size=25 maxlength=40 value="<?php echo $iTEAMNAME;?>"></TD></TR>
	<?php
		if ($UserClan > 0) {
			echo "<TR><TD class=\"dblau\"><input type=\"radio\" value=\"clan\" name=\"NimmClan\" checked></td><TD class='hblau'>Clan anmelden</TD><TD class='hblau'>".db2display($UserClanName)."</TD></TR>";
		}
	?>
	<tr><td colspan="3" height="40" class='dblau'><input type="submit" value="Eintragen" name="knopf">
</TABLE></p>
</form>

<?php
}

//Ist noch ein Platz frei?
$result= mysql_db_query ($dbname, "select COUNT(*) from TURNIERTEILNEHMER where TURNIEREID='$turnierid and' MANDANTID=$nPartyID",$dbh);
//echo mysql_errno().": ".mysql_error()."<BR>";
if ($result != "" ) { $rowATeilnehmer = mysql_fetch_array($result); }

//Vielleicht schon eingetragen?
$result= mysql_db_query ($dbname, "select USERID from TURNIERTEILNEHMER where TURNIERID='$turnierid' and USERID=$nLoginID and MANDANTID=$nPartyID",$dbh);
//echo mysql_errno().": ".mysql_error()."<BR>";
if ($result != "" ) { $rowDrin = mysql_fetch_array($result); }

// Bereits in irgendeinem Team als Member?
$result= mysql_db_query ($dbname, "select USERID from TURNIERGRUPPE where USERID=$nLoginID and TURNIERID='$turnierid' and MANDANTID=$nPartyID",$dbh);
//echo mysql_errno().": ".mysql_error()."<BR>";
$imTeam = mysql_fetch_array($result);

echo "<h2>Teilnehmen am Turnier $TurnierName</h2>";

if ($AnmeldungOffen != "J") {
	echo "<p>Die Anmeldung f&uuml;r dieses Turnier wurde noch nicht gestartet.</p>";
} elseif ($nLoginID < 1) {
	echo "<p>Nur eingeloggte Benutzer k&ouml;nnen an einem Turnier teilnehmen.</p>";
	
	
	
	
	
	
	
	
	
	
	
	
	
} elseif (!User::hatBezahlt()) {
//} elseif ($UserStatus != $STATUS_BEZAHLT && $UserStatus != $STATUS_BEZAHLT_LOGE) {
	echo "<p>Nur Benutzer die gezahlt haben d&uuml;rfen an einem Turnier teilnehmen.</p>";
} elseif ( $rowTurnier[AKTUELLE_RUNDE] >= 1) {
	//Turnier noch nicht gestartet?
	echo "<p>Das Turnier wurde bereits gestartet. Du kannst jetzt nicht mehr teilnehmen.</p>";
} elseif ( $rowATeilnehmer[0] >= $Anzahl_Teilnehmer) {
	//Ist noch ein Platz frei?
	echo "<p>Bei diesem Turnier sind keine Plätze mehr frei.</p>";
} elseif ( $rowDrin[USERID] != "") {
	//Vielleicht schon eingetragen?
	echo "<p>Du bist bereits f&uuml;r dieses Turnier angemeldet.</p>";
} elseif ( $imTeam[USERID] != "") {
	//Schon als Member eines Teams drin?
	echo "<p>Du bist bereits als Mitglied eines Teams f&uuml;r dieses Turnier angemeldet.</p>";
} elseif (Verbrauchte_Coins($nLoginID) + $nCoins > $nMaxCoins) {
	//Maximale Coins überschritten
	$nVerbleibend = $nMaxCoins - Verbrauchte_Coins($nLoginID);
	echo "<p>Dein Clanmate hat nicht mehr gen&uuml;gend Coins ($nVerbleibend vorhanden, $nCoins ben&ouml;tigt).</p>";
} else {
	if ( $GruppenGroesse > 1 ) {
	//###### Teamspiel ######
		if (!isset($_POST[iTEAMNAME]) ) {
			show_form();
		} else {
			//Clan schon eingetragen?
			if ($NimmClan == "clan" and $UserClan > 0) {
				$result= mysql_db_query ($dbname, "select CLANID from TURNIERTEILNEHMER where TURNIERID='$turnierid' and CLANID='$UserClan' and MANDANTID=$nPartyID",$dbh);
				//echo mysql_errno().": ".mysql_error()."<BR>";
				if ($result != "" ) { $rowClanDrin = mysql_fetch_array($result); }
			}

			if ($rowClanDrin > 0) {
				echo "<p class=\"fehler\">Dieser Clan ist bereits f&uuml;r dieses Turnier angemeldet.</p>";
				show_form();
			} elseif ( $NimmClan != "clan" && empty($_POST[iTEAMNAME]) ) {
				echo "<p class='fehler'>Bitte einen Teamnamen angeben.</p>";
				show_form();
			} else {
				//Alles i.O. teilnehmen
				if ($NimmClan == "clan") {
					// Clan anmelden
					$result= mysql_db_query ($dbname, "insert into TURNIERTEILNEHMER (TURNIERID, MANDANTID, CLANID, USERID, KOMMENTAR) values ('$turnierid', $nPartyID, '$UserClan', $nLoginID, '$iKOMMENTAR')",$dbh);
					//echo mysql_errno().": ".mysql_error()."<BR>";
				} else {
					// Frei fliegendes Team anmelden
					$result= mysql_db_query ($dbname, "insert into TURNIERTEILNEHMER (TURNIERID, MANDANTID, USERID, KOMMENTAR, TEAMNAME) values ('$turnierid', $nPartyID, $nLoginID, '$iKOMMENTAR', '$iTEAMNAME')",$dbh);
					//echo mysql_errno().": ".mysql_error()."<BR>";
				}

				if (mysql_errno() == 0) {
					echo "<p>Du hast nun ein Team f&uuml;r dieses Turnier angemeldet. </p>";
				} else {	
					echo "<p>Es gab technische Probleme bei der Anmeldung. Bitte wende Dich an einen Administrator.</p>";
				}
			}
		}
	} else {
	//###### Einzelspiel ######
		//Alles i.O. teilnehmen
		$result= mysql_db_query ($dbname, "insert into TURNIERTEILNEHMER (TURNIERID, MANDANTID, USERID, KOMMENTAR) values ('$turnierid', $nPartyID, $nLoginID, '')",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";

		if (mysql_errno() == 0) {
			echo "<p>Du hast Dich f&uuml;r dieses Turnier angemeldet.</p>";
		} else {	
			echo "<p>Es gab technische Probleme bei der Anmeldung. Bitte wende Dich an einen Administrator.</p>";
		}
	}
}
echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";


} elseif ($action == "join") {
	// Team joinen
	
	// Voraussetzungen erkunden
	$result = mysql_db_query ($dbname, "select c.NAME, tt.CLANID from TURNIERTEILNEHMER tt, CLAN c where tt.MANDANTID=$nPartyID and tt.TURNIERID='$turnierid' and tt.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and tt.CLANID=c.CLANID", $dbh);
	$row2 = mysql_fetch_array($result);
	if ($row2[CLANID] > 0) {
		$TeamName = db2display($row2[NAME]);
	} else {
		$result = mysql_db_query ($dbname, "select TEAMNAME from TURNIERTEILNEHMER where MANDANTID=$nPartyID and TURNIERID='$turnierid' and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'", $dbh);
		$row2 = mysql_fetch_array($result);
		$TeamName = db2display($row2[TEAMNAME]);
	}
	
	echo "<h2>Team $TeamName joinen</h2>";
	
	// Bereits im Team?
	$result= mysql_db_query ($dbname, "select USERID from TURNIERGRUPPE where USERID=$nLoginID and TURNIERID='$turnierid' and MANDANTID=$nPartyID",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$imTeam = mysql_fetch_array($result);
	$result= mysql_db_query ($dbname, "select USERID from TURNIERTEILNEHMER where USERID=$nLoginID and TURNIERID='$turnierid' and MANDANTID=$nPartyID",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$imTeamLeader = mysql_fetch_array($result);
	
	// Team bereits voll?
	$result= mysql_db_query ($dbname, "select count(*) from TURNIERGRUPPE where TURNIERID='$turnierid' and MANDANTID=$nPartyID and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	if ($result != "" ) { $CountMembers = mysql_fetch_array($result); }
	
	if ($AnmeldungOffen != "J") {
		echo "<p>Die Anmeldung f&uuml;r dieses Turnier wurde noch nicht gestartet.</p>";
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	} elseif (!User::hatBezahlt()) {
	//} elseif ($UserStatus != $STATUS_BEZAHLT && $UserStatus != $STATUS_BEZAHLT_LOGE) {
		// Gezahlt?
		echo "<p class=\"fehler\">Du musst gezahlt haben, um an einem Turnier teilzunehmen.</p>";
	} elseif ($imTeam[USERID] > 0 || $imTeamLeader[USERID] > 0) {
		// Schon drin?
		echo "<p class=\"fehler\">Du bist bereits in einem Team.</p>";
	} elseif ($CountMembers[0] >= $GruppenGroesse -1) {
		// Team bereits voll?
		echo "<p class=\"fehler\">Dieses Team ist bereits vollz&auml;hlig.</p>";
	} elseif (Verbrauchte_Coins($nLoginID) + $nCoins > $nMaxCoins) {
		//Maximale Coins überschritten
		$nVerbleibend = $nMaxCoins - Verbrauchte_Coins($nLoginID);
		echo "<p>Dein Clanmate hat nicht mehr gen&uuml;gend Coins ($nVerbleibend vorhanden, $nCoins ben&ouml;tigt).</p>";
	} else {
		//ok, go!
		$result= mysql_db_query ($dbname, "insert into TURNIERGRUPPE (TURNIERID, MANDANTID, TURNIERTEILNEHMERID, USERID) values ('$turnierid', $nPartyID, '$TurnierTeilnehmerID', $nLoginID)",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
		
		echo "Du bist dem Team erfolgreich beigetreten.</p>";
	}
	
	echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";

} elseif ($action == "ttn") {
	// Voraussetzungen erkunden
	$result = mysql_db_query ($dbname, "select c.NAME, tt.CLANID from TURNIERTEILNEHMER tt, CLAN c where tt.MANDANTID=$nPartyID and tt.TURNIERID='$turnierid' and tt.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and tt.CLANID=c.CLANID", $dbh);
	$row2 = mysql_fetch_array($result);
	$ClanID = $row2[CLANID];
	if ($ClanID > 0) {
		$TeamName = db2display($row2[NAME]);
	} else {
		$result = mysql_db_query ($dbname, "select TEAMNAME from TURNIERTEILNEHMER where MANDANTID=$nPartyID and TURNIERID='$turnierid' and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'", $dbh);
		$row2 = mysql_fetch_array($result);
		$TeamName = db2display($row2[TEAMNAME]);
	}
	
	echo "<h2>Aufstellung $TeamName</h2>";

	// Wenn WWCL-Turnier, dann WWCL-ID ausgeben
	if ($sArt == "WWCL") {
		// WWCL-ID lesen
		$result = mysql_db_query ($dbname, "select u.WWCL_TEAM from TURNIERTEILNEHMER t ,USER u where u.USERID=t.USERID and t.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and t.TURNIERID='$turnierid'", $dbh);
		$row = mysql_fetch_array($result);
		$sTeam   = $row[WWCL_TEAM];
		if ($sTeam != "") {
			// Vorhanden!
			echo "<p>Dieses Team tritt mit der WWCL-ID <b>$sTeam</b> an.</p>";
		} else {
			// Nicht vorhanden
			echo "<p>F&uuml;r dieses Team ist keine WWCL-ID angegeben.</p>";
		}
	}
	if ($sArt == "NGL") {
		// NGL-ID lesen
		$result = mysql_db_query ($dbname, "select u.NGL_TEAM from TURNIERTEILNEHMER t ,USER u where u.USERID=t.USERID and t.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and t.TURNIERID='$turnierid'", $dbh);
		$row = mysql_fetch_array($result);
		$sTeam   = $row[NGL_TEAM];
		if ($sTeam != "") {
			// Vorhanden!
			echo "<p>Dieses Team tritt mit der NGL-ID <b>$sTeam</b> an.</p>";
		} else {
			// Nicht vorhanden
			echo "<p>F&uuml;r dieses Team ist keine NGL-ID angegeben.</p>";
		}
	}

	$result = mysql_db_query ($dbname, "select u.USERID, u.LOGIN from TURNIERTEILNEHMER t ,USER u where u.USERID=t.USERID and t.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and t.MANDANTID=$nPartyID and t.TURNIERID='$turnierid'", $dbh);
	$row = mysql_fetch_array($result);
	$LeaderName = db2display($row[LOGIN]);
	$LeaderID   = $row[USERID];
	
	echo "<p><table class=\"rahmen_allg\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\" width=\"400\">";

	echo "<tr><td class=\"dblau\" width=\"90\">Leader</td><td class=\"hblau\">$LeaderName <a href=\"benutzerdetails.php?nUserID=$LeaderID\"><img src=\"/gfx/showinfo.gif\" border=\"0\" alt=\"Teilnehmer Info\"></a></td></tr>";
	
	$result = mysql_db_query ($dbname, "select u.USERID, u.LOGIN from TURNIERGRUPPE t ,USER u where u.USERID=t.USERID and t.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and t.MANDANTID=$nPartyID and t.TURNIERID='$turnierid'", $dbh);
	$counter = 1;
	while ($row = mysql_fetch_array($result)) {
		echo "<tr><td class=\"dblau\">Spieler $counter</td><td class=\"hblau\"> ".db2display($row[LOGIN])." <a href=\"benutzerdetails.php?nUserID=$row[USERID]\"><img src=\"/gfx/showinfo.gif\" border=\"0\" alt=\"Teilnehmer Info\"></a></td></tr>";
		$counter++;
	}
	
	echo "</table></p>";
	
	if ($ClanID > 0) {
		
		displayClanPic($ClanID,$nPartyID);
		
		echo "<p><a href=\"clandetails.php?nClanID=$ClanID\">";
		echo "Claninfos";
		echo "</a></p>";
	}

	echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";

} elseif ($action == "austreten") {
	//#############################
	// Aus dem Team austreten

	echo "<h2>Vom Turnier ".db2display($TurnierName)." abmelden</h2>";

	if ($nLoginID != $UserID) {
		echo "<p>Du bist nicht unter diesem Login angemeldet und kannst Dich deswegen nicht abmelden.</p>";
	} elseif ( $rowTurnier[AKTUELLE_RUNDE] >= 1) {
		//Turnier noch nicht gestartet?
		echo "<p>Das Turnier wurde bereits gestartet. Du kannst Dich jetzt nicht mehr abmelden.</p>";
	} elseif ($DoIt != "yes") {
		//***** Sicherheitsabfrage *****
		echo "<p>Willst Du wirklich aus diesem Team austreten und Dich somit vom Turnier $TurnierName abmelden?";

		echo "<form method=\"post\" action=\"turniere.php?action=austreten&turnierid=$turnierid&UserID=$UserID&TurnierTeilnehmerID=$TurnierTeilnehmerID\">";
		echo "<input type=\"hidden\" name=\"DoIt\" value=\"yes\">";
		echo "<input type=\"submit\" value=\" Ja \">";
		echo " <input type=\"button\" value=\" Nein \" OnClick=\"document.location.href='turniere.php?action=detail&turnierid=$turnierid';\">";
		echo "</form>";
	} else {
	//*** Ok, abmelden
		$result= mysql_db_query ($dbname, "delete from TURNIERGRUPPE where TURNIERID='$turnierid' and USERID='$UserID' and MANDANTID=$nPartyID and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";

		echo "<p>Du hast Dich soeben vom Turnier $TurnierName abgemeldet.</p>";
	}

	echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";

} elseif ($action == "clanmates") {
	// Clanmates hinzufügen
	
	// Voraussetzungen erkunden
	$result = mysql_db_query ($dbname, "select c.NAME, tt.CLANID from TURNIERTEILNEHMER tt, CLAN c where tt.MANDANTID=$nPartyID and tt.TURNIERID='$turnierid' and tt.TURNIERTEILNEHMERID='$TurnierTeilnehmerID' and tt.CLANID=c.CLANID", $dbh);
	$row2 = mysql_fetch_array($result);
	if ($row2[CLANID] > 0) {
		$TeamName = db2display($row2[NAME]);
	} else {
		$result = mysql_db_query ($dbname, "select TEAMNAME from TURNIERTEILNEHMER where MANDANTID=$nPartyID and TURNIERID='$turnierid' and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'", $dbh);
		$row2 = mysql_fetch_array($result);
		$TeamName = db2display($row2[TEAMNAME]);
	}
	
	echo "<h2>Clanmates zum Team $TeamName hinzuf&uuml;gen</h2>";
	
	if ($nClanMateID > 0) {
		// go!
	
		// ist der User wirklich in dem gleichen Clan?
		$result= mysql_db_query ($dbname, "select CLANID from USER_CLAN where USERID='$nClanMateID' and MANDANTID=$nPartyID",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
		$ClanCheck = mysql_fetch_array($result);
	
		// hat das Clanmate gezahlt?
		$result= mysql_db_query ($dbname, "select STATUS from ASTATUS where USERID='$nClanMateID' and MANDANTID=$nPartyID",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
		$MateStatus = mysql_fetch_array($result);
	
		// Bereits im Team?
		$result= mysql_db_query ($dbname, "select USERID from TURNIERGRUPPE where USERID='$nClanMateID' and TURNIERID='$turnierid' and MANDANTID=$nPartyID",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
		$imTeam = mysql_fetch_array($result);
		$result= mysql_db_query ($dbname, "select USERID from TURNIERTEILNEHMER where USERID='$nClanMateID' and TURNIERID='$turnierid' and MANDANTID=$nPartyID",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
		$imTeamLeader = mysql_fetch_array($result);
	
		// Team bereits voll?
		$result= mysql_db_query ($dbname, "select count(*) from TURNIERGRUPPE where TURNIERID='$turnierid' and MANDANTID=$nPartyID and TURNIERTEILNEHMERID='$TurnierTeilnehmerID'",$dbh);
		//echo mysql_errno().": ".mysql_error()."<BR>";
		if ($result != "" ) { $CountMembers = mysql_fetch_array($result); }
	
		if ($AnmeldungOffen != "J") {
			echo "<p>Die Anmeldung f&uuml;r dieses Turnier wurde noch nicht gestartet.</p>";
		} elseif ($ClanCheck[CLANID] != $UserClan) {
			echo "<p>Dieser Spieler ist nicht in Deinem Clan.</p>";
		} elseif ($UserStatus != $STATUS_BEZAHLT && $UserStatus != $STATUS_BEZAHLT_LOGE) {
			// Gezahlt?
			echo "<p class=\"fehler\">Du musst gezahlt haben, um an einem Turnier teilzunehmen.</p>";
















		} elseif ( !User::hatBezahlt($nClanMateID)) {
//		} elseif ($MateStatus[STATUS] != $STATUS_BEZAHLT && $MateStatus[STATUS] != $STATUS_BEZAHLT_LOGE) {
			echo "<p>Dein Clanmate hat noch nicht gezahlt.</p>";
		} elseif ($imTeam[USERID] > 0 || $imTeamLeader[USERID] > 0) {
			// Schon drin?
			echo "<p class=\"fehler\">Dieser Spieler ist bereits in Deinem Team.</p>";
		} elseif ($CountMembers[0] >= $GruppenGroesse -1) {
			// Team bereits voll?
			echo "<p class=\"fehler\">Dieses Team ist bereits vollz&auml;hlig.</p>";
		} elseif (Verbrauchte_Coins($nClanMateID) + $nCoins > $nMaxCoins) {
			//Maximale Coins überschritten
			$nVerbleibend = $nMaxCoins - Verbrauchte_Coins($nClanMateID);
			echo "<p>Dein Clanmate hat nicht mehr gen&uuml;gend Coins ($nVerbleibend vorhanden, $nCoins ben&ouml;tigt).</p>";
		} else {
			//ok, go!
			$result= mysql_db_query ($dbname, "insert into TURNIERGRUPPE (TURNIERID, MANDANTID, TURNIERTEILNEHMERID, USERID) values ('$turnierid', $nPartyID, '$TurnierTeilnehmerID', '$nClanMateID')",$dbh);
			//echo mysql_errno().": ".mysql_error()."<BR>";
			
			echo "Du hast Dein Clanmate erfolgreich aufgenommen.</p>";
		}
		echo "<p><a href=\"turniere.php?action=clanmates&turnierid=$turnierid&TurnierTeilnehmerID=$TurnierTeilnehmerID\">Zur&uuml;ck zur Clanmate-&Uuml;bersicht</a></p>";
	} else {
		// Memberliste anzeigen
		$result = mysql_db_query ($dbname, "select a.STATUS, u.LOGIN, u.USERID from USER u, USER_CLAN uc, ASTATUS a where a.MANDANTID=$nPartyID and a.USERID=u.USERID and uc.AUFNAHMESTATUS = $AUFNAHMESTATUS_OK and u.USERID = uc.USERID and uc.CLANID = '$UserClan' and uc.MANDANTID = $nPartyID and u.USERID != $nLoginID", $dbh);
		
		echo "<table class=\"rahmen_allg\" width=\"350\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";
		
		$counter = 0;
		while ($row=mysql_fetch_array($result)) {
			echo "<tr><td class=\"TNListeTDA\">".db2display($row[LOGIN])."</td><td class=\"TNListeTDB\">";

			if ( User::hatBezahlt($row[USERID]) ) {
//			if ( $row[STATUS] == $STATUS_BEZAHLT_LOGE || $row[STATUS] == $STATUS_BEZAHLT) { 
				$result2 = mysql_db_query ($dbname, "select USERID, TURNIERTEILNEHMERID from TURNIERGRUPPE where USERID=$row[USERID] and MANDANTID=$nPartyID and TURNIERID='$turnierid'", $dbh);
				//echo mysql_errno().": ".mysql_error()."<BR>";
				$row2 = mysql_fetch_array($result2);
				if ($row2[TURNIERTEILNEHMERID] != $TurnierTeilnehmerID && $row2[USERID] > 0) {
					echo "Spielt in einem anderen Team"; 
				} elseif ($row2[USERID] > 0) {
					echo "Ist bereits in Deinem Team";
				} else {
					echo "<a href=\"turniere.php?action=clanmates&turnierid=$turnierid&TurnierTeilnehmerID=$TurnierTeilnehmerID&nClanMateID=$row[USERID]\">Eintragen</a>";
				}
			} else if ( $row[STATUS] == $STATUS_ANGEMELDET) {
				echo "Nicht gezahlt!"; 
			} else {
				echo "Nicht angemeldet!";
			}
			echo "</td></tr>";
			$counter++;
		}
		if ($counter <= 0) {
			echo "<tr><td><p>Es gibt keine Clanmates die Du hinzuf&uuml;gen kannst.</p></td></tr>";
		}
		echo "</table>";
		echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";
	}
} else {
//##################################
// Uebersichtsliste anzeigen
	if ($nMaxCoins > 0 && $nLoginID > 0 ) {
		$nVerbleibend = $nMaxCoins - Verbrauchte_Coins($nLoginID);
		echo "<p>Verf&uuml;gbare Coins: $nVerbleibend</p>";
	} else 
	    echo "<p>F&uuml;r die Turnieranmeldung bitte einloggen.</p>";
	
	echo "<p><table class=\"rahmen_allg\" cellpadding=\"4\" cellspacing=\"1\" border=\"0\">";
	echo "<tr><td class=\"TNListe\">&nbsp;</td><td class=\"TNListe\"><b>Turnier</b> <i>(Teilnehmer)</i></td><td class=\"TNListe\"><b>Coins</b></td><td class=\"TNListe\"><b>Startzeit</b></td></tr>";

	if (CFG::getMandantConfig("TURNIER_AB18_AUSBLENDEN", $nPartyID) == "J" && $ab18 != "true") {
		$sAddWhere = " and AB18 != 'J'";
	} else {
		$sAddWhere = "";
	}
	
	$result= mysql_db_query ($dbname, "select t.STARTZEIT, t.ANZAHL_TEILNEHMER, t.TURNIERID, t.NAME, t.ART, t.REGELN, t.BILDKL, t.COINS from TURNIERLISTE t where t.MANDANTID = $nPartyID $sAddWhere order by t.NAME",$dbh);
	//echo mysql_errno().": ".mysql_error()."<BR>";

	while ($row = mysql_fetch_array($result)) {

		$result2= mysql_db_query ($dbname, "select count(*) from TURNIERTEILNEHMER where MANDANTID=$nPartyID and TURNIERID=$row[TURNIERID]",$dbh);
		$row2 = mysql_fetch_array($result2);
		$TAnz = $row2[0];

		echo "<TR><TD class='TNListeTDA' align=\"center\"><img src=\"$row[BILDKL]\" border=\"0\"></TD>";
		
		echo "<TD class='TNListeTDB'><a href=\"turniere.php?action=detail&turnierid=$row[TURNIERID]\">$row[NAME]</a> <i>($TAnz / $row[ANZAHL_TEILNEHMER])</i> ";
		// Wenn angemeldet dann abmelde-Button zeigen
			// Gesamtcoins herausfinden Teamleader
			$q = "select USERID, TURNIERTEILNEHMERID from TURNIERTEILNEHMER where MANDANTID='$nPartyID' and TURNIERID='".$row['TURNIERID']."' and USERID='$nLoginID'";
			$res = DB::query($q);
			$rowTemp = mysql_fetch_array($res);
			if ($rowTemp['USERID'] > 0) {
				echo " <a href=\"turniere.php?action=abmelden&turnierid=".$row['TURNIERID']."&UserID=".$rowTemp['USERID']."&TurnierTeilnehmerID=".$rowTemp['TURNIERTEILNEHMERID']."\"><img src=\"/gfx/turnier_abmelden.gif\" border=\"0\" alt=\"Vom Turnier abmelden\"></a>";
			}
			// Gesamtcoins herausfinden Gruppenmitglied
			$q = "select USERID, TURNIERTEILNEHMERID from TURNIERGRUPPE where MANDANTID='$nPartyID' and TURNIERID='".$row['TURNIERID']."' and USERID='$nLoginID'";
			$res = DB::query($q);
			$rowTemp = mysql_fetch_array($res);
			if ($rowTemp['USERID'] > 0) {
				echo " <a href=\"turniere.php?action=austreten&turnierid=".$row['TURNIERID']."&UserID=".$rowTemp['USERID']."&TurnierTeilnehmerID=".$rowTemp['TURNIERTEILNEHMERID']."\"><img src=\"/gfx/turnier_abmelden.gif\" border=\"0\" alt=\"Vom Turnier abmelden\"></a>";
			}
		echo "</TD>";
		
		echo "<TD class='TNListeTDA'>".$row['COINS']."</td>";
		echo "<TD class='TNListeTDB'>$row[STARTZEIT]</td>";
		echo "</td></TR>\n";
	}
	
	echo "</table></p>";
}

} else {
	echo "<p>Die Anmeldung f&uuml;r dieses Turnier ist geschlossen.</p>";
	echo "<p><a href=\"turniere.php?action=detail&turnierid=$turnierid\">Zur&uuml;ck zur Turnierseite</a></p>";
}
?>                                                                      
