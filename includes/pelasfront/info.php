<?php
######################################################
# PELAS-Datei: info.php
# 
# Zeigt Infos und Medien aus dem Archiv zur Party an
# Übernimmt auch alle Frontend Archiv-Funktionen
#
# Variablen, die übergeben werden können:
#
# $seitenBreite: Breite der Tabellen in em (Def: 40em)
#
######################################################

include_once "dblib.php";
include_once "format.php";
include_once "upload.php";
include_once "session.php";
include_once "language.inc.php";

# Turniersystem-includes
include_once 't_compat.inc.php';
include_once "turnier/Turnier.class.php";
include_once "turnier/TeamSystem.class.php";
include_once "turnier/TurnierGroup.class.php";

$dbh = DB::connect();

# Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);
if (!isset($archivId)) {$archivId = -1;}

$archivId = intval($archivId);

$tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

if (isset($hideZuschauer)) {
  if ($hideZuschauer != 1) {
    $hideZuschauer = 0;
  }
}

if (!isset($_GET['archivId'])) {
	$anzeigePartyID = $aktuellePartyID;
} else {
	$anzeigePartyID = $archivId;
}

# Partydaten für Anzeige holen
$q = "select *
	from party
	where partyId = '$anzeigePartyID'
";
$partyData = DB::getRow($q);

# Ticketdaten für Anzeige holen
$q = "select *
	from acc_ticket_typ
	where partyId = '$anzeigePartyID'
	and translation is not NULL
  and translation != '$STATUS_BEZAHLT_SUPPORTERPASS'
";
$ticketData = DB::getRows($q);

# Alle Partys holen
$q = "select *
	from party
	where mandantId = '$nPartyID'
	order by terminBis desc
";
$archivData = DB::getRows($q);


# Archive laden

// 1. Schritt: Inhalte der Selektierten Party laden
$q = "select *
	from ARCHIV
	where partyId = '$anzeigePartyID'
	AND locked = 'no'
	order by WANNANGELEGT desc, TYP DESC, BESCHREIBUNG
";
$Archive_1 = DB::getRows($q);

// 2. Schritt: Inhalte von älteren Partys laden
$q = "select *
	from 
		ARCHIV
	where
		locked = 'no' and
		MANDANTID = '$nPartyID' and
		PARTYID != '$anzeigePartyID'
	order by
		PARTYID desc,
		WANNANGELEGT desc,
		TYP DESC,
		BESCHREIBUNG
	limit 15
";
$Archive_2 = DB::getRows($q);

// Beide Arrays zusammenfügen
$Archive = array_merge($Archive_1, $Archive_2);


# Turnierdatensätze holen
$turniere = Turnier::getTourneyList($anzeigePartyID);
$groups = TurnierGroup::getGroups();

# Bild-URL für Altersfreigabe ermitteln
switch ($partyData['mindestalter']) {
	case 0: $bildUrl = PELASHOST."/gfx/ab0.png";
	break;
	case 16: $bildUrl = PELASHOST."/gfx/ab16.png";
	break;
	case 18: $bildUrl = PELASHOST."/gfx/ab18.png";
	break;
}

//Nut für neues Archiv
function getFirstImageURL($archivid) {
  $dirname = PELASDIR."archiv/_img/".$archivid;
  if (is_dir($dirname)) {
    $glob = glob($dirname."/tn*");
    foreach ($glob as $val) {
     return basename($val);
    }
  }
}

// Checken ob die Party-ID zur Mandant-ID passt, ansonsten anzeige verweigern
if ($partyData['mandantId'] != $nPartyID) {
	echo "<p class='fehler'>Ungültige Party-ID. Ausführung abgebrochen.</p>";
} else {
?>



<?php
/*
# Medienliste deaktiviert

<table cellspacing='0' cellpadding='0' class='kasten' width='180' align='right' style='margin-left:10px;'>
<tr><th>Medien</th></tr>
<tr><td style="text-align:left; padding:12px; font-size:11px;">
	
	<?php if (LOCATION <> "intranet") { ?>
	<a href='/archiv_upload.php'><img src='<?=PELASHOST ?>/gfx/film_add.png' border='0' title='Medien hochladen'> Eigene Medien hochladen!</a><hr width='95%'><br>
	<?php }
	$i = 1;
	foreach ($Archive as $archiv) {
	if ($i <= 9) {
   			if($archiv['TYP'] == 'img') {
			printf("<a href='/archiv.php?selectPartyID=%s&selectTyp=img&archivID=%s'><img src='%s' width='155' border='0'></a><br><img src='%s/gfx/camera.png' border='0' title='Bilder ansehen'> %s<br><br>",
   					$archiv['PARTYID'],
   					db2display($archiv['ARCHIVID']),
				$sPelasHost."archiv/_img/".db2display($archiv['ARCHIVID'])."/".getFirstImageURL(db2display($archiv['ARCHIVID'])), 
				PELASHOST,
   					db2display($archiv['KOMMENTAR'])
   				);
		} elseif($archiv['TYP'] == 'youtube') {
			printf("<object width='155' height='125'><param name='movie' value='http://www.youtube-nocookie.com/v/%s&hl=de&fs=1&rel=0'></param><param name='allowFullScreen' value='true'></param><param name='allowscriptaccess' value='always'></param><embed src='http://www.youtube-nocookie.com/v/%s&hl=de&fs=1&rel=0' type='application/x-shockwave-flash' allowscriptaccess='always' allowfullscreen='true' width='155' height='125'></embed></object><br><a href='/archiv.php?selectPartyID=%s&selectTyp=youtube&archivID=%s'><img src='%s/gfx/film.png' border='0' title='Video ansehen'> %s</a><br><br>",
				$archiv['LINK'],
				$archiv['LINK'],
				$archiv['PARTYID'],
   					db2display($archiv['ARCHIVID']),
				PELASHOST,
   					db2display($archiv['KOMMENTAR'])
			);
		}
		$i++;
	} else {
   			if($archiv['TYP'] == 'img') {
			printf("<a href='/archiv.php?selectPartyID=%s&selectTyp=img&archivID=%s'><img src='%s/gfx/camera.png' border='0' title='Bilder ansehen'> %s</a><br>",
    				$archiv['PARTYID'],
   					db2display($archiv['ARCHIVID']),
   					PELASHOST,
				db2display($archiv['KOMMENTAR'])
   				);
		} elseif($archiv['TYP'] == 'youtube') {
  					printf("<a href='/archiv.php?selectPartyID=%s&selectTyp=youtube&archivID=%s'><img src='%s/gfx/film.png' border='0' title='Video ansehen'> %s</a><br>",
    				$archiv['PARTYID'],
   					db2display($archiv['ARCHIVID']),
   					PELASHOST,
				db2display($archiv['KOMMENTAR'])
   				);
		}
	}#if
	}#foreach
	?>
	
</td>
</tr>
</table>

*/
?>


<img align='right' src='<?php echo $bildUrl; ?>' title='Mindestalter: <?php echo $partyData['mindestalter']; ?> Jahre' width='115'>

<div class='factlist'>
   <div class='factlist_header'><?php echo $str['infoheadline']." ".db2display($partyData['beschreibung']); ?></div>
	<?php
	$timeArray = date("w",strtotime($partyData['terminVon']));
	echo $str['beginn'].": ".$tage[$timeArray].", ".dateDisplay2($partyData['terminVon']); 
	?> Uhr<br />
	<?php 
	$timeArray = date("w",strtotime($partyData['terminBis']));
	echo $str['ende'].": ".$tage[$timeArray].", ".dateDisplay2($partyData['terminBis']); 
	?> Uhr<br />
	<?php echo $partyData['teilnehmer']." ".$str['teilnehmer']; ?><br />
	<?php echo $str['mindestalter'].": ".$partyData['mindestalter']." ".$str['jahre']; ?><br />
	Location: <?php echo db2display($partyData['location']); ?><br />
	<?php echo db2display($partyData['locationStrasse']).", "; ?> 
	<?php echo db2display($partyData['locationPLZ'])." ".db2display($partyData['locationOrt']); ?><br />
	<?php
    if ($hideZuschauer != 1) {
      echo db2display($partyData['begleitereintritt'])." Euro ".$str['begleitertext']."<br />";
    }
  ?>
</div>

<br><br>

<table>
<tr>
<td align='center'>
	<table class='liste'>
		<tr><th><?= $str['ticketsundeintritt']; ?></th></tr>
		<?php 
		foreach ($ticketData as $ticket) {
			if ($sLang == "en") {
				printf('<tr><td><b>%s ---> %s Euro</b> <br>%s</td></tr>', db2display($ticket['kurzbeschreibung']), $ticket['preis'], $ticket['beschreibung']);
			} else {
				printf('<tr><td><b>%s ---> %s Euro</b> <br>%s</td></tr>', db2display($ticket['kurzbeschreibung']), $ticket['preis'], $ticket['beschreibung']);			}
		}
		?>
	</table>
</td></tr>
</table>


<?php
/*
# Turnierliste hier deaktiviert

<table class='liste' cellspacing="0" cellpadding="0" border="0">
	<tr><th colspan='5'>Turniere (Ergebnisse)</th></tr>
		<?php 		
		$groupid = 0;
		foreach ($turniere as $turnierInfo) {
			if ($turnierInfo['pturnierid'] == 0) {
				if ($groupid != $turnierInfo['groupid']) {
					if ($groups[$turnierInfo['groupid']]['flags'] & GROUP_SHOW) {
						$groupid = $turnierInfo['groupid'];
						printf("<tr><td colspan='5' style='text-align:left; padding:8px;'><b>%s</b></td></tr>\n",db2display($groups[$turnierInfo['groupid']]['name']));
					}
				}
				if ($turnierInfo['icon'] != "") {
					$bildStr = "<img src='".$turnierInfo['icon']."' width='16'>";
				} else {
					$bildStr =  "&nbsp;";
				}
				printf("<tr>\n<td>%s&nbsp;</td>\n", $bildStr);
				printf("<td>%s &nbsp; </td>\n", db2display($turnierInfo['name']));
				printf("<td><a href='/turnier/turnier_detail.php?turnierid=%s'>
					<img src='%s/gfx/icon_user.gif' border='0' title='Turnierdetails und Teilnehmer'></a></td>\n", $turnierInfo['turnierid'], PELASHOST);
				printf("<td><a href='/turnier/turnier_tree.php?turnierid=%s'>
					<img src='%s/gfx/icon_network.gif' border='0' title='Turnierbaum'></a></td>\n", $turnierInfo['turnierid'], PELASHOST);
				printf("<td><a href='/turnier/turnier_ranking.php?turnierid=%s'>
					<img src='%s/gfx/calendar.gif' border='0' title='Ranking'></a></td>\n", $turnierInfo['turnierid'], PELASHOST);
				echo "</tr>\n";
			}
		}
		?>
	<tr>
		<td colspan='4' style='border:none;'>
			<table cellspacing='5' cellpadding='2'>
			<tr>
				<td style='border:none;'><img src='<?php echo PELASHOST; ?>/gfx/icon_user.gif' title='Turnierdetails und Teilnehmer'> <small>Details/ Teilnehmer</small></td>
				<td style='border:none;'><img src='<?php echo PELASHOST; ?>/gfx/icon_network.gif' title='Turnierbaum'> <small>Baum</small></td>
				<td style='border:none;'><img src='<?php echo PELASHOST; ?>/gfx/calendar.gif' title='Ranking'> <small>Ranking</small></td>
			</tr>
			</table>
		</td>
	</tr>
</table>

<br>
			
<h2>Andere Partys aufrufen</h2>
<p>
<?php
	foreach ($archivData as $archiv) {
   		printf("<a class='arrow' href='%s'>%s</a> %s bis %s (%s Plätze)<br />",
   			$_SERVER['SCRIPT_NAME']."?archivId=".$archiv['partyId'],
	    	db2display($archiv['beschreibung']),
		    dateDisplay2Short($archiv['terminVon']),
		    dateDisplay2Short($archiv['terminBis']),
		    $archiv['teilnehmer']
		   );
	}
?></p>

<p class='kasten'>Ältere Fotos und Turnierergebnisse sind im  
	<a class='arrow' href='/archiv.php'>alten Archiv</a> verfügbar.
</p>
		
<?php

*/

}
?>