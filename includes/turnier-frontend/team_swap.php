<?php
/**
 * Zeigt die Details zu einem Match an
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 * @todo keine ueberprufung von
 */
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "turnier/Tree.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/Jump.class.php";

DB::connect();

function swapTeams() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['matchid']) || !is_numeric($_GET['matchid']))
		return;

	if (!isset($_GET['side']) || !is_numeric($_GET['side']))
		return;
	
	if (!isset($_POST['tauschid']) || !is_numeric($_POST['tauschid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;
		
	$match = Match::load($turnier->turnierid, $_GET['matchid']);
	if (!is_a($match, 'Match'))
		return;

	// altes team
	$teamid = ($_GET['side'] == 0) ? $match->team1 : $match->team2;
	if ($teamid != T_FREILOS) {
		$team = Team::load($turnier->turnierid, $teamid);
		if (!is_a($team, 'Team'))
			return;
	}

	// alles Matches in dieser Runde holen
	$matches = Match::getMatchResultList($turnier->turnierid, $match->round);
	foreach ($matches as $tmp) {
		// ist das team gemeint?
		if ($tmp['team1'] == $_POST['tauschid']) {
			$matchid = $tmp['matchid'];
			$side = 0;
			break;
		
		// oder dieses?
		} else if ($tmp['team2'] == $_POST['tauschid']) {
			$matchid = $tmp['matchid'];
			$side = 1;
			break;
		}
	}

	switch ($_GET['side']) {
	case 0:
		$match->team1 = $_POST['tauschid'];
		break;
	case 1:
		$match->team2 = $_POST['tauschid'];
		break;
	}
	$match->setReady();
	$match->save();
	
	if (isset($side) && isset($matchid)) {
		$match2 = Match::load($turnier->turnierid, $matchid);
		
		switch ($side) {
		case 0:
			$match2->team1 = $team->teamid;
			break;
		case 1:
			$match2->team2 = $team->teamid;
			break;
		}
		$match2->setReady();
		$match2->save();
	}

	TurnierSystem::flushCache($turnier->turnierid);
	header ("Location: ?page=26&turnierid={$turnier->turnierid}&matchid={$match->matchid}");
}


function showTeamList() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['matchid']) || !is_numeric($_GET['matchid']))
		return;
	if (!isset($_GET['side']) || !is_numeric($_GET['side']))
		return;
	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;

	$match = Match::load($turnier->turnierid, $_GET['matchid']);
	if (!is_a($match, 'Match'))
		return;

# auf MATCH_COMPLETE darf nicht abgefragt werden, da matche mit Freilosen auch "complete" und nicht zuruecksetzbar sind!
#	if (!($match->flags & MATCH_COMPLETE)) {
		$teamid = ($_GET['side'] == 0) ? $match->team1 : $match->team2;

		$team = Team::load($turnier->turnierid, $teamid);

		// alle teams des turniers
		$teams = Team::getTeamNameList($turnier->turnierid);
		
		// alles Matches in dieser Runde holen
		$matches = Match::getMatchResultList($turnier->turnierid, $match->round);
		
		// team tauschen -> nur teams aus ungespielten matches
		// freilos einsetzen -> nur teams ohne match
		foreach ($matches as $tmp) {
			if (($tmp['flags'] & MATCH_COMPLETE) || ($teamid == T_FREILOS)) {
				unset($teams[$tmp['team1']]);
				unset($teams[$tmp['team2']]);
			}
		}

		foreach ($teams as $id => $tmp)
			$teams[$id] = $tmp['name'];

#	} else {
#		$err = "Das Match ist bereits gespielt. Bitte vorher Resetten.";
#	}

	$jump = new Jump();
	$jump->size = $turnier->teamnum /2;
	$match->viewnum = $jump->calcReal($match->matchid);
	
	$smarty = new PelasSmarty("turnier");
	$smarty->assign('intranet', (LOCATION == "intranet"));
	$smarty->assign('turnier', $turnier);
	$smarty->assign('match', $match);
	$smarty->assign('side', $_GET['side']);
	$smarty->assign('teams', $teams);
	$smarty->assign('team', $team);

	if (isset($err))
		$smarty->assign('err', $err);

	$smarty->displayWithFallback('team_swap.tpl');
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'swap':		swapTeams();
				break;
	default:
	case 'show':		showTeamList();
				break;
}

?>
