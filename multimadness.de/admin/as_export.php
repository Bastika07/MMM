<?php
/* Export */

set_time_limit(180);
ob_start();
require('controller.php');
require_once('dblib.php');
$iRecht = 'USERADMIN';
require('checkrights.php');
require('format.php');


function parse($str) {
    return preg_replace("/'/", " \\'", $str);
}

function unroll_arr($row) {
    $retval = '';
    foreach ($row as $key => $val)
	if (! empty($key) and ! empty($val))
	    $retval.= "`{$key}` = '" . parse($val) . "', ";

    # Letztes Komma entfernen.
    return substr($retval, 0, -2);
}

$mandant = (int) $_REQUEST['mandant'];
if (! $mandant) {
	require_once('admin/helpers.php');
	include('admin/vorspann.php');
?>

<h1>Export Astatus</h1>

<p>Hinweis: Wenn der automatische Download nicht richtig funktioniert,
kann der Export aus dem Hauptverzeichnis des Webservers heruntergeladen werden.</p>

<?php
	$currentUser = new User();
	$mandanten = $currentUser->getMandanten('USERADMIN');
	show_mandant_selection_dropdown($mandanten);
	include('admin/nachspann.php');
} else {
	$dbh = DB::connect();
	$fhandle = fopen('../../export.sql', 'w');

	# Mandant
	$res = DB::query("select * from MANDANT where MANDANTID='$mandant'");
	fwrite($fhandle, "delete from MANDANT;\n");
	while ($row = $res->fetch_assoc()) {
		fwrite($fhandle,"insert into MANDANT set ".unroll_arr($row).";");
	}

	# User
	$sql = "select
		u.USERID, u.LOGIN, u.PASSWORD_HASH, u.EMAIL, u.NAME, u.NACHNAME,
		u.STRASSE, u.PLZ, u.ORT, u.LAND, u.HOMEPAGE, u.BILD_VORHANDEN,
		u.TOPGAME, u.KOMMENTAR_PUBLIC, u.KOMMENTAR_INTERN, u.AKTIV,
		u.PERSONR, u.WWCL_SINGLE, u.WWCL_TEAM, u.NGL_SINGLE, u.NGL_TEAM, u.SHIRTSIZE
		from USER u
		left join ASTATUS a on a.MANDANTID='$mandant' and a.USERID=u.USERID
		left join USER_EXT e on e.USERID=u.USERID
		where a.USERID is not null or e.USERID is not null";
	##$result = DB::query($sql);
	$result = DB::query($sql);
	fwrite($fhandle, "delete from USER;\n");
	while ($row = $result->fetch_assoc()) {
		fwrite($fhandle, "insert into USER (USERID, LOGIN, PASSWORD_HASH, EMAIL, NAME, NACHNAME, STRASSE, PLZ, ORT, LAND, HOMEPAGE, BILD_VORHANDEN, TOPGAME, SHIRTSIZE, KOMMENTAR_PUBLIC, KOMMENTAR_INTERN, AKTIV, PERSONR, WWCL_SINGLE, WWCL_TEAM, NGL_SINGLE, NGL_TEAM, WERANGELEGT, WANNANGELEGT, WANNGEAENDERT, WERGEAENDERT) ".
			"values (".$row['USERID'].", '".parse($row[LOGIN])."', '".parse($row[PASSWORD_HASH])."', '".parse($row[EMAIL])."', '".parse($row[NAME])."', '".parse($row[NACHNAME])."', '".parse($row[STRASSE])."', '".parse($row[PLZ])."', '".parse($row[ORT])."', '".parse($row[LAND])."', '".parse($row[HOMEPAGE])."', '$row[BILD_VORHANDEN]', '$row[TOPGAME]', '$row[SHIRTSIZE]', '".parse($row[KOMMENTAR_PUBLIC])."', '".parse($row[KOMMENTAR_INTERN])."', '$row[AKTIV]', '".parse($row[PERSONR])."', '".parse($row[WWCL_SINGLE])."', '".parse($row[WWCL_TEAM])."', '".parse($row[NGL_SINGLE])."', '".parse($row[NGL_TEAM])."', $loginID, now(), $loginID, now());"."\n");
	}

	# Teamdaten
	$res = DB::query("SELECT * FROM USER_EXT");
	fwrite($fhandle, "delete from USER_EXT;\n");
	while ($row = $res->fetch_assoc())
		fwrite($fhandle, "INSERT INTO USER_EXT SET ".unroll_arr($row).";\n");

	# Rechte
	$res = DB::query("SELECT * FROM RECHTZUORDNUNG where MANDANTID=$mandant");
	fwrite($fhandle, "delete from RECHTZUORDNUNG;\n");
	while ($row = $res->fetch_assoc())
		fwrite($fhandle, "INSERT INTO RECHTZUORDNUNG SET ".unroll_arr($row).";\n");

	# Anmeldestatus
	$result = DB::query("select * from ASTATUS where MANDANTID=$mandant");
	fwrite($fhandle, "delete from ASTATUS;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into ASTATUS (MANDANTID, USERID, STATUS, BEZ_IN_CLAN, RABATTSTUFE, WANNANGELEGT, WERANGELEGT, WANNGEAENDERT, WERGEAENDERT, WANNANGEMELDET, WANNBEZAHLT) values ($row[MANDANTID], $row[USERID], $row[STATUS], '$row[BEZ_IN_CLAN]', '$row[RABATTSTUFE]', '$row[WANNANGELEGT]', '$row[WERANGELEGT]', '$row[WANNGEAENDERT]', '$row[WERGEAENDERT]', '$row[WANNANGEMELDET]', '$row[WANNBEZAHLT]');"."\n");
	}

	# Config
	$result = DB::query("select PARAMETER, MANDANTID, STRINGWERT, BESCHREIBUNG from CONFIG where MANDANTID=$mandant");
	fwrite($fhandle, "delete from CONFIG;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into CONFIG (MANDANTID, PARAMETER, STRINGWERT, BESCHREIBUNG) values ($row[MANDANTID], '$row[PARAMETER]', '".parse($row[STRINGWERT])."', '".parse($row[BESCHREIBUNG])."');"."\n");
	}
	fwrite($fhandle, "insert into CONFIG (PARAMETER, STRINGWERT, BESCHREIBUNG) values ('MANDANTID', $mandant, 'Der exportierte Mandant, wird in dblib.php abgefragt.');"."\n");

	# Sitzplan
	$result = DB::query("select MANDANTID, EBENE , XCORD , YCORD ,REIHE ,LAENGE ,AUSRICHTUNG ,ISTLOGE, ISTGESPERRT  from SITZDEF where MANDANTID=$mandant");
	fwrite($fhandle, "delete from SITZDEF;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into SITZDEF (MANDANTID, EBENE , XCORD , YCORD ,REIHE ,LAENGE ,AUSRICHTUNG ,ISTLOGE, ISTGESPERRT ) values ($row[MANDANTID], $row[EBENE], $row[XCORD] , $row[YCORD] ,$row[REIHE] ,$row[LAENGE] ,$row[AUSRICHTUNG] ,$row[ISTLOGE] ,$row[ISTGESPERRT] );"."\n");
	}

	# Sitzplatz
	$result = DB::query("select MANDANTID, USERID, REIHE, PLATZ, RESTYP from SITZ where MANDANTID=$mandant");
	fwrite($fhandle, "delete from SITZ;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into SITZ (MANDANTID, USERID, REIHE, PLATZ, RESTYP) values ($row[MANDANTID], $row[USERID], $row[REIHE], $row[PLATZ], $row[RESTYP]);"."\n");
	}

	# Sitzgruppen
	$result = DB::query("select * from sitzgruppe where MANDANTID='$mandant'");
	fwrite($fhandle, "delete from sitzgruppe;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into sitzgruppe (GRUPPEN_ID, MANDANTID, REIHE, UMBRECHENNACH, GRUPPEN_NAME, CLANID, ERSTELLT_VON, WANNANGELEGT, WANNGEAENDERT) values ('$row[GRUPPEN_ID]', '$row[MANDANTID]', '$row[REIHE]', '$row[UMBRECHENNACH]', '".parse($row[GRUPPEN_NAME])."', '$row[CLANID]', '$row[ERSTELLT_VON]', '$row[WANNANGELEGT]', '$row[WANNGEAENDERT]');"."\n");
	}

	# nochma Sitzplan
	$result = DB::query("select * from sitzplan_def where mandantID='$mandant'");
	fwrite($fhandle, "delete from sitzplan_def;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into sitzplan_def (mandantID, ebene, xcord, ycord, reihe, platz, type) values ($row[mandantID], $row[ebene], '$row[xcord]', '$row[ycord]', '$row[reihe]', '$row[platz]', '$row[type]');"."\n");
	}

	# und nochma Sitzgruppen
	$result = DB::query("select * from sitzgruppen_mitglieder where mandantID='$mandant'");
	fwrite($fhandle, "delete from sitzgruppen_mitglieder;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into sitzgruppen_mitglieder (USERID, MANDANTID, GRUPPEN_ID) values ($row[USERID], $row[MANDANTID], $row[GRUPPEN_ID]);"."\n");
	}

	# Clan blub
	$result = DB::query("select MANDANTID, CLANID, NAME, URL, IRC_CHANNEL, WANNANGELEGT, WERANGELEGT, WANNGEAENDERT, WERGEAENDERT from CLAN where MANDANTID=$mandant");
	fwrite($fhandle, "delete from CLAN;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into CLAN (MANDANTID, CLANID, NAME, URL, IRC_CHANNEL, WANNANGELEGT, WERANGELEGT, WANNGEAENDERT, WERGEAENDERT) values ($row[MANDANTID], $row[CLANID], '".parse($row[NAME])."', '".parse($row[URL])."', '".parse($row[IRC_CHANNEL])."', '$row[WANNANGELEGT]', $row[WERANGELEGT], '$row[WANNGEAENDERT]', $row[WERGEAENDERT]);"."\n");
	}

	# Zuordnung User-Clan
	$result = DB::query("select MANDANTID, USERID, CLANID, AUFNAHMESTATUS, WANNANGELEGT, WERANGELEGT, WANNGEAENDERT, WERGEAENDERT from USER_CLAN where MANDANTID=$mandant");
	fwrite($fhandle, "delete from USER_CLAN;\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into USER_CLAN (MANDANTID, USERID, CLANID, AUFNAHMESTATUS, WANNANGELEGT, WERANGELEGT, WANNGEAENDERT, WERGEAENDERT) values ($row[MANDANTID], $row[USERID], $row[CLANID], $row[AUFNAHMESTATUS], '$row[WANNANGELEGT]', $row[WERANGELEGT], '$row[WANNGEAENDERT]', $row[WERGEAENDERT]);"."\n");
	}
	
	# Gastserver
	$result = DB::query("select * from GASTSERVER where MANDANTID=$mandant");
	fwrite($fhandle, "delete from GASTSERVER where MANDANTID='$mandant';\n");
	while ($row = $result->fetch_array()) {
		fwrite($fhandle, "insert into GASTSERVER (LFDNR, USERID, MANDANTID, NAMEDNS, BESCHREIBUNG, REVERSE, WANNANGELEGT) values ('".$row[LFDNR]."', '".$row[USERID]."', '".$row[MANDANTID]."', '".parse($row[NAMEDNS])."', '".parse($row[BESCHREIBUNG])."', '".$row[REVERSE]."', '".$row[WANNANGELEGT]."');"."\n");
	}

	# neuer Turnierstuff, 29.09.2004 ore
	$partyid = PELAS::mandantAktuelleParty($mandant);
	fwrite($fhandle, "DELETE FROM t_turnier;\n");
	fwrite($fhandle, "DELETE FROM t_admin;\n");
	fwrite($fhandle, "DELETE FROM t_preise;\n");
	fwrite($fhandle, "DELETE FROM t_team;\n");
	fwrite($fhandle, "DELETE FROM t_team2user;\n");
	$res = DB::query("SELECT * FROM t_turnier WHERE partyid = '{$partyid}'");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO t_turnier SET ".unroll_arr($row).";\n");

	    $res2 = DB::query("SELECT * FROM t_admin WHERE turnierid = '{$row['turnierid']}'");
	    while ($row2 = $res2->fetch_assoc()) {
		fwrite($fhandle, "INSERT INTO t_admin SET ".unroll_arr($row2).";\n");
	    }

	    $res2 = DB::query("SELECT * FROM t_preise WHERE turnierid = '{$row['turnierid']}'");
	    while ($row2 = $res2->fetch_assoc()) {
		fwrite($fhandle, "INSERT INTO t_preise SET ".unroll_arr($row2).";\n");
	    }

	    $res2 = DB::query("SELECT * FROM t_team WHERE turnierid = '{$row['turnierid']}'");
	    while ($row2 = $res2->fetch_assoc()) {
		fwrite($fhandle, "INSERT INTO t_team SET ".unroll_arr($row2).";\n");

		$res3 = DB::query("SELECT * FROM t_team2user WHERE turnierid = '{$row['turnierid']}' AND teamid = '{$row2['teamid']}'");
		while ($row3 = $res3->fetch_assoc()) {
		    fwrite($fhandle, "INSERT INTO t_team2user SET ".unroll_arr($row3).";\n");
		}
	    }
	}

	fwrite($fhandle, "DELETE FROM t_events;\n");
	fwrite($fhandle, "DELETE FROM t_match;\n");
	fwrite($fhandle, "DELETE FROM t_rounds;\n");

	$res = DB::query("SELECT * FROM t_ranking");
	fwrite($fhandle, "DELETE FROM t_ranking;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO t_ranking SET ".unroll_arr($row).";\n");
	}

	$res = DB::query("SELECT * FROM t_ligagame");
	fwrite($fhandle, "DELETE FROM t_ligagame;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO t_ligagame SET ".unroll_arr($row).";\n");
	}

	$res = DB::query("SELECT * FROM t_group");
	fwrite($fhandle, "DELETE FROM t_group;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO t_group SET ".unroll_arr($row).";\n");
	}

	// partyid wieder auf mandant zurücksetzen!
	$partyid = $mandant;



	# Neues Accounting, 08.08.06, mgi

	$res = DB::query("SELECT * FROM party where mandantId='{$partyid}'");
	fwrite($fhandle, "DELETE FROM party;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO party SET ".unroll_arr($row).";\n");
	}

 	$res = DB::query("SELECT t.* FROM acc_ticket_typ t, party p where p.mandantId='{$partyid}' and p.partyId=t.partyId and p.aktiv='J'");
	fwrite($fhandle, "DELETE FROM acc_ticket_typ;\n");
 	while ($row = $res->fetch_assoc()) {
 	    fwrite($fhandle, "INSERT INTO acc_ticket_typ SET ".unroll_arr($row).";\n");
	}

	$res = DB::query("SELECT b.* FROM acc_bestellung b, party p where p.mandantId='{$partyid}' and p.partyId=b.partyId and p.aktiv='J'");
	fwrite($fhandle, "DELETE FROM acc_bestellung;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO acc_bestellung SET ".unroll_arr($row).";\n");
	}

	$res = DB::query("SELECT t.* FROM acc_tickets t, party p where p.mandantId='{$partyid}' and p.partyId=t.partyId and p.aktiv='J'");
	fwrite($fhandle, "DELETE FROM acc_tickets;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO acc_tickets SET ".unroll_arr($row).";\n");
	}

	$res = DB::query("SELECT a.* FROM aussteller a, party p where p.mandantId='{$partyid}' and p.partyId=a.partyId and p.aktiv='J'");
	fwrite($fhandle, "DELETE FROM aussteller;\n");
	while ($row = $res->fetch_assoc()) {
	    fwrite($fhandle, "INSERT INTO aussteller SET ".unroll_arr($row).";\n");
	}

	fclose($fhandle);

	$data = implode('', file('export/export.sql'));
	$gz_data = gzencode($data);
	$fp = fopen('export/export.sql.gz', 'w');
	fwrite($fp, $gz_data);
	fclose($fp);

	##exec('rm export/export.sql');

	ob_end_clean();
	header('Content-Type: application/x-gzip');
	header('Content-Encoding: x-gzip');
	header('Content-Disposition: attachment; filename="export.sql.gz"');
	readfile('export/export.sql.gz');

	exit;
}

ob_end_flush();
?>
