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
require_once "turnier/TurnierLiga.class.php";
require_once "turnier/TurnierExportWWCL.class.php";
#require_once "turnier/TurnierExportNGL.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

function generateExport() {
	if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
		return;

	if (!isset($_GET['liga']) || !is_numeric($_GET['liga']))
		return;

	if ($_GET['liga'] == TURNIER_LIGA_WWCL) {
		$export = TurnierExportWWCL::create(
				$_POST['form']['wwcl']['partyname'],
				$_POST['form']['wwcl']['partyid'],
				$_POST['form']['wwcl']['veranstalterid'],
				$_POST['form']['wwcl']['stadt']);


	} else if ($_GET['liga'] == TURNIER_LIGA_NGL) {
		$export = TurnierExportNGL::create(
				$_POST['form']['ngl']['partyid'],
				$_POST['form']['ngl']['partyname'],
				$_POST['form']['ngl']['datum'],
				$_POST['form']['ngl']['contact']);
	}

	foreach ($_POST['check'] as $turnierid => $tmp)
		$export->addTurnier($turnierid);

//	header("Content-Type: file/plain");
	header("Content-Type: text/plain");
	$export->view();
	die();
}


function showExportList() {
	if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
		return;

	$partyid = $_GET['partyid'];
	$turniere = Turnier::getTourneyList($partyid);

	$ligagames = TurnierLiga::getGameList();

	$t_stat = Turnier::getStatusArr();
	foreach ($turniere as $turnierid => $turnier) {
		$turniere[$turnierid]['statusstr'] = $t_stat[$turnier['status']];
		$turniere[$turnierid]['check'] = ($turnier['teams'] != 0 && $turnier['status'] == TURNIER_STAT_FINISHED && $turnier['pturnierid'] == 0);
		$turniere[$turnierid]['liga'] = $ligagames[$turnier['gameid']]['liga'];
		$turniere[$turnierid]['liganame'] = $ligagames[$turnier['gameid']]['shortname'];
	}

	// keine Liga uebergeben, also nach verschiedenen Ligen suchen
	if (!isset($_GET['liga']) || !is_numeric($_GET['liga'])) {
		$liga = TURNIER_LIGA_NORMAL;
		foreach ($turniere as $turnierid => $turnier) {
			if ($turnier['liga'] == TURNIER_LIGA_WWCL) {
				$liga = TURNIER_LIGA_WWCL;
				break;

			} else if ($turnier['liga'] == TURNIER_LIGA_NGL) {
				$liga = TURNIER_LIGA_NGL;
				break;
			}
		}

	} else {
		$liga = $_GET['liga'];
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('partyid', $partyid);
	$smarty->assign('turniere', $turniere);
	$smarty->assign('liga', $liga);
	$smarty->display('turnier_export.tpl');
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'generate':
			generateExport();

	case 'show':
	default:	showExportList();
			break;
}

?>
