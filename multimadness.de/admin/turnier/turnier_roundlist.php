<?php
/**
 * @package turniersystem
 * @subpackage admin
 */
$iRecht = 'TURNIERADMIN';
define('MANDANTID', 'admin');

require('../controller.php');
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "turnier/t_constants.php";
require_once "turnier/Round.class.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

// ok
function saveList() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;

	foreach ($_POST['form'] as $roundid => $data) {
		$round = Round::load($turnier->turnierid, $roundid);
		$round->begins = $data['begins'];
		$round->ends = $data['ends'];
		$round->info = $data['info'];
		$round->save();
	}

	TurnierSystem::flushCache($turnier->turnierid);
	
	header ("Location: /admin/turnier/turnier_verwaltung_list.php?partyid={$turnier->partyid}");
}

// ok
function showRoundList() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;

	$rounds = Round::getRoundList($turnier->turnierid);

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('turnier', $turnier);
	$smarty->assign('rounds', $rounds);
	$smarty->display('turnier_roundlist.tpl');
}


// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'save':	saveList();
			break;
	default:	showRoundList();
			break;
}

?>
