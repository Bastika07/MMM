<?php
/**
 * Admin erstellt/importiert ein neues team
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 */
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierGroup.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Team.class.php";

DB::connect();

if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
	return;

$turnier = Turnier::load($_GET['turnierid']);
if (!is_a($turnier, 'Turnier'))
	return;

// user nicht eingeloggt
if (!COMPAT::sessionIsValid()) {
	$err = TS_NOT_LOGGED_IN;

} else if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid)) {
	$err = TS_NOT_ADMIN;

} else {
	$teamname = $_POST['teamname'];
	$usernames = (isset($_POST['usernames'])) ? $_POST['usernames'] : array_fill(1, $turnier->teamsize, "");
	$ligaid = $_POST['ligaid'];
	
	switch ($_POST['action']) {
	case 'Resolve IDs':
		$tmp = array();
		foreach ($usernames as $id => $num) {
			if (is_numeric($num)) {
				$tmp[$num] = User::name($num);
			} else {
				$tmp[$id] = $num;
			}
		}
		$usernames = $tmp;
		break;

	case 'Reset':
		$teamname = "";
		$usernames = array_fill(1, $turnier->teamsize, "");
		break;

	case 'Import':
		$team = Team::load($_POST['turnier_sel'], $_POST['team_sel']);
		if (is_a($team, 'Team')) {
			$teamname = $team->name;
			$usernames = $team->namelist;
			$ligaid = $team->ligaid;

		} else {
			$warn = $team;
		}
		break;

	case 'Create Team':
		if (empty($teamname)) {
			$warn = TS_TEAMNAME_EMPTY;
			break;
		}
		
		$userids = array_keys($usernames);
		$leader = array_shift($userids);
		$team = TeamSystem::createTeam($turnier, $teamname, $leader, COMPAT::currentID());
		if (!is_a($team, 'Team')) {
			$err = TS_RESOLVE_IDS;
			break;
		}
		TeamSystem::setTeamLigaId($team, $ligaid, COMPAT::currentID());
		foreach ($userids as $id) {
			$err = TeamSystem::addUserToTeam($team, $id, COMPAT::currentID());
			if ($err != TS_SUCCESS)
				break;
		}

		if ($err == TS_SUCCESS) {
			// wenn ein team erstellt wurde, reload mit der neuen teamid
			header ("Location: ?page=29&action=show&turnierid={$team->turnierid}&teamid={$team->teamid}");
			die();
		}
		break;

	default:
	}
}
$turnier_sel = (isset($_POST['turnier_sel'])) ? $_POST['turnier_sel'] : 0;
$turnier_sel = (isset($_GET['turnier_sel'])) ? $_GET['turnier_sel'] : $turnier_sel;
$team_sel = (isset($_POST['team_sel'])) ? $_POST['team_sel'] : 0;

// wenn ngl oder wwcl, liga id anzeigen
$ligagame = TurnierLiga::load($turnier->gameid);
$flags['wwclid'] = ($ligagame->liga == TURNIER_LIGA_WWCL);
$flags['nglid'] = ($ligagame->liga == TURNIER_LIGA_NGL);

$groups = TurnierGroup::getGroups();

$turnier_list = array();
$tlist = Turnier::getTourneyList($turnier->partyid);
foreach ($tlist as $tmp) {
	if ($tmp['turnierid'] == $turnier->turnierid)
		continue;

	if ($tmp['teamsize'] == $turnier->teamsize) {
		if ($turnier_sel == 0)
			$turnier_sel = $tmp['turnierid'];

		if (!isset($turnier_list[$groups[$tmp['groupid']]['name']]))
			$turnier_list[$groups[$tmp['groupid']]['name']] = array();

		$turnier_list[$groups[$tmp['groupid']]['name']][$tmp['turnierid']] = $tmp['name'];
	}
}

$team_list = array();
$t2list = Team::getTeamNameList($turnier_sel);
foreach ($t2list as $tmp) {
	if ($team_sel == 0)
		$team_sel = $tmp['teamid'];

	$team_list[$tmp['teamid']] = "{$tmp['name']} ({$tmp['size']}/{$turnier->teamsize})";
}

$smarty = new PelasSmarty("turnier");
$smarty->assign('intranet', (LOCATION == "intranet"));
$smarty->assign('turnier', $turnier);
$smarty->assign('turnier_list', $turnier_list);
$smarty->assign('team_list', $team_list);
$smarty->assign('turnier_sel', $turnier_sel);
$smarty->assign('team_sel', $team_sel);
$smarty->assign('teamname', $teamname);
$smarty->assign('usernames', $usernames);
$smarty->assign('ligaid', $ligaid);
$smarty->assign('flags', $flags);

if (isset($err))
	$smarty->assign('errstr', TurnierSystem::getErrStr($err));
else if (isset($warn))
	$smarty->assign('warnstr', TurnierSystem::getErrStr($warn));

$smarty->displayWithFallback('team_create2.tpl');

?>
