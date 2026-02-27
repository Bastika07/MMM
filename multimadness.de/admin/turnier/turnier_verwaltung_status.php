<?php
/**
 * @package turniersystem
 * @author Markus Thomas
 * @subpackage admin
 */

define('MANDANTID', 'admin');
$iRecht = 'TURNIERADMIN';

require('../controller.php');
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "turnier/t_constants.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierGroup.class.php";
require_once "turnier/TurnierLiga.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Team.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require_once "turnier/TeamSystem.class.php";
require('admin/vorspann.php');

DB::connect();

function setStatus() {
	// command uebergeben?
	if (!isset($_GET['cmd']) || !is_numeric($_GET['cmd']))
		return;

	// turnier id brauchen wir auch
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnierid = $_GET['turnierid'];
	$cmd = $_GET['cmd'];

	$turnier = Turnier::load($turnierid);
	if (!is_a($turnier, 'Turnier'))
		return;

	// aufrufender User muss Admin des Turniers sein
	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;

	switch ($cmd) {
		// anmeldung noch nicht eroeffnet
		case TURNIER_CMD_NOT_OPEN:
			if ($turnier->status == TURNIER_STAT_RES_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_CLOSED ||
			    $turnier->status == TURNIER_STAT_CANCELED ||
			    $turnier->status == TURNIER_STAT_SEEDING )
				$turnier->status = TURNIER_STAT_RES_NOT_OPEN;
			else
			    die("Wrong actions!");
			break;
		// anmeldung wird geoeffnet
		case TURNIER_CMD_OPEN:
			if ($turnier->status == TURNIER_STAT_RES_NOT_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_CLOSED ||
			    $turnier->status == TURNIER_STAT_CANCELED ||
			    $turnier->status == TURNIER_STAT_SEEDING )
				$turnier->status = TURNIER_STAT_RES_OPEN;
			else
			    die("Wrong actions!");
			break;

		// anmeldung wird geschlossen
		case TURNIER_CMD_CLOSE:
			if ($turnier->status == TURNIER_STAT_RES_NOT_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_OPEN ||
			    $turnier->status == TURNIER_STAT_CANCELED ||
			    $turnier->status == TURNIER_STAT_SEEDING )
				$turnier->status = TURNIER_STAT_RES_CLOSED;
			else
			    die("Wrong actions!");
			break;

		// turnier wird erstellt/geseeded/gelost
		case TURNIER_CMD_SEED:
			if ($turnier->status == TURNIER_STAT_RES_NOT_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_CLOSED ||
			    $turnier->status == TURNIER_STAT_CANCELED ||
			    $turnier->status == TURNIER_STAT_SEEDING ) {
				$turnier->status = TURNIER_STAT_SEEDING;
				$turnier->save();
			}
			else {
			    die("Wrong actions!");
			}
			header ("Location: turnier_seeding.php?turnierid={$turnier->turnierid}");
			die();
			break;

		// turnier wird gespielt
		case TURNIER_CMD_PLAY:
			if (($turnier->status == TURNIER_STAT_SEEDING ||
			    $turnier->status == TURNIER_STAT_PAUSED ||
			    $turnier->status == TURNIER_STAT_FINISHED ) &&
			    (!($turnier->flags & TURNIER_RUNDEN))) { # kein rundenturnier
				// Normalturnier also single oder double elimination
				if (($turnier->flags & TURNIER_SINGLE || $turnier->flags & TURNIER_DOUBLE) && (!($turnier->flags & TURNIER_TREE_RUNDEN))) {
					if (TurnierSystem::checkFirstRound($turnier)) {  
						$turnier->status = TURNIER_STAT_RUNNING;
						$turnier->save();
						TurnierSystem::startupTourney($turnier);
					}
				#Hauptturnier mit vorrunden
                                } elseif (($turnier->flags & TURNIER_TREE_RUNDEN)) {                                                     
					if (false==TurnierSystem::existSubtourneys($turnier)) {                         
						TurnierSystem::createSubtourneys($turnier);  
						TurnierSystem::startupTourney($turnier);
						$turnier->status = TURNIER_STAT_RUNNING;                                                                                  
						$turnier->save();
					} else {
						$turnier->status = TURNIER_STAT_RUNNING;                                                                                  
						$turnier->save();
					}
				}
			} elseif (($turnier->status == TURNIER_STAT_RES_NOT_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_CLOSED ||
			    $turnier->status == TURNIER_STAT_CANCELED ||
			    $turnier->status == TURNIER_STAT_PAUSED ||
			    $turnier->status == TURNIER_STAT_FINISHED ) &&
			    ($turnier->flags & TURNIER_RUNDEN)) { # Rundenturnier
					if (false==TurnierSystem::checkFirstRound($turnier)) {                                      
						TurnierSystem::createRoundTourney($turnier);                                                                              
						$turnier->status = TURNIER_STAT_RUNNING;                                                                                  
						$turnier->save();
						TurnierSystem::startupTourney($turnier);
					} else {
						$turnier->status = TURNIER_STAT_RUNNING;
						$turnier->save();
					}
			}
			else
			    die("Wrong actions!");
			break;

		// turnier wird pausiert
		case TURNIER_CMD_PAUSE:
			if ($turnier->status == TURNIER_STAT_FINISHED ||
			    $turnier->status == TURNIER_STAT_RUNNING )
				$turnier->status = TURNIER_STAT_PAUSED;
			else
			    die("Wrong actions!");
			break;

		// turnier wir beendet ( z.B. nach neiner manuellen öffnung, nachdem es schon zu ende war.)
		case TURNIER_CMD_FINISHED:
			if ($turnier->status == TURNIER_STAT_PAUSED ||
			    $turnier->status == TURNIER_STAT_RUNNING )
				$turnier->status = TURNIER_STAT_FINISHED;
			else
			    die("Wrong actions!");
			break;

		// turnier absagen
		case TURNIER_CMD_CANCEL:
			if ($turnier->status == TURNIER_STAT_RES_NOT_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_OPEN ||
			    $turnier->status == TURNIER_STAT_RES_CLOSED ||
			    $turnier->status == TURNIER_STAT_SEEDING ) {
				$turnier->status = TURNIER_STAT_CANCELED;
				// wenn turnier abgesagt wurde, coins auf 0 setzen (rueckgabe coins)
				$turnier->coins = 0;
			    }
			else
			    die("Wrong actions!");
			break;

		// Vorrunde auswerten und an Hauptturnier uebertragen
		case TURNIER_CMD_TRANSFER:
			if ($turnier->status == TURNIER_STAT_RUNNING ||
			    $turnier->status == TURNIER_STAT_PAUSED ) {
			    header ("Location: turnier_transfer.php?turnierid={$turnier->turnierid}");
			    die();
			} else
			    die("Wrong actions!");
			break;

// 		// Alle Vorrunden löschen - DOPPELT, siehe Teams neu seeden
// 		case TURNIER_CMD_PRELIM_DEL:
// 			if ($turnier->status == TURNIER_STAT_RUNNING ||
// 			    $turnier->status == TURNIER_STAT_PAUSED ) {
// 			    TurnierSystem::delPrelim($turnier->turnierid);
// 			    $turnier->status = TURNIER_STAT_SEEDING;
// 			}
// 			else
// 			    die("Wrong actions!");
// 			break;

		// Alle Vorrunden aktualiseren
		case TURNIER_CMD_PRELIM_UP:
			if ($turnier->status == TURNIER_STAT_RUNNING ||
			    $turnier->status == TURNIER_STAT_PAUSED )
			    echo ("<b>Noch nicht implementiert</b>");
			else
			    die("Wrong actions!");
			break;

		// Teams neu seeden
		case TURNIER_CMD_RESEED:
			if ($turnier->status == TURNIER_STAT_RUNNING ||
			    $turnier->status == TURNIER_STAT_PAUSED )
			{
			      $turnier->status = TURNIER_STAT_SEEDING;
			      $turnier->save();
			      // erstma alten Muell loeschen
			      Match::delete($turnier->turnierid);
			      Match::deleteEvents($turnier->turnierid);
			      Round::delete($turnier->turnierid);
			      //Vorrunden werden pauschal geloescht, auch wenn kein Vorrundenturnier
			      TurnierSystem::delPrelim($turnier->turnierid);
			      // Cache loeschen
			      TurnierSystem::flushCache($turnier->turnierid);

			      header ("Location: turnier_seeding.php?turnierid={$turnier->turnierid}");
			      die();
			}
			else
			    die("Wrong actions!");
			break;

		// Turnier zuruecksetzten
		case TURNIER_CMD_RESET:
			if ($turnier->status == TURNIER_STAT_RUNNING ||
			    $turnier->status == TURNIER_STAT_PAUSED )
			{
			      $turnier->status = TURNIER_STAT_RES_NOT_OPEN;
			      $turnier->save();
			      // erstma alten Muell loeschen
			      Match::delete($turnier->turnierid);
			      Match::deleteEvents($turnier->turnierid);
			      Round::delete($turnier->turnierid);

			      //Vorrunden werden pauschal geloescht, auch wenn kein Vorrundenturnier
			      TurnierSystem::delPrelim($turnier->turnierid);

			      // Cache loeschen
			      TurnierSystem::flushCache($turnier->turnierid);
			}
			else
			    die("Wrong actions!");
			break;
	}
	$turnier->save();
	showStatus();

}

function showStatus() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnierid = $_GET['turnierid'];

	$turnier = Turnier::load($turnierid);
	if (!is_a($turnier, 'Turnier'))
		return;

	// aufrufender User muss Admin des Turniers sein
	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;

	switch ($turnier->status) {
	case TURNIER_STAT_RES_NOT_OPEN:
		$turnier_cmds = TURNIER_CMD_CANCEL | TURNIER_CMD_OPEN | TURNIER_CMD_CLOSE | TURNIER_CMD_SEED;
		break;
	case TURNIER_STAT_RES_OPEN:
		$turnier_cmds = TURNIER_CMD_CANCEL | TURNIER_CMD_NOT_OPEN | TURNIER_CMD_CLOSE | TURNIER_CMD_SEED;
		break;
	case TURNIER_STAT_RES_CLOSED:
		$turnier_cmds = TURNIER_CMD_CANCEL | TURNIER_CMD_NOT_OPEN | TURNIER_CMD_SEED | TURNIER_CMD_OPEN;
		break;
	case TURNIER_STAT_SEEDING:
		$turnier_cmds = TURNIER_CMD_CANCEL | TURNIER_CMD_SEED | TURNIER_CMD_PLAY | TURNIER_CMD_CLOSE | TURNIER_CMD_NOT_OPEN;
		break;
	case TURNIER_STAT_RUNNING:
		$turnier_cmds = TURNIER_CMD_PAUSE | TURNIER_CMD_FINISHED | TURNIER_CMD_RESEED | TURNIER_CMD_RESET;
		break;
	case TURNIER_STAT_PAUSED:
		$turnier_cmds = TURNIER_CMD_PLAY | TURNIER_CMD_FINISHED | TURNIER_CMD_RESEED | TURNIER_CMD_RESET;
		break;
	case TURNIER_STAT_FINISHED:
		$turnier_cmds = TURNIER_CMD_PLAY;
		break;
	case TURNIER_STAT_CANCELED:
		$turnier_cmds = TURNIER_CMD_NOT_OPEN | TURNIER_CMD_OPEN | TURNIER_CMD_CLOSE | TURNIER_CMD_SEED;
		break;
	}

	if ($turnier->flags & TURNIER_TREE_RUNDEN) { //Turnier hat Vorrunden
		switch ($turnier->status) {
		case TURNIER_STAT_RUNNING:
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PRELIM_DEL | TURNIER_CMD_PRELIM_UP | TURNIER_CMD_TRANSFER;
			break;
		case TURNIER_STAT_PAUSED:
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PRELIM_DEL | TURNIER_CMD_PRELIM_UP | TURNIER_CMD_TRANSFER;
			break;
		}
	}
	if ($turnier->pturnierid <> 0) { //Turnier ist vorrunde
		switch ($turnier->status) {
		case TURNIER_STAT_RES_CLOSED:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_SEED;
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PLAY;
			break;
		}
	}
	if ($turnier->flags & TURNIER_RUNDEN) { //Rundenturniere werden nicht geseedet
		switch ($turnier->status) {
		case TURNIER_STAT_RES_CLOSED:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_SEED;
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PLAY;
		case TURNIER_STAT_RES_OPEN:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_SEED;
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PLAY;
		case TURNIER_STAT_CANCELED:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_SEED;
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PLAY;
		case TURNIER_STAT_RES_NOT_OPEN:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_SEED;
			$turnier_cmds = $turnier_cmds | TURNIER_CMD_PLAY;
		case TURNIER_STAT_RUNNING:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_RESEED;
			break;
		case TURNIER_STAT_PAUSED:
			$turnier_cmds = $turnier_cmds & ~TURNIER_CMD_RESEED;
			break;
		}
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('turnier', $turnier);
	$smarty->assign('turnier_cmds', $turnier_cmds);
	$smarty->assign('partyid', $partyid);
	$smarty->display('turnier_verwaltung_status.tpl');

}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'show':	showStatus();
			break;

	case 'setStatus':	setStatus();
			break;
	default:		showStatus();
			break;

}

require('admin/nachspann.php');
?>
