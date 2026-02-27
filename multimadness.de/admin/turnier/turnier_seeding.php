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
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/Round.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/TeamSystem.class.php";
require_once "turnier/Jump.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();

// ok
function resetTourney() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!TurnierAdmin::isADmin(COMPAT::currentID(), $_GET['turnierid']))
		return;

	if ($turnier->flags & TURNIER_TREE_RUNDEN) {
		$sql = "UPDATE t_team
			SET seedpos = 0
			WHERE turnierid = '{$turnier->turnierid}'";
		$res = DB::query($sql);
	} else {
		// alles Matches zuruecksetzen
		// TODO: kommentare
		Match::resetMatches($_GET['turnierid']);
		Match::deleteEvents($_GET['turnierid']);
	}
	TurnierSystem::flushCache($_GET['turnierid']);

	header ("Location: {$_SERVER['PHP_SELF']}?action=show&turnierid={$_GET['turnierid']}");
}


// ok
function kickTeam() {

	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['teamid']) || !is_numeric($_GET['teamid']))
		return;

	$team = Team::load($_GET['turnierid'], $_GET['teamid']);
	if (!is_a($team, 'Team'))
		return;

	// team aus dem Turnier loeschen
	TeamSystem::deleteTeam($team, COMPAT::currentID());
	TurnierSystem::flushCache($_GET['turnierid']);

	header ("Location: {$_SERVER['PHP_SELF']}?action=show&turnierid={$_GET['turnierid']}");
}


// ok
function resizeTourney() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!TurnierAdmin::isADmin(COMPAT::currentID(), $_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	// anzahl teams
	$teamcount = Team::getTeamCount($turnier->turnierid);

	// teamgroesse verkleinern
	while ($turnier->teamnum/2 >= $teamcount)
		$turnier->teamnum /= 2;

	$turnier->save();
	TurnierSystem::flushCache($turnier->turnierid);

	header ("Location: {$_SERVER['PHP_SELF']}?action=show&turnierid={$_GET['turnierid']}");
}

// ok
function createTourney() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!TurnierAdmin::isADmin(COMPAT::currentID(), $_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	// erstma alten muell loeschen
	Match::delete($turnier->turnierid);
	Match::deleteEvents($turnier->turnierid);
	Round::delete($turnier->turnierid);

	// und dann neu erzeugen
	TurnierSystem::createInternalTree($turnier);
	TurnierSystem::flushCache($turnier->turnierid);

	header ("Location: {$_SERVER['PHP_SELF']}?action=show&turnierid={$_GET['turnierid']}");
}

// ok
function seedTeam() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!isset($_GET['teamid']) || !is_numeric($_GET['teamid']))
		return;

	TurnierSystem::seedTeam($turnier, $_GET['teamid']);
	TurnierSystem::flushCache($turnier->turnierid);
	header ("Location: {$_SERVER['PHP_SELF']}?action=show&turnierid={$turnier->turnierid}");
}

// ok
//@todo noch ist hier nichts "random"
function randomFill() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	// Teamliste holen
	$teams = Team::getTeamNameList($turnier->turnierid);

	// matchliste erste runde checken
	$matches = Match::getMatchResultList($turnier->turnierid, 0);

	// teams die gesetzt worden sin, aus dem TeamArray loeschen
	foreach ($matches as $matchid => $match) {
		if ($match['flags'] & MATCH_TEAM1_SEEDED)
			unset($teams[$match['team1']]);

		else if ($match['flags'] & MATCH_TEAM2_SEEDED)
			unset($teams[$match['team2']]);
	}

	$teamlist = array();
	foreach ($teams as $teamid => $team)
		array_push($teamlist, $teamid);

	TurnierSystem::fillSeeding($turnier, $teamlist);
	TurnierSystem::flushCache($turnier->turnierid);
	header ("Location: {$_SERVER['PHP_SELF']}?action=show&turnierid={$turnier->turnierid}");
}

// ok
function showSeeding() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if ($turnier->flags & TURNIER_RUNDEN) #beim Rundenturnier kein Seeding
		return; 

	// Teamliste holen
	$teams = Team::getTeamNameList($turnier->turnierid);

	if ($turnier->flags & TURNIER_TREE_RUNDEN) {
		//@todo prüfen ob turnier noch nicht gestartet
		$seed = TurnierSystem::getSeedList($turnier);
		$fill = array(); # wird nicht genutzt...
		
	} else {

		// seeding tabellen aufstellen
		$seedTableRev = array_flip(Jump::getSeedTable($turnier->teamnum, $turnier));
	
		// matchliste erste runde checken
		$matches = Match::getMatchResultList($turnier->turnierid, 0);
		$alreadyPlayed = false;
		$seed = array();
		$fill = array();
		foreach ($matches as $matchid => $match) {
			// wenn irgendwas anderes ausser seeding passiert ist
			if ($match['flags'] & ~(MATCH_TEAM1_SEEDED | MATCH_TEAM2_SEEDED))
				$alreadyPlayed = true;
	
			if ($match['flags'] & MATCH_TEAM1_SEEDED)
				$seed[$match['team1']] = $seedTableRev[$matchid<<1];
	
			else if ($match['team1'] > 0)
				$fill[$match['team1']] = $seedTableRev[($matchid<<1)];
	
			if ($match['flags'] & MATCH_TEAM2_SEEDED)
				$seed[$match['team2']] = $seedTableRev[($matchid<<1) +1];
	
			else if ($match['team2'] > 0)
				$fill[$match['team2']] = $seedTableRev[($matchid<<1) +1];
		}
	
		// check ob richtig round & match anzahl
		// das reicht uns als check dafür, ob das turnier richtig angelegt wurde
		// <crappycode>
		$i = $turnier->teamnum;
		$x = 1; $y = 1;
		while ($i > 2) {
			$x = ($x<<1) | 1;
			$y++;
			$i /=2;
		}
	
		if ($turnier->flags & TURNIER_DOUBLE) {
			$x *= 2;
			$y = $y * 3 -1;
		} else {
			// spiel um platz 3
			$x++;
		}
	
		$wrongSize = ((count(Match::getMatchResultList($turnier->turnierid)) != $x) ||
			(count(Round::getRoundList($turnier->turnierid)) != $y));
		// </crappycode>
	}


	if (LOCATION == "intranet") {
		$frontend = "http://www.lan.multimadness.de";

	} else {
		$mandantid = COMPAT::getMandantFromParty($turnier->partyid);
		$row = DB::query("SELECT REFERER FROM MANDANT WHERE MANDANTID = '{$mandantid}'")->fetch_assoc();
		$frontend = $row['REFERER'];
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('frontend', $frontend);
	$smarty->assign('turnier', $turnier);
	$smarty->assign('teams', $teams);
	$smarty->assign('seed', $seed);
	$smarty->assign('fill', $fill);
	$smarty->assign('wrongSize', $wrongSize);
	$smarty->assign('alreadyPlayed', $alreadyPlayed);
	$smarty->assign('teamcount', count($teams));
	$smarty->assign('freilose', $turnier->teamnum - count($teams));
	$smarty->assign('toobig', ($turnier->teamnum/2 >= count($teams)) && $turnier->teamnum > 4);
	$smarty->display('turnier_seeding.tpl');
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'kick':	kickTeam();
			break;
	case 'reset':	resetTourney();
			break;
	case 'resize':	resizeTourney();
			break;
	case 'create':	createTourney();
			break;
	case 'seed':	seedTeam();
			break;
	case 'random':	randomFill();
			break;
	default:	showSeeding();
			break;
}

?>
