<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "SITZPLANADMIN";
include "checkrights.php";
include_once "format.php";
include_once "pelasfunctions.php";

include "admin/vorspann.php";

// Wir verwenden hier das Script vom Frontend. Damit es mit den aus dem Admin gelieferten
// Daten funktioniert, setzen wir nachfolgend entscheidende Variablen neu

// Variable setzen, damit vom Script kein Frontend Session-Handling geladen wird
$callFromAdmin = 1;

// Selektierter Mandant:  $nPartyID
// Ebene                  $ebene
// Selektierte Ticket-ID: $iTicket
// Acting User:           $nLoginID


// $nPartyID in Cookie schreiben, damit beim nächsten Aufruf wieder verfügbar
if ($_GET['nPartyID'] > 0 && $_GET['nLoginID'] > 0) {
	// Keks neu setzen
	$_SESSION["ADMIN_PARTYID"] = intval($_GET['nPartyID']);
	$nPartyID = $_GET['nPartyID'];
	$_SESSION["ADMIN_LOGINID"] = intval($_GET['nLoginID']);
	$nLoginID = $_GET['nLoginID'];
	
} else {
	// Keks auslesen
	$nPartyID = $_SESSION['ADMIN_PARTYID'];
	$nLoginID = $_SESSION['ADMIN_LOGINID'];
}

include_once "pelasfront/accounting_sitzplan.php";


include "admin/nachspann.php";

?>
