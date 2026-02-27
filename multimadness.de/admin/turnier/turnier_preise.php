<?php
/**
 * @package turniersystem
 * @subpackage admin
 */

$iRecht = 'TURNIERLEITUNG';
define('MANDANTID', 'admin');

require('../controller.php');
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once 'turnier/t_constants.php';
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierGroup.class.php";
require_once "turnier/TurnierPreis.class.php";
require_once "turnier/TurnierRanking.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function savePreisList() {
	if (isset($_POST['form']) && is_array($_POST['form']) && isset($_GET['turnierid']) && is_numeric($_GET['turnierid'])) {
		TurnierPreis::setList($_GET['turnierid'], $_POST['form']);
	}
	showPreisList();
}

function editPreisList() {
	// TODO: check turnierleitung
	if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
		return;

	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turniere = Turnier::getTourneyList($_GET['partyid']);

	foreach ($turniere as $turnierid => $turnier)
		$turniere[$turnierid]['preise'] = TurnierPreis::getList($turnierid);

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('partyid', $_GET['partyid']);
	$smarty->assign('turniere', $turniere);
	$smarty->assign('editid', $_GET['turnierid']);
	$smarty->assign('groups', TurnierGroup::getGroups());
	$smarty->assign('groupid', 0);
	$smarty->display('turnier_preise.tpl');
}


function showPreisList() {
	// TODO: check turnierleitung
	if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
		return;

	$turniere = Turnier::getTourneyList($_GET['partyid']);

	foreach ($turniere as $turnierid => $turnier)
		$turniere[$turnierid]['preise'] = TurnierPreis::getList($turnierid);

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('partyid', $_GET['partyid']);
	$smarty->assign('turniere', $turniere);
	$smarty->assign('groups', TurnierGroup::getGroups());
	$smarty->assign('groupid', 0);
	$smarty->display('turnier_preise.tpl');
}


function printPreisList() {
	if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
		return;

	$turniere = Turnier::getTourneyList($_GET['partyid']);

	foreach($turniere as $turnierid => $turnier) {
		$turniere[$turnierid]['preise'] = TurnierPreis::getList($turnierid);
		if (count($turniere[$turnierid]['preise']) == 0)
			unset($turniere[$turnierid]);
		else
			$turniere[$turnierid]['ranking'] = TurnierRanking::getShortRanking($turniere[$turnierid]);
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('partyid', $_GET['partyid']);
	$smarty->assign('turniere', $turniere);
	$smarty->assign('groups', TurnierGroup::getGroups());
	$smarty->assign('groupid', 0);
	$smarty->display('turnier_preise_print.tpl');
}


// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'save':	savePreisList();
			break;

	case 'edit':	editPreisList();
			break;

	case 'print':	printPreisList();
			break;

	case 'show':
	default:	showPreisList();
			break;
}

?>
