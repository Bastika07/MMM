<?php
/*
Teamliste

Man kann nur die Teammember der Mandanten sehen, bei denen man auch selber
Teammember ist
*/
require('controller.php');
require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require_once('checkrights.php');
require_once('admin/helpers.php');
include('admin/vorspann.php');


$currentUser = new User();
$mandanten = $currentUser->getMandanten();

echo "<h1>Teamliste</h1>\n";
show_mandant_selection_dropdown($mandanten, 'mandantid');

$mandantID = (int) $_REQUEST['mandantid'];
if (array_key_exists($mandantID, $mandanten)) {
    $q = 'SELECT r.RECHTID,
                 u.USERID, u.USERID, u.NAME, u.NACHNAME, u.LOGIN, u.EMAIL, u.PLZ, u.ORT, u.SKYPE,
		 ue.TELEFON, ue.MOBIL, ue.AUFGABE, ue.GEBURTSTAG, ue.INAKTIV, uet.description, uet.leader_id, uet.proxy_id
	  FROM RECHTZUORDNUNG r
	    LEFT JOIN USER u ON (u.USERID = r.USERID)
	    LEFT JOIN USER_EXT ue ON (u.USERID = ue.USERID)
	    LEFT JOIN user_ext_team uet ON (ue.TEILTEAMID = uet.id)
	  WHERE r.MANDANTID = "' . $mandantID . '"
	    AND (r.RECHTID = "TEAMMEMBER"
	      OR r.RECHTID = "TEAMMEMBER2"
	      OR r.RECHTID = "GASTADMIN")
	  ORDER BY ue.TEILTEAMID, u.LOGIN';
    $rows = DB::getRows($q);
    $list = array();
    foreach ($rows as $row) {
        $id = $row['USERID'];
	
	# Auf existierende Einträge in der Liste anhand der User-ID als Key
	# prüfen und den Eintrag ersetzen, wenn der aktuelle Datensatz
	# höhere Rechte hat.
        if (! isset($list[$id])) {
            # Neuer Eintrag
            $list[$id] = $row;
        } elseif ($list[$id]['RECHTID'] == 'GASTADMIN') {
            # Gastadmin, der noch höhere Rechte hat (Trainee/Member).
            $list[$id] = $row;
        } elseif ($list[$id]['RECHTID'] == 'TEAMMEMBER') {
            # Trainee, der noch höhere Rechte hat (Member).
            if ($row['RECHTID'] == 'TEAMMEMBER2') {
                $list[$id] = $row;
            }
        }
    }

    $details = (User::hatRecht('USERADMIN', -1, $mandantID) or User::hatRecht('USERADMIN_READONLY', -1, $mandantID));

    $groups = array(
        'TEAMMEMBER2' => 'Team',
	'TEAMMEMBER' => 'Trainees',
	'GASTADMIN' => 'Gastadmins');
    foreach ($groups as $right => $title) {
        show_users($title, filter_users($list, $right), $details);
    }
} else {
    if (array_key_exists($mandantID, PELAS::mandantArray(False))) {
        PELAS::fehler('Keine Rechte zum Anzeigen.');
        exit;
    }
}

include('admin/nachspann.php');

# ---------------------------------------------------------------- #

function filter_users($users, $right) {
    $filtered = array();
    foreach ($users as $user) {
        if ($user['RECHTID'] == $right) {
            $filtered[] = $user;
        }
    }
    return $filtered;
}

function show_users($title, $users, $details) {
    if (! $users) {
        return;
    }
?>
<h2><?= $title ?></h2>

<table cellspacing="1" class="outer">
  <tr>
    <th>Login</th>
    <th>Vor- &amp; Nachname</th>
    <th>Telefon</th>
    <th>Mobil</th>
    <th>Team</th>
    <th>PLZ, Ort</th>
<!--
	<th>Geburtstag</th>
    <th>Bild</th>
-->
    <th>Email</th>
	<th>Skype</th>
  </tr>
<?php
    $row_idx = 0;
		$mailingListe = array();
    foreach ($users as $user) {
        $name = db2display($user['LOGIN']);
        $name = '<a href="benutzerprofil.php?userid=' . $user['USERID'] . '">' . $name . '</a>';
        $image_path = 'userbild/' . $user['USERID'] . '.jpg';
        if (file_exists(PELASDIR . $image_path)) {
            $image = '<a href="' . PELASHOST . $image_path . '">Bild</a>';
        } else {
            $image = '-';
        }
?>
  <tr class="row-<?= ($user['INAKTIV'] ? 'inactive' : ($row_idx++ % 2)) ?>">
    <td><?= $name ?></td>
    <td><?= db2display($user['NAME']) ?> <?= db2display($user['NACHNAME']) ?></td>
    <td><?= db2display($user['TELEFON']) ?></td>
    <td><?= db2display($user['MOBIL']) ?></td>
    <td><?php
    	echo db2display($user['description']);
    	if ($user['leader_id'] == $user['USERID']) {
    		echo " (1. Leiter)";
    	} elseif ($user['proxy_id'] == $user['USERID']) {
    		echo " (2. Leiter)";
    	}
    ?></td>
    <td><?= db2display($user['PLZ']) ?> <?= db2display($user['ORT']) ?></td>
<!--
	<td><?php if ($user['GEBURTSTAG']) { echo dateDisplay2Short($user['GEBURTSTAG']); } ?></td>
    <td><?= $image ?></td>
-->
    <td><a href="mailto:<?= db2display($user['EMAIL']) ?>">E-Mail</a></td>
	<td><?= db2display($user['SKYPE']) ?> 
    <?php
    	echo ($user['SKYPE'] != "") ? " <small><a href='callto:".db2display($user['SKYPE'])."'>call</a></td></small>\n" : " -";
	?>
    </td>
  </tr>
<?php
	$mailingListe[] = $user['EMAIL'];
    }
?>
</table>
<?php

echo "<p style='padding:10px; border:1px solid #bbb; color:#bbb; max-width:800px;'>".htmlspecialchars(implode($mailingListe, '; '))."</p>\n";

}
include('admin/nachpann.php');
?>
