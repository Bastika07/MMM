<?php
/* Benutzer auflisten, die Tickets bezahlt, aber noch keinen Sitzplatz reserviert haben. */
require('controller.php');
require_once('dblib.php');
$iRecht = array('USERADMIN', 'USERADMIN_READONLY');
require('checkrights.php');
require_once('admin/helpers.php');
include('vorspann.php');


echo "<h1>Benutzer ohne Sitzplatz</h1>\n";

$iMandantID = $_REQUEST['iMandantID'];
if ($iMandantID < 1) {
    # Kein Mandant ausgewählt.
    $currentUser = new User();
    $mandanten = $currentUser->getMandanten('SITZPLANADMIN');
    show_mandant_selection_dropdown($mandanten, 'iMandantID');
} else {
    # Mandant ausgewählt.
    $users = DB::getRows('
	SELECT a.USERID id, u.LOGIN, a.WANNBEZAHLT
	FROM USER u, ASTATUS a
	  LEFT JOIN SITZ s ON (s.RESTYP = 1 AND a.USERID = s.USERID AND a.MANDANTID = s.MANDANTID)
	WHERE a.MANDANTID = ?
	  AND s.REIHE IS NULL
	  AND a.USERID = u.USERID
	  AND a.STATUS IN (?, ?)
	', $iMandantID, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE);
?>
<p>Nachfolgend sind alle Benutzer aufgelistet, die noch keinen Sitzplatz reserviert haben.</p>

<table cellspacing="1" class="outer">
  <tr>
    <th>ID</th>
    <th>Nickname</th>
    <th>Zeitpunkt der Bezahlung</th>
    <th>Reservierung</th>
  </tr>
<?php
	if (! $users) {
?>
  <tr class="row-0">
    <td colspan="4">Es wurden keine Benutzer gefunden, die bezahlt, aber noch keinen Sitzplatz reserviert haben.</td>
  </tr>
<?php
        }
	$sql = '
	    SELECT DISTINCT s.EBENE
	    FROM SITZDEF s, ASTATUS a
	    WHERE s.MANDANTID = ?
	      AND s.MANDANTID = a.MANDANTID
	      AND a.USERID = ?
	      AND a.STATUS > 0
	    ORDER BY s.EBENE
	    ';
	$row_idx = 0;
	foreach ($users as $user) {
?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td style="text-align: right;"><?= $user['id'] ?></td>
    <td><a href="benutzerdetails.php?id=<?= $user['id'] ?>"><?= db2display($user['LOGIN']) ?></a></td>
    <td><?= dateDisplay2($user['WANNBEZAHLT']) ?> Uhr</td>
    <td>
<?php
	    # Welche Ebenen gibt es auf den Partys?
	    $ebenen = DB::getCol($sql, $iMandantID, $user['id']);
	    foreach ($ebenen as $ebene) {
	        if ($ebene != $ebenen[0]) {
		    echo ' &middot; ';
		}
		printf('<a href="sitzplan.php?nPartyID=%s&amp;ebene=%s&amp;userID=%s" target="_blank">Ebene %s</a> ' . "\n",
		    $iMandantID, $ebene, $user['id'], $ebene);
	    }
	}
?>
    </td>
  </tr>
</table>
<?php
}

include('nachspann.php');
?>
