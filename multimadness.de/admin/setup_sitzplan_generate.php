<?php
/* Sitzplan generieren */
require('controller.php');
require_once('dblib.php');
$iRecht = 'MANDANTADMIN';
require('checkrights.php');
require_once('admin/helpers.php');
include('admin/vorspann.php');


echo "<h1>Sitzplan generieren</h1>\n";

$mandantID = (int) $_GET['iMandantID'];
if ($mandantID < 1) {
    # Kein Mandant ausgewählt.
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('MANDANTADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.
    require_once('sitzplan_generate_newaccounting.php');

    $maxEbenen = DB::getOne('
        SELECT MAX(ebene)
	FROM SITZDEF
	WHERE MANDANTID = ?
	', intval($_GET['iMandantID']));
?>

<p>Generiere Sitzplan mit <?= $maxEbenen ?> Ebenen...</p>
<ul>
<?php
    $seatsTotal = 0;
    for ($i = 1; $i <= $maxEbenen; $i++) {
	$seats = GeneriereSitzplan($mandantID, $i, 0, PELAS::mandantAktuelleParty($mandantID));
	printf("  <li>Ebene %s: %s Plätze - OK.</li>\n", $i, $seats);
	$seatsTotal += $seats;
    }
?>
</ul>
<p><?= $seatsTotal ?> Plätze generiert!</p>

<?php
}

include('nachspann.php');
?>
