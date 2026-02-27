<?php
/* Export der Sitzplatznummern */
require('controller.php');
set_time_limit(180);
require_once('dblib.php');
$iRecht = 'SITZPLANADMIN';
require('checkrights.php');
require_once('admin/helpers.php');


$mandantID = (int) $_REQUEST['mandant'];
if (! $mandantID) {
    # Kein Mandant ausgewählt.
    include('admin/vorspann.php');
    echo "<h1>Export Sitzplatznummern</h1>";
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('SITZPLANADMIN');
    show_mandant_selection_dropdown($mandanten, 'mandant');
    include('admin/nachspann.php');
} else {
    # Mandant ausgewählt.
    header('Content-Type: text/plain');
    $rows = DB::getRows('
	SELECT REIHE, LAENGE
	FROM SITZDEF
	WHERE MANDANTID = ?
	ORDER BY REIHE
	', $mandantID);

    # Alle Reihen durchgehen.
    foreach ($rows as $row) {
        # Alle Plätze dieser Reihe ausgeben.
        foreach (range(1, $row['LAENGE']) as $i) {
	    
			$infoRow = DB::getRow('
				SELECT t.ticketId, u.LOGIN
				FROM acc_tickets t, USER u
				WHERE t.userId = u.USERID
				AND t.partyId = ?
				AND t.sitzReihe = ?
				AND t.sitzPlatz = ?
			', PELAS::mandantAktuelleParty($mandantID), $row['REIHE'], $i);

			printf("%s,%s,%s,\"%s\"\n", $row['REIHE'], $i, PELAS::formatTicketNr($infoRow['ticketId']), str_replace("\"", "''", $infoRow['LOGIN']));
		}
    }
    exit;
}
?>
