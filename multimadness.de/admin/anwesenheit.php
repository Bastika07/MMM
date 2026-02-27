<?php
/*
 * Anwesenheitsübersicht
 * Events sind immer einem Mandanten zugehörig. Events können nur angelegt werden, wenn
 * man das Recht "MANDANTADMIN" zu dem Mandanten besitzt, zu dem das Event gehört.
 */

setlocale(LC_ALL, 'de_DE', 'de_DE@euro');

require('controller.php');

require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require('checkrights.php');
require_once('util.php');

require('format.php');
require('admin/vorspann.php');


$user_id = User::loginID();
# Darf das Event angezeigt werden?
# (Ist der user Teammember bei dem Mandanten, zu dem das Event gehört?)
if (! empty($_GET['eventID']) and ! right_on_event($_GET['eventID'])) {
  PELAS::fehler('Kein Recht auf Event "' . $_GET['eventID'] . '"!');
  $_GET['eventID'] = 0;
}

if (($_GET['action'] == 'update_absence') and ($_SERVER['REQUEST_METHOD'] == 'POST')) {
    $is_absent = (int) (bool) $_POST['is_absent'];
    # Alle eventuellen bisherigen Abwesenheitseinträge löschen.
    DB::query('
        DELETE FROM ANWESENHEITEN
	WHERE event = ?
	  AND usrid = ?
	  AND start = 0
	  AND end = 0
        ', intval($_GET['eventID']), $user_id);
    if ($is_absent) {
        # Neuen Abwesenheitseintrag einfügen.
        DB::query('
	    INSERT INTO ANWESENHEITEN (event, usrid, start, end)
	    VALUES (?, ?, 0, 0)
	    ', $_GET['eventID'], $user_id);
    }
    header('Location: anwesenheit.php?eventID=' . intval($_GET['eventID']));
    exit;
} elseif ($_GET['action'] == 'eventAdd') {
    # Event hinzufügen.
    $s = mktime(intval($_POST['start_h']), 0, 0, intval($_POST['start_m']), intval($_POST['start_d']), intval($_POST['start_y']));
    $e = mktime(intval($_POST['end_h']), 0, 0, intval($_POST['end_m']), intval($_POST['end_d']), intval($_POST['end_y']));
  
    if (empty($_POST['description'])) {
        PELAS::fehler('Keine Beschreibung angegeben.');
    } elseif ($s >= $e) {
        PELAS::fehler('Der Start muss vor dem Ende liegen.');
    } elseif(!User::hatRecht('MANDANTADMIN', -1, $_POST['mandant'])) {
        PELAS::fehler('Du hast kein Mandantenrecht an '.PELAS::mandantByID($_POST['mandant']));
    } else {
        $q = "INSERT INTO ANWESENHEITEN_EVENTS
              (mandantID, description, start, end, show_calendar)
              VALUES ('".intval($_POST['mandant'])."', '".safe($_POST['description'])."', '$s', '$e', ".intval($_POST['show_calendar']).")";
        if (DB::query($q)) {
            PELAS::confirm('Eintragung erfolgreich!');
        } else {
            PELAS::fehler('Eintragung nicht erfolgreich!');
        }
    }  
} elseif ($_GET['action'] == 'eventDelete') {
    eventDelete($_GET['event'], $confirm);
}

if ($_GET['eventID']) {
    # Formular abgeschickt, neues Intervall eintragen.
    if ($_POST['start_tmj']) {
        intervalAdd($_GET['eventID'], $_POST['start_stunde'], $_POST['start_tmj'], $_POST['end_stunde'], $_POST['end_tmj'], $_POST['description'], $_POST['type']);
    }
    if ($_GET['action'] == 'delete') {
        intervalDelete($_GET['interval'], $_GET['eventID']);
    }
    # Event wurde angegeben, Ãœbersicht anzeigen.
    show_event($_GET['eventID']);
} else {
    # Keine oder eine ungültige EventID (0, string etc.) wurde angegeben; Menü anzeigen.
    show_index();
}

# ---------------------------------------------------------------- #

/* Ãœbersicht der vorhandenen Anwesenheitslisten anzeigen. */
function show_index() {
    global $smarty;

    # Liste der Events zusammenstellen.
    $events = array();
    $q = 'SELECT e.event id, e.start, e.end, e.description name,
                 m.MANDANTID mandant_id, m.BESCHREIBUNG mandant_name
          FROM ANWESENHEITEN_EVENTS e
	       INNER JOIN MANDANT m ON (e.mandantID = m.MANDANTID)
          ORDER BY e.start DESC';
    foreach (DB::getRows($q) as $row) {
        if (User::hatRecht('TEAMMEMBER', -1, $row['mandant_id'])) {
	    		$events[] = $row;
        }
    }

    # Hat der User bei irgendeinem Mandanten Adminrechte?
    $user_is_admin = User::hatRecht('MANDANTADMIN');

    $dropdowns = array();
    $mandanten_admin = array();
    if ($user_is_admin) {
        foreach (array('start', 'end') as $name) {
            $dropdowns[] = sprintf("%s h %s.%s.%s",
                show_dropdown($name . '_h', 23, 0),
                show_dropdown($name . '_d', 31, 1),
                show_dropdown($name . '_m', 12, 1),
                show_dropdown($name . '_y', date('Y') + 5, date('Y') - 5)
		);
	}
        foreach (PELAS::mandantArray() as $id => $name) {
            # Hat der User Mandant-Rechte bei dem anzuzeigenden Mandanten?
            if (User::hatRecht('MANDANTADMIN', -1, $id)) {
		$mandanten_admin[$id] = $name;
            }
        }
    }

    # Template rendern.
    $smarty->assign(array(
        'events' => $events,
	'user_is_admin' => $user_is_admin,
	'dropdowns' => $dropdowns,
	'mandanten_admin' => $mandanten_admin,
	));
    $smarty->display('anwesenheit_index.tpl');
}

/* Gibt ein Dropdown-Feld aus. */
function show_dropdown($name, $to, $from=1) {
    global $$name;
    $out = sprintf('<select name="%s">'."\n", $name);
    for ($i = $from; $i <= $to; $i++) {
        $out .= sprintf('<option value="%d"%s>%02d</option>'."\n", $i, ($$name == $i) ? ' selected="selected"' : '', $i);
    }
    $out .= "</select>\n";
    return $out;
}

# ---------------------------------------------------------------- #

/* Einträge für das Event anzeigen. */
function show_event($eventID) {
    global $smarty;

    # Daten für Event holen.
    $event = DB::getRow('
        SELECT start, end, description
        FROM ANWESENHEITEN_EVENTS
        WHERE event = ?
        ', intval($eventID));
    if (! $event) {
        # Kein Event mit der ID, Umleitung zur Ãœbersicht.
        header('Location: anwesenheit.php');
        return;
    }


    # Daten für Aufgaben (Aufbau, Abbau etc.) aus der Datenbank holen.
    $q = 'SELECT start, end, description label
          FROM ANWESENHEITEN
          WHERE event = ' . intval($eventID) . '
            AND usrid < 0
          ORDER BY description';
    $tasks = get_points_in_time($q);

    # Daten für Anwesenheiten aus der Datenbank holen.
    $q = 'SELECT a.start, a.end, u.LOGIN label, uet.description
          FROM ANWESENHEITEN a, USER u
          LEFT JOIN (user_ext_team uet, USER_EXT ue)
          ON (uet.id = ue.TEILTEAMID AND ue.USERID = u.USERID)
          WHERE a.event = ' . intval($eventID) . '
            AND a.usrid > 0
            AND u.USERID = a.usrid
          ORDER BY uet.description, u.LOGIN';

    $presences = get_points_in_time($q, true);


    # Eine Liste `$hours` von Stunden anlegen.
    for ($i = $event['start']; $i <= $event['end']; $i += 3600) {
        $hours[] = Hour::fromTimestamp($i);
    }

    # Jeden Tag einmal in das Array `$days2show` packen.
    $unique_dates = array();
    $days2show = array();
    foreach ($hours as $hour) {
        $date = $hour->getDate();
        $date_str = $date->toString();
        if (! in_array($date_str, $unique_dates)) {
            $unique_dates[] = $date_str;
            $days2show[] = $date;
        }
    }


    # Anwesenheiten des aktuell eingeloggten Users holen und anzeigen.
    $user_presences = DB::getRows('
        SELECT ID, start, end
        FROM ANWESENHEITEN
        WHERE event = ?
          AND usrid = ?
	', intval($eventID), User::loginID());

    # Abwesenheit des aktuell eingeloggten Users feststellen.
    $user_is_absent = DB::getOne('
        SELECT COUNT(*) > 0
	FROM ANWESENHEITEN
	WHERE event = ?
	  AND usrid = ?
	  AND start = 0
	  AND end = 0
	', intval($eventID), User::loginID());

    # Als abwesend eingetragene Personen holen.
    $absents = DB::getRows('
        SELECT u.LOGIN, u.NAME, u.NACHNAME
	FROM ANWESENHEITEN a
	  INNER JOIN USER u ON (a.usrid = u.USERID)
	WHERE a.event = ?
	  AND a.start = 0
	  AND a.end = 0
	ORDER BY u.LOGIN
	', intval($eventID));

    # Aufgabenzeiten holen, wenn Recht dafür vorhanden.
    $user_is_mandantadmin = User::hatRecht('MANDANTADMIN', -1, mandantByEvent($eventID));
    if ($user_is_mandantadmin) {
        # Zeiten aus der DB holen und anzeigen.
	$task_items = DB::getRows('
            SELECT ID id, start, end, description
            FROM ANWESENHEITEN
            WHERE event = ?
              AND usrid = -1
    	    ', intval($eventID));
    }


    # Template rendern.
    $smarty->assign(array(
        'eventID' => $eventID,
	'event' => $event,
        'days2show' => $days2show,
	'hours' => $hours,
	'tasks' => $tasks,
	'presences' => $presences,
        'user_presences' => $user_presences,
	'days' => prepare_days($days2show, $hours, $tasks, $presences),
	'user_is_absent' => $user_is_absent,
	'hours_range' => range(0, 23),
        'start_stunde' => $start_stunde,
        'end_stunde' => $end_stunde,
	'absents' => $absents,
	'task_items' => $task_items,
	'user_is_mandantadmin' => $user_is_mandantadmin,
        ));
    $smarty->display('anwesenheit_show_event.tpl');
}

class Date {

    function Date($year, $month, $day) {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    function toTimestamp() {
        return mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }

    function toString() {
        return date('Y-m-d', $this->toTimestamp());
    }

    function createHour($hour) {
        return new Hour($this->year, $this->month, $this->day, $hour);
    }
}

class Hour extends Date {

    function Hour($year, $month, $day, $hour) {
        parent::Date($year, $month, $day);
        $this->hour = $hour;
    }

    function getDate() {
        return new Date($this->year, $this->month, $this->day);
    }

    function fromTimestamp($timestamp) {
        $time = explode(' ', date('Y m d H', $timestamp));
        return new Hour($time[0], $time[1], $time[2], $time[3]);
    }
}

/* Erstelle eine Liste von Zeitpunkten. */
function get_points_in_time($query, $addDesc = false) {
    $items = array();
    foreach (DB::getRows($query) as $row) {
       if ($addDesc == true) {
		$label = $row['label']." <i style='font-size:0.7em;'>".$row['description']."</i>";
	} else { 
		$label = $row['label'];
	}
        if (! array_key_exists($label, $items)) {
            $items[$label] = array();
        }

        # Erstelle eine Liste von Stunden.
        for ($i = $row['start']; $i <= $row['end']; $i += 3600) {
            $items[$label][] = Hour::fromTimestamp($i);
        }
    }
    return $items;
}

/* Eintragungen für die angegebenen Tage vorbereiten. */
function prepare_days($days, $hours, $tasks, $presences) {
    $tpl_days = array();
    foreach ($days as $date) {
        # Zeile mit Stunden erzeugen.
        $tpl_hours = array();
        foreach (range(0, 23) as $hour) {
            $tpl_hours[$hour] = hour_exists($date->createHour($hour), $hours);
        }

        # Zeiten einzeichnen. Dazu Aufgaben und Anwesenheiten des Teams durchgehen.
        $tpl_rows = array();
        $row_idx = 0;
        foreach (range(0, 1) as $z) {
            $is_task = ($z == 0);
            $rows = $is_task ? $tasks : $presences;
            foreach ($rows as $label => $entry_hours) {
                # Zeile (Aufgabe oder Anwesenheit) eines Tages ausgeben.
                $is_current_user = ((! $is_task) and ($label == User::name()));

                $show = False;
                $cells = array();
                foreach (range(0, 23) as $hour) {
                    $exists = hour_exists($date->createHour($hour), $entry_hours);
                    if ($exists) {
                        $show = True;
                    }
                    $cells[] = ($exists ? 1 : -1);
                }

                if ($show) {
                    # HTML für Zelleninhalt vorbereiten.
                    if ($is_task) {
                        $class = 'task';
                    } elseif ($is_current_user) {
                        $class = 'currentuser';
                    } else {
                        $class = Null;
                    }
                    $div = '<div' . ($class ? ' class="' . $class . '"' : '') . '>&nbsp;</div>';

                    # Ein Array für die Tabelle erstellen, dass colspans berücksichtigt.
                    $row = array();
                    foreach (sum_neighbors($cells) as $cell) {
                        $colspan = abs($cell);
 	                $colspan = ($colspan > 1) ? ' colspan="'.$colspan.'"' : '';
	                $content = ($cell > 0) ? $div : '';
	                $row[] = array('colspan' => $colspan, 'content' => $content);
	            }
	            $tpl_rows[] = array('label' => $label, 'cells' => $row);
                }
            }
        }
	$tpl_days[] = array('date' => $date, 'hours' => $tpl_hours, 'rows' => $tpl_rows);
    }
    return $tpl_days;
}

/* Feststellen, ob die Stunde in der Liste der Stunden des Users/der Aktion existiert. */
function hour_exists($hour, $hours) {
    foreach ($hours as $h) {
        if ($hour == $h) {
            return True;
        }
    }
    return False;
}

/* Nebeneinander liegende Werte im Array addieren.
 * Dabei werden jeweils positive und negative Werte zusammengefasst.
 * Nullen bleiben separat erhalten. Die Quersumme bleibt dabei gleich.
 *
 * Beispiel 1:
 *   vorher:  0, 1, 1, 0, 0, 0, 1, 1, 1, 1, 0, 1, 1
 *   nachher: 0, 2, 0, 0, 0, 4, 0, 2
 *
 * Beispiel 2:
 *   vorher:  -1, -1, -1, 1, 1, -1, -1, -1, -1
 *   nachher: -3, 2, -4 
 */
function sum_neighbors($list) {
    $new = array();
    $tmp = array_shift($list);
    foreach (array_values($list) as $x) {
        if ((($x > 0) and ($tmp > 0)) or (($x < 0) and ($tmp < 0))) {
            $tmp += $x;
        } else {
            $new[] = $tmp;
            $tmp = $x;
        }
    }
    $new[] = $tmp;
    return $new;
}

# ---------------------------------------------------------------- #

/* Intervall hinzufügen. */
function intervalAdd($eventID, $start_stunde, $start_tmj, $end_stunde, $end_tmj, $description, $type) {
    # Zeit in Datenbank eintragen.
    list($start_jahr, $start_monat, $start_tag) = explode('-', $start_tmj);
    list($end_jahr, $end_monat, $end_tag) = explode('-', $end_tmj);
    $start = mktime($start_stunde, 0, 0, $start_monat, $start_tag, $start_jahr);
    $end = mktime($end_stunde, 0, 0, $end_monat, $end_tag, $end_jahr);
    $svalid = checkdate($start_monat, $start_tag, $start_jahr);
    $evalid = checkdate($end_monat, $end_tag, $end_jahr);

    # Zeit des Events für spätere Ãœberprüfung holen.
    $q = 'SELECT start, end, description
          FROM ANWESENHEITEN_EVENTS
          WHERE event = '.intval($eventID);
    if ($res = DB::query($q)) {
      $row = mysql_fetch_assoc($res);
    } else {
      PELAS::error('MySQL-Fehler: '.mysql_error($res));
    }

    $overlap = false;
    $exists = false;
    if ($type == 'p') {
        # Anwesenheit eintragen. Dazu Anwesenheiten des aktuellen Users zur Ãœberprüfung auf Ãœberlappung holen.
        $q = 'SELECT start, end
              FROM ANWESENHEITEN
              WHERE event = '.intval($eventID).'
	        AND usrid = '.User::loginID();
        if ($res = DB::query($q)) {
            $overlap = false;
            while ($row2 = mysql_fetch_assoc($res)) {
                if (
                    # Ende der neuen Zeitspanne liegt in einer alten Zeitspanne.
                    ($end >= $row2['start'] and $end <= $row2['end'])
                    or
                    # Start der neuen Zeitspanne liegt in einer alten Zeitspanne.
                    ($start >= $row2['start'] and $start <= $row2['end'])
                    or
                    # Start der neuen Zeitspanne liegt in einer alten Zeitspanne.
                    ($row2['start'] >= $start and $row2['start'] <= $end)
                    or
                    # Ende der neuen Zeitspanne liegt in einer alten Zeitspanne.
                    ($row2['end'] >= $start and $row2['end'] <= $end)
                ) {
                    $overlap = true;
		}
            }
        }
        $insertquery = 'INSERT INTO ANWESENHEITEN
            (usrid, event, start, end)
            VALUES('.User::loginID().", ".intval($eventID).", $start,$end)";
    } elseif ($type == 't') {
        # Zeit eintragen. Dazu prüfen, ob gleichnamige Zeit schon existiert.
        $q = "SELECT COUNT(*)
              FROM ANWESENHEITEN
              WHERE event = '".intval($eventID)."'
                AND usrid = '-1'
                AND description = '$description'";
        if ($res = DB::query($q)) {
            $rowcheck = mysql_fetch_row($res);
            $exists = ($rowcheck[0] != 0);
        }
        $insertquery = "INSERT INTO ANWESENHEITEN
            (usrid, event, start, end, description)
            VALUES(-1, ".intval($eventID).", $start, $end, '".safe($description)."')";
    }
 
    if (! svalid) {
        PELAS::fehler('Startzeit ungültig!');
    } else if (!$evalid) {
        PELAS::fehler('Endzeit ungültig!');
    } else if (!($end >= $start)) {
        PELAS::fehler('Das Ende muss nach dem Start liegen!');
    } else if ($overlap) {  
        PELAS::fehler('Die einzutragende Zeit überlappt mit einer schon vorhandenen!');
    } else if ($exists) {
        PELAS::fehler('Eine Zeit mit diesem Namen existiert bereits!');
    } else if ($start > $row['end'] || $start < $row['start'] || $end > $row['end'] || $end < $row['start']) {
        PELAS::fehler('Zeiten müssen innerhalb der Zeiten des Events liegen!');
        printf("start: %s<br/>\n", $start);
        printf("rowstart: %s<br>\n", $row['start']);
        printf("end: %s<br>\n", $end);
        printf("rowend: %s<br>\n", $row['end']);
    } else {
        if ($res = DB::query($insertquery)) {
            PELAS::confirm('Zeit erfolgreich eingetragen.');
        }      
    } 
}

/* Intervall löschen. */
function intervalDelete($interval_id, $event_id) {
    if (
        ! (type($interval_id) == 't' and User::hatRecht('MANDANTADMIN', -1, mandantByEvent($event_id)))
        and ! (type($interval_id) == 't' and isOwner($interval_id))
		and ! (type($interval_id) == 'p' and User::hatRecht('MANDANTADMIN', -1, mandantByEvent($event_id)))
    ) {
        PELAS::fehler('Zugriffsverletzung: Löschen nicht möglich!');
        return;
    }
    $q = 'DELETE FROM ANWESENHEITEN WHERE ID = '.intval($interval_id);
    if ($res = DB::query($q)) {
        PELAS::confirm('Löschen erfolgreich.');
    } else {
        PELAS::fehler('Löschen fehlgeschlagen.');
    }
}

/* Event löschen. */
function eventDelete($event, $confirm=0) {
    # Darf der User Events löschen?
    if (! User::hatRecht('MANDANTADMIN', -1, mandantByEvent($event))) {
        PELAS::fehler('Zugriffsverletzung: Löschen nicht möglich!');
        exit();
    }
    switch (intval($confirm)) {
        case 0:
            printf('<p>Löschen von Event %s bestätigen: <a href="anwesenheit.php?action=eventDelete&amp;event=%s&amp;confirm=1">löschen</a>', $event, $event);
            break;
        case 1:
            $q = 'DELETE FROM ANWESENHEITEN_EVENTS WHERE event = '.intval($event);
            $q2 = 'DELETE FROM ANWESENHEITEN WHERE event = '.intval($event);
            if ($res = DB::query($q) and $res2 = DB::query($q2)) {
                PELAS::confirm('Löschen erfolgreich.');
            } else {
                PELAS::fehler('Löschen fehlgeschlagen.');
            }
            break;
        default:
            PELAS::fehler('Ungültiger Wert für confirm.');
    }
}

# ---------------------------------------------------------------- #

/* Hat der eingeloggte User das Recht an `$event_id`?
 * (Ist er Teammember bei dem Mandanten, zu dem das Event gehört?)
 */
function right_on_event($event_id) {
    $count = DB::getOne('
        SELECT COUNT(*)
        FROM RECHTZUORDNUNG r, ANWESENHEITEN_EVENTS a
        WHERE a.event = ?
          AND r.RECHTID = "TEAMMEMBER"
          AND r.MANDANTID = a.mandantID
          AND r.USERID = ?
	', intval($event_id), User::loginID());
    return ($count == 1);
}

function mandantByEvent($event_id) {
    return DB::getOne('
        SELECT mandantID
        FROM ANWESENHEITEN_EVENTS
        WHERE event = ?
	', intval($event_id));
}

function isOwner($id) {
    $count = DB::getOne('
        SELECT COUNT(*)
        FROM ANWESENHEITEN
        WHERE ID = ?
          AND usrid = ?
	', intval($id), User::loginID());
    return ($count == 1);
}

function type($id) {
    $user_id = DB::getOne('
        SELECT usrid
        FROM ANWESENHEITEN
        WHERE ID = ?
	', intval($id));
    return ($user_Id == -1) ? 't' : 'p';
}


include('admin/nachspann.php');
?>
