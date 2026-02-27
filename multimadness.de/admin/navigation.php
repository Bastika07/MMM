<?php
require('controller.php');
require_once('dblib.php');
require_once('security.php');
$iRecht = 'TEAMMEMBER';
require_once 'checkrights.php';
require_once('format.php');


$currentUser = new User();
$rights = $currentUser->getRights();

function showlink($label, $href, $required_rights, $target='haupt') {
    global $rights;
    echo '  <li>';
    if (! is_array($required_rights)) {
        $required_rights = array($required_rights);
    }
    $found = false;
    foreach ($required_rights as $rr) {
        if (in_array($rr, $rights)) { 
            printf('<a href="%s" target="%s">%s</a>', $href, $target, $label);
            $found = true;
            break;
        }
    }
    if (! $found) {
        echo $label;
    }
    echo "</li>\n";
}
?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="style/navigation.css">
</head>
<body>

<ul>
  <li><a href="home.php" target="haupt">Home/Termine</a></li>
</ul>

<h2>User: <?= $currentUser->name; ?></h2>
<ul>
	<li><a href="benutzerprofil.php" target="haupt">Mein Profil</a></li>
	<li><a href="login.php?Action=logout" target="haupt">Logout</a></li>
<?php
# TEAMMEMBER2
/*if (LOCATION <> "intranet") showlink('Intranet', 'http://www-lan.innovalan.de:81', 'TEAMMEMBER2', '_blank');
if (LOCATION <> "intranet") showlink('Intranet Admin', 'http://admin-lan.innovalan.de:82', 'TEAMMEMBER2', '_blank');*/
?>
</ul>

<?php if (LOCATION <> "intranet") { ?>
<h2>Team</h2>
<ul>
<?php
# TEAMMEMBER
showlink('Forum', 'forum.php', 'TEAMMEMBER');
showlink('FTP-Server', 'transfer.php', 'TEAMMEMBER');
showlink('Anwesenheit', 'anwesenheit.php', 'TEAMMEMBER');
//showlink('Mitfahrzentrale', 'mitfahrzentrale.php', 'TEAMMEMBER');
showlink('Teamliste', 'teamliste.php', 'TEAMMEMBER');
//showlink('Fahrtkostenrechnung', 'ftp://tmpupload:fee8Lich@80.252.99.166/Fahrtkostenabrechnung.pdf', 'TEAMMEMBER', '_blank');
?>
</ul>
<?php } ?>

<h2>Benutzer</h2>
<ul>
<?php
# USERADMIN / TECHNIKADMIN / USERADMIN_READONLY
showlink('Anlegen', 'benutzerdetails.php', 'USERADMIN');
showlink('Verwalten', 'benutzerverwaltung.php', array('USERADMIN', 'USERADMIN_READONLY'));
showlink('Gastserverliste', 'gastserver.php', 'TECHNIKADMIN');
if (LOCATION <> "intranet") showlink('Medien verwalten', 'archivadmin2.php', 'ARCHIVADMIN');
?>
</ul>

<h2>Accounting</h2>
<ul>
<?php
# ACCOUNTADMIN / STATISTIKADMIN / SITZPLANADMIN
showlink('Bestellungen', 'tickets_bestellungen.php', 'ACCOUNTINGADMIN');
showlink('Sitzpl&auml;tze &auml;ndern', 'tickets_sitze.php', 'SITZPLANADMIN');
showlink('Auswertung &amp; Storno', 'tickets_artikelauswertung.php', array('ACCOUNTINGADMIN', 'STATISTIKADMIN'));
showlink('Checkin &amp; Abendkasse', 'tickets_checkin_abendkasse.php', 'EINLASSADMIN');
showlink('Ausstellereingang', 'ausstellereingang.php', 'EINLASSADMIN');
?>
</ul>

<h2>Turniere</h2>
<ul>
<?php
# TURNIERADMIN / TURNIERLEITUNG
showlink('Verwalten', 'turnier/turnier_verwaltung_list.php', array('TURNIERADMIN', 'TURNIERLEITUNG'));
showlink('Admin Panel', 'turnier/tap.php', array('TURNIERADMIN', 'TURNIERLEITUNG'));
showlink('Turnierleitung', 'turnier/turnier_verwaltung.php', 'TURNIERLEITUNG');
showlink('Turniergruppen', 'turnier/turnier_gruppen.php', 'TURNIERLEITUNG');
showlink('Liga Support', 'turnier/turnier_ligasupport.php', 'TURNIERLEITUNG');
?>
</ul>

<h2>News</h2>
<ul>
<?php
# NEWSADMIN
showlink('Verwalten', 'news.php', 'NEWSADMIN');
showlink('Bilderadmin', 'newsbild.php', 'NEWSADMIN');
?>
</ul>

<h2>Bilder</h2>
<ul>
<?php
# BILDERADMIN
showlink('Slideradmin', 'sliderbilder.php', 'BILDERADMIN');
?>
</ul>

<?php if (LOCATION <> "intranet") { ?>
<h2>Mailing</h2>
<ul>
<?php
# MAILINGADMIN
showlink('Anlegen', 'redaktion.php?nKategorieID=' . $KATEGORIE_MAILING . '&amp;nActionID=1&amp;iAction=new', 'MAILINGADMIN');
showlink('Verwalten', 'redaktionsverwaltung.php?nKategorieID=' . $KATEGORIE_MAILING, 'MAILINGADMIN');
?>
</ul>
<?php } ?>

<h2>Umfrage</h2>
<ul>
<?php
# UMGFRAGEADMIN
showlink('Verwalten', 'umfrageadmin.php', 'UMFRAGEADMIN');
?>
</ul>

<?php if (LOCATION == "intranet") { ?>
<h2>Beamerfolien</h2>
<ul>
<?php
# UMGFRAGEADMIN
showlink('Verwalten', 'beameradmin.php', 'BEAMERADMIN');
showlink('Neu anlegen', 'beameradmin_edit.php', 'BEAMERADMIN');

showlink('Duschen', 'duschen.php', 'TEAMMEMBER');
?>
</ul>
<?php } ?>

<?php if (LOCATION == "intranet") { ?>
<h2>Casemod</h2>
<ul>
<?php
# UMGFRAGEADMIN
showlink('Status', 'casemod.php?action=status', 'CASEMODADMIN');
showlink('Jurybewertung', 'casemod.php?action=jury', 'CASEMODADMIN');
?>
</ul>
<?php } ?>


<?php if (LOCATION <> "intranet") { ?>
<h2>NorthCon.TV</h2>
<ul>
<?php
showlink('Sendeplan', 'eatv_schedule.php' , 'ESPORTARENATV');
?>
</ul>
<?php } ?>


<h2>Mein Mandant</h2>
<ul>
<?php
# MANDANTADMIN / LAPADMIN
showlink('Partys', 'mandant_party.php', 'MANDANTADMIN');
showlink('Tickets &amp; Artikel', 'mandant_tickets.php', 'MANDANTADMIN');
showlink('Einstellungen', 'config.php', 'MANDANTADMIN');
showlink('Sitzplan generieren', 'setup_sitzplan_generate.php', 'MANDANTADMIN');
?>
</ul>

<?php
if (LOCATION <> "intranet") {
# MANDANTADMIN
if (in_array('MANDANTADMIN', $rights)) {
?>
<h2>Mandanten</h2>
<ul>
<?php
showlink('Verwalten', 'mandanten.php', 'MANDANTADMIN');
?>
</ul>
<?php
}
}
?>

<?php if (LOCATION <> "intranet") { ?>
<h2>Export</h2>
<ul>
<?php
# USERADMIN
showlink('Export', 'as_export.php', 'USERADMIN');
showlink('Sitzplatznummern', 'export_sitzplatznummern.php', 'SITZPLANADMIN');
?>
</ul>
<?php } ?>

<?php if (LOCATION <> "intranet") { ?>
<h2>alt/unbenutzt</h2>
<ul>
<?php
showlink('Anmeldestatistik', 'anmeldestatistik.php', 'STATISTIKADMIN');
showlink('User ohne Platz', 'benutzer_ohne_sitzplatz.php', array('USERADMIN', 'USERADMIN_READONLY'));
showlink('Bungalowbelegung', 'export_bungalows.php', 'USERADMIN');
// showlink('Archiv verwalten (altes)', 'archivadmin.php', 'ARCHIVADMIN');
?>
</ul>
<?php } ?>

</body>
</html>
