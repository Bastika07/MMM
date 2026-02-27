<?php
/* Sitzplatzreservierung */

require_once('dblib.php');
if (isset($callFromAdmin) and ($callFromAdmin == 1)) {
    # Session-Krams nur aufrufen, wenn nicht vom Admin aufgerufen.
} else {
    include_once('session.php');
}
include_once('format.php');
include_once('language.inc.php');

if (isset($_GET['ebene'])) {
    $ebene = $_GET['ebene'];
	if (!is_numeric($ebene) || $ebene < 1 || ($nPartyID == 5 && $ebene != 1)) {
        $ebene = 1;
    }
} else {
	$ebene = 1;
}

# Aktuelle Party-ID ermitteln.
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

require_once('sitzlib.php');

if ($nPartyID < 1) {
    PELAS::fehler('Es wurden nicht genügend Daten geliefert, um den Sitzplan anzuzeigen.');
    exit;
}

# Reservierung offen?
if ((CFG::getMandantConfig('SITZPLATZRES_OFFEN') != 'J') and ($callFromAdmin != 1)) {
    $sResOffenAb = CFG::getMandantConfig('SITZPLATZRES_OFFEN_AB');
    $resOffen = False;
} else {
    $resOffen = True;
}

# ################# OK #############################

if (($_GET['iAction'] >= 1) and ($nLoginID >= 1)) {
    # Checken, ob Ticketauswahl OK ist und weitere wichtige Infos holen.
    
		$sql = "
        SELECT t.ticketId, y.translation, t.sitzReihe, t.sitzPlatz, u.LOGIN
        FROM acc_tickets t, acc_ticket_typ y, USER u
        WHERE t.typId = y.typId
          AND t.ticketId = '".intval($_GET['iTicket'])."'
          AND (t.userId = '".intval($nLoginID)."'
            OR t.ownerId = '".intval($nLoginID)."')
          AND t.statusId = " . ACC_STATUS_BEZAHLT . "
          AND y.partyId = '".intval($aktuellePartyID)."'
          AND u.USERID = t.userId
    ";
    $resTicket = DB::query($sql);
    $rowTicket = $resTicket->fetch_array();

    # Anmeldung offen?
    if (($callFromAdmin != 1) and (LOCATION == 'intranet')) {
        # Im Intranet nur vom Admin aufrufbar.
        PELAS::fehler('Die Sitzplatzreservierung kann vor Ort nur durch einen Admin veränndert werden.');
    } elseif (! $resOffen) {
        # Sitzplatzreservierung noch nicht offen.
        PELAS::fehler($str['sitzres_nichtoffen'] . ': ' . $sResOffenAb);
    } elseif (! $resTicket->num_rows) {
        # Kein Recht auf das Ticket.
        PELAS::fehler($str['acc_keinrecht']);
    } else {
        # Beginne Reservierung.
        $realLoginID = $nLoginID;

        # Herausfinden, ob der gewählte Platz in der Loge ist.
        $ist_loge = (bool) DB::getOne('
	    SELECT ISTLOGE
	    FROM SITZDEF
	    WHERE MANDANTID = ?
	      AND REIHE = ?
	', intval($nPartyID), intval($_GET['reihe']));

        # Herausfinden, ob der gewählte Platz in der Demoarea ist.
        $ist_cloge = (bool) DB::getOne('
	    SELECT ISTDEMO
	    FROM SITZDEF
	    WHERE MANDANTID = ?
	      AND REIHE = ?
	', intval($nPartyID), intval($_GET['reihe']));

		
	# Ticket-ID des gewählten Platzes ermitteln.
	$alter_platz_ticket_id = DB::getOne('
	    SELECT ticketId
	    FROM acc_tickets
	    WHERE partyId = ?
	      AND sitzReihe = ? 
	      AND sitzPlatz = ?
	', intval($aktuellePartyID), intval($_GET['reihe']), intval($_GET['tisch']));
		
        # Alte Reihe ermitteln.
        $alte_reihe = DB::getOne('
            SELECT sd.REIHE
	    FROM SITZDEF sd
	      LEFT JOIN acc_tickets t ON (sd.REIHE = t.sitzReihe)
	    WHERE sd.MANDANTID = ?
	      AND t.partyId = ?
	      AND t.ticketId = ?
	', intval($nPartyID), intval($aktuellePartyID), intval($_GET['iTicket']));

        # Prüfen, ob die Kategorie auch dem Ticket entspricht.
        $sFehler = '';
        if (($rowTicket['translation'] != $STATUS_BEZAHLT) and (!$ist_loge && !$ist_cloge)) {
            $sFehler = 'Dein Ticket ist nicht für Parkett zugelassen. Bitte wähle auf dem Plan die korrekte Kategorie.';
        } elseif (($rowTicket['translation'] != $STATUS_BEZAHLT_LOGE) and $ist_loge) {
            $sFehler = 'Dein Ticket ist nicht für die Loge zugelassen. Bitte wähle auf dem Plan die korrekte Kategorie.';
        } elseif (($rowTicket['translation'] != $STATUS_BEZAHLT_CLOGE) and $ist_cloge) {
            $sFehler = 'Dein Ticket ist nicht für Clan-Loge zugelassen. Bitte wähle auf dem Plan die korrekte Kategorie.';
        } else {
            # OK, reservieren.
            if ($_GET['iAction'] == 1) {
                # Reservieren
                if ($alter_platz_ticket_id <= 0) {
                    # Platz ist frei.

                    # Abteilung umsetzen/neu reservieren.
                    $sql = "
                        UPDATE acc_tickets
                        SET sitzReihe = '".intval($_GET['reihe'])."',
                            sitzPlatz = '".intval($_GET['tisch'])."',
                            werGeaendert = '".intval($nLoginID)."'
                        WHERE partyId = '".intval($aktuellePartyID)."'
                          AND ticketId = '".intval($_GET['iTicket'])."'
							";
						DB::query($sql);
	
							$generate = True;
							$log_msg = sprintf('Seat change %s-%s for PartyID %s and ticket %s by ',
						$_GET['reihe'], $_GET['tisch'], $aktuellePartyID, PELAS::formatTicketNr($_GET['iTicket']));
							if (isset($callFromAdmin) and ($callFromAdmin == 1)) {
									# Admin setzt User um.
									PELAS::logging($log_msg . 'admin-interface', 'sitzplan', $nLoginID);
							} else {
									# User setzt sich selbst um.
									PELAS::logging($log_msg . $realLoginID, 'sitzplan', $nLoginID);
							}
					} elseif ($alter_platz_ticket_id == $_GET['iTicket']) {
				    # Ticket-ID des Platzes entspricht der Ticket-ID des Users.
				    # Demnach war das bereits sein Platz.
				    # Die Reservierung wird aufgehoben!
                    $sql = "
                        UPDATE acc_tickets
	                SET sitzReihe = NULL,
	                    sitzPlatz = NULL,
                            werGeaendert = '".intval($nLoginID)."'
                        WHERE partyId = '".intval($aktuellePartyID)."'
                          AND ticketId = '".intval($_GET['iTicket'])."'
                    ";
									DB::query($sql);
                    $generate = True;
		            $log_msg = sprintf('Seat delete %s-%s for PartyID %s and ticket %s by ',
			        	$_GET['reihe'], $_GET['tisch'], $aktuellePartyID, PELAS::formatTicketNr($_GET['iTicket']));
	              if (isset($callFromAdmin) and ($callFromAdmin == 1)) {
					        # Admin setzt User um.
					        PELAS::logging($log_msg . 'admin-interface', 'sitzplan', $nLoginID);
		            } else {
					        # User setzt sich selbst um.
								PELAS::logging($log_msg . $realLoginID, 'sitzplan', $nLoginID);
                }
         	 }
         }
        }

        # Selektive Generierung der Reihen.
        if ($generate) {
            include_once('sitzplan_generate_newaccounting.php');
            # Feststellen, ob die vorherige Ebene auch generiert werden muss.
            if ($alte_reihe and ($_GET['reihe'] > 0) and ($alte_reihe != $_GET['reihe'])) {
                GeneriereSitzplanSelektiv($alte_reihe, $nPartyID, 0, $aktuellePartyID);
            }
            GeneriereSitzplanSelektiv($_GET['reihe'], $nPartyID, 0, $aktuellePartyID);
        }
        if ($sFehler != '') {
	    PELAS::fehler($str['fehler'] . ': ' . $sFehler);
        } else {
            $target = '?page=13&ebene=' . $ebene . '&iTicket=' . $_GET['iTicket'];
            header('Location: ' . $target);
        }
    }
}

# Grundlagen für weiteres Arbeiten

# Gültige Tickets heraussuchen.
if ($nLoginID < 1) {
    $rowTickets = '';
} else {
    $sql = "
        SELECT t.ticketId, y.translation, t.sitzReihe, t.sitzPlatz, t.ownerId, t.userId, u.LOGIN
        FROM acc_tickets t,acc_ticket_typ y,USER u
        WHERE t.typId = y.typId
	  AND (t.userId = '".intval($nLoginID)."'
	    OR t.ownerId = '".intval($nLoginID)."')
	  AND t.statusId = " . ACC_STATUS_BEZAHLT . "
	  AND y.partyId = '".intval($aktuellePartyID)."'
	  AND u.USERID = t.userId
    ";
    $resTicket = DB::query($sql);

    # Prüfen, ob Ticket vorhanden ist!
    $ticket_vorhanden = (bool) $resTicket->num_rows;
}
?>

<meta http-equiv="pragma" content="no-cache">

<div id="layer2" style="position: absolute; top: 160px; left: 0; width: 210px; height: 1px; padding: 10px; visibility: hide; visibility: hidden;">wird vor dem Sichtbarmachen überschrieben</div>
<script type="text/javascript" src="<?= PELASHOST ?>accounting_sitzplan.js"></script>
<script type="text/javascript">init('<?= PELASHOST ?>userbild/')</script>
<script type="text/javascript">
<!--
function gores(Reihe, Platz) {
<?php
    if (! $resOffen) {
        echo 'alert("Die Sitzplatzreservierung wurde noch nicht eröffnet!");' . "\n";
    } elseif ($nLoginID < 1)  {
        echo 'alert("Nicht eingeloggt!");' . "\n";
    } elseif (! $ticket_vorhanden) {
	echo 'alert("' . $str['bezahlen'] . '");' . "\n";
    } else {
			if (strpos($_SERVER['SCRIPT_NAME'], 'admin') !== false)
				$urlpart = $_SERVER['SCRIPT_NAME'].'?';
			else 
				$urlpart = '?page=13&';
?>
  	iTicket = document.forms.theaction.iTicket.value;
  	document.location.href = "<?= $urlpart; ?>ebene=<?= $ebene ?>&reihe=" + Reihe + "&tisch=" + Platz + "&iAction=1&iTicket=" + iTicket;
<?php
    }
?>
}
//-->
</script>

<?php
if (! $resOffen) {
    # Sitzplatzreservierung ist noch nicht offen.
    PELAS::fehler($str['sitzres_nichtoffen'] . ': ' . $sResOffenAb);
} else {
    echo '<p align="justify">' . $str['infotext_new'] . "</p>\n";
    if ($nLoginID >= 1) {
        if ($ticket_vorhanden) {
	    # Tickets anzeigen.
	    echo '<form name="theaction">' . "\n";
	    echo '<p>' . $str['ticketauswahl'] . ': ';
	    echo '<select name="iTicket">' . "\n";
	    $sInfo = False;
	    while ($row = $resTicket->fetch_array()) {
		echo '  <option value="' . $row['ticketId'] . '"';
		if ($iTicket == $row['ticketId']) {
		    echo ' selected="selected"';
		}
		echo '> ' . PELAS::formatTicketNr($row['ticketId']);
		# Wenn Loge, dann ein 'L' anhängen.
		if ($row['translation'] == $STATUS_BEZAHLT_LOGE) {
		    echo 'L';
		}
		echo '&nbsp; (' . $row['sitzReihe'] . '-' . $row['sitzPlatz'] . ')&nbsp; ' . db2display($row['LOGIN']);
		if (($row['userId'] != $row['ownerId']) and ($nLoginID != $row['ownerId'])) {
		    echo ' *';
		    $sInfo = True;
		}
	        echo "</option>\n";
	    }
	    echo "</select>\n";
	    if ($sInfo) {
	        echo ' &nbsp * ' . $str['ticketsassigned'];
	    }
	    echo "</p>\n";
	    echo "</form>\n";
        } else {
            PELAS::fehler($str['fehler'] . ': ' . $str['acc_keinetickets']);
        }
    } else {
        PELAS::fehler($str['loginfuerplatz']);
    }
}

$sitzplan_filepath = PELASDIR . 'sitzbild/sitzplan_html_' . $nPartyID . '_' . $ebene . '.txt';
if (is_readable($sitzplan_filepath)) {
	readfile($sitzplan_filepath);
} else {
	echo "Fehler: Sitzplan für die Ebene '".$ebene."' dieser Party wurde nicht gefunden.";
}


##$img_src = PELASHOST . 'sitzbild/sitzplan_bild_' . $nPartyID . '_' . $ebene . '.png?time=' . time();
if (isset($_GET['locateUser'])) {
    # User wurde ausgewählt und soll auf dem Sitzplan hervorgehoben werden.
    $mode = 'locateUser=' . htmlspecialchars($_GET['locateUser']);
} else {
    # Es wurde kein User ausgewählt.
    $mode = 'time=' . time();
}
$img_src = PELASHOST . '/sitzplan_bild.php?nPartyID=' . $nPartyID . '&ebene=' . $ebene . '&' . $mode;
echo '<img src="' . $img_src . '" usemap="#mmm_map" border="0"/>' . "\n";
?>

<p>
  <img src="<?= PELASHOST ?>/gfx/sitz_leg_frei.gif"> <?= $str['frei'] ?>
  &nbsp;
  <img src="<?= PELASHOST ?>/gfx/sitz_leg_loge.gif"> <?= $str['loge'] ?>
  &nbsp;
  <img src="<?= PELASHOST ?>/gfx/sitz_leg_besetzt.gif"> <?= $str['besetzt'] ?>
  &nbsp;
  <img src="<?= PELASHOST ?>/gfx/sitz_leg_gesperrt.png"> <?= $str['gesperrt'] ?>
</p>
