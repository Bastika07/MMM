<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "ARCHIVADMIN";
include "checkrights.php";
header("Content-type: image/jpeg");
$nGallery = intval($_GET['nGallery']);
$sPic     = basename($_GET['sPic'] ?? '');
readfile(ARCHIV_UPLOADDIR.$nGallery."/".$sPic);
?>
