<?php
require('controller.php');
require_once "dblib.php";
$iRecht = array("SITZPLANADMIN");
include "checkrights.php";
include_once "format.php";
include_once "pelasfunctions.php";
include "admin/vorspann.php";

$dbh = DB::connect();

?>
	<h1>Accounting: Sitzpl&auml;tze &auml;ndern</h1>

	<table cellspacing="0" cellpadding="0" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">

	<form method="post" name="filter" action="tickets_sitze.php">
	<?= csrf_field() ?>

	<input type="hidden" name="iGo" value="yes">

	<tr><td class="navbar" colspan="2"><b>Filtereinstellungen</b></td></tr>
	<tr>
		<td class="dblau" width="70">Ticket-Nr. </td><td width="120" class="hblau"><input type="text" name="iTicketId" size=7 maxlength=14 value="<?= htmlspecialchars($_POST['iTicketId'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
		<td class="dblau">Mandant</td>
		<td class="hblau" width="220"><select name="iMandant">
		<?php
			$result= DB::query("select m.MANDANTID, m.BESCHREIBUNG from MANDANT m, RECHTZUORDNUNG r where r.MANDANTID=m.MANDANTID and r.USERID='".intval($loginID)."' and r.RECHTID='SITZPLANADMIN'");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			while ($row = $result->fetch_array()) {
				echo "<option value=\"$row[MANDANTID]\"";
				if (isset($_POST['iMandant']) && $_POST['iMandant'] == $row['MANDANTID']) {echo " selected";}
				echo ">$row[BESCHREIBUNG]";
			}
		?>
		</select></td>
	</tr><tr>
		<td class="dblau" width="60">User-ID </td><td width="120" class="hblau"><input type="text" name="iId" size=5 maxlength=10 value="<?= (isset($_POST['iId']) && $_POST['iId'] !== '') ? intval($_POST['iId']) : '' ?>"></td>
		<td class="dblau" width="60">Login </td><td width="220" class="hblau"><input type="text" name="iLogin" size=25 maxlength=100 value="<?= htmlspecialchars($_POST['iLogin'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
	</tr><tr>
		<td colspan="4" class="dblau" align="center"><input type="submit" value="Tickets auflisten"></td>
	</tr>
	</form>
	</table></td></tr></table>

	<?php 

if (isset($_POST['iGo']) && $_POST['iGo'] == 'yes') {

	?>
	<br>
	<table cellspacing="0" cellpadding="0" width="750" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">
	<tr>
		<td class="navbar"><b>Bestell-Nr.</b></td>
		<td class="navbar"><b>Ticket-Nr.</b></td>
		<td class="navbar"><b>Besitzer (UID)</b></td>
		<td class="navbar"><b>Benutzer (UID)</b></td>
		<td class="navbar"><b>Party</b></td>
		<td class="navbar"><b>Platz</b></td>
		<td class="navbar"><b>Reservieren od. Status</b></td>
	</tr>
	<?php


	$sAddWhere = '';

	// Filter
	if (!empty($_POST['iLogin'])) {
	  $sAddWhere = "$sAddWhere and (u1.LOGIN like '%".safe($_POST['iLogin'])."%' or u2.LOGIN like '%".safe($_POST['iLogin'])."%')";
	} 
	if (!empty($_POST['iTicketId'])) {
	  $sAddWhere = "$sAddWhere and t.ticketId = '".intval($_POST['iTicketId'])."'";
	} 
	if (!empty($_POST['iId'])) {
	  $sAddWhere = "$sAddWhere and (u1.USERID = '".intval($_POST['iId'])."' or u2.USERID = '".intval($_POST['iId'])."')";
	} 
	if ($_POST['iMandant'] > 0) {
	  $sAddWhere = "$sAddWhere and t.mandantId = '".intval($_POST['iMandant'])."'";
	}

	$sWhere = "select distinct
         u1.LOGIN userName,
         u2.LOGIN ownerName,
		     t.ticketId,
		     t.statusId,
		     t.userId,
		     t.ownerId,
		     t.sitzReihe,
		     t.sitzPlatz,
		     s.beschreibung,
		     p.beschreibung as partyname,
		     p.partyId,
		     t.bestellId
		   from 
		     USER u1,
		     USER u2,
		     RECHTZUORDNUNG r,
		     acc_tickets t,
		     acc_ticket_bestellung_status s,
		     acc_ticket_typ y,
		     party p
		   where 
		     u1.USERID = t.userId AND
		     u2.USERID = t.ownerId AND
		     y.typId = t.typId and
		     t.mandantId = r.MANDANTID and
		     r.RECHTID = 'SITZPLANADMIN' and
		     r.USERID = '".intval($loginID)."' and
		     s.statusId = t.statusId and
		     p.mandantId = y.mandantId and
		     p.partyId = y.partyId and
		     t.partyId = p.partyId and
		     p.aktiv = 'J'
		     $sAddWhere
		     $sAddWhereMandant
		   order by 
		     t.ticketId";

	//echo "<pre>$sWhere</pre>";

	$result= DB::query($sWhere);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";

	$bgc = 'hblau';
	$id = 0;

	while ($row = $result->fetch_array()) {
		// $id für logbuch füllen
		if ($id == 0) {
			$id = $row['ownerId'];
		} else {
			$id = $id.",".$row['ownerId'];
		}
		$id = $id.",".$row['userId'];
		
		echo "<tr>";
		echo "<td class=\"$bgc\">".PELAS::formatBestellNr($row['partyId'], $row['bestellId'])."&nbsp;</td>";
		echo "<td class=\"$bgc\">".PELAS::formatTicketNr($row['ticketId'])."&nbsp;</td>";
		echo "<td class=\"$bgc\"><a href=\"/admin/benutzerdetails.php?id=".$row['ownerId']."\">".db2display($row['ownerName'])."</a> ($row[ownerId])</td>";
		echo "<td class=\"$bgc\"><a href=\"/admin/benutzerdetails.php?id=".$row['userId']."\">".db2display($row['userName'])."</a> ($row[userId])</td>";
		echo "<td class=\"$bgc\">".db2display($row['partyname'])."&nbsp;</td>";
		echo "<td class=\"$bgc\">";
		if ($row['sitzReihe'] > 0) {
			echo $row['sitzReihe']."-".$row['sitzPlatz'];
		} else {
			// Kein Platz
			echo "<i>(kein)</i>";
		}
		echo "&nbsp;</td>";
		echo "<td class=\"$bgc\"><nobr>";
		
		if ($row['statusId'] == ACC_STATUS_BEZAHLT) {
			// Ebenen auflisten für den Mandanten
			$sql=  "select distinct
					s.EBENE
				from
					SITZDEF s
				where
					s.MANDANTID = '".intval($_POST['iMandant'])."'
				order by
					s.EBENE";
			$resEbene= DB::query($sql);
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";

			$doIt = 0;
			while ($rowEbene= $resEbene->fetch_array()) {
				if ($doIt == 1) {
					echo " | ";
				}
				echo '<a href="tickets_sitze_reserve.php?nPartyID='.intval($_POST['iMandant']).'&ebene='.$rowEbene['EBENE'].'&nLoginID='.$row['ownerId'].'&iTicket='.$row['ticketId'].'" target="_blank">Ebene '.$rowEbene['EBENE'].'</a> '."\n";
				$doIt = 1;
			}
		} else {
			echo db2display($row['beschreibung']);
		}
		
		echo "</nobr></td>";
		echo "</tr>\n";
		if ($bgc == 'hblau')
		  $bgc = 'dblau';
		else 
		  $bgc = 'hblau';
	}

	echo "</table></td></tr></table>";


    // Logbuch: Letzte Actions aus der Logging-Tabelle
    if ($id != "") {
        echo '<br><table cellspacing="0" cellpadding="0" border="0" width="600">'."\n";
        echo '<tr><td class="navbar">'."\n";
        echo '<table width="100%" cellspacing="1" cellpadding="3" border="0">'."\n";
        echo '<tr><td class="navbar"><b>Die letzten 15 Logbuch-Eintr&auml;ge zu den Benutzern im Sitzplan</b></td><td class="navbar"><b>Datum</b></td><td class="navbar"><b>Kategorie</b></td></tr>'."\n";
    
        $sTBG = "dblau";
        
        $sql=  "select *
                from
                    logging
                where
                    userID in (".intval($id).")
                    and cat = 'sitzplan'
                order by
                    time desc
                limit 15";
                    
        $res= DB::query($sql);

        while ($row= $res->fetch_array()) {
        
            echo '<tr><td class="'.$sTBG.'">'.db2display($row['msg']).'</td><td class="'.$sTBG.'">'.dateDisplay2($row['time']).'</td><td class="'.$sTBG.'">'.$row['cat'].'</td></tr>'."\n";
            
            if ($sTBG == "dblau") {
              $sTBG = "hblau";
            } else {
              $sTBG = "dblau";
            }
        }
    
        echo "</table></td></tr></table><br>"."\n";
      }

}

include "admin/nachspann.php";
?>
