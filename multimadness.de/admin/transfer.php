<?php
$dir = "/homepages/19/d519525094/htdocs/transfer/";
$datei = str_replace("/", "", $_GET['download']);

require('controller.php');
require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require_once('checkrights.php');
require_once('format.php');

#
# Wurde ein Download angefordert?
#
if (isset($datei) && is_file($dir.$datei)) {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
	header('Content-Disposition: attachment; filename="'.$datei.'');
	readfile($dir.$datei);
	exit;
}

include('admin/vorspann.php');
?>

<h1>FTP-Server</h1>

<h2>Direkter Zugriff auf den FTP-Server</h2>
<ul>
	<li>Host: www.multimadness.de</li>
  <li>User: u76624264-transfer</li>
  <li>Pass: (bitte beim Administrator erfragen)</li>
</ul>

<h2>Im Hauptverzeichnis vorhandene Dateien</h2>

<table cellspacing="1" cellpadding="5" width="600">
<tr>
	<th>Dateiname</th>
	<th>GröÃŸe</th>
  <th>Upload-Zeit</th>
</tr>

<?php
$handle=opendir($dir);
$class = "hblau";
while ($datei = readdir ($handle)) {
	if (substr($datei, 0, 1) != ".") {
		if ($class == "dblau") $class = "hblau"; else $class = "dblau";
		$groesse = number_format(filesize($dir.$datei), 0, ",", ".");
		$datum = date("d.m.Y H:i", filemtime($dir.$datei));
		printf("<tr><td class='%s'><a href='transfer.php?download=%s' target='_blank'>%s</a></td><td class='%s'>%s Kb</td><td class='%s'>%s</td></tr>\n",
			$class,
			$datei,
			$datei,
			$class,
			$groesse,
			$class,
			$datum);
	}
} 
closedir($handle);
?>
</table>
<?php
include('admin/nachpann.php');
?>