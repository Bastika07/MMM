<?php
/**
 * @package turniersystem
 * @subpackage admin
 */

define('MANDANTID', 'admin');
$iRecht = 'TURNIERLEITUNG';

require('../controller.php');
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "turnier/Turnier.class.php";
require_once "turnier/Round.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/TurnierGroup.class.php";
require_once "turnier/TurnierCoverage.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function multiCmd() {
	// TODO: check turnierleitung

	if (isset($_POST['multi']) && is_array($_POST['multi']) && (count($_POST['multi']) > 0)) {
		switch ($_POST['cmd']) {
		case 'coverage':
			$coverage = new TurnierCoverage();
			foreach ($_POST['multi'] as $turnierid => $tmp)
				$coverage->export($turnierid);
			$coverage->upload();
			die();
			break;

		case 'flush':
			foreach ($_POST['multi'] as $turnierid => $tmp)
				TurnierSystem::flushCache($turnierid);
			break;

		case 'empty':
		case 'remove':
			foreach ($_POST['multi'] as $turnierid => $tmp) {

				$turnier = Turnier::load($turnierid);
				$turnier->status = TURNIER_STAT_RES_NOT_OPEN;
				$turnier->save();

				// teams loeschen
				$teamlist = Team::getTeamNameList($turnierid);
				foreach ($teamlist as $teamid => $team)
					Team::delete($turnierid, $teamid);

				// Rounds loeschen
				Round::delete($turnierid);

				// matches loeschen
				Match::delete($turnierid);

				// Events loeschen
				Match::deleteEvents($turnierid);

				if ($_POST['cmd'] == "remove")
					Turnier::delete($turnierid);
			}
			break;
		case 'copy':
			if (count($_POST['multi']) <> 1) {
				echo "Bitte EIN Turnier zum kopieren markieren!";
				die();
			}
			foreach ($_POST['multi'] as $turnierid => $tmp) {
				header("Location: /turnier/turnier_verwaltung_detail.php?action=new&template=".$turnierid);
				die();
			}
			break;
		default:
			break;
		}
	}
	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}


function showTourneyList() {
	$mandantArr = COMPAT::getMandantArrFromRight(COMPAT::currentID(), 'TURNIERLEITUNG');

	if (isset($_GET['partyid']) && is_numeric($_GET['partyid']))
		$partyid = $_GET['partyid'];

	$t_stat = Turnier::getStatusArr();

	foreach ($mandantArr as $mandantid => $mandant) {

		if (!isset($partyid))
			$partyid = $mandant['partyid'];

		/* Partyname uebergeben */
		$retval[$mandant['partyid']]['partyname'] = $mandant['partyname'];
		$retval[$mandant['partyid']]['mandantid'] = $mandantid;

		/* turniere fuer diese party holen */
		$turniere = Turnier::getTourneyList($mandant['partyid']);
		foreach ($turniere as $turnierid => $turnier)
			$turniere[$turnierid]['statusstr'] = $t_stat[$turniere[$turnierid]['status']];

		$retval[$mandant['partyid']]['turniere'] = $turniere;
	}
	$smarty = new PelasSmarty("turnier");
	$smarty->assign('partys', $retval);
	$smarty->assign('partyid', $partyid);
	$smarty->assign('groupid', 0);
	$smarty->assign('groups', TurnierGroup::getGroups());
	$smarty->display('turnier_verwaltung.tpl');
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'multicmd':
			multiCmd();
			break;

	case 'show':
	default:	showTourneyList();
			break;
}

?>
