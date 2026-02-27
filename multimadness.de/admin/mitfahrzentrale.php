<?php
setlocale(LC_ALL, 'de_DE');
require('controller.php');
require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require_once('checkrights.php');
include('admin/vorspann.php');

$dbh = DB::connect();

/* Format a Unix timestamp as a German weekday+date string.
 * If $with_time is true, the time (HH:MM) is appended.
 */
function format_de_date($timestamp, $with_time = false) {
    static $days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    static $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    $result = sprintf('%s, %s. %s %s',
        $days[(int)date('w', $timestamp)],
        date('d', $timestamp),
        $months[(int)date('n', $timestamp)],
        date('Y', $timestamp)
    );
    if ($with_time) {
        $result .= ' - ' . date('H:i', $timestamp);
    }
    return $result;
}


$eventID = intval($eventID);
# Darf das Event angezeigt werden?
# (Ist der user Teammember bei dem Mandanten, zu dem das Event gehört?).
if (! empty($eventID) and ! right_on_event($eventID)) {
  PELAS::fehler('Kein Recht auf Event "' . $eventID . '"!');
  $eventID = 0;
}


$start_stunde = intval($start_stunde);
$end_stunde = intval($end_stunde);


if ($action == 'eventAdd') {
  # Event hinzufügen.
  $s = mktime(intval($start_h), 0, 0, intval($start_m), intval($start_d), intval($start_y));
  $e = mktime(intval($end_h), 0, 0, intval($end_m), intval($end_d), intval($end_y));
  
  if (empty($description)) {
    PELAS::fehler('Keine Beschreibung angegeben');
  } elseif ($s >= $e) {
    PELAS::fehler('Der Start muss vor dem Ende liegen');
  } elseif(! User::hatRecht('MANDANTADMIN', -1, $mandant)) {
    PELAS::fehler('Du hast kein Mandantenrecht an ' . PELAS::mandantByID($mandant));
  } else {
    $q = "INSERT INTO ANWESENHEITEN_EVENTS
            (mandantID, description, start, end)
            VALUES ('$mandant', '$description', '$s', '$e')";
    if (DB::query($q)) {
      PELAS::confirm('Eintragung erfolgreich!');
    } else {
      PELAS::fehler('Eintragung nicht erfolgreich!');
    }
  }  
}

if ($action == 'eventDelete') {
  eventDelete($event, $confirm);
}

if ($aktion == 'showdetails') {
  show_details($ID);  
} elseif ($aktion == 'mitfahrenadd') {
  mitfahrenadd($ID);
  show_details($ID);
} elseif ($aktion == 'mitfahrendel') {  
  mitfahrendel($ID);
  show_details($ID);
} elseif ($aktion == 'angebotdel') {  
  angebotdel($ID);
  show_event($eventID);
} elseif ($aktion == 'angebotadd') {  
  angebotadd($eventID);
} elseif ($aktion == 'angebotaddsave') {  
  angebotaddsave();
  show_event($eventID);
} else {
  if ($eventID) {
    # Formular abgeschickt, neues Intervall eintragen.
    show_event($eventID);
  } else {
    # Es wurde keine gültige EventID (0, string, etc) angegeben. Menü anzeigen.
    show_menue();
  }
}

# ---------------------------------------------------------------- #

/* Mitfahrer hinzufügen. */
function mitfahrenadd($id) {
  DB::query('INSERT INTO MITFAHRZENTRALE (mfzID, usrid, description, wannangelegt) VALUES (?, ?, ?, ?)',
      intval($_POST['ID']), User::loginID(), $_POST['KOMMENTAR'], time());
}

/* Mitfahrer entfernen. */
function mitfahrendel($id) {
  $q = 'DELETE FROM MITFAHRZENTRALE
        WHERE mfzID = ' . $id . '
	  AND usrid = ' . User::loginID();
  DB::query($q);
}

/* Angebot hinzufügen. */
function angebotadd($eventID) {
    $class = 'hblau';
    $class2 = 'dblau';
  
    $q = 'SELECT description
          FROM ANWESENHEITEN_EVENTS
          WHERE event = ' . $eventID;
    $event_title = DB::getOne($q);
?>
<h1>Mitfahrzentrale</h1>

<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
<?= csrf_field() ?>
  <input type="hidden" name="aktion" value="angebotaddsave"/>
  <input type='hidden' name='eventID' value="<?= $eventID ?>"/>
<table cellspacing="1" class="outer">
  <tr>
    <th colspan="2">Mitfahrmöglichkeit für &quot;<?= $event_title ?>&quot; anbieten</th>
  </tr>
  <tr class="row-0">
    <td>Fahrtrichtung:</td>
    <td>
      <select name="RICHTUNG">
        <option>Hinfahrt</option>
        <option>Rückfahrt</option>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Abfahrts-/Zielort:</td>
    <td><input type="text" size="27" name="ORT"/></td>
  </tr>
  <tr class="row-0">
    <td>Abfahrtstag:</td>
    <td>
      <select name="TAG">
<?php
    foreach (range(1, 31) as $i) {
        printf('<option value="%s">%s</option>' . "\n", $i, $i);
    }
?>
      </select>
      <select name="MONAT">
<?php
    foreach (range(1, 12) as $i) {
        printf('<option value="%s">%s</option>' . "\n", $i, $i);
    }
?>
      </select>
      <select name="JAHR">
<?php
    $thisyear = (int) date('Y');
    foreach (array($thisyear, $thisyear + 1) as $i) {
        printf('<option value="%s">%s</option>' . "\n", $i, $i);
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Abfahrtszeit:</td>
    <td>
      <select name="STUNDE">
<?php
    foreach (range(0, 23) as $i) {
        printf('<option value="%s">%s</option>' . "\n", $i, $i);
    }
?>
      </select>:<select name="MINUTE">
<?php
    foreach (array('00', '15', '30', '45') as $i) {
        printf('<option value="%s">%s</option>' . "\n", $i, $i);
    }
?>
      </select> Uhr
    </td>
  </tr>
  <tr class="row-0">
    <td>freie Plätze:</td>
    <td>
      <select name="PLAETZE">
<?php
    foreach (range(1, 9) as $i) {
        printf('<option value="%s">%s</option>' . "\n", $i, $i);
    }
?>
      </select>
    </td>
  </tr>
  <tr class="row-1">
    <td>Kommentar:</td>
    <td><textarea name="KOMMENTAR" cols="20" rows="8"></textarea></td>
  </tr>
  <tr class="row-0">
    <td>&nbsp;</td>
    <td><input type="submit" value="anbieten"/></td>
  </tr>
</table>
<?php
}

/* Neues Angebot speichern. */
function angebotaddsave() {
    $time = sprintf('%s/%s/%s %s:%s:00', $_POST['MONAT'], $_POST['TAG'], $_POST['JAHR'],
        $_POST['STUNDE'], $_POST['MINUTE']);
    $startzeit = strtotime($time);
    DB::query('INSERT INTO MITFAHRZENTRALE_ANGEBOTE
            (event, usrid, richtung, startzeit, ort, plaetze, description, wannangelegt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        intval($_POST['eventID']), User::loginID(), $_POST['RICHTUNG'], $startzeit,
        $_POST['ORT'], intval($_POST['PLAETZE']), $_POST['KOMMENTAR'], time());
}

/* Angebot löschen.  */
function angebotdel($id) {
    $q = 'SELECT ID
          FROM MITFAHRZENTRALE_ANGEBOTE
          WHERE usrid = ' . User::loginID() . '
            AND ID = ' . $id;
    $id = DB::getOne($q);

    $q = 'DELETE FROM MITFAHRZENTRALE
          WHERE mfzID = ' . $id;
    DB::query($q2);

    $q = 'DELETE FROM MITFAHRZENTRALE_ANGEBOTE
          WHERE ID = ' . $id . '
            AND usrid = ' . User::loginID();
    DB::query($q);
}

/* Angebote auflisten. */
function show_menue() {
    $q = 'SELECT e.event, e.start, e.end, e.description, e.mandantID, m.BESCHREIBUNG
          FROM ANWESENHEITEN_EVENTS as e, MANDANT as m
          WHERE m.MANDANTID = e.mandantID
          ORDER BY e.start DESC';
    $offers = DB::getRows($q);
?>
<h1>Mitfahrzentrale</h1>

<table cellspacing="1" class="outer">
  <tr>
    <th>Event</th>
    <th>von</th>
    <th>bis</th>
  </tr>
<?php
    $row_idx = 0;
    foreach ($offers as $offer) {
        if (! User::hatRecht('TEAMMEMBER', -1, $offer['mandantID'])) {
            continue;
	}
?>
  <tr class="row-<?= $row_idx++ % 2; ?>">
    <td><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?eventID=<?= $offer['event'] ?>"><?= db2display($offer['description']) ?></a></td>
    <td><?= format_de_date($offer['start']) ?></td>
    <td><?= format_de_date($offer['end']) ?></td>
  </tr>
<?php
    }
?>
</table>
<?php
}

/* Mitfahrgelegenheiten für ein Event anzeigen. */
function show_event($eventID) {
    $q = 'SELECT description
          FROM ANWESENHEITEN_EVENTS
          WHERE event = ' . $eventID;
    $event_title = DB::getOne($q);
?>
<h1>Mitfahrzentrale: Angebote für &quot;<?= $event_title ?>&quot;</h1>
<p><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?aktion=angebotadd&amp;eventID=<?= $eventID ?>">Angebot hinzufügen</a></p>
<?php
    foreach (range(1, 2) as $i) {
        if ($i == '1') {
            $richtung = 'Hinfahrt';
        } else {
            $richtung = 'Rückfahrt';
        }

        # Daten für Event holen.
        $q = 'SELECT id, usrid, startzeit, ort, plaetze
              FROM MITFAHRZENTRALE_ANGEBOTE
              WHERE event = ' . $eventID . '
	        AND richtung = "' . $richtung . '"
              ORDER BY startzeit ASC';
	$offers = DB::getRows($q);
?>
<h2><?= $richtung ?></h2>

<table cellspacing="1" class="outer">
  <tr>
    <th>Teammitglied</th>
    <th><?= ($i == 1) ? 'Abfahrtsort' : 'Zielort' ?></th>
    <th>Abfahrt</th>
    <th>freie Plätze</th>
  </tr>
<?php
        $row_idx = 0;
        foreach ($offers as $offer) {
            # Usernamen suchen.
            $q = 'SELECT LOGIN
                  FROM USER
                  WHERE USERID = ' . $offer['usrid'];
	    $username = DB::getOne($q);
 
            # Freie Plätze ermitteln.
	    $q = 'SELECT COUNT(ID)
	          FROM MITFAHRZENTRALE
	          WHERE mfzID = ' . $offer['id'];
	    $freieplaetze = $offer['plaetze'] - DB::getOne($q);
            if ($freieplaetze) {
                $freieplaetzeshow = $freieplaetze . ' / ' . $offer['plaetze'];
	    } else {
                $freieplaetzeshow = 'keine';
            }
?>
  <tr class="row-<?= $row_idx++ % 2; ?>">
    <td><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?aktion=showdetails&amp;ID=<?= $offer['id'] ?>"><?= $username ?></a></td>
    <td><?= $offer['ort'] ?></td>
    <td><?= format_de_date($offer['startzeit'], true) ?> Uhr</td>
    <td style="text-align: center;"><?= $freieplaetzeshow ?></td>
  </tr>
<?php
        }
?>
</table>
<?php
    }
}

function show_details($id) {
    # Daten für Event holen.
    $q = 'SELECT *
          FROM MITFAHRZENTRALE_ANGEBOTE
          WHERE ID = ' . $id;
    $row = DB::getRow($q); 
  
    $q2 = 'SELECT description
           FROM ANWESENHEITEN_EVENTS
           WHERE event = ' . $row['event'];
    $row2 = DB::getRow($q2);   
         
    # Usernamen suchen.
    $q3 = 'SELECT LOGIN, EMAIL
           FROM USER
           WHERE USERID = ' . $row['usrid'];
    $row3 = DB::getRow($q3);
  
    $class = 'hblau';
    $class2 = 'dblau';

    if ($row['richtung'] == 'Hinfahrt') {
        $Menutitelrichtung = 'Abfahrtsort';
    } else {
        $Menutitelrichtung = 'Zielort';
    }

    # Freie Plätze ermitteln.
    $q = 'SELECT COUNT(ID)
          FROM MITFAHRZENTRALE
          WHERE mfzID = ' . $id;
    $freieplaetze = $row['plaetze'] - DB::getOne($q);
    if ($freieplaetze) {
	$freieplaetzeshow = $freieplaetze . ' / ' . $row['plaetze'];
    } else {
	$freieplaetzeshow = 'keine';
    }
          
    $dabei = DB::getOne('SELECT COUNT(*) FROM MITFAHRZENTRALE WHERE mfzID = ? AND usrid = ?',
        intval($row['ID']), User::loginID());

    if ($row['usrid'] != User::loginID()) {
        if ($dabei == '0') {
            if ($freieplaetze) {
                $aktionshow = "Kommentar:
                    <form method='post' action='mitfahrzentrale.php'>
                    " . csrf_field() . "<textarea name='KOMMENTAR' cols='20' rows='7'></textarea><br>
                    <input type='hidden' name='aktion' value='mitfahrenadd'>
                    <input type='hidden' name='ID' value='" . $id . "'>
                    <input type='submit' name='Submit' value='Mitfahren'>
                    </form>";
            } else {
               $aktionshow = 'kein möglich';
            }
        } else {
            $aktionshow = "<a href='mitfahrzentrale.php?aktion=mitfahrendel&ID=" . $id . "'>mitfahren löschen</a>";
        }
    } else {
        $aktionshow = "<a href='mitfahrzentrale.php?aktion=angebotdel&eventID=" . $row['event'] . "&ID=" . $id . "'>Angebot löschen</a>";
    }
?>
<h1>Mitfahrzentrale: Angebote für &quot;<?= $row2['description'] ?>&quot;</h1>
 
<table cellspacing="0" class="outer">
  <tr>
    <th colspan="2">Angebot von <?= $row3['LOGIN'] ?> (<?= $row['richtung'] ?>)</th>
  </tr>
  <tr class="row-0">
    <td><?= $Menutitelrichtung ?>:</td>
    <td><?= htmlspecialchars($row['ort'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
  </tr>
  <tr class="row-1">
    <td>Abfahrtstag:</td>
    <td><?= format_de_date($row['startzeit']) ?></td>
  </tr>
  <tr class="row-0">
    <td>Abfahrtszeit:</td>
    <td><?= date('H:i', $row['startzeit']) ?> Uhr</td>
  </tr>
  <tr class="row-1">
    <td>Freie Plätze:</td>
    <td><?= $freieplaetzeshow ?></td>
  </tr>
  <tr class="row-0">
    <td>Kontakt:</td>
    <td><a href="mailto:<?= $row3['EMAIL'] ?>"><?= $row3['EMAIL'] ?></a></td>
  </tr>
  <tr class="row-1">
    <td>Kommentar:</td>
    <td><?= htmlspecialchars($row['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
  </tr>
  <tr class="row-0">
    <td>&nbsp;</td>
    <td><?= $aktionshow ?></td>
  </tr>
</table>

<h2>Mitfahrer</h2>
<?php
    # Daten der Mitfahrer holen.
    $q = 'SELECT m.*, u.LOGIN, u.EMAIL
          FROM MITFAHRZENTRALE m, USER u
          WHERE m.mfzID = ' . $id . '
            AND m.usrid = u.USERID
          ORDER BY wannangelegt ASC';
    $passengers = DB::getRows($q);
    if ($passengers) {
?>
<table cellspacing="0" class="outer">
  <tr>
    <th>Nr.</th>
    <th>Teammitglied</th>
    <th>Kommentar</th>
  </tr>
<?php
	$row_idx = 0;
	foreach ($passengers as $row) {
?>
  <tr class="row-<?= $row_idx++ % 2 ?>">
    <td><?= $row_idx ?></td>
    <td><a href="mailto:<?= $row['EMAIL'] ?>"><?= $row['LOGIN'] ?></a></td>
    <td><?= db2display($row['description']) ?></td>
  </tr>
<?php
        }
?>
</table>
<?php
    } else {
        echo "<p>Zur Zeit keine Mitfahrer.</p>\n";
    }
}

# ---------------------------------------------------------------- #
# Hilfsfunktionen

/* Hat der eingeloggte User das Recht an $eventID?
 * (Ist er Teammember bei dem Mandanten, zu dem das Event gehört?)
 */
function right_on_event($eventID) {
    $q = 'SELECT COUNT(*)
          FROM RECHTZUORDNUNG r, ANWESENHEITEN_EVENTS a
          WHERE a.event = ' . $eventID . '
            AND r.RECHTID = "TEAMMEMBER"
            AND r.MANDANTID = a.mandantID
            AND r.USERID = ' . User::loginID();
    return (DB::getOne($q) > 0);
}

function mandantByEvent($eventID) {
    $q = 'SELECT mandantID
          FROM ANWESENHEITEN_EVENTS
          WHERE event = ' . $eventID;
    return DB::getOne($q);
}

function isOwner($id) {
    $q = 'SELECT COUNT(*)
          FROM ANWESENHEITEN
          WHERE ID = ' . $id . '
            AND usrid = ' . User::loginID();
    return (DB::getOne($q) > 0);
}

function type($id) {
    $q = 'SELECT usrid
          FROM ANWESENHEITEN
          WHERE ID = ' . $id;
    return (DB::getOne($q) == -1) ? 't' : 'p';
}


include('admin/nachspann.php');
?>
