<?php
/**
 * Zeigt die Details zu einem Turnier an
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 */
 
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierPreis.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierLiga.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Team.class.php";

DB::connect();

if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
	return;

$turnier = Turnier::load($_GET['turnierid']);
if (!is_a($turnier, 'Turnier'))
	return;

$t_statusArr = Turnier::getStatusArr();
$statusStr = $t_statusArr[$turnier->status];

$preise = TurnierPreis::getList($turnier->turnierid);
$admins = TurnierAdmin::getListByTourney($turnier->turnierid);
natcasesort($admins);

$subtourneys = TurnierSystem::getSubtourneys($turnier->turnierid);

$teams = Team::getTeamNameList($turnier->turnierid);

$ligagame = TurnierLiga::load($turnier->gameid);

$smarty = new PelasSmarty("turnier");
$smarty->assign('intranet', (LOCATION == "intranet"));
$smarty->assign('turnier', $turnier);
if (isset($icon_big)) {
	$smarty->assign('icon_big', $icon_big);
}
if ($turnier->mindestalter != "") {
	$smarty->assign('icon_mindestalter', PELASHOST."gfx/ab".$turnier->mindestalter.".png" );
} else {
	$smarty->assign('icon_mindestalter', PELASHOST."gfx/ab0.png" );
}

$smarty->assign('ligagame', $ligagame);
$smarty->assign('statusStr', $statusStr);
$smarty->assign('preise', $preise);
$smarty->assign('admins', $admins);
$smarty->assign('teamcount', count($teams));
$smarty->assign('subtourneys', $subtourneys);
$smarty->assign('teams', $teams);
$smarty->assign('isadmin', TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid));
$smarty->displayWithFallback('turnier_detail.tpl');

?>
