<?php
/* Anwesenheitsübersicht Verpflegungsnachweis
 * Zeigt die eingetragenen Anwesenden Personen und das Datum des Events.
 * Ort muss per Hand nachgetragen werden.
 */

setlocale(LC_ALL, 'de_DE');

require('controller.php');

require_once('dblib.php');
$iRecht = 'TEAMMEMBER';
require_once('checkrights.php');
require_once('util.php');


# Action übergangsweise manuell setzen, damit der Controller später leichter
# mit dem von anwesenheit.php verschmolzen werden kann und der Template-Name
# gleich bleibt.
$_GET['action'] = 'catering';


class Controller {

    /* Verpflegungsnachweis für das Event anzeigen. */
    function catering() {
        $event_id = (int) $_GET['event'];

        # Darf das Event angezeigt werden?
        # (Ist der User Teammember bei dem Mandanten, zu dem das Event gehört?)
        if (! right_on_event($event_id)) {
            PELAS::fehler('Kein Recht auf Event "' . $event_id . '"!');
	    exit;
        }

        # Daten zum Event holen.
        $event = DB::getRow('
            SELECT e.event, e.start, e.end, e.description, e.mandantID, m.BESCHREIBUNG name
            FROM ANWESENHEITEN_EVENTS e
	      INNER JOIN MANDANT m ON (m.MANDANTID = e.mandantID)
            WHERE e.event = ?
            ORDER BY e.start DESC
	    ', $event_id);

        # Für das Event eingetragene User holen.
        $users = DB::getRows('
	    SELECT DISTINCT u.NAME, u.NACHNAME
            FROM ANWESENHEITEN a
	      INNER JOIN USER u ON (a.usrid = u.USERID)
            WHERE a.event = ?
	      AND a.start > 0
	      AND a.end > 0
            ORDER BY u.NACHNAME
	    ', $event_id);

        return array('event' => $event, 'users' => $users);
    }

}

exec_ctrl();

# ---------------------------------------------------------------- #

/* Hat der eingeloggte User das Recht an $event_id?
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
?>
