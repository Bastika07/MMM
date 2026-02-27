<?php
require_once "dblib.php";
include_once "format.php";
include_once "pelasfunctions.php";
include "language.inc.php";

if ($AnzahlProSeite < 20 || $AnzahlProSeite > 100) {
	$AnzahlProSeite = 30;
}

$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

function ShowBlaettern()
{
	global $iSortierung, $sAddQuery, $limitListe, $str, $AnzahlDS_blaettern, $AnzahlProSeite, $AktSeite;
	echo "<tr><td colspan=\"5\" align=\"right\" class=\"TNListe\">";
	$counter=0;
	for ($i=0;$i<= (ceil($AnzahlDS_blaettern/$AnzahlProSeite)-1);$i++) {
		$counter++;
		if ($i!= 0) { echo "&nbsp;|&nbsp;"; }
		echo "<a href=\"?page=8&AktSeite=".$i.$_GET['sAddQuery']."&iSortierung=".$_GET['iSortierung']."\" class=\"TNLink\">";
		if ($i==$_GET['AktSeite']) { echo "<b>"; }
		echo ($i*$AnzahlProSeite+1)."-".($i*$AnzahlProSeite+$AnzahlProSeite)."</a>";
		if ($i==$_GET['AktSeite']) { echo "</b>"; }
		if ($counter>=6) {
			$counter=0;
			echo "<br>";
		}
	}
	echo "&nbsp;|&nbsp;<a href=\"?page=8&AktSeite=-1".$_GET['sAddQuery']."&iSortierung=".$_GET['iSortierung']."\" class=\"TNLink\">";
	if ($_GET['AktSeite'] == -1) { echo "<b>"; }
	echo "$str[alle]</a> ";
	if ($_GET['AktSeite']== -1) { echo "</b>"; }
	echo "</td></tr>";
}

// ***********************************

if ($aktuellePartyID < 1) {
	echo "<p class=\"fehler\">Keine aktuelle Party gefunden.</p>";
} else {
	//ok, Script

	$limitString = "";

	// Ticketfilter?!
	if (isset($_GET['ticketFilter']) && is_numeric($_GET['ticketFilter']) ) {
		$limitString .= " and y.typId = '".intval($_GET['ticketFilter'])."'";
	}

	//suchen angewaehlt?
	if (isset($_POST['limitListe'])) {
		$limitString .= " and u.LOGIN like '%".safe($_POST['limitListe'])."%'";
	} else {
		$limitListe = '';
	}
/* 20.11.2013 - azi
 * evtl. XSS & Injection anfällig 	
	if ($limitListe != "") {
		$limitString .= " and u.LOGIN like '%$limitListe%'";
	}
*/

	// Sortierung
	if ($_GET['iSortierung'] == "nick") {
		$sAddSort = "order by u.LOGIN";
	} else {
		$sAddSort = "order by t.ticketId";
	}

	if ($_GET['AktSeite'] == "-1") {
		$sAddWhere = "";
	} else {
		$sAddWhere = "limit ".(intval($_GET['AktSeite'])*$AnzahlProSeite).",".$AnzahlProSeite;
	}

	// Userliste holen
	// Es werden alle zugeordneten angezeigt, anfangs ist der Inhaber immer der zugeordnete
	// Es werden sowohl offene, als auch bezahlte tickets gezeigt. Keine stornierten
	$sql = "select 
		  u.USERID, 
		  u.LOGIN, 
		  u.KOMMENTAR_PUBLIC, 
		  u.HOMEPAGE,
		  u.LAND,
		  t.statusId,
		  t.ticketId,
		  t.sitzReihe,
		  t.sitzPlatz
		from 
		  USER u, 
		  acc_tickets t,
		  acc_ticket_typ y
		where 
		  y.typId    = t.typId and
		  t.userId   = u.USERID and
		  y.partyId  = '$aktuellePartyID' and
		  t.statusId = ".ACC_STATUS_BEZAHLT."
		$limitString 
		$sAddSort $sAddWhere
	      ";
		  
	$result = DB::query($sql);

	// Gesamtanzahl der Datensätze feststellen für blättern
	$sql = "select 
		  u.USERID
		from 
		  USER u, 
		  acc_tickets t,
		  acc_ticket_typ y
		where 
		  y.typId    = t.typId and
		  t.userId   = u.USERID and
		  y.partyId  = '$aktuellePartyID' and
		  t.statusId = ".ACC_STATUS_BEZAHLT."
		$limitString 
	      ";
	$result2 = DB::query($sql);
	if ($_GET['AktSeite'] == "") {
		$_GET['AktSeite'] = 0;
	}
	$AnzahlDS_blaettern = $result2->num_rows;
	if ($AnzahlDS_blaettern == 0) {
		$AnzahlDS_blaettern = 1;
	}
	?>	
	
	<p>
	<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr><td valign="top">
		<p><?=$str['verfuegbar']?>:<br>
		<?php
		// Statistik
		
		// Ticketfilter?!
		if (isset($_GET['ticketFilter']) && is_numeric($_GET['ticketFilter']) ) {
			$limitTyp = " and typId = '".intval($_GET['ticketFilter'])."'";
		} else {
			$limitTyp = "";
		}
		
		$sql = "select 
			  *
			from 
			  acc_ticket_typ
			where 
			  partyId     = '$aktuellePartyID' and
			  translation > 1 and
				translation != '$STATUS_BEZAHLT_SUPPORTERPASS'
			$limitTyp
		";
		$res = DB::query($sql);
		// durchloopen und Tickettypen einzeln ausgeben
		while ($rowTemp = $res->fetch_array()) {
			if ($sLang == "en") {
				// Englische Artikelbeschreibung zeigen
				$sText = db2display($rowTemp['beschreibung']);
			} else {
				// Deutsch
				$sText = db2display($rowTemp['kurzbeschreibung']);
			}
			echo verfuegbareTickets($rowTemp['typId'], $aktuellePartyID)." $str[of] ".$rowTemp['anzahlVorhanden'].": ".$sText."<br>";
		}

		?>
		</p>
	</td><td align="right" valign="top">
		<form method="post" action="?page=8">
		<?= csrf_field() ?>
		<p><input type="text" name="limitListe" size="20" maxlength="20" value="<?=$_POST['limitListe'];?>"> <input type="submit" value="<?=$str['TN_Suchen']?>"></p>
		</form>
	</td></tr>
	</table>
	</p>



	<table class="rahmen_allg" cellpadding='1' cellspacing='1' border='0' width="100%">
	<?php

		ShowBlaettern();

		?>

		<tr>
		<td class="TNListe" width="15%"><b><a href="?page=8&AktSeite=<?=$_GET['AktSeite'];?><?=$_GET['sAddQuery'];?>" class="TNLink"><?=$str['acc_ticketnr']?></a></b></td>
		<td class="TNListe" width="35%"><b><a href="?page=8&AktSeite=<?=$_GET['AktSeite'];?><?=$_GET['sAddQuery']?>&iSortierung=nick" class="TNLink">Login</a></b></td>
		<td class="TNListe" width="35%"><b>Clan</b></td>
		<td class="TNListe" width="15%"><b><?=$str['sitzplatz']?></b></td>
		</tr>

		<tr><td class="Header_Separator" colspan="4"><img src="/gfx/lgif.gif" width="1" height="1"></td></tr>

		<?php



		//echo DB::$link->errno.": ".DB::$link->error."<BR>";

		while ($row = $result->fetch_array()) {
			$tempULogin = $row['LOGIN'];
			if (strlen($tempULogin) > 30 ) {
				$Anzeige_Name = db2display(substr( $tempULogin, 0, 30)."...");
			} else {
				$Anzeige_Name = db2display($tempULogin);
			}

			echo "<tr>";
			echo "<td class=\"TNListeTDA\">".PELAS::formatTicketNr($row['ticketId'])."</td>\n";
			echo "<TD class=\"TNListeTDB\">";
			echo PELAS::displayFlag($row['LAND'])." <a href=\"?page=4&nUserID=$row[USERID]\" class=\"inlink\">$Anzeige_Name</a></TD>";


			echo "<TD class='TNListeTDA'>";		
			// Clan raussuchen
			$result2 = DB::query("select c.CLANID, c.NAME from CLAN c, USER_CLAN uc where c.CLANID = uc.CLANID and uc.USERID=$row[USERID] and uc.MANDANTID=$nPartyID and uc.AUFNAHMESTATUS='$AUFNAHMESTATUS_OK'");
			$row2    = $result2->fetch_array();
			$sClan   = db2display($row2['NAME']);
			$nClanID = $row2['CLANID'];
			if (strlen($sClan) > 22 ) {
				$sClan = substr( $sClan, 0, 22)."...";
			}

			if ($nClanID > 0) {
				echo "<a href=\"?page=19&nClanID=$nClanID\" class=\"inlink\">$sClan</a>";
			} else {
				  echo "&nbsp;";
			}  	
			
			echo "<td class='TNListeTDB'>";
			if ($row['sitzReihe'] > 0) {
				$sql = "select 
					  EBENE
					from 
					  SITZDEF
					where 
					  MANDANTID ='$nPartyID' and
					  REIHE     = ".$row['sitzReihe'];
				$resTemp2 = DB::query($sql);
				$rowTemp2 = $resTemp2->fetch_array();
				$ebene   = $rowTemp2['EBENE'];
				echo "<a href=\"?page=13&ebene=$ebene&locateUser=".$row['USERID']."\">";
				echo $row['sitzReihe']."-".$row['sitzPlatz'];
				echo "</a>";
				
			} else {
				echo "<i>(</i>kein<i>)</i>";
			}
			echo "&nbsp;</td></TR>\n";
		  }
		  echo "</TD>";

	ShowBlaettern();


	echo "</table>";
	echo "<p>".$str['acc_nurbezahlte']."</p>";
}

?>
