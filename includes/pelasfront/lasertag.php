<?php
// Lasertag anmeldescript

/* Maximalzahl von Anmeldungen pro User und Party */
define ("MAX_ANMELDUNGEN", 2);

include_once "dblib.php";
include_once "pelasfunctions.php";

$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

if (User::hatRecht('TEAMMEMBER2', $nLoginID) || User::hatRecht('GASTADMIN', $nLoginID) )
	$istAdmin = true;
else
	$istAdmin = false;

/* Anmeldung eines Spielers */
if (isset($_GET['action']) && $_GET['action'] == "subscribe" && $nLoginID > 0) {
	if (User::hatBezahlt()) {
		// TODO: Sicherheitsabfragen auf doppelte Anmeldungen!
		$sql = "SELECT count(*)
				FROM ltag_runde r,
					ltag_anmeldung a
				WHERE a.runde_id = r.id
				AND a.userid = '".intval($nLoginID)."'
				AND r.mandantid = '".intval($nPartyID)."'
				AND r.partyid = '".intval($aktuellePartyID)."'
		";
		$anzahl = DB::getOne($sql);
		if ($anzahl == "" || $anzahl >= MAX_ANMELDUNGEN) {
			echo "<p class='fehler'>Fehler: Du hast Dich bereits zu ".MAX_ANMELDUNGEN." Spielen angemeldet. Mehr ist für eine Party nicht zulässig.</p>\n";
		} else {
			// Sicherheitsabfrage auf freie Plätze und vorhandene Runde
			$sql = "SELECT r.spieler - count(*) as restplaetze
					FROM ltag_anmeldung a,
						ltag_runde r
					WHERE a.runde_id = '".intval($_GET['runde'])."'
					AND a.lfdnr = '".intval($_GET['lfdnr'])."'
					AND a.team = '".intval($_GET['team'])."'
					AND r.id = a.runde_id
			";
			$restplaetze = DB::getOne($sql);
			if ($restplaetze >= 1) {
				$sql = "INSERT INTO ltag_anmeldung (
							runde_id,
							lfdnr,
							team,
							userid
						) VALUES (
							'".intval($_GET['runde'])."',
							'".intval($_GET['lfdnr'])."',
							'".intval($_GET['team'])."',
							'".intval($nLoginID)."'
						)
				";
				if ( DB::query($sql) )
					echo "<p class='confirm'>Erfolg: Du wurdest erfolgreich für die Runde eingetragen.</p>\n";
				else
					echo "<p class='fehler'>Fehler: Irgendwas hat nicht funktioniert</p>\n";
			} else {
				echo "<p class='fehler'>Fehler: Keine Plätze mehr frei in diesem Team</p>\n";
			}
		}
	} else {
		// Voranmeldung nur mit Ticket!
		echo "<p class='fehler'>Fehler: Anmeldung zum Lasertag nur mit NorthCon-Eintrittskarte</p>\n";
	}
} else if (isset($_GET['action']) && $_GET['action'] == "unsubscribe" && ($nLoginID == $_GET['userid'] || $istAdmin) ) {
	/* Anmeldung löschen */
	$sql = "DELETE FROM ltag_anmeldung
			WHERE runde_id = '".intval($_GET['runde'])."'
			AND lfdnr = '".intval($_GET['lfdnr'])."'
			AND team = '".intval($_GET['team'])."'
			AND userid = '".intval($_GET['userid'])."'
	";
	$res = DB::query($sql);
	if (DB::$link->affected_rows > 0)
		echo "<p class='confirm'>Erfolgreich abgemeldet.</p>\n";
	else
		echo "<p class='fehler'>Fehler: Abmeldung nicht erfolgreich.</p>\n";
}

/* Auslesen der Rundendaten */
$sql = "SELECT *
		FROM ltag_runde
		WHERE partyid = '".intval($aktuellePartyID)."'
		AND mandantid = '".intval($nPartyID)."'
		AND start >= UNIX_TIMESTAMP(now()) - 43200
		ORDER by start
";
$res = DB::query($sql);

/* Alle gefundenen Runden durchgehen */
$countit = 0;
while ($row = $res->fetch_array()) {
	echo "<p><table width='650' cellspacing='2' cellpadding='2'>\n";
	echo "<tr><td class='forum_titel' colspan='4'>Spielblock mit ".$row['spieler']." Spielern je Team am ".date("l d F Y", $row['start'])."</td></tr>\n";
	
	$tblclass = "hblau";
	for ($i=1;$i<=$row['anzahl'];$i++) {
		($tblclass == "dblau") ? $tblclass = "hblau" : $tblclass = "dblau";

		if ($i > 1)
			echo "<tr height='6'><td colspan='4'><hr style='margin:0px;'></td></tr>";
		
		// Alle Runden durch
		echo "<tr class='".$tblclass."' height='38' valign='top'>\n";
		echo "<td width='90'>".date("H:i", ($row['start'] + ($i-1)*$row['dauer']*60) )." - ";
		echo date("H:i", ($row['start'] + ($i-1)*$row['dauer']*60+$row['dauer']*60-60) )."</td>\n";
		echo "<td width='260'>";
			if (showTeams($row['id'], $i, 1) < $row['spieler'] && $nLoginID > 0)
				echo " <a class='arrow' href='lasertag.php?action=subscribe&runde=".$row['id']."&lfdnr=".($i)."&team=1'> eintragen</a>";
		echo "</td>\n";
		echo "<td width='25'>vs.</td>\n";
		echo "<td width='260'>";
			if (showTeams($row['id'], $i, 2) < $row['spieler'] && $nLoginID > 0)
				echo " <a class='arrow' href='lasertag.php?action=subscribe&runde=".$row['id']."&lfdnr=".($i)."&team=2'> eintragen</a>";
		echo "</td>\n";
		echo "</tr>\n";
		
	}

	echo "</table></p><br>\n";
	
	$countit++;
}	

echo ($countit == 0) ? "<p class='fehler'>Keine aktiven Zeitslots gefunden - Anmeldung nicht möglich.</p>" : "";

function showTeams ($runde, $lfdnr, $team)
{
	global $nLoginID, $istAdmin;
	$sql = "SELECT u.LOGIN,
				u.USERID
			FROM USER u,
				ltag_anmeldung a
			WHERE u.USERID = a.userid
				AND a.runde_id = '".intval($runde)."'
				AND a.lfdnr = '".intval($lfdnr)."'
				AND a.team = '".intval($team)."'
	";
	$res =  DB::query($sql);
	$count = 0;
	while ($row = $res->fetch_array()) {
		echo ($count >= 1) ? "+ " : "";
		echo "<a style='text-decoration:none;' href='benutzerdetails.php?nUserID=".$row['USERID']."'>".db2display($row['LOGIN'])."</a>";
		/* TODO: Abmelde-Link für Admins überall und immer erlauben! */
		echo ($nLoginID == $row['USERID'] || $istAdmin) ? " <a href='lasertag.php?action=unsubscribe&runde=$runde&lfdnr=$lfdnr&team=$team&userid=".$row['USERID']."'><img src='".PELASHOST."/gfx/icons/action_stop.gif' width='16' border='0' title='unsubscribe'></a> " : " ";
		$count++;
	}
	return $count;
}
?>