<?php
/**
 * Zeigt ein Turnier als Turnierbaum an
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

$smarty = new PelasSmarty("turnier");
$smarty->caching = !isset($_GET['nocache']);
if (!$smarty->isCachedWithFallback('turnier_tree.tpl', $_GET['turnierid'])) {

	DB::connect();

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	// teamids / namen laden
	$teamlist = Team::getTeamNameList($turnier->turnierid);

	// alle runden fuer das turnier laden
	$roundlist = Round::getRoundList($turnier->turnierid);

	// alle matches laden
	$matchlist = Match::getMatchResultList($turnier->turnierid);

	$jump = new Jump();
	$jump->size = $turnier->teamnum / 2;

	// Spruenge ins loserbracket angeben
	if ($turnier->flags & TURNIER_DOUBLE) {
		foreach ($matchlist as $matchid => $match) {
			if (($matchid >= ($turnier->teamnum / 2)) && ($matchid < ($turnier->teamnum -1))) {

				// sprung berechnen
				$dest = $jump->getNewLoserPos($matchid);

				// umrechung fuer anzeige
				$matchlist[$matchid]['note'] = "loser to # ".$jump->calcReal($dest);
				$matchlist[$dest]['note'] = "loser from # ".$jump->calcReal($matchid);
			}

			// anzeige der richtigen ID
			$matchlist[$matchid]['viewnum'] = $jump->calcReal($matchid);
		}

		// LB -> WB (overall finale)
		$matchlist[$turnier->teamnum -1]['note'] = "winner from # ".($turnier->teamnum *2 -1);
	} else {
		foreach ($matchlist as $matchid => $match) {
			// anzeige der richtigen ID
			$matchlist[$matchid]['viewnum'] = $jump->calcReal($matchid);
		}

		$matchlist[$turnier->teamnum -1]['note'] = "Spiel um Platz 3";
	}

	// turnierbaum aufbau
	$mode = ($turnier->flags & TURNIER_DOUBLE) ? 'double' : 'single';
	$table = buildTree($mode, $turnier->teamnum);

	$smarty->assign('intranet', (LOCATION == "intranet"));
	$smarty->assign('turnier', $turnier);
	$smarty->assign('table', $table);
	$smarty->assign('teams', $teamlist);
	$smarty->assign('rounds', $roundlist);
	$smarty->assign('matches', $matchlist);

}
$smarty->displayWithFallBack('turnier_tree.tpl', $_GET['turnierid']);

?>
