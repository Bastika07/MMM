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
require_once "turnier/Turnier.class.php";
include "language.inc.php";

if (!isset($dbh))
	$dbh = DB::connect();

# Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

//Userdaten
if (!isset($_GET['nUserID']) || !is_numeric($_GET['nUserID']) || $_GET['nUserID'] < 0) {
  PELAS::fehler('Ungültige Benutzer-ID!');
} else {
  $result = DB::query("select * from USER where USERID = '".intval($_GET['nUserID'])."'");
  if ($result->num_rows != 1) {
    PELAS::fehler('Kein Benutzer mit dieser ID!');
  } else {

	$benutzerInfo = $result->fetch_array();

	//########################
	// Altes Accounting
	$statusbeschreibung = DB::query("select b.BESCHREIBUNG from STATUS b, ASTATUS a where a.USERID='".intval($_GET['nUserID'])."' and a.MANDANTID=".intval($nPartyID)." and b.STATUSID=a.STATUS");
	$rowStat = $statusbeschreibung->fetch_array();
	$sitz = DB::query("select * from SITZ where USERID='".intval($_GET['nUserID'])."' and MANDANTID='".intval($nPartyID)."' AND RESTYP='$SITZ_RESERVIERT'");
	$row_platz = $sitz->fetch_array();
	//########################
	
	$besuchteParties = DB::query("
	select m.REFERER, p.NAME, p.BEGINN 
	from MANDANT m, ASTATUSHISTORIE a, PARTYHISTORIE p 
	where m.MANDANTID=p.MANDANTID 
		and a.USERID = '".intval($_GET['nUserID'])."' 
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
	order by p.BEGINN desc");

	// Clan raussuchen
	$result2 = DB::query("
		select c.CLANID, c.NAME 
		from CLAN c, USER_CLAN uc 
		where c.CLANID = uc.CLANID 
		and uc.USERID='".intval($_GET['nUserID'])."' 
		and uc.MANDANTID='".intval($nPartyID)."' 
		and uc.AUFNAHMESTATUS='$AUFNAHMESTATUS_OK'
	");
	$row2    = $result2->fetch_array();
	$sClan   = db2display($row2['NAME']);
	$nClanID = $row2['CLANID'];

?>
<table cellspacing="0" cellpadding="0" width="99%">
<tr><td colspan="2">
	
	<div style='
		float:left; 
		width:155px;
		margin-right:15px; 
		background-color:#AAAAAA;
		text-align:center;
		'>
		<div style='padding:5px;'>
		<?php displayUserPic(intval($_GET['nUserID'])); ?>
		</div>
		<div style='background-color:#CCCCCC; padding:5px;'>
		
			<?php 		
			if (LOCATION == "intranet") {
				printf("&nbsp;<a href='JavaScript:openPELAS(%s)'><img src='/gfx/icon_email.gif' align='top' title='Kontakt' border='0'></a>&nbsp;", intval($_GET['nUserID']));
			} else {
				printf("&nbsp;<a href='?page=17&nUserID=%s'><img src='/gfx/icon_email.gif' title='Kontakt' align='top' border='0'></a>&nbsp;", intval($_GET['nUserID']));
			}
			
			if ($benutzerInfo['HOMEPAGE'] != '') {
				printf("&nbsp;<a href='%s'><img src='%s/gfx/icon_home.gif' title='Homepage' align='top' border='0'></a>&nbsp;", db2display($benutzerInfo['HOMEPAGE']), PELASHOST);	
			}
			?>
		</div>
	</div>
	
	<div>
		<?php
		printf("<span style='font-size:1.5em;'>Profil von %s</span><br>", db2display($benutzerInfo['LOGIN']));
		printf("<p>%s %s %s", 
			PELAS::displayFlag($benutzerInfo['LAND']), 
			db2display($benutzerInfo['PLZ']), 
			db2display($benutzerInfo['ORT'])
		);
		printf('<br>Gast seit %s</p>', dateDisplay2Short($benutzerInfo['WANNANGELEGT']));
		
		if ($nClanID > 0) {
			printf("<p>Clan <a href='?page=19&nClanID=%s' class='arrow'>%s</a></p>", $nClanID, db2display($sClan));
		};
		
		if ($benutzerInfo['KOMMENTAR_PUBLIC'] != '') {
			printf('<p>Spiele: %s.</p>', db2display($benutzerInfo['KOMMENTAR_PUBLIC']));	
		};

		?>
		
	</div>
</td>
</tr>

<tr>
<td colspan='2'><img src='/gfx/lgif.gif' width='0' height='25' border='0'></td>
</tr><tr>
<td valign="top" width="35%">
	
	<table class='liste' cellspacing='0' cellpadding='0' width='95%'>
	<tr><th colspan='3'>Ticketzuordnung</th></tr>
		<?php 
		
			if (ACCOUNTING == "OLD") {
				// Sitzplätze altes Accounting
				
				//Zoom auf Ebene
				$ebene = -1;
				if($row_platz['REIHE']>0){
				  $sql = " select
					    EBENE
					   from 
					    SITZDEF
					   where
					    MANDANTID='".intval($nPartyID)."' AND REIHE = '$row_platz[REIHE]'";
				  $res = DB::query($sql);
				  if($row = $res->fetch_row()){
				    $ebene = $row[0];
				  }  
				}
			
				echo "<table><tr><td>$str[reihe]</td><td>$row_platz[REIHE] &nbsp; ";
			
				  if ($ebene > 0 ) {
				    echo "<i>( </i><a href=sitzplan.php?ebene=$ebene&locateUser=$nUserID>zoom</a> <i>)</i>";
				  }
			
				echo "</td></tr>";
			
				echo "<tr><td>$str[platz]</td><td>$row_platz[PLATZ] &nbsp; ";
			
				  if ($ebene > 0 ) {
				    echo "<i>( </i><a href=sitzplan.php?ebene=$ebene&locateUser=$nUserID>zoom</a> <i>)</i>";
				  }
			
				echo "</td></tr></table>";

			} else {
				// Neues Accounting, Tickets heraussuchen
				$sql = "select
					  t.ticketId,
					  t.sitzReihe,
					  t.sitzPlatz,
					  t.userId,
					  t.eingecheckt
					from
					  acc_tickets t,
					  party p
					where
					  t.partyId   = p.partyId and
					  p.aktiv     = 'J' and
					  t.userId    = '".intval($_GET['nUserID'])."' and
					  t.statusId  = '".ACC_STATUS_BEZAHLT."' and
					  p.mandantId = '".intval($nPartyID)."'
				";
				$res = DB::query($sql);
			
				$counter = 0;
				while ($rowTemp = $res->fetch_array()) {
					if ($counter >= 1) {
						echo "<br>";
					}
			
					echo "<tr><td>Nr. ".PELAS::formatTicketNr($rowTemp['ticketId'])." </td><td>Platz ";
			
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
					echo " <a href=\"?page=9&ebene=$ebene&locateUser=".$rowTemp['userId']."\">";
					echo $rowTemp['sitzReihe']."-".$rowTemp['sitzPlatz'];
					echo "</a></td>";
						if ( $rowTemp['eingecheckt'] == "J") {
						echo "<td bgcolor='green'><b>Checked In</b></td>";
						} else {
						echo "<td bgcolor='red'><b>not Checked In</b></td>";
						}
					echo "</tr>";
			
					$counter++;
				}
				if ($counter == 0) {
					echo "<tr><td colspan='3'>(".$str['keine'].")</td></tr>";
				}
			}
			echo "";
		?>
	</table>
 
</td>
<td valign="top" width="65%" >
	<table class='liste' cellspacing='0' cellpadding='0' width='95%'>
		<tr><th colspan='4'>Angemeldete Turniere</th></tr>
		<?php 
			// ANFANG Turniere (Softbuilder)
			
			// TODO: Übersetzen!
			/* */
		
			$nCounter = 0;
			
			$turnierInfo = TURNIER::getTourneyList($aktuellePartyID);
			$userTurniere = TURNIER::getTourneyListForUser($aktuellePartyID, $nUserID);
			
			foreach ($userTurniere as $turnierid => $turnier) {
				//echo " $turnierInfo[$turnierid]['name'];
				
				if ($turnierInfo[$turnierid]['icon'] != "") {
					$bildStr = "<img src='".$turnierInfo[$turnierid]['icon']."' width='16'>";
				} else {
					$bildStr =  "&nbsp;";
				}
				
				printf("<tr>\n<td><nobr>%s&nbsp;</nobr></td>\n", $bildStr);

				echo "<td> <a href=\"?page=29&turnierid=".$turnierid."\" target=\"_blank\">".db2display($turnierInfo[$turnierid]['name'])."</a>&nbsp;</td> <td>".db2display($turnier['name'])."&nbsp;</td> <td>(<b>$turnier[coins]</b>)</td></tr>";
				
				$nCounter++;
			}
			
			if ($nCounter == 0) {
				echo "<tr><td colspan='4'>(keine)</td></tr>";
			}
			/* */
			// ENDE Turniere
		?>
	</table>
</td>
</tr>
</table>

<br>

<table cellspacing='0' cellpadding='0' class='kasten' width='97%'>
<tr><th>Eingestellte Medien</th></tr>
<tr><td>

		<table cellspacing='0' cellpadding='0'>
		<tr><td valign='top'>

			<table cellspacing='2' cellpadding='2' border='0' style='margin-right:20px'>
			<tr>
				<td>				
					<button style='width:130px; height=120px;' name="Video" type="button" value="Video" onclick="document.location.href='/archiv_upload.php?typ=youtube';">
						<p><img src='<?php echo PELASHOST; ?>/gfx/archiv_icon_videos.png'><br>
						  Video hinzufügen
						</p>
					</button>
				</td>
			</tr><tr>
				<td>
					<button style='width:130px; height=120px;' name="Foto" type="button" value="Foto" onclick="document.location.href='/archiv_upload.php?typ=img';">
						<p><img src='<?php echo PELASHOST; ?>/gfx/archiv_icon_fotos.png'><br>
						  Fotos hinzufügen
						</p>
					</button>
				</td>
			</tr>
			</table>


		</td><td valign='top'>


			<table cellspacing='2' cellpadding='2'><tr>
			<?php
				$i = 0;

				# Archive laden
				$q = "select m.REFERER, a.*, p.beschreibung AS partyname, p.terminVon AS terminVon
					from MANDANT m,
					ARCHIV a
					join party p
					on a.PARTYID = p.partyId
					where userId = '$nUserID'
					and m.MANDANTID=p.mandantId
					AND locked = 'no'
					order by TerminVon DESC, TYP DESC, a.BESCHREIBUNG
				";
				$Archive = DB::getRows($q);

				foreach ($Archive as $archiv) {
						$i++;
					if($archiv['TYP'] == 'img') {
						printf("<td><a href='?page=14&selectPartyID=%s&selectTyp=img&archivID=%s' title='Fotos ansehen'><img src='%s' width='129' border='0'></a><br>

									<table callpadding='1' cellspacing='1'><tr><td valign='top'>
										<img src='%s/gfx/camera.png' border='0' title='Bilder ansehen'>
									</td><td style='font-size:0.6em;'>
										%s<br>
										<a href='%s'>%s</a> (%s)
									</td></tr></table>

								</td>",

								$archiv['PARTYID'],
								db2display($archiv['ARCHIVID']),
								$sPelasHost."archiv/_img/".db2display($archiv['ARCHIVID'])."/".getFirstImageURL(db2display($archiv['ARCHIVID'])), 
								PELASHOST,
								db2display($archiv['KOMMENTAR']),
								db2display($archiv['REFERER']),
								db2display($archiv['partyname']),
								dateDisplay2Short($archiv['terminVon'])
							);
						if($i % 2 == 0) echo "</tr><tr>";
					} elseif($archiv['TYP'] == 'youtube') {
						printf("<td><object width='155' height='125'>
									<param name='movie' value='http://www.youtube-nocookie.com/v/%s&hl=de&fs=1&rel=0'></param>
									<param name='allowFullScreen' value='true'></param>
									<param name='allowscriptaccess' value='always'></param>
									<embed src='http://www.youtube-nocookie.com/v/%s&hl=de&fs=1&rel=0' 
										type='application/x-shockwave-flash' 
										allowscriptaccess='always' 
										allowfullscreen='true' 
										width='155' height='125'>
									</embed></object><br>

									<table callpadding='1' cellspacing='1'><tr><td valign='top'>
										<img src='%s/gfx/camera.png' border='0' title='Video ansehen'>
									</td><td style='font-size:0.6em;'>
										%s<br>
										<a href='%s'>%s</a> (%s)
										<a class='arrow' href='/archiv.php?selectPartyID=%s&selectTyp=youtube&archivID=%s'>Fullsize</a>
									</td></tr></table>

									</td>",
							$archiv['LINK'],
							$archiv['LINK'],
							PELASHOST,
							db2display($archiv['KOMMENTAR']),
							db2display($archiv['REFERER']),
							db2display($archiv['partyname']),
							dateDisplay2Short($archiv['terminVon']),
							$archiv['PARTYID'],
							db2display($archiv['ARCHIVID'])
						);
						if($i % 2 == 0) echo "</tr><tr>";
					}
				} 

				if ($i == 0) { echo "<td>(keine)</td></tr>"; } else { echo "</tr>"; }
			?>
			</table>
	

		</td>
		</tr>
		</table>
	
</td>
</tr>
</table>

<br>

<table cellspacing='0' cellpadding='0' border='0' width='97%'>
<tr><td valign='top'>

	<table class='liste' cellspacing='0' cellpadding='0'>
	<tr><th colspan='2'>Besuchte Partys</th></tr>
		<?php 
			$nCounter = 0;
				
			// Workaround: Partys von neuem Accounting auslesen
			$sql = "select distinct
					m.REFERER,
					p.beschreibung,
					p.terminVon
				from
					party p,
					MANDANT m,
					acc_tickets t,
					acc_ticket_typ y
				where
					m.MANDANTID = p.mandantId and
					p.terminBis < now() and
					t.partyId = p.partyId and
					t.statusId = '".ACC_STATUS_BEZAHLT."' and
					t.userId = '".intval($_GET['nUserID'])."' and
					t.typId = y.typId and
					y.translation is not NULL
				order by
					p.terminVon desc
				";
			$res = DB::query($sql);
			while ($rowTemp=$res->fetch_array()) {
				echo " <tr><td> <a href=\"$rowTemp[REFERER]\" target=\"_blank\">".db2display($rowTemp['beschreibung'])."</a> <small>(".dateDisplay2Short($rowTemp['terminVon']).")</small></td></tr>";
				$nCounter++;
			}
		
			// Nun die alten Partys
			while ($row=$besuchteParties->fetch_array()) {
				echo " <tr><td> <a href=\"$row[REFERER]\" target=\"_blank\">".db2display($row['NAME'])."</a> <small>(".dateDisplay2Short($row['BEGINN']).")</small></td></tr>";
				$nCounter++;
			}
			if ($nCounter == 0) {
				echo "<tr><td>keine</td></tr>";
			}
		?>
	</table>

</td><td valign='top' align='right'>
<?php
	// Supporterpässe anzeigen, wenn vorhanden
	$sql = "
		select 
			count(s.passId) as anzahl,
			p.supporterPassPicBig
		from
			party p,
			acc_supporterpass s
		where
			s.mandantId = '".intval($nPartyID)."' and
			s.ownerId = '".intval($_GET['nUserID'])."' and
			s.partyId = p.partyId and
			s.statusId = '".ACC_STATUS_BEZAHLT."'
		group by
			p.partyId
		order by
			p.partyId desc
		";
		$res = DB::query($sql);
		while ($rowTemp=$res->fetch_array()) {
				echo "<img vpsace='5' hspace='5' src='".db2display($rowTemp['supporterPassPicBig'])."' title='Supporterpässe: ".$rowTemp['anzahl']." Stück'> &nbsp; ";
		}
?>

</td>
</tr>
</table>

<?php
  }
}

//Nur für neues Archiv
function getFirstImageURL($archivid) {
  $dirname = PELASDIR."archiv/_img/".$archivid;
  if (is_dir($dirname)) {
    $glob = glob($dirname."/tn*");
    foreach ($glob as $val) {
     return basename($val);
    }
  }
}
?>
