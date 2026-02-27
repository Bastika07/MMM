<?php
/* Eine Liste von wichtigen Dokumenten */
require('controller.php');
require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require('checkrights.php');
include('admin/vorspann.php');
?>

<h1>Wichtige Dokumente</h1>

<p>Wenn ihr an dieser Stelle Dokumente eingefügt haben möchtet, damit sie allen zur Verfügung stehen, wendet euch bitte an Ove, TraXo oder Mad.</p>

<table cellspacing="1" class="outer">
  <tr>
    <th colspan="3">Dokumente für das Team (auch Trainees)</th>
  </tr>
<?php
# Dateinamen der Dokument aus Dateisystem lesen.
$path = 'dokumente/';
exec('ls ' . $path, $filenames, $rc);

$row_idx = 0;
foreach ($filenames as $filename) {
    $filepath = $path . $filename;
?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td><a href="dokumente/<?= $filename ?>"><?= $filename ?></a></td>
    <td style="text-align: right;"><?= round(filesize($filepath) / 1024, 0) ?> kB</td>
    <td><?= date('d.m.Y, H:i', filemtime($filepath)) ?> Uhr</td>
  </tr>
<?php
}
?>
</table>

<?php
include('admin/nachspann.php');
?>
