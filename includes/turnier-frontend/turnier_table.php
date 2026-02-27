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

require_once "turnier/Tree.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/Round.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/Jump.class.php";

if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
	return;
$turnierid = $_GET['turnierid'];

$smarty = new PelasSmarty("turnier");
$smarty->caching = !isset($_GET['nocache']);
if (!$smarty->isCachedWithFallback('turnier_table.tpl', $turnierid)) {

	DB::connect();
	$turnier = Turnier::load($turnierid);
	if (!is_a($turnier, 'Turnier'))
		return;

	// teamids / namen laden
	$teamlist = Team::getTeamNameList($turnier->turnierid);

	// alle runden fuer das turnier laden
	$roundlist = Round::getRoundList($turnier->turnierid);

	$jump = new Jump();
	$jump->size = $turnier->teamnum /2;

	// matchlist holen
	foreach ($roundlist as $roundid => $round) {
		$matches = Match::getMatchResultList($turnier->turnierid, $roundid);
		foreach ($matches as $matchid => $match)
			$matches[$matchid]['viewnum'] = $jump->calcReal($matchid);

		$roundlist[$roundid]['matches'] = $matches;
	}

	$smarty->assign('intranet', (LOCATION == "intranet"));
	$smarty->assign('turnier', $turnier);
	$smarty->assign('teams', $teamlist);
	$smarty->assign('rounds', $roundlist);

}
$smarty->displayWithFallback('turnier_table.tpl', $turnierid);

?>
