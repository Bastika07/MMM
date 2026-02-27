<?php
/* Ausgabe einer Liste der Orgas, gruppiert nach Team, Trainees und Gastadmins. */

require_once('dblib.php');
require_once('format.php');
include_once('session.php');
include_once('language.inc.php');

?>

<p><?= $str['teamlist_satz1'] ?></p>

<?php 
/* "Wollt ihr dem Team beitreten auskommentiert: 
<p style="text-align: justify;"><?= $str['teamlist_satz2'] ?></p> */ 
?>

<?php
function displayOrga($user) {
    global $str;
?>
<table cellpadding="2" cellspacing="1" border="0" lass="rahmen_allg">
  <tr>
    <td class="pelas_benutzer_prefix" width="120">Login</td>
    <td class="pelas_benutzer_inhalt" width="272"><?= db2display($user['LOGIN']) ?></td>
    <td class="pelas_benutzer_inhalt" rowspan="4" valign="top" width="118"><?= displayUserPic($user['USERID']) ?></td>
  </tr>
  <tr>
    <td class="pelas_benutzer_prefix">Name</td>
    <td class="pelas_benutzer_inhalt"><?= db2display($user['NAME']) ?> <?= db2display($user['NACHNAME']) ?></td>
  </tr>
  <tr>
    <td class="pelas_benutzer_prefix"><?= $str['teamlist_aufgabe'] ?></td>
    <td class="pelas_benutzer_inhalt"><?= db2display($user['AUFGABE']) ?></td>
  </tr>
  <tr>
    <td class="pelas_benutzer_prefix">Email</td>
    <td class="pelas_benutzer_inhalt">
<?php if (LOCATION == 'intranet'): ?>
      <a href="javascript:openPELAS(<?= $user['USERID'] ?>);">PELAS.mail</a>
<?php else: ?>
      <a href="?page=17&nUserID=<?= $user['USERID'] ?>"><?= $str['kontakt_ub'] ?></a>
<?php endif; ?>
    </td>
  </tr>
</table><br/>
<?php
}

# Alle Orgas holen.
$orgas = DB::getRows('
    SELECT DISTINCT u.USERID, u.LOGIN, u.NAME, u.NACHNAME, u.EMAIL, ue.AUFGABE
    FROM USER u
      INNER JOIN RECHTZUORDNUNG r USING (USERID)
      LEFT JOIN USER_EXT ue USING(USERID)
    WHERE r.MANDANTID = ?
      AND ue.INAKTIV = 0
    ORDER BY u.LOGIN
    ', $nPartyID);

# Orgas sortieren.
$team = array();
$trainees = array();
$gastadmins = array();
foreach ($orgas as $orga) {
    if (User::hatRecht('GASTADMIN', $orga['USERID'], $nPartyID)) {
        $gastadmins[] = $orga;
    } elseif (User::hatRecht('TEAMMEMBER2', $orga['USERID'], $nPartyID)) {
        $team[] = $orga;
    } elseif (User::hatRecht('TEAMMEMBER', $orga['USERID'], $nPartyID)) {
        $trainees[] = $orga;
    }
}

# Orgas ausgeben.

foreach ($team as $orga) {
    displayOrga($orga);
}

if ($trainees) {
    echo "<br/><h2>Trainees</h2>\n";
    echo "<p>Trainees sind Bewerber auf einen Posten im Team. Sie sind zum ersten Mal bei dieser Party dabei.</p>\n";
}
foreach ($trainees as $orga) {
    displayOrga($orga);
}

if ($gastadmins) {
    echo "<br/><h2>Gastadmins</h2>\n";
    echo "<p>Gastadmins gehören nicht zum innovaLAN-Team, unterstützen aber bei der Durchführung der Veranstaltung.</p>\n";
}
foreach ($gastadmins as $orga) {
    displayOrga($orga);
}
?>
