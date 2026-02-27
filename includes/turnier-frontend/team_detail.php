<?php
/**
 * Zeigt die Details zu einem Team an
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 */
 
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/TeamSystem.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierLiga.class.php";
require_once "turnier/Jump.class.php";

DB::connect();

function doSomeThing($action) {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['teamid']) || !is_numeric($_GET['teamid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	$team = Team::load($turnier->turnierid, $_GET['teamid']);
	if (!is_a($team, 'Team'))
		return;

	// user eingeloggt?
	if (!COMPAT::sessionIsValid()) {
		_show("show", $turnier, $team, TS_NOT_LOGGED_IN);
		return;
	}

	$err = TS_SUCCESS;
	$newaction = "show";

	switch($action) {
		case 'join':
			$err = TeamSystem::joinUserToTeam($team, COMPAT::currentID(), COMPAT::currentID());
			break;

		case 'accept':
			if (isset($_GET['userid']) || is_numeric($_GET['userid']))
				$err = TeamSystem::acceptUserToTeam($team, $_GET['userid'], COMPAT::currentID());
			break;

		case 'add':
			if (isset($_POST['userid']) && is_numeric($_POST['userid']))
				$err = TeamSystem::addUserToTeam($team, $_POST['userid'], COMPAT::currentID());
			break;

		case 'del':
			if (isset($_GET['userid']) && is_numeric($_GET['userid']))
				$err = TeamSystem::deleteUserFromTeam($team, $_GET['userid'], COMPAT::currentID());

			if ($err == TS_NO_SUCH_TEAM) {
				header ("Location: ?page=21&turnierid={$team->turnierid}");
				die();
			}
			break;

		case 'setleader':
			if (isset($_GET['userid']) && is_numeric($_GET['userid']))
				$err = TeamSystem::setNewLeader($team, $_GET['userid'], COMPAT::currentID());
			break;

		case 'setligaid':
			if (isset($_POST['ligaid'])) {
				if ($_POST['ligaid'] != $team->ligaid)
					$err = TeamSystem::setTeamLigaId($team, $_POST['ligaid'], COMPAT::currentID());
			} else {
				$newaction = "setligaid";
			}
			break;

		case 'coinsback':
			if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $team->turnierid)) {
				$err = TS_NOT_ADMIN;

			} else if (($team->flags & (TEAM_IS_ACTIVE | TEAM_USE_COINS)) == TEAM_USE_COINS) {
				$team->flags &= ~(TEAM_USE_COINS);
				$team->save();
			}
			break;

		case 'setligamail':
			if (isset($_GET['userid']) && is_numeric($_GET['userid']) && (COMPAT::currentID() == $_GET['userid'])) {
				$ligaMail = $team->getLigaMail($_GET['userid']);
				$team->setLigaMail($_GET['userid'], !$ligaMail);
				$team->save();
			}
			break;
	}

	if (($err == TS_SUCCESS) && ($newaction == "show")) {
		header ("Location: ?page=29&action=show&turnierid={$team->turnierid}&teamid={$team->teamid}");
		die();
	}

	_show($newaction, $turnier, $team, $err);
}


function showTeam() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['teamid']) || !is_numeric($_GET['teamid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	$team = Team::load($turnier->turnierid, $_GET['teamid']);
	if (!is_a($team, 'Team'))
		return;

	_show("show", $turnier, $team);
}


function _show($action, $turnier, $team, $err = TS_SUCCESS) {
	$tempflags = array();

	// sitzplaetze holen
	$seats = array();
	foreach ($team->userlist as $userid => $flags)
		$seats[$userid] = COMPAT::getSeat($turnier->partyid, $userid);

	// wenn ngl oder wwcl, liga id anzeigen
	$ligagame = TurnierLiga::load($turnier->gameid);
	$tempflags['wwclid'] = ($ligagame->liga == TURNIER_LIGA_WWCL);
	$tempflags['nglid'] = ($ligagame->liga == TURNIER_LIGA_NGL);

	// user ist eingeloggt, also infos zum team erzeugen
	if (COMPAT::sessionIsValid() && isset($team)) {

		// als user darf man sich selbst kicken
		$tempflags['kick'][COMPAT::currentID()] = true;

		// und man darf entscheiden ob man seine mail weitergeben will
		$tempflags['ligamail']['show'][COMPAT::currentID()] = true;
		$tempflags['ligamail']['enabled'][COMPAT::currentID()] = ($turnier->status == TURNIER_STAT_RES_OPEN);
		$tempflags['ligamail']['checked'] = $team->getLigaMail();

		// wenn man noch nicht in dem team ist, kann man joinen
		$tempflags['join'] = (($turnier->status == TURNIER_STAT_RES_OPEN) && ($team->size < $turnier->teamsize) && !isset($team->userlist[COMPAT::currentID()]));

		// als leader darf man nur handeln wenn die anmeldung offen ist
		if (($team->leader == COMPAT::currentID()) && ($turnier->status == TURNIER_STAT_RES_OPEN)) {

			// als leader darf man kicken, ligamail status anzeigen, und accepten aller teammitglieder
			foreach ($team->userlist as $userid => $flags) {
				$tempflags['kick'][$userid] = true;
				$tempflags['accept'][$userid] = ($flags & TEAM2USER_QUEUED);
				$tempflags['ligamail']['show'][$userid] = true;
			}
			// und man darf die ligaid setzen
			$tempflags['setligaid'] = true;

			// und clanmember hinzufuegen
			$tempflags['addclan'] = ($team->size < $turnier->teamsize);

			// und das team loeschen (wenn man alleine drin ist)
			$tempflags['cancelteam'] = ($team->size == 1);
		}

		// als admin darf man immer etwas machen
		// man darf: kicken, accepten, leader setzen, ligaid aendern, team loeschen, ligamail einsehen
		if (TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid)) {

			// als admin darf man immer kicken, accepten, leader setzen und ligamail status sehen
			foreach ($team->userlist as $userid => $flags) {
				$tempflags['kick'][$userid] = true;
				$tempflags['accept'][$userid] = ($flags & TEAM2USER_QUEUED);
				$tempflags['setleader'][$userid] = ($flags & TEAM2USER_MEMBER);
				$tempflags['ligamail']['show'][$userid] = true;
			}

			// das team darf man auch auflösen
			$tempflags['cancelteam'] = ($team->size == 1);

			// die liga id darf auch immer eingegeben werden
			$tempflags['setligaid'] = true;

			// clanmember des leaders darf man auch adden
			$tempflags['addclan'] = ($team->size < $turnier->teamsize);

			// manuelle coinrueckgabe
			$tempflags['coinsback'] = (($team->flags & (TEAM_IS_ACTIVE | TEAM_USE_COINS)) == TEAM_USE_COINS);

			// user per hand hinzufuegen
			$tempflags['adduser'] = ($team->size < $turnier->teamsize);
		}
	}

	// clanmember des leaders adden
	if (isset($tempflags['addclan']) && $tempflags['addclan']) {
		$mandantid = COMPAT::getMandantFromParty($turnier->partyid);
		$clan = COMPAT::getClanMemberOf($mandantid, $team->leader);

		// liste reduzieren
		foreach ($clan as $userid => $name) {
			// clanmember sollten noch nicht im team sein
			if (isset($team->userlist[$userid]))
				unset($clan[$userid]);

			// clanmember muessen bezahlt haben (+1 query)
			else if (!COMPAT::userHasPayed($userid, $turnier->partyid))
				unset($clan[$userid]);

			// clanmember duerfen noch nicht am selben turnier angemeldet sein (+1 query)
			else if (Team::findUser($team->turnierid, $userid) != 0)
				unset($clan[$userid]);
		}

		// keiner mehr uebrig im clan -> kein adden moeglich
		$tempflags['addclan'] = (count($clan) > 0);

	} else {
		$clan = array();
	}

	// im intranet noch die match history holen
	if (LOCATION == "intranet") {
		// fuer umrechnung zu echten matchids benoetigt
		$jump = new Jump();
		$jump->size = $turnier->teamnum /2;

		// turnierverlauf holen
		$matches = Match::getTeamResultList($turnier->turnierid, $team->teamid);
		foreach ($matches as $matchid => $match) {
			$matches[$matchid]['viewnum'] = $jump->calcReal($matchid);
			switch ($match['result']) {
				case T_TEAM1:
					$matches[$matchid]['win'] = ($match['team1'] == $team->teamid);
					break;

				case T_TEAM2:
					$matches[$matchid]['win'] = ($match['team2'] == $team->teamid);
					break;
			}
		}
	}

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('intranet', (LOCATION == "intranet"));
	$smarty->assign('action', $action);
	$smarty->assign('team', $team);
	$smarty->assign('turnier', $turnier);
	$smarty->assign('matches', $matches);
	$smarty->assign('clan', $clan);
	$smarty->assign('seats', $seats);
	$smarty->assign('tempflags', $tempflags);
	if ($err != TS_SUCCESS)
		$smarty->assign('errstr', TurnierSystem::getErrStr($err));
	$smarty->displayWithFallback('team_detail.tpl');
}


// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'join':
	case 'accept':
	case 'add':
	case 'del':
	case 'setleader':
	case 'setligaid':
	case 'coinsback':
	case 'setligamail':	doSomeThing($action);
				break;

	default:
	case 'show':		showTeam();
				break;
}
?>
