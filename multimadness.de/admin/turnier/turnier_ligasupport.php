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
require_once "turnier/TurnierLiga.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function showList() {
	$list = TurnierLiga::getGameList();

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('list', $list);
	$smarty->assign('ligaArr', TurnierLiga::getLigaArr());
	$smarty->assign('teamSizeArr', array(1=>1,2,3,4,5,6,7,8,9,10,11,12,13,14,15));
	$smarty->display('turnier_ligasupport.tpl');
}

function addGame() {
	if (!isset($_POST['liga']) || !is_numeric($_POST['liga']))
		return;

	if (!isset($_POST['shortname']) || empty($_POST['shortname']))
		return;

	if (!isset($_POST['teamsize']) || !is_numeric($_POST['teamsize']))
		return;

	if (!isset($_POST['name']) || empty($_POST['name']))
		return;

	$game = new TurnierLiga();
	$game->liga = $_POST['liga'];
	$game->shortname = $_POST['shortname'];
	$game->teamsize = $_POST['teamsize'];
	$game->name = $_POST['name'];
	$game->create();

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'add':	addGame();
			break;

	case 'show':
	default:	showList();
			break;
}

?>
