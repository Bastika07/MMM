<?php
/**
 * Zeigt ein Turnier als Tabellen uebersicht an
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 */
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierRanking.class.php";
require_once "turnier/Team.class.php";

if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
	return;
$turnierid = $_GET['turnierid'];

$smarty = new PelasSmarty("turnier");
$smarty->caching = !isset($_GET['nocache']);
if (!$smarty->isCachedWithFallback('turnier_ranking.tpl', $turnierid)) {

	DB::connect();

	$turnier = Turnier::load($turnierid);
	if (!is_a($turnier, 'Turnier'))
		return;

	// teamids / namen laden
	$teamlist = Team::getTeamNameList($turnier->turnierid);

	// ranking holen
	
	if ($turnier->flags & TURNIER_RUNDEN) {
		$ranking = TurnierRanking::getRankingRunden($turnier->turnierid);
	} else {
		$ranking = TurnierRanking::getRanking($turnier->turnierid);
	}

	foreach($ranking as $num => $rank)
		$ranking[$num]['teamname'] = isset($teamlist[$rank['teamid']]['name']) ? $teamlist[$rank['teamid']]['name'] : "";

	$smarty->assign('intranet', (LOCATION == "intranet"));
	$smarty->assign('turnier', $turnier);
	$smarty->assign('teams', $teamlist);
	$smarty->assign('ranking', $ranking);
}
$smarty->displayWithFallback('turnier_ranking.tpl', $turnierid);

?>
