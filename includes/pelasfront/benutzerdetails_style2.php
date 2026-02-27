
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
include_once "sitzgruppenfunctions.php";
include "language.inc.php";


if ($nUserID < 1) { $nUserID = -1;}

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

$row = mysql_fetch_array($result);

//Anmeldestatus und beschreibung.
$anmelderesult= mysql_db_query ($dbname, "select STATUS from ASTATUS where USERID='$nUserID' AND MANDANTID='$nPartyID'",$dbh);
$anmelderow = mysql_fetch_array($anmelderesult);
$anmeldestatus = $anmelderow['STATUS'];
$statusbeschreibung = mysql_db_query ($dbname, "select b.BESCHREIBUNG from STATUS b, ASTATUS a where a.USERID='$nUserID' and a.MANDANTID=$nPartyID and b.STATUSID=a.STATUS",$dbh);
$rowStat = mysql_fetch_array($statusbeschreibung);

//Partynamen raussuchen
$partynameresult= mysql_db_query ($dbname, "select BESCHREIBUNG from MANDANT where MANDANTID='$nPartyID'",$dbh);
$partynamerow = mysql_fetch_array($partynameresult);
$partyname = $partynamerow['BESCHREIBUNG'];

//Sitz und besuchte Parties
//echo mysql_errno().": ".mysql_error()."<BR>";
$sitz = mysql_db_query ($dbname, "select * from SITZ where USERID='$nUserID' and MANDANTID='$nPartyID'",$dbh);
$besuchteParties = mysql_db_query ($dbname, "select m.REFERER, p.NAME, p.BEGINN from MANDANT m, ASTATUSHISTORIE a, PARTYHISTORIE p where m.MANDANTID=p.MANDANTID and a.USERID = '$nUserID' and a.MANDANTID = p.MANDANTID and a.LFDNR=p.LFDNR and (a.STATUS='$STATUS_BEZAHLT' or a.STATUS='$STATUS_BEZAHLT_LOGE') order by p.BEGINN desc", $dbh);
$row_platz = mysql_fetch_array($sitz);
//echo mysql_errno().": ".mysql_error()."<BR>";

// Clan raussuchen
$result2 = mysql_db_query ($dbname, "select c.CLANID, c.NAME from CLAN c, USER_CLAN uc where c.CLANID = uc.CLANID and uc.USERID='$nUserID' and uc.MANDANTID='$nPartyID' and uc.AUFNAHMESTATUS='$AUFNAHMESTATUS_OK'",$dbh);
$row2    = mysql_fetch_array($result2);
$sClan   = db2display($row2['NAME']);
$nClanID = $row2['CLANID'];


//---------- Benutzerdetails

//Benutzerdetails  inner Table
	echo "<TABLE cellspacing=0 cellpadding=3 border=0 width=\"100%\">";
	
//Benutzerdetails Part
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP><b>Userdaten:</b><hr noshade class=newsline></TD>";
	echo "  </TR> ";
	
//Nickname Anzeigen & Userpic eingebunden
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">$str[login]</TD><TD><span class=\"clantag\">".(isset($row['CLAN']) ? db2display($row['CLAN']) : '')."</span>" .db2display($row['LOGIN'])."</TD>";
	echo "    <TD rowspan=7 valign=\"top\" align=\"right\"><b>Bild / Avatar</b><br><div style=\"margin-right: 15px;\"><br>";
		displayUserPic($nUserID);
		echo "</a><br><br></div></TD>";
	echo "  </TR>";
	
//User-ID Anzeigen //TODO Datenbankzugriff hier eigentlich unschön
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">User-ID:</TD><TD>$row[USERID]</TD>";
	echo "  </TR>"; 
	
//Vorname Anzeigen //TODO Datenbankzugriff hier eigentlich unschön
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">Vorname:</TD><TD>$row[NAME]</TD>";
	echo "  </TR>"; 
	
//Wohnort anzeigen
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">$str[ort]</TD><TD>".db2display($row['ORT']) ."</TD>";
	echo "  </TR>"; 
	
//Clan Anzeigen
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">Clan:</TD><TD><a href=\"clandetails.php?nClanID=$row2[CLANID]\">$row2[NAME]</a></TD>";
	echo "  </TR>";
	
//Datum der Registrierung anzeigen
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">Registriert seit:</TD><TD>".date("d.m.Y", strtotime($row['WANNANGELEGT']))."</TD>";
	echo "  </TR>";

//NGL-ID	
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">NGL-ID:</TD><TD>".db2display($row['NGL_SINGLE'])."</TD>";
	echo "  </TR>";

//WWCL-ID	
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">WWCL-ID:</TD><TD>".db2display($row['WWCL_SINGLE'])."</TD>";
	echo "  </TR>";
	
//Forum Beiträge
	// Anzahl Forenpostings
	$row2 = @mysql_fetch_array(mysql_db_query($dbname, "select count(*) cnt from forum_boards b, forum_content c where b.mandantID='$nPartyID' and c.authorID='$nUserID' and b.boardID=c.boardID", $dbh), MYSQL_ASSOC);
	$anzahlForenpostings = $row2['cnt'];
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">Forum Beitr&auml;ge:</TD><TD> $anzahlForenpostings</TD>";
	echo "  </TR>"; 
	
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP>&nbsp;</TD>";
	echo "  </TR>"; 
	
//Internet Part  
	echo "  <TR>\n";
	echo "    <TD colspan=3 NOWRAP><b>Internet:</b><hr noshade class=newsline></TD>\n";
	echo "  </TR>\n"; 
	
//Kontakt zum Benutzer
	echo "  <TR>";
	echo "    <TD NOWRAP width=\"25%\">$str[kontakt]</TD><TD colspan=2>";
		if (LOCATION == "intranet") {
			echo "<a href=\"JavaScript:openPELAS(".$row['USERID'].")\">PELAS-Mail</a>\n";
		} else {
			echo "<a href=\"kontaktformular.php?nUserID=".$nUserID."\">Kontaktformular</a>\n";
		}
		echo "</TD>";
	echo "  </TR>";
	
//Webseite Anzeigen
	echo "<TR>\n";
	echo "<TD NOWRAP width=\"25%\">$str[homepage]</TD>\n";
	echo "<TD colspan=2>";
		if (strpos ($row['HOMEPAGE'], "://") > 0 ) {
			echo "<A HREF=\"".$row['HOMEPAGE']."\" target=\"_blank\" class=\"inlink\">".db2display($row['HOMEPAGE'])."</A>";
		} else {
			echo db2display($row['HOMEPAGE']);
		}
	echo "</TD>\n";
	echo "</TR>\n";
	 
	
////ICQ Anzeigen  //TODO ICQ machen?
//	echo "  <TR>";
//	echo "    <TD NOWRAP width=\"25%\">ICQ:</TD><TD colspan=2><table cellpadding=0 cellspacing=0 border=0><tr valign=middle><td><a href=\"http://wwp.mirabilis.com/scripts/Search.dll?to=XXXXX\">XXXXX</A> ::&nbsp;</TD><TD><a href=\"http://wwp.mirabilis.com/scripts/Search.dll?to=XXXX\\\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=XXXXX&amp;img=1\" border=\"0\" height=\"14\" alt=\"XXXXX\" width=\"44\"></a></td></tr></table></TD>";
//	echo "  </TR>"; 

	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP>&nbsp;</TD>";
	echo "  </TR>";   
	
////Optionen Part //TODO BuddyListe machen?
//	echo "  <TR>";
//	echo "    <TD colspan=3 NOWRAP><b>Optionen:</b><hr noshade class=newsline></TD>";
//	echo "  </TR>";   
//	echo "  <TR>";
//	echo "    <TD colspan=3 NOWRAP>";
////	echo "		<LI><A HREF=\"/user/?do=new&touser=Suicider&touserid=1701\"><I>Suicider</I> eine private Nachricht senden</A><Br>";
//	echo "		<LI><A HREF=\"/user/?do=buddy_add&id=1701\"><I>"  .db2display($row[LOGIN])."</I> zur Buddylist hinzuf&uuml;gen</A><br></TD>";
//	echo "  </TR>"; 
//	echo "  <TR>";
//	echo "    <TD colspan=3 NOWRAP>&nbsp;</TD>";
//	echo "  </TR>";
	
//Last 5 Postings Part

	$result = mysql_db_query($dbname, "select FROM_UNIXTIME(c.time, '%d.%m') as DATUM, FROM_UNIXTIME(c.time, '%H:%i') as POSTTIME, c.contentID, c.title, c.parent
				from forum_boards b, forum_content c 
				where b.mandantID='$nPartyID' and 
				b.boardID=c.boardID and 
				(b.type=1) and 
				c.hidden != 1 and
				c.authorID = $nUserID
				order by c.time desc 
				limit 5", $dbh);
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP><b>Die 5 letzten Postings im Forum von "  .db2display($row['LOGIN']).":</b><hr noshade class=newsline></TD>";
	echo "  </TR>";   
	echo "    <TR>";
	echo "    <TD colspan=3 NOWRAP><TABLE cellpadding=1 cellspacing=0 border=0>";

	$counter = 0;
	while ($row2 = mysql_fetch_array($result)) {
		$counter = 1;
		// Den Titel des Parents fischen
		if ($row2['parent'] != "-1") {
			$result3 = mysql_db_query($dbname, "select title, contentID
						from forum_content
						where contentID = ".$row2['parent'], $dbh);
			$row3 = mysql_fetch_array($result3);
			$sTitel     = $row3['title'];
			$nContentID = $row3['contentID'];
		} else {
			$sTitel = $row2['title'];
			$nContentID = $row2['contentID'];
		}
		echo "<tr><td>".$row2['DATUM']."</td><td>&nbsp;</td><td>".$row2['POSTTIME']."</td><td>&nbsp;</td><td><a href=\"/forum.php?thread=$nContentID\">".db2display($sTitel)."</a></td></tr>";
	}
	if ($counter == 0) {
	echo "<tr><td>keine</td></tr>";
	}

	echo "    </TABLE></TD>";
	echo "  </TR>"; 
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP>&nbsp;</TD>";
	echo "  </TR>";
	echo "  <TR>";
	
// Besuchte Parties  
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP><b>$str[besuchteparties]</b><hr noshade class=newsline></TD>";
	echo "  </TR>";   
	echo "    <TR>";
	echo "    <TD colspan=3 NOWRAP><TABLE cellpadding=1 cellspacing=0 border=0>";
	$nCounter = 0;
	while ($row=mysql_fetch_array($besuchteParties)) {
		echo " <tr><td> <a href=\"$row[REFERER]\" target=\"_blank\">".db2display($row['NAME'])."</a> <small>(".dateDisplay2Short($row['BEGINN']).")</small></td></tr>";
		$nCounter++;
	}
	if ($nCounter == 0) {
		echo "<tr><td>keine</td></tr>";
	}
	echo "    </TABLE></TD>";
	echo "  </TR>";
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP>&nbsp;</TD>";
	echo "  </TR>";
	
// Event Status Part //TODO Remove or keep?
	echo "  <TR>";
 	echo "    <TD colspan=3 NOWRAP><b>Event Status</b><hr noshade class=newsline></TD>";
	echo "  </TR>";   
	echo "  <TR>";
	echo "    <TD>$partyname :</TD>";
	echo "<TD>";
		if (User::hatRecht("GASTADMIN", $nUserID, $nPartyID)) {
		    echo "innovaLAN Gastadmin";
		} elseif (User::hatRecht("TEAMMEMBER2", $nUserID, $nPartyID)) {
		    echo "innovaLAN Admin";
		} elseif (User::hatRecht("TEAMMEMBER", $nUserID, $nPartyID)) {
		    echo "innovaLAN Trainee";
		} else {
		    echo "Registrierter Benutzer";
	    }
	echo"</TD>";
	echo "</TR>";
	echo " </TR>"; 
	
	if ($anmeldestatus == $STATUS_NICHTANGEMELDET) {
		echo "<tr><td>Status:</td><td>$str[nichtangemeldet]</td></tr>";
	}
	elseif ($anmeldestatus == $STATUS_ANGEMELDET) {
		echo "<tr><td>Status:</td><td>$str[angemeldet]</td></tr>";
	}
	elseif ($anmeldestatus == $STATUS_BEZAHLT||$STATUS_BEZAHLT_LOGE	) {
	  //Zoom auf Ebene
	  $ebene = -1;
	  if($row_platz[REIHE]>0){
      $sql = " select
                EBENE
               from 
                sitzplan_def
               where
                MANDANTID='$nPartyID' AND REIHE = '$row_platz[REIHE]'";
      $res = DB::query($sql);
      if($row = mysql_fetch_row($res)){
        $ebene = $row[0];
      }
	  }
		echo "  <TR>"; 
		echo "      <TD>$str[reihe]</TD><TD width=150>$row_platz[REIHE]</TD>";
	  if($ebene>0){
      echo "<td class=\"pelas_benutzer_prefix\" rowspan=2><a href=sitzplan.php?block=$ebene> Zum Block</a></td>";
    }
		echo "  </TR>"; 
		echo "  <TR>"; 
		echo "      <TD>$str[platz]</TD><TD width=150>$row_platz[PLATZ]</TD>";
		echo "  </TR>";
	}
	elseif ($anmeldestatus == $STATUS_ABGEMELDET) {
		echo "<tr><td>Status:</td><td>$str[abgemeldet]</td></tr>";
	}
	
	echo "  <TR>";
	echo "    <TD>Sitzgruppe:</TD>";
	$usergroup = checkUserSeatgroup ($nPartyID, $_GET['nUserID']);
	if($usergroup){
		$sql = "select
								GRUPPEN_NAME
							from
								sitzgruppe 
							where
								GRUPPEN_ID=$usergroup";
			$res = DB::query($sql);
			if(!$row = mysql_fetch_row($res)){
				PELAS::fehler('Ein Fehler ist bei der Verarbeitung der Daten aufgetreten');			
			} else {
				echo "<TD><a href=sitzgruppen.php?gruppenID=$usergroup>". db2display($row[0]). "</a></TD>";
			}			
	} else {
	  if(sitzPlatzResOffen($nPartyID)){
	    //Evaluieren, ob der Benutzer eingeladen werden kann
		  $watchergroup = checkUserSeatgroup ($nPartyID, $nLoginID);
      if($watchergroup && USER::hatBezahlt($_GET['nUserID'])){
			  echo "<TD>Keine - <a href=sitzgruppen.php?einladen=".$_GET['nUserID'].">Benutzer einladen</a></TD>";
		  }
		  else{
  			echo "<TD>Keine</TD>";	
		  }
		}
	}
	echo "  </TR>";
	
	echo "  <TR>";
	echo "    <TD colspan=3 NOWRAP>&nbsp;</TD>";
	echo "  </TR>";

//Inner Table end
	echo "</TABLE>";
}
}
?>
