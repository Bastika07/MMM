<?php
/**
 * @package turniersystem
 * @subpackage admin
 */

define('MANDANTID', 'admin');
$iRecht = 'TURNIERADMIN';

require('../controller.php');
require_once 'dblib.php';
require_once("turnier/t_constants.php");
require_once("turnier/TurnierAdmin.class.php");
require_once("turnier/Match.class.php");
require_once("turnier/Team.class.php");
#require_once("turnier/Jump.class.php");
require_once("turnier/Round.class.php");
require_once("t_compat.inc.php");
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

function showOwn() {
	$mandantArr = COMPAT::getMandantArrFromRight(COMPAT::currentID(), 'TURNIERADMIN');
	#$_GET['partyid'] = 19;
	if (isset($_GET['partyid']) && is_numeric($_GET['partyid']))
		$partyid = $_GET['partyid'];

	foreach ($mandantArr as $mandantid => $mandant) {

		if (!isset($partyid))
			$partyid = $mandant['partyid'];
	}

	#eigene turniere dieser party ermitteln
	$sql = "SELECT t_admin.turnierid, t_turnier.name AS turniername FROM t_admin 
		JOIN t_turnier
		on t_admin.turnierid = t_turnier.turnierid
		WHERE t_admin.userid = ".COMPAT::currentID()." and t_turnier.partyid = ".$partyid. "
		Group By t_admin.turnierid, t_turnier.name 
		Order By t_turnier.name";
	$res = DB::query($sql);
	$tourney_list = array();
	while ($row = $res->fetch_assoc()) {
		array_push($tourney_list, $row);
	}

	#matches zu den selektieren turnieren holen
	$matches = array();
	foreach($tourney_list as $turnierid){

		$teams[$turnierid['turnierid']] = Team::getTeamNameList($turnierid['turnierid']);
		$matches[$turnierid['turnierid']] = Match::getMatchResultList($turnierid['turnierid']);
		$rounds[$turnierid['turnierid']] = Round::getRoundList($turnierid['turnierid']);
	}
	$smarty = new PelasSmarty("turnier");
	$smarty->assign('matches', $matches);
	if (isset($teams)) {
		$smarty->assign('teams', $teams);
	}
	if (isset($rounds)) {
		$smarty->assign('rounds', $rounds);
	}
	$smarty->assign('tourney_list', $tourney_list);
	$smarty->display('turnier_tap_matchlist.tpl');

}

function showAll() {
	$mandantArr = COMPAT::getMandantArrFromRight(COMPAT::currentID(), 'TURNIERADMIN');
	#$_GET['partyid'] = 19;
	if (isset($_GET['partyid']) && is_numeric($_GET['partyid']))
		$partyid = $_GET['partyid'];

	foreach ($mandantArr as $mandantid => $mandant) {

		if (!isset($partyid))
			$partyid = $mandant['partyid'];
	}

	#eigene turniere dieser party ermitteln
	$sql = "SELECT t_admin.turnierid, t_turnier.name AS turniername FROM t_admin 
		JOIN t_turnier
		on t_admin.turnierid = t_turnier.turnierid
		WHERE t_turnier.partyid = ".$partyid."
		AND t_turnier.status = ".TURNIER_STAT_RUNNING."
		Group By t_admin.turnierid, t_turnier.name 
		Order By t_turnier.name";
	$res = DB::query($sql);
	$tourney_list = array();
	while ($row = $res->fetch_assoc()) {
		array_push($tourney_list, $row);
	}

	#matches zu den selektieren turnieren holen
	$matches = array();
	foreach($tourney_list as $turnierid){

		$teams[$turnierid['turnierid']] = Team::getTeamNameList($turnierid['turnierid']);
		$matches[$turnierid['turnierid']] = Match::getMatchResultList($turnierid['turnierid']);
		$rounds[$turnierid['turnierid']] = Round::getRoundList($turnierid['turnierid']);
	}
	$smarty = new PelasSmarty("turnier");
	$smarty->assign('matches', $matches);
	if (isset($teams)) {
		$smarty->assign('teams', $teams);
	}
	if (isset($rounds)) {
		$smarty->assign('rounds', $rounds);
	}
	$smarty->assign('tourney_list', $tourney_list);
	$smarty->display('turnier_tap_matchlist.tpl');


}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'own':	showOwn();
			break;
	case 'all':
			showAll();
			break;
	default:	showOwn();
			break;
}

?>
