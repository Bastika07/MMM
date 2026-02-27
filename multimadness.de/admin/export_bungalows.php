<?php
/* LANresort Bungalow-Belegung */
require('controller.php');
require_once('dblib.php');
$iRecht = array('USERADMIN', 'USERADMIN_READONLY');
require('checkrights.php');
include('vorspann.php');


# Bungalows mit Hauptmieter auslesen.
$bungalows = DB::getRows('
    SELECT b.ID id,
      CONCAT(u.NACHNAME, ", ", u.NAME, ", ", u.PLZ, " ",u.ORT) mieter
    FROM bungalows b
      INNER JOIN USER u ON (b.bookedBy = u.USERID)
    ORDER BY b.ID
    ');
?>
<h1>Belegung der Bungalows</h1>

<p>Es sind <strong><?= count($bungalows) ?></strong> Bungalows gebucht.</p>

<p><table>
<?php
foreach ($bungalows as $bungalow) {
?>
  <tr>
    <td>Bungalow-Nr.:</td>
    <td><strong><?= $bungalow['id'] ?></strong></td>
  </tr>
  <tr>
    <td>Mieter:</td>
    <td><strong><?= $bungalow['mieter'] ?></strong></td>
  </tr>
<?php
    # Untermieter dieses Bungalows auslesen.
    $sub_rows = DB::getRows('
        SELECT CONCAT(u.NACHNAME, ", ", u.NAME, ", ", u.PLZ, " ",u.ORT) mieter
	FROM bungalow2user b
	  INNER JOIN USER u ON (b.userID = u.USERID)
	WHERE b.bungalow = ?
	ORDER BY u.NACHNAME
	', $bungalow['id']);
    $count = 0;
    foreach ($sub_rows as $sub_row) {
        echo "  <tr>\n";
	echo '    <td>' . (($count == 0) ? 'Untermieter:' : '&nbsp;') . "</td>\n";
	echo '    <td>' . $sub_row['mieter'] . "</td>\n";
        echo "  </tr>\n";
        $count++;
    }
    echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
}
?>
</table></p>

<?php
include('nachspann.php');
?>
