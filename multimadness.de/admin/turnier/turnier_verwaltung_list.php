<?php
/**
 * @package turniersystem
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
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function setStatus($turnierid, $cmd) {
	$turnier = Turnier::load($turnierid);
	if (!is_a($turnier, 'Turnier'))
		return;

	// aufrufender User muss Admin des Turniers sein
	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;

	switch ($cmd) {
		// anmeldung wird geoeffnet
		case TURNIER_CMD_OPEN:
			if ($turnier->status == TURNIER_STAT_RES_NOT_OPEN || $turnier->status == TURNIER_STAT_RES_CLOSED)
				$turnier->status = TURNIER_STAT_RES_OPEN;
			break;

		// anmeldung wird geschlossen
		case TURNIER_CMD_CLOSE:
			if ($turnier->status == TURNIER_STAT_RES_OPEN)
				$turnier->status = TURNIER_STAT_RES_CLOSED;
			break;

		// turnier wird erstellt/geseeded/gelost
		case TURNIER_CMD_SEED:
			if ($turnier->status == TURNIER_STAT_RES_OPEN || $turnier->status == TURNIER_STAT_RES_CLOSED) {
				$turnier->status = TURNIER_STAT_SEEDING;
				$turnier->save();
			}
			header ("Location: turnier/turnier_seeding.php?turnierid={$turnier->turnierid}");
			die();
			break;

		// turnier wird gespielt
		case TURNIER_CMD_PLAY:
			if ($turnier->status == TURNIER_STAT_SEEDING || $turnier->status == TURNIER_STAT_PAUSED)
				// ist die erste runde klar zum spielen?
				if (TurnierSystem::checkFirstRound($turnier)) {
					$turnier->status = TURNIER_STAT_RUNNING;
					$turnier->save();

					// dann gehts los;
					TurnierSystem::startupTourney($turnier);
				}
			break;

		// turnier wird pausiert
		case TURNIER_CMD_PAUSE:
			if ($turnier->status == TURNIER_STAT_RUNNING)
				$turnier->status = TURNIER_STAT_PAUSED;
			break;
	}
	$turnier->save();
}

function setSingleStatus() {
	// command uebergeben?
	if (!isset($_GET['cmd']) || !is_numeric($_GET['cmd']))
		return;

	// turnier id brauchen wir auch
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	setStatus($_GET['turnierid'], $_GET['cmd']);

	showTourneyList();
}

function setMultiStatus() {
	// command uebergeben und turnier ids vorhanden
	if (isset($_POST['cmd']) && is_numeric($_POST['cmd']) && isset($_POST['multi'])) {
		foreach ($_POST['multi'] as $turnierid => $tmp)
			setStatus($turnierid, $_POST['cmd']);
	}
	showTourneyList();
}

function showTourneyList() {
	$mandantArr = COMPAT::getMandantArrFromRight(COMPAT::currentID(), 'TURNIERADMIN');

	if (isset($_GET['partyid']) && is_numeric($_GET['partyid']))
		$partyid = $_GET['partyid'];

	$t_stat = Turnier::getStatusArr();
	$t_gamelist = TurnierLiga::getGameList();

	foreach ($mandantArr as $mandantid => $mandant) {

		if (!isset($partyid))
			$partyid = $mandant['partyid'];

		/* Partyname uebergeben */
		$retval[$mandant['partyid']]['partyname'] = $mandant['partyname'];
		$retval[$mandant['partyid']]['mandantid'] = $mandantid;

		/* turniere fuer diese party holen */
		$turniere = Turnier::getTourneyList($mandant['partyid']);
		foreach ($turniere as $turnierid => $turnier) {
			
      $turniere[$turnierid]['ligastr'] = isset($t_gamelist[$turniere[$turnierid]['gameid']]['liganame']) ? $t_gamelist[$turniere[$turnierid]['gameid']]['liganame'] : "";
		
			$turniere[$turnierid]['statusstr'] = $t_stat[$turniere[$turnierid]['status']];

			$turniere[$turnierid]['admin'] = TurnierAdmin::isAdmin(COMPAT::currentID(), $turnierid);

			switch ($turnier['status']) {
			case TURNIER_STAT_RES_NOT_OPEN:
				$turniere[$turnierid]['cmds'] = TURNIER_CMD_OPEN;
				break;
			case TURNIER_STAT_RES_OPEN:
				$turniere[$turnierid]['cmds'] = TURNIER_CMD_SEED | TURNIER_CMD_CLOSE;
				break;
			case TURNIER_STAT_RES_CLOSED:
				$turniere[$turnierid]['cmds'] = TURNIER_CMD_SEED | TURNIER_CMD_OPEN;
				break;
			case TURNIER_STAT_SEEDING:
				$turniere[$turnierid]['cmds'] = TURNIER_CMD_SEED | TURNIER_CMD_PLAY;
				break;
			case TURNIER_STAT_RUNNING:
				$turniere[$turnierid]['cmds'] = TURNIER_CMD_PAUSE;
				break;
			case TURNIER_STAT_PAUSED:
				$turniere[$turnierid]['cmds'] = TURNIER_CMD_PLAY;
				break;
			case TURNIER_STAT_FINISHED:
				$turniere[$turnierid]['cmds'] = 0;
				break;
			case TURNIER_STAT_CANCELED:
				$turniere[$turnierid]['cmds'] = 0;
				break;
			}
		}
		$retval[$mandant['partyid']]['turniere'] = $turniere;
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('partys', $retval);
	$smarty->assign('partyid', $partyid);
	$smarty->assign('groupid', 0);
	$smarty->assign('groups', TurnierGroup::getGroups());
	$smarty->display('turnier_verwaltung_list.tpl');
}


// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
## Status wurd ueber separate Seite gesetzt turnier_verwaltung_status.php
#	case 'cmd':	setSingleStatus();
#			break;
#
#	case 'multicmd':
#			setMultiStatus();
#			break;

	case 'show':
	default:	showTourneyList();
			break;
}

require('../../../includes/admin/nachspann.php');
?>
