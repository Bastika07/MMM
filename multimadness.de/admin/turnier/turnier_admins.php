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
require_once "turnier/TurnierAdmin.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function editAdminList() {
	// TODO: check turnierleitung
	if (!isset($_POST['form']) || !is_array($_POST['form']))
		return;

	$form = $_POST['form'];

	if (!isset($_POST['form']['partyid']) || !is_numeric($_POST['form']['partyid']))
		return;

	$partyid = $_POST['form']['partyid'];
	$mandantid = COMPAT::getMandantFromParty($partyid);
	$allAdmins = COMPAT::getUserListByRight($mandantid, 'TURNIERADMIN');
	$turniere = Turnier::getTourneyList($partyid);

	foreach ($turniere as $turnierid => $turnier) {
		$arr[$turnierid] = array();
		foreach ($allAdmins as $userid => $login) {
			if (isset($form[$turnierid][$userid])) {
				$arr[$turnierid][$userid]= $userid;
			}
		}
		TurnierAdmin::setListByTourney($turnierid, $arr[$turnierid]);
	}

	header ("Location: /turnier/turnier_verwaltung.php?partyid={$partyid}");
}


function showAdminList() {
	// TODO: check turnierleitung
	if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
		return;
	$partyid = $_GET['partyid'];
	$mandantid = COMPAT::getMandantFromParty($partyid);
	$allAdmins = COMPAT::getUserListByRight($mandantid, 'TURNIERADMIN');
	$turniere = Turnier::getTourneyList($partyid);

	$arr = array();
	foreach ($turniere as $turnierid => $turnier) {
		$arr[$turnierid] = TurnierAdmin::getListByTourney($turnierid);
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('action', 'edit');
	$smarty->assign('partyid', $partyid);
	$smarty->assign('arr', $arr);
	$smarty->assign('allAdmins', $allAdmins);
	$smarty->assign('turniere', $turniere);
	$smarty->display('turnier_admin.tpl');
}


// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'edit':	editAdminList();
			break;

	case 'show':
	default:	showAdminList();
			break;
}

?>
