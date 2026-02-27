<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once ("turnier/t_constants.php");
require_once ("turnier/Team.class.php");
require_once ("turnier/Turnier.class.php");
require_once ("turnier/TurnierAdmin.class.php");
require_once ("turnier/TurnierLiga.class.php");

/**
 * Funktions Sammlung für das Turniersystem
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/24 ore - initial version
 * @version 2004/11/23 ore - von TurnierSystem.class abgetrennt
 * @static
 */
class TeamSystem {

	/** Erstellt ein Team
	 * @param Turnier $turnier Turnierobject in dem das Team spielt
	 * @param string $teamname Name des Teams oder empty wenn name des Users genommen werden soll
	 * @param int $userid User der der Leader des Teams wird
	 * @param int $admin User der das Team erstellt (kann ein admin sein)
	 * @return mixed Team | TS_ERROR
	 * @todo $admin noch ohne funktion
	 */
	function createTeam($turnier, $teamname, $userid, $admin) {
		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($admin, $turnier->turnierid)) {

			// turnieranmeldung offen?
			if ($turnier->status != TURNIER_STAT_RES_OPEN)
				return TS_REG_CLOSED;

			// bezahlt?
			if (!COMPAT::userHasPayed($userid, $turnier->partyid))
				return TS_NOT_PAYED;
		}

		// user schon in einem anderen Team in diesem Turnier angemeldet?
		if (Team::findUser($turnier->turnierid, $userid) != 0)
			return TS_ALREADY_REG;

		// genug coins?
		if (TeamSystem::calcCoins($turnier->partyid, $userid) < $turnier->coins)
			return TS_TOO_FEW_COINS;

		// Anzahl Teams?
		if (Team::getTeamCount($turnier->turnierid) >= $turnier->teamnum)
			return TS_TOO_MANY_TEAMS;

		// Teamname schon vorhanden?
		if (Team::findTeamName($turnier->turnierid, $teamname) != 0)
			return TS_DUP_TEAM;

		// Ligaid von Leader holen
		$ligaid = TeamSystem::getLigaID($turnier, $userid);

		// Ligaid schon vorhanden?
		if (!empty($ligaid) && Team::findLigaID($turnier->turnierid, $ligaid))
			return TS_DUP_LEAGUE_ID;

		$team = new Team();
		$team->turnierid = $turnier->turnierid;
		$team->addUser($userid);
		$team->name = $teamname;
		$team->ligaid = $ligaid;
		$team->flags = (TEAM_USE_COINS | TEAM_IS_ACTIVE);
		$team->create();
		return $team;
	}


	/**
	 * loescht ein Team aus einem Turnier
	 * @param Team $team Team das geloescht werden soll
	 * @param int $leader User der das Team loeschen will
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function deleteTeam($team, $admin) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// aufrufender User muss Admin sein
		if (!TurnierAdmin::isAdmin($admin, $turnier->turnierid))
			return TS_NOT_ADMIN;

		// turnier darf noch nicht laufen
		if ($turnier->status == TURNIER_STAT_RUNNING || $turnier->status == TURNIER_STAT_PAUSED)
			return TS_TOURNEY_RUNNING;

		Team::delete($team->turnierid, $team->teamid);
		return TS_SUCCESS;
	}


	/**
	 * fuegt einen User dem Team als Anwaerter hinzu
	 * @param Team $team Teamobject zu dem der User joinen will
	 * @param int $userid User der joinen will
	 * @param int $admin User der den User joinen will (kann admin sein)
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function joinUserToTeam($team, $userid, $admin) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($admin, $turnier->turnierid)) {

			// turnieranmeldung offen?
			if ($turnier->status != TURNIER_STAT_RES_OPEN)
				return TS_REG_CLOSED;
		}

		// bezahlt?
		if (!COMPAT::userHasPayed($userid, $turnier->partyid))
			return TS_NOT_PAYED;

		// user schon irgendwo in diesem Turnier angemeldet?
		if (Team::findUser($turnier->turnierid, $userid) != 0)
			return TS_ALREADY_REG;

		// genug coins?
		if (TeamSystem::calcCoins($turnier->partyid, $userid) < $turnier->coins)
			return TS_TOO_FEW_COINS;

		$team->addQueue($userid);
		$team->save();

		return TS_SUCCESS;
	}


	/**
	 * fuegt einen User dem Team als Anwaerter hinzu
	 * @param Team $team Teamobject zu dem der User joinen will
	 * @param int $userid User der joinen will
	 * @param int $leader User der den User accepten will
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function acceptUserToTeam($team, $userid, $leader) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($leader, $turnier->turnierid)) {

			// aufrufender User muss Leader sein
			if ($team->leader != $leader)
				return TS_NOT_LEADER;

			// turnieranmeldung offen?
			if ($turnier->status != TURNIER_STAT_RES_OPEN)
				return TS_REG_CLOSED;
		}

		// bezahlt?
		if (!COMPAT::userHasPayed($userid, $turnier->partyid))
			return TS_NOT_PAYED;

		// user nicht in der queue?
		if (!isset($team->userlist[$userid]))
			return TS_NOT_QUEUED;

		// user bereits member? -> KEINE FEHLERMELDUNG
		if ($team->userlist[$userid] & TEAM2USER_MEMBER)
			return TS_SUCCESS;

		// user will auch ein anderes Team joinen?
		if (Team::findUser($turnier->turnierid, $userid) != $team->teamid)
			return TS_ALREADY_REG;

		// genug coins?
		if (TeamSystem::calcCoins($turnier->partyid, $userid) < $turnier->coins)
			return TS_TOO_FEW_COINS;

		// Anzahl der Member im Team?
		if ($team->size >= $turnier->teamsize)
			return TS_TEAM_FULL;

		$team->acceptUser($userid);
		$team->save();

		return TS_SUCCESS;
	}


	/**
	 * fuegt dem Team einen User ohne Queue hinzu
	 * @param Team $team Team
	 * @param int $userid User des aufgenommen werden soll
	 * @param int $leader User der den User aufnimmt
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	 function addUserToTeam($team, $userid, $leader) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($leader, $turnier->turnierid)) {

			// aufrufender User muss Leader sein
			if ($team->leader != $leader)
				return TS_NOT_LEADER;

			// turnieranmeldung offen?
			if ($turnier->status != TURNIER_STAT_RES_OPEN)
				return TS_REG_CLOSED;

			// bezahlt?
			if (!COMPAT::userHasPayed($userid, $turnier->partyid))
				return TS_NOT_PAYED;
		}

		// user bereits in queue oder member?
		if (isset($team->userlist[$userid]))
			return TS_NOT_QUEUED;

		// user schon irgendwo in diesem Turnier angemeldet?
		if (Team::findUser($turnier->turnierid, $userid) != 0)
			return TS_ALREADY_REG;

		// genug coins?
		if (TeamSystem::calcCoins($turnier->partyid, $userid) < $turnier->coins)
			return TS_TOO_FEW_COINS;

		// Anzahl der Member im Team?
		if ($team->size >= $turnier->teamsize)
			return TS_TEAM_FULL;

		$team->addUser($userid);
		$team->save();

		return TS_SUCCESS;
	 }


	/**
	 * loescht einen User aus dem Team
	 * @param Team $team Teamobject aus dem gekickt werden soll
	 * @param int $userid User die gekickt werden soll
	 * @param int $leader User der den User kicken will
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function deleteUserFromTeam($team, $userid, $leader) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($leader, $turnier->turnierid)) {

			// aufrufender User muss er selber oder der Leader sein
			if (($userid != $leader) && ($team->leader != $leader))
				return TS_NOT_LEADER;

			// turnieranmeldung muss offen sein?
			if ($turnier->status != TURNIER_STAT_RES_OPEN)
				return TS_REG_CLOSED;
		}

		// user muss bei uns vorhanden sein
		if (!isset($team->userlist[$userid]))
			return TS_NOT_MEMBER;

		// soll der teamleader gekickt werden?
		if ($team->leader == $userid) {

			// wenn noch mitspieler vorhanden sind -> nicht moeglich
			if ($team->size != 1) {
				return TS_IS_LEADER;

			} else {
				// turnieranmeldung muss offen sein! (im turnier darf kein team geloescht werden)
				if ($turnier->status != TURNIER_STAT_RES_OPEN)
					return TS_REG_CLOSED;

				Team::delete($team->turnierid, $team->teamid);
				return TS_NO_SUCH_TEAM;
			}
		}

		$team->delUser($userid);
		$team->save();
		return TS_SUCCESS;
	}


	/**
	 * Setzt einen neuen TeamLeader
	 * @param Team $team Team dessen Leader gesetzt werden soll
	 * @param int $userid User der Leader werden soll
	 * @param int $leader User der neuen Leader setzen will
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function setNewLeader($team, $userid, $leader) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($leader, $turnier->turnierid)) {

			// aufrufender User muss Leader sein
			if ($team->leader != $leader)
				return TS_NOT_LEADER;

			// turnieranmeldung muss offen sein?
	 		if ($turnier->status != TURNIER_STAT_RES_OPEN)
	 			return TS_REG_CLOSED;
		}

		// user muss im Team sein
		else if (!isset($team->userlist[$userid]) || !($team->userlist[$userid] & TEAM2USER_MEMBER))
			return TS_NOT_MEMBER;

		$team->setLeader($userid);
		$team->save();

		return TS_SUCCESS;
	}


	/**
	 * Setzt eine neue Team-ID
	 * @param Team $team Team dessen LigaID gesetzt werden soll
	 * @param string $ligaid id die gesetzt werden soll
	 * @param int $leader User der neue LigaID setzen will
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function setTeamLigaId($team, $ligaid, $leader) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($team->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wenn aufrufender User ein Admin ist, fallen ein paar tests weg
		if (!TurnierAdmin::isAdmin($leader, $turnier->turnierid)) {

			// aufrufender User muss Leader sein
			if ($team->leader != $leader)
				return TS_NOT_LEADER;

			// turnieranmeldung offen?
			if ($turnier->status != TURNIER_STAT_RES_OPEN)
				return TS_REG_CLOSED;
		}

		// Ligaid schon vorhanden?
		if (!empty($ligaid) && Team::findLigaID($turnier->turnierid, $ligaid))
			return TS_DUP_LEAGUE_ID;

		$team->ligaid = $ligaid;
		$team->save();

		return TS_SUCCESS;
	}

	/**
	 * Holt von einem User die benoetigte Liga-ID
	 * @param Turnier $turnier TurnierObj fuer das die LigaID benoetigt wird
	 * @param int $userid User von dem die ID geholt wird.
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function getLigaID($turnier, $userid) {
		$game = TurnierLiga::load($turnier->gameid);

		if ($game->liga == TURNIER_LIGA_WWCL) {
			$field = ($turnier->teamsize == 1) ? "WWCL_SINGLE" : "WWCL_TEAM";

		} else if ($game->liga == TURNIER_LIGA_NGL) {
			$field = ($turnier->teamsize == 1) ? "NGL_SINGLE" : "NGL_TEAM";

		} else {
			return '';
		}

		$sql = "SELECT {$field} FROM USER WHERE USERID = '{$userid}'";
		$res = DB::query($sql);
		$row = $res->fetch_assoc();

		return $row[$field];
	}


	/**
	 *  gibt die anzahl der verfuegbaren coins fuer diesen user auf dieser party zurueck
	 * @param int $partyid Partyid
	 * @param int $userid User dessen coins berechnet werden sollen
	 * @return int Coins
	 */
	function calcCoins($partyid, $userid) {
		$mandantid = COMPAT::getMandantFromParty($partyid);
		$maxCoins = TeamSystem::getMaxCoins($mandantid);
		$usedCoins = TeamSystem::calcUsedCoins($partyid, $userid);
		return $maxCoins - $usedCoins;
	}

	/**
	 * holt die maximalen coins fuer diesen Mandanten zurueck
	 * @param int $mandantid
	 * @return int coins
	 */
	static function getMaxCoins($mandantid) {
		$sql = "SELECT STRINGWERT AS coins
			FROM CONFIG
			WHERE MANDANTID = '{$mandantid}'
			AND PARAMETER = 'COINS_GESAMT'";
		$res = DB::query($sql);
		$row = $res->fetch_assoc();
		return $row['coins'];
	}

	/**
	 * holt die verbrauchten coins des users
	 * @param int $partyid
	 * @param int $userid
	 * @return int used coins
	 */
	function calcUsedCoins($partyid, $userid) {
		$sql = "SELECT SUM(t.coins) as used
			FROM t_team2user t2u
			LEFT JOIN t_team team ON t2u.userid = '{$userid}'
			AND t2u.teamid = team.teamid
			AND t2u.turnierid = team.turnierid
			AND (team.flags & '".TEAM_USE_COINS."') = '".TEAM_USE_COINS."'
			AND (t2u.flags & '".TEAM2USER_MEMBER."') = '".TEAM2USER_MEMBER."'
			LEFT JOIN t_turnier t ON team.turnierid = t.turnierid
			AND t.partyid = '{$partyid}'";
		$res = DB::query($sql);
		$row = $res->fetch_assoc();
		return $row['used'];
	}
}
?>
