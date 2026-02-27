<?php
/* Rechte überprüfen.
 * 
 * Per Variable als String oder Array von Strings festgelegte Rechte
 * auf Vorhandensein beim Benutzer überprüfen und, wenn nicht der Fall,
 * Fehlermeldung ausgeben und abbrechen.
 */

require_once('dblib.php');
require_once('security.php');

if (!isset($loginID) || $loginID <= 0) {
	deny();
} else if (is_array($iRecht)) {
    # Wenn ein Array aus Rechten übergeben wurde.
    $q = "SELECT DISTINCT r.RECHTID
          FROM RECHTZUORDNUNG r
          WHERE r.USERID = '$loginID'";
    $alleRechte = DB::getCol($q);

    # Prüfen, ob der User mindestens eins der erforderlichen Rechte hat.
    $intersection = array_intersect($iRecht, $alleRechte);
    if (! $intersection) {
        deny();
    }
} else {
    if (! BenutzerHatRecht($iRecht)) {
        deny();
    }
}


function deny() {
	
		// Gleich redirecten, wenn keine Rechte.
		header("Location: login.php");
	
    echo "<h1>Zugang verweigert</h1>\n";
    echo '<p class="fehler">Sie haben nicht die benötigten Zugriffsrechte für diese Seite.</p>' . "\n";
		if (!isset($loginID) || $loginID <= 0) {
			echo "<p><a href='login.php' target='_top'>Einloggen</a></p>\n";
		}
    exit;
}
?>
