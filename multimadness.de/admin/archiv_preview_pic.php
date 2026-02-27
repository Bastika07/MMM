<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "ARCHIVADMIN";
include "checkrights.php";
header("Content-type: video/mpeg");
$nGallery = intval($_GET['nGallery']);
$sPic     = basename($_GET['sPic'] ?? '');
$datei = ARCHIV_UPLOADDIR."_oldarchiv/".$nGallery."/".$sPic;

#
# Wurde ein Download angefordert?
#
if (isset($datei) && is_file($dir.$datei)) {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
	header('Content-Disposition: attachment; filename="'.$datei.'');
	readfile($dir.$datei);
	exit;
} else echo "Fehler: Datei nicht auf dem Server vorhanden."; ?>
