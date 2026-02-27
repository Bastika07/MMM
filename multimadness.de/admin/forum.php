<?php
require('controller.php');
require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require_once('checkrights.php');
require_once('format.php');
include('admin/vorspann.php');
?>

<h1>Forum</h1>

<div style="max-width:1000px;">
<?php
$nPartyID = 9999; // Spezielle Party-ID um das Adminforum aufzurufen
include_once "forum_forum.php";
?>
</div>

<?php
include('admin/nachspann.php');
?>