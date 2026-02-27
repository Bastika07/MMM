<?php
require('controller.php');
require_once('dblib.php');
require_once('security.php');
$iRecht = 'TEAMMEMBER';
require_once 'checkrights.php';
include('format.php');
include('admin/vorspann.php');


$SHOW_MAILINGS = 5;


// Rausfinden, ob gerade eine Party läuft
$runningParty = false;
$sql = "select
				count(*) as anzahl,
				cast(p.beschreibung AS CHAR) as beschreibung,
				p.terminVon,
				p.terminBis,
				p.teilnehmer,
				p.partyId
		from 
				party p
		where 
			DATE(terminVon) <= CURDATE()
			AND DATE(terminBis) >= CURDATE()";
			
$row = DB::getRow($sql);
if ($row && $row['anzahl'] > 0)
	$runningParty = true;
		

?>
<table width="100%" height="100%">
  <tr>
    <td align="center">
      <img src="gfx/logo_gross.gif" border="0">
      <br><br><br />

<?php
# Party-Termine ausgeben
if (User::hatRecht('TEAMMEMBER')) {
?>


	<h2>Partys und Termine</h2>
	
<table cellspacing="1" cellpadding="3" border="0"  style="width:50%;">
<tr><th>Party</th><th>Datum</th><th>Pl&auml;tze</th><th>Bezahlt*</th>
<?php	
if ($runningParty === true)
{
	echo "<th width=\"55\">Begleiter</th>";
	echo "<th width=\"55\">Anwesend</th>";
}
?>
</tr>
<?php
	$sql = "select
			  cast(p.beschreibung AS CHAR) as beschreibung,
			  p.terminVon,
			  p.terminBis,
			  p.teilnehmer,
			  p.partyId,
				-1 as event
			from 
			  party p
			where 
			  DATE_SUB(CURDATE(),INTERVAL 10 DAY) <= p.terminBis
			
			UNION
			
				select 
					e.description as beschreibung,
					FROM_UNIXTIME(e.start) as terminVon,
					FROM_UNIXTIME(e.end) as terminBis,
					'-' as teilnehmer,
					-1 as partyId,
					event as event
				from
					ANWESENHEITEN_EVENTS e
				where 
					e.start >= ".(time()-500000)."
					AND e.show_calendar > 0
					
				order by
			  	terminVon";
					
	$res = DB::query($sql);         
	while($row = $res->fetch_array()) {
		if ($tclass == "dblau") {
		  $tclass = "hblau";
		} else {
		  $tclass = "dblau";
		}
		echo "<tr><td class=\"$tclass\">".htmlspecialchars($row['beschreibung']).(($row['event'] != -1) ? " (Team)" : "")."</td>";

		if ($row['partyId']	 > 0) {
			// Anzahl verkaufte
			$sql = "select
					  count(t.ticketId) as tickets
					from 
					  acc_ticket_typ y,
					  acc_tickets t
					where 
					  y.partyId  = '".$row['partyId']."' and
					  y.typId    = t.typId and
					  t.statusId = ".ACC_STATUS_BEZAHLT."
				";
			$rowStats = DB::getOne($sql);
			
			// Anzahl Freikarten 2
			$sql = "SELECT
							  SUM(b.anzahl) anzahl
							FROM 
							  acc_bestellung b,
							  acc_ticket_typ t
							WHERE 
							  b.zahlungsweiseId = 4 and
  							b.partyId  = ".$row['partyId']." and
  							b.status = ".ACC_STATUS_BEZAHLT." and
  							b.ticketTypId = t.typId and
  							t.translation > 0
			";
			$anzahlFreikarten = DB::getOne($sql);
			if ($anzahlFreikarten <1) {
				$anzahlFreikarten = "-";
			}
		} else {
			# Aus Anwesenheit anzahl der anwesenden laden
			$sql = "select count(distinct usrid) 
							from ANWESENHEITEN
							where event = ".intval($row['event'])."
							and usrid > 0";
			$rowStats = DB::getOne($sql);
			$anzahlFreikarten = "-";
		}
			
			echo "<td class=\"$tclass\">".dateDisplay2Short($row['terminVon'])." bis ".dateDisplay2Short($row['terminBis'])."</td>
				<td class=\"$tclass\">".$row['teilnehmer']."</td>
				<td class=\"$tclass\">".$rowStats." ($anzahlFreikarten)</td>";

			if ($runningParty === true)
			{
				# Begleiter
				$sql = "SELECT COUNT(*)
					FROM acc_gast_checkin
					WHERE partyId = ".$row['partyId'];
				$anzahlGastCheckins = DB::getOne($sql);
				echo "<td class=\"$tclass\">".$anzahlGastCheckins."</td>";
				# Eingecheckte
				$sql = 'SELECT COUNT(*)
					FROM acc_tickets t
					WHERE t.partyId = ' . $row['partyId'] . '
					  AND t.eingecheckt = "J"';
				$anzahlCheckins = DB::getOne($sql);
				echo "<td class=\"$tclass\">".$anzahlCheckins."</td>";
			}

			echo "</tr>";
	}
?>
</table>
<small>* In Klammern Freikarten. Bei Team-Anwesenheit die Anzahl der angemeldeten Teammitglieder.</small>
<?php
}

	###################### Newsletter-Sendestatistik ###############
	if (LOCATION <> "intranet" && User::hatRecht('TEAMMEMBER2'))
	{
		$sql = "select
					count(distinct u.USERID)
				from
				    USER u, 
					ASTATUS a
				where 
					a.USERID = u.USERID 
					and a.MANDANTID=2 
					and u.NEWSLETTER = 1 
					and u.KEIN_MAILING = 'N'";

		 $anzahlEmpfaenger = DB::getOne($sql); 
?>
	<br /><br />
	<h2>Mailings Sendestatistik (Letzte <?= $SHOW_MAILINGS; ?>)</h2>
  
	<p>Die MultiMadness hat <b><?= $anzahlEmpfaenger; ?></b> Newsletter-Empfänger, bestätigt mit Double-Opt-In.</p>

  <table cellspacing="1" cellpadding="3" border="0" style="width:60%;">
  <tr>
  	<th>ID</th>
    <th>Angelegt</th>
    <th>Betreff</th>
    <th>Letzter Gesendet</th>
    <th>Gesamt</th>
    <th>Gesendet</th>
    <th>Bounced*</th>
    <th>4 Retry**</th>
   </tr>
  
<?php
		$sql = "SELECT
							m.mailing_id,
							(SELECT betreff FROM mailing_execute WHERE mailing_id = m.mailing_id LIMIT 1) as betreff,
							(SELECT wann_angelegt FROM mailing_execute WHERE mailing_id = m.mailing_id LIMIT 1) as wann_angelegt,
							(SELECT COUNT(*)
								FROM mailing_execute
								WHERE mailing_id = m.mailing_id) as anzahl_gesamt,
							(SELECT COUNT(*)
								FROM mailing_execute
								WHERE mailing_id = m.mailing_id
									AND sent IS NOT NULL) as anzahl_gesendet,
							(SELECT COUNT(*)
								FROM mailing_execute
								WHERE mailing_id = m.mailing_id
									AND sent IS NOT NULL
									AND bounced > 0) as anzahl_bounced,
							(SELECT COUNT(*)
								FROM mailing_execute
								WHERE mailing_id = m.mailing_id
									AND sent IS NULL
									AND tries > 0) as anzahl_retrying,
							MAX(m.sent) as letzter_gesendet
						FROM mailing_execute m
						GROUP BY m.mailing_id
						ORDER BY wann_angelegt DESC
						LIMIT ".$SHOW_MAILINGS;

		$res = DB::getRows($sql);

		
		$class = "hblau";
		$count = 0;
		foreach ($res as $key => $row) { ?>
			<tr>
      	<td class="<?= $class; ?>"><?= $row['mailing_id']; ?></td>
        <td class="<?= $class; ?>"><?= date('d.m.Y', strtotime($row['wann_angelegt'])); ?></td>
        <td class="<?= $class; ?>"><?= htmlspecialchars(substr($row['betreff'],0,30)); ?>...</td>
        <td class="<?= $class; ?>"><?= ($row['letzter_gesendet'] != "") ? date('d.m.Y H:i', strtotime($row['letzter_gesendet'])) : "(keiner)"; ?></td>
        <td class="<?= $class; ?>"><?= $row['anzahl_gesamt']; ?></td>
        <td class="<?= $class; ?>"><?= $row['anzahl_gesendet']; ?></td>
        <td class="<?= $class; ?>"><?= $row['anzahl_bounced']; ?></td>
        <td class="<?= $class; ?>"><?= $row['anzahl_retrying']; ?></td>
      </tr>
      
<?php	($class == "hblau") ? $class = "dblau" : $class = "hblau";
			$count++;
		}
		if ($count == 0) echo "<tr><td class='hblau' colspan='7'>Keine Mailings in der Warteschlange bzw. vor Kurzem versendet.</td></tr>\n";
?>
	</table>

	<small>* Bounced Mails werden nicht verarbeitet. ** Wiederholung ist deaktiviert, da meist dauerhafte Probleme.</small>
<?php
	}

	########################### START Neue Medien #########################
	if (LOCATION <> "intranet" && User::hatRecht('ARCHIVADMIN'))  {
?>
	<br><br>
	<h2>Freischaltbare Medien</h2>

	<table cellspacing="1" cellpadding="3" border="0" style="width:45%;">
	<tr>
		<th>Titel</th>
		<th>Party</th>
		<th>Typ</th>
		<th>Autor</th>
		<th>Angelegt</th>
		<th>Action</th>
	</tr>
	<?php

	$bgc="hblau";

	$sWhere = "select
		     	a.LINK, a.PARTYID, m.REFERER, p.beschreibung as NAME, u.LOGIN, u.EMAIL, 
			a.ARCHIVID, a.USERID, a.TYP, a.LOCKED, a.KOMMENTAR, a.WANNANGELEGT 
		   from 
			MANDANT m, ARCHIV a, USER u, party p, RECHTZUORDNUNG r
		   where 
			m.MANDANTID = a.MANDANTID and 
			a.PARTYID=p.partyid and
			u.USERID=a.USERID and
			r.MANDANTID=a.MANDANTID and p.MANDANTID=r.MANDANTID and r.USERID='".intval($loginID)."' and r.RECHTID='ARCHIVADMIN' 
			and a.LOCKED='yes'
		   order by 
			WANNANGELEGT desc";

	$result = DB::query($sWhere);

	$count = 0;
	while ($row = $result->fetch_array()) {
		$sDatum = $row['WANNANGELEGT'];
		$date2Display = substr($sDatum,8,2).".".substr($sDatum,5,2).".".substr($sDatum,0,4)." ".substr($sDatum,11,2).":".substr($sDatum,14,2);

		echo "<tr>";
		echo "<td class=\"$bgc\">".db2display($row['KOMMENTAR'])."&nbsp;</a></td>";
		echo "<td class=\"$bgc\">".db2display($row['NAME'])."&nbsp;</td>";
		echo "<td class=\"$bgc\">",db2display($row['TYP']),"&nbsp;</td>";
		echo "<td class=\"$bgc\"><a href=\"mailto:".$row['EMAIL']."?subject=Party-Archiv ".db2display($row['NAME'])."\">",db2display($row['LOGIN']),"</a>&nbsp;</td>";
		echo "<td class=\"$bgc\"><nobr>",$date2Display,"</nobr></td>";
		echo "<td class=\"$bgc\"><nobr>";
		
		if ($row['TYP'] == $KATEGORIE_ARCH_LINK) {
			echo "<a href=\"".$row['LINK']."\" target=\"_blank\">Visit</a>";
		} elseif ($row['TYP'] == $KATEGORIE_ARCH_TURNIER) {
			echo "<a href='".PELASHOST."/archiv/".$row['TYP']."/".$row['ARCHIVID'].".phpl' target=\"_blank\">View</a>";
		} else {
			if ($row['LOCKED'] == "yes") {
				echo "<a href='archivadmin2.php?action=preview&id=",$row['ARCHIVID'],"'>Preview</a>";
			} else {
				echo "<a href='".$row['REFERER']."/archiv.php?selectPartyID=".$row['PARTYID']."&selectTyp=".$row['TYP']."&archivID=",$row['ARCHIVID'],"' target=\"_blank\">View</a>";
			}
		}

		echo " | <a href='archivadmin2.php?action=release&id=",$row['ARCHIVID'],"'>Release</a>";
	
		echo " | <a href='archivadmin2.php?action=delete&id=",$row['ARCHIVID'],"'>Del.</a>";

		echo "</nobr></td>";
		echo "</tr>";
		if ($bgc=="hblau") $bgc="dblau"; else $bgc="hblau";
		$count++;
	}

	if ($count == 0)
		echo "<tr><td class='hblau' colspan='6'>Keine freischaltbaren Medien vorhanden.</td></tr>\n";
	?>

	</table>

<?php

	}
	########################### ENDE Neue Medien #########################
	$loadAvg = explode(' ', `cat /proc/loadavg`, 4);
	$loadAvg = implode(', ', array_slice($loadAvg, 0, -1));
?>

<br><br>


	<h2>Geburtstage</h2>
	<table>
	<?php
	zeigeGeburtstage();
	?>
	</table>

<br /><br />

<p align="center"><strong>Load Average:</strong> <?= $loadAvg ?></p>



    </td>
  </tr>
</table>

<?php

# ---------------------------------------------------------------- #

/*
 * Show birthdays within next "$days" days, including "new" age
 * birthday-field has to be like "1980-09-30"
 */
function zeigeGeburtstage($tage=60) {
    /*
    $q = "SELECT ue.USERID, ue.GEBURTSTAG, u.LOGIN,
          TO_DAYS(ADDDATE(ue.GEBURTSTAG, INTERVAL ((YEAR(NOW()) - YEAR(ue.GEBURTSTAG)) + (IF (DAYOFYEAR(ue.GEBURTSTAG) < DAYOFYEAR(NOW()), 1, 0))) YEAR))  - TO_DAYS(NOW()) AS unterschied,
          YEAR(NOW()) - YEAR(ue.GEBURTSTAG) AS age
          FROM USER_EXT as ue, USER as u
          WHERE TO_DAYS(ADDDATE(ue.GEBURTSTAG, INTERVAL ((YEAR(NOW()) - YEAR(ue.GEBURTSTAG)) + (IF (DAYOFYEAR(ue.GEBURTSTAG) < DAYOFYEAR(NOW()), 1, 0))) YEAR))  - TO_DAYS(NOW()) BETWEEN 0 AND " . $tage . "
            AND ue.USERID = u.USERID
            AND ue.GEBURTSTAG IS NOT NULL
          ORDER BY diff";
    */

    $q = "SELECT ue.USERID, ue.GEBURTSTAG, u.LOGIN,
            IF(
              TO_DAYS(ue.GEBURTSTAG + INTERVAL (YEAR(NOW()) - YEAR(ue.GEBURTSTAG)) YEAR) - TO_DAYS(NOW()) < 0,
              YEAR(NOW()) - YEAR(ue.GEBURTSTAG) + 1,
              YEAR(NOW()) - YEAR(ue.GEBURTSTAG)
            ) AS age,
            TO_DAYS(
              IF(
                TO_DAYS(ue.GEBURTSTAG + INTERVAL (YEAR(NOW()) - YEAR(ue.GEBURTSTAG)) YEAR) - TO_DAYS(NOW()) < 0,
                ue.GEBURTSTAG + INTERVAL ((YEAR(NOW()) - YEAR(ue.GEBURTSTAG))+ 1) YEAR,
                ue.GEBURTSTAG + INTERVAL (YEAR(NOW()) - YEAR(ue.GEBURTSTAG)) YEAR
              )
            ) - TO_DAYS(NOW()) AS diff
          FROM USER_EXT AS ue, USER AS u
          WHERE ue.USERID = u.USERID
            AND ue.GEBURTSTAG IS NOT NULL
          HAVING diff <= ".$tage."
          ORDER BY diff";

    $birthdays = DB::getRows($q);
    $mandanten = PELAS::mandantArray(True);

    foreach ($birthdays as $row) {
      $show = False;
      foreach ($mandanten as $id => $name) {
        if (User::hatRecht('TEAMMEMBER', $row['USERID'], $id)) {
          $show = True;
          break;
        }
      }
      if (! $show) {
        continue;
      }
      $date = date('d.m.', strtotime($row['GEBURTSTAG']));
      if ($row['diff'] == 0) {
        $str = sprintf('<strong class="fehler">%s wird heute %s</strong>',
          $row['LOGIN'], $row['age']);
      } elseif ($row['diff'] == 1) {
        $str = sprintf('<em class="confirm">%s wird morgen %s (%s)</em>',
          $row['LOGIN'], $row['age'], $date);
      } else {
        $str = sprintf('%s wird in %s Tagen %s (%s)',
          $row['LOGIN'], $row['diff'], $row['age'], $date);
      }
      echo '<tr><td>' . $str . "</td></tr>\n";
    }
    if (! $birthdays) {
      echo '<tr><td>Keine in den n?chsten ' . $tage . " Tagen.</td></tr>\n";
    }
}

include "admin/nachspann.php";
?>