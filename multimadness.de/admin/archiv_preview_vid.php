<?php
set_time_limit(3600);
require('controller.php');
require_once "dblib.php";
$iRecht = "ARCHIVADMIN";
include "checkrights.php";
header("Content-type: video/mpeg");
ob_start();
$nGallery = intval($_GET['nGallery']);
$sPic     = basename($_GET['sPic'] ?? '');
$fp = fopen(ARCHIV_UPLOADDIR."/".$nGallery."/".$sPic, 'r');
while (($buf = fread($fp, 102400)) != EOF) {
  fwrite(stdout, $buf);
  ob_flush();
}
fclose($fp);
ob_flush();
//readfile($ARCHIV_UPLOADDIR."/$nGallery/$sPic");
?>
