<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once 'turnier/t_constants.php';
require_once "turnier/Team.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Round.class.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierRanking.class.php";
require_once "XML/Tree.php";


/**
 * Klasse fuer NGL XML Export
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/10/07 ore - initial version
 */
class TurnierExportNGL {
        var	$XML_tree;
	var	$XML_root;

	/**
	* Helper Funktion fuer "sauberes" XML
	* @param string $str - zu escapender string
	* @return string - escapter string
	*/
	function char2xml($str) {
		return htmlspecialchars(utf8_encode($str));
	}

	/**
	 * Erzeugt ein Objekt mit dem NGL XML root blub drin
	 * @param string $partyid - NGL Party ID
	 * @param string $partyname - NGL Party Name
	 * @param string $datum - Startdatum der Party
	 * @param string $contact - Mailaddresse fuer Rueckfragen
	 * @return mixed Objekt
	 */
	function create($partyid, $partyname, $datum, $contact) {
		$retval = new TurnierExportNGL();

		$retval->XML_tree = new XML_Tree(NULL, '1.0');
		$retval->XML_root = &$retval->XML_tree->addRoot('export', NULL, array('version' => '1.4'));
		$XML_laninfo = &$retval->XML_root->addChild('laninfo');
		$XML_laninfo->addChild('eventid', $retval->char2xml($partyid));
		$XML_laninfo->addChild('name', $retval->char2xml($partyname));
		$XML_laninfo->addChild('country', $retval->char2xml('DE'));
		$XML_laninfo->addChild('date', $retval->char2xml($datum));
		$XML_laninfo->addChild('contact', $retval->char2xml($contact));

		return $retval;
	}

	/**
	 * fuegt dem Objekt ein Turnier in XML form hinzu
	 * @param int $turnierid - Turnierid des Turniers
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function addTurnier($turnierid) {
		$turnier = Turnier::load($turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		$ranking = TurnierRanking::getRanking($turnier->turnierid);
		foreach($ranking as $num => $rank)
			$ranking2[$rank['teamid']] = $rank['pos'];

		$last_place = TurnierRanking::getLastPlace($turnier->turnierid);
		$ligagame = TurnierLiga::load($turnier->gameid);

		// gameinfo
		$XML_game = &$this->XML_root->addChild('game');
		$XML_gameinfo = &$XML_game->addChild('gameinfo');
		$XML_gameinfo->addChild('type', $ligagame->shortname);
		$XML_gameinfo->addChild('mode', ($turnier->flags & TURNIER_DOUBLE) ? 'DE' : 'SE');

		// jetzt die teams
		$XML_teams = &$XML_game->addChild('teams');
		$teamlist = Team::getTeamNameList($turnier->turnierid);
		foreach ($teamlist as $teamid => $team) {
			$team = Team::load($turnier->turnierid, $teamid);

			// jedes team einzeln
			$XML_team = &$XML_teams->addChild('team');

			// disqualifiziert?
			if (isset($ranking2[$team->teamid]))
			    $XML_team->addChild('place', $ranking2[$team->teamid]);
			else
			    $XML_team->addChild('place', $last_place);

			$XML_team->addchild('nglid', !empty($team->ligaid) ? $team->ligaid : 0);
			$XML_team->addchild('name', $this->char2xml($team->name));
			$XML_team->addchild('tmpid', $teamid);

			// jeder member des teams
			$XML_members = &$XML_team->addChild('members');
			foreach ($team->userlist as $userid => $flags) {
				// wenn das nur ein anwaerter ist, den nicht abschicken
				if (!($flags & TEAM2USER_MEMBER))
					continue;

				$XML_player = &$XML_members->addChild('player');

				$res = DB::query("select LOGIN as login, NAME as name,
						NACHNAME as nachname, EMAIL as email,
						NGL_SINGLE as nglid
						from USER where USERID = {$userid}");
				$player = $res->fetch_assoc();

				$XML_player->addChild('nglid', !empty($player['nglid']) ? $player['nglid'] : 0);
				$XML_player->addChild('nickname', $this->char2xml($player['login']));
				if ($flags & TEAM2USER_LIGAMAIL)
				    $XML_player->addChild('email', $this->char2xml($player['email']));
				else
				    $XML_player->addChild('email', $this->char2xml("no@email"));

				$XML_player->addChild('firstname', $this->char2xml($player['name']));
				$XML_player->addChild('lastname', $this->char2xml($player['nachname']));
				$XML_player->addChild('leader', ($flags & TEAM2USER_LEADER) ? "yes" : "no");
			}
		}

		// und jetzt die runden
		$XML_matches = &$XML_game->addChild('matches');
		$rounds = Round::getRoundList($turnierid);
		foreach ($rounds as $roundid => $round) {
			$roundname = ($roundid <= 255) ? array('WB' => $roundid +1) : array('LB' => ($roundid & 255));
			$XML_round = &$XML_matches->addChild('round', NULL, $roundname);

			// innerhalb der runden die matches
			$matches = Match::getMatchResultList($turnierid, $roundid);
			foreach ($matches as $matchid => $match) {
				$XML_match = &$XML_round->addChild('match');
				$XML_match->addChild('tmpid1', $match['team1'] != T_FREILOS ? $match['team1'] : 0);
				$XML_match->addChild('tmpid2', $match['team2'] != T_FREILOS ? $match['team2'] : 0);
				$XML_match->addChild('score1', $match['result1']);
				$XML_match->addChild('score2', $match['result2']);

				switch ($match['result']) {
					case T_TEAM1:
						$XML_match->addchild('winner', $match['team1'] != T_FREILOS ? $match['team1'] : 0);
						break;

					case T_TEAM2:
						$XML_match->addchild('winner', $match['team2'] != T_FREILOS ? $match['team2'] : 0);
						break;
				}
			}
		}
		return TS_SUCCESS;
	}

	/**
	 * Gibt den XML Tree als Ascii zurueck
	 * @return string XML Tree
	 */
	function view() {
		$this->XML_tree->dump();
	}
}

?>
