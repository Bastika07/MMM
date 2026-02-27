<?php

ob_start();
require('controller.php');
require_once "dblib.php";
$iRecht = "ACCOUNTINGADMIN";
include "checkrights.php";
	$menu_deactivate = true;
	include "admin/vorspann.php";

// Cookie setzen um Frontend einen anderen User vorzuspielen
if ($_GET['iId'] > 0) {
	// Keks neu setzen
	setcookie("ADMIN_PARTYID", intval($_GET['nPartyID']), time()+1800);
	setcookie("ADMIN_LOGINID", intval($_GET['iId']), time()+1800);
	$nPartyID = intval($_GET['nPartyID']);
	$nLoginID = intval($_GET['iId']);
} else {
	// Keks auslesen
	$nPartyID = $_COOKIE['ADMIN_PARTYID'];
	$nLoginID = $_COOKIE['ADMIN_LOGINID'];
}

// Funktion für Vorverkauf 
if (isset($_GET['VVKpartyNummerID']))
{
	$nPartyNummerID = $_GET['VVKpartyNummerID'];
	setcookie("ADMIN_PARTYNUMMERID", $nPartyNummerID, time()+1800);
}
else if (isset($_COOKIE['ADMIN_PARTYNUMMERID']))
{
	$nPartyNummerID = $_COOKIE['ADMIN_PARTYNUMMERID'];
}


if ($nLoginID < 1) {
	echo "<p class=\"fehler\">Du musst eine Benutzer-ID im vorhergehenden Formular angeben.</p>";
} else {

	$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);
	// prüfen ob noch eine offene Bestellung vorliegt
	$sql = "select count(*) as vorhanden
		from acc_bestellung b,
		     acc_ticket_typ t
		where t.partyId = ".intval($aktuellePartyID)."
		and b.ticketTypId = t.typId
		and b.status = ".ACC_STATUS_OFFEN."
		and b.bestellerUserId = '".intval($nLoginID)."'
		and b.partyId = '".intval($aktuellePartyID)."'";

	$res = DB::query($sql);
	$rowTemp = $res->fetch_array();

	if ($rowTemp['vorhanden'] > 0) {
		// Bestellung vorhanden, gleich zu bestellung verwalten forwarden
		header ("Location: tickets_bestellungen.php?iId=$nLoginID");
	} else {
		// Bestellformular einbinden
		// Datei vom Frontend einbinden
		$callFromAdmin = 1;
		$_GET['action']  = "order";
		include "pelasfront/accounting.php";
	}
}

ob_flush();

include "admin/nachspann.php";
?>
