<?php
/**
 * Erstellt ein neues Team
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 */
 
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
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

} else {
	// als jetziger User ein 1on1 Team anlegen mit dem namen des Users als Teamnamen
	if ($turnier->teamsize == 1) {
		$login = COMPAT::getLoginByID(COMPAT::currentID());
		$team = TeamSystem::createTeam($turnier, $login, COMPAT::currentID(), COMPAT::currentID());

	// wenn namefeld angegeben wurde, als jetziger User ein XonX Team anlegen
	} else if (isset($_POST['name'])) {
		if (empty($_POST['name'])) {
			$warn = TS_TEAMNAME_EMPTY;
		} else {
			$team = TeamSystem::createTeam($turnier, $_POST['name'], COMPAT::currentID(), COMPAT::currentID());
		}
	}

	if (isset($team)) {
		if (is_a($team, 'Team')) {
			// wenn ein team erstellt wurde, reload mit der neuen teamid
			header ("Location: ?page=29&action=show&turnierid={$team->turnierid}&teamid={$team->teamid}");
			die();
		// rueckgabe war Fehlercode
		} else {
			$err = $team;
		}
	}
}

$smarty = new PelasSmarty("turnier");
$smarty->assign('intranet', (LOCATION == "intranet"));
$smarty->assign('turnier', $turnier);

if (isset($err))
	$smarty->assign('errstr', TurnierSystem::getErrStr($err));
else if (isset($warn))
	$smarty->assign('warnstr', TurnierSystem::getErrStr($warn));

$smarty->displayWithFallback('team_create.tpl');

?>