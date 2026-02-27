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
require_once "turnier/TurnierGroup.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function showGroupList() {
	$groups = TurnierGroup::getGroups();

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('list', $groups);
	$smarty->display('turnier_gruppen.tpl');
}

function addGroup() {
	if (isset($_POST['name']) && !empty($_POST['name']))
		TurnierGroup::addGroup($_POST['name']);

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

function delGroup() {
	if (!isset($_GET['groupid']) || !is_numeric($_GET['groupid']))
		return;

	TurnierGroup::delGroup($_GET['groupid']);

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

function moveGroup() {
	if (!isset($_GET['groupid']) || !is_numeric($_GET['groupid']))
		return;

	if (!isset($_GET['to']) || !is_numeric($_GET['to']))
		return;

	TurnierGroup::moveGroup($_GET['groupid'], $_GET['to']);

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

function hideGroup() {
	if (!isset($_GET['groupid']) || !is_numeric($_GET['groupid']))
		return;

	TurnierGroup::hide($_GET['groupid']);

	header("Location: {$_SERVER['PHP_SELF']}?action=show");
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'add':	addGroup();
			break;

	case 'del':	delGroup();
			break;

	case 'move':	moveGroup();
			break;

	case 'hide':	hideGroup();
			break;

	case 'show':
	default:	showGroupList();
			break;
}

?>
