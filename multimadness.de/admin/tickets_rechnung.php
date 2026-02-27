<?php
require('controller.php');
require_once "dblib.php";
$iRecht = array("ACCOUNTINGADMIN");
include "checkrights.php";
include "pelasfunctions.php";
include "format.php";

$dbh = DB::connect();
# Workaround. ezpdf schreibt sonst die Zahlen mit Komma in die PDF-Datei und die wird dann ungültig.
# Wie scheisse ist das denn?
zeigeRechnung($_GET['iPartyId'], $_GET['iBestellId']);

?>
