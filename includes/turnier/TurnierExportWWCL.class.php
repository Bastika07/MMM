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
require_once "turnier/TurnierLiga.class.php";
require_once "turnier/TurnierRanking.class.php";
//require_once "XML/Tree.php";


/**
 * Klasse fuer WWCL XML Export
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2005/08/30 ore - initial version
 */
class TurnierExportWWCL {
        var	$XML_tree;
	var	$XML_root;

	var	$XML_submit;
	var	$XML_tmpplayer;

	var	$tmpplayer = 1;

	/**
	* Helper Funktion fuer "sauberes" XML
	* @param string $str - zu escapender string
	* @return string - escapter string
	*/
	function char2xml($str) {
//		return htmlspecialchars(utf8_encode($str));
		return htmlspecialchars(utf8_encode($str));
	}

	/**
	 * Erzeugt ein Objekt mit dem WWCL XML root blub drin
	 * @param string $partyname - WWCL Party Name
	 * @param string $partyid - WWCL Party ID
	 * @param string $veranstalterid - WWCL Veranstalter ID
	 * @param string $stadt - Stadt der party
	 * @return mixed Objekt
	 */
	function create($partyname, $partyid, $veranstalterid, $stadt) {
		$retval = new TurnierExportWWCL();

		$xmltext = "<?xml version=\"1.0\"?>\n<wwcl></wwcl>";

		$retval->XML_tree = simplexml_load_string($xmltext);

		$retval->XML_submit = $retval->XML_tree->addChild('submit');
		$retval->XML_tmpplayer = $retval->XML_tree->addChild('tmpplayer');

		$retval->XML_submit->addChild('tool', $retval->char2xml('PELAS'));
		$retval->XML_submit->addChild('timestamp', $retval->char2xml(time()));
		$retval->XML_submit->addChild('party_name', $retval->char2xml($partyname));
		$retval->XML_submit->addChild('pid', $retval->char2xml($partyid));
		$retval->XML_submit->addChild('pvdid', $retval->char2xml($veranstalterid));
		$retval->XML_submit->addChild('stadt', $retval->char2xml($stadt));

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

		if ($turnier->flags & TURNIER_TREE_RUNDEN) {
			$ranking = TurnierRanking::getRankingRundenComplete($turnierid);
			$mode = 'M';
		} elseif($turnier->flags & TURNIER_RUNDEN) {
			$ranking = TurnierRanking::getRankingRunden($turnierid);
			$mode = 'M2';
		} else {
			#$ranking = TurnierRanking::getRanking($turnierid);
			$mode = ($turnier->flags & TURNIER_DOUBLE) ? 'D' : 'S';
		}
		#$last_place = TurnierRanking::getLastPlace($turnier);
		$ligagame = TurnierLiga::load($turnier->gameid);

		// gameinfo
		$XML_game = &$this->XML_tree->addChild('tourney');
		$XML_game->addChild('name', $this->char2xml($turnier->name));
		$XML_game->addChild('gid', $ligagame->shortname);
		
		$XML_game->addChild('mode', $mode);

		if ($mode  == 'D' or $mode  == 'S') {
			// jetzt die teams
			$player = array();
			$teamlist = Team::getTeamNameList($turnier->turnierid);
			$XML_game->addChild('maxplayer', $turnier->teamnum);
			foreach ($teamlist as $teamid => $team) {
				$team = Team::load($turnier->turnierid, $teamid);
	
				// team hat keine ligaid, also tmpplayer anlegen
				if (empty($team->ligaid)) {
					$XML_tmpteam = &$this->XML_tmpplayer->addChild('data');
	
					$tmpid = ($turnier->teamsize == 1) ? 'PT' : 'CT';
					$tmpid .= $this->tmpplayer++;
					$XML_tmpteam->addchild('tmpid', $tmpid);
					$XML_tmpteam->addchild('name', $this->char2xml($team->name));
	
					// soll eine neues wwcl team angemeldet werden?
					// -> hat der leader ne mail angegeben?
					$ligamail = false;
					foreach ($team->userlist as $userid => $flags) {
						if ($flags & TEAM2USER_LEADER) {
							if ($flags & TEAM2USER_LIGAMAIL) {
								$res = DB::query("select EMAIL from USER where USERID = '{$userid}'");
								$row = $res->fetch_assoc();
								$XML_tmpteam->addchild('email', $row['EMAIL']);
							} else {
								$XML_tmpteam->addchild('email');
							}
						}
					}
	
					$player[$team->teamid] = $tmpid;
				} else {
					$player[$team->teamid] = $team->ligaid;
				}
			}
	
			// und jetzt die matches
			$rounds = Round::getRoundList($turnierid);
			foreach ($rounds as $roundid => $round) {
				if ($roundid <= 255)
					$XML_round = &$XML_game->addChild('winner'.($roundid));
				else
					$XML_round = &$XML_game->addChild('looser'.($roundid & 255));
	
				// innerhalb der runden die matches
				$matches = Match::getMatchResultList($turnierid, $roundid);
				foreach ($matches as $matchid => $match) {
					$XML_match = &$XML_round->addChild('match');
	
					if ($match['result'] == T_TEAM1) {
						$XML_match->addchild('win', $match['team1'] != T_FREILOS ? $player[$match['team1']] : 'F');
						$XML_match->addchild('loose', $match['team2'] != T_FREILOS ? $player[$match['team2']] : 'F');
	
					} else if ($match['result'] == T_TEAM2) {
						$XML_match->addchild('win', $match['team2'] != T_FREILOS ? $player[$match['team2']] : 'F');
						$XML_match->addchild('loose', $match['team1'] != T_FREILOS ? $player[$match['team1']] : 'F');
					}
				}
			}
		} elseif ($mode  == 'M') { #if mode = M
			#zurückrechnen, wie viele teilnehmer maximal teilnehmen durften.
			if (count($ranking) > 128) { $teamnum = 256; }
			elseif (count($ranking) > 96) { $teamnum = 128; }
			elseif (count($ranking) > 64) { $teamnum = 96; }
			elseif (count($ranking) > 32) { $teamnum = 64; }
			elseif (count($ranking) > 16) { $teamnum = 32; }
			$XML_game->addChild('maxplayer', $teamnum);
			$XML_ranking = &$XML_game->addChild('ranking'); 
			foreach ($ranking as $rank_tmp) {
				$XML_data = &$XML_ranking->addChild('data');
				$XML_data->addChild('rank', $rank_tmp['pos']);

				$team = Team::load($rank_tmp['turnierid'], $rank_tmp['teamid']);
	
				// team hat keine ligaid, also tmpplayer anlegen
				if (empty($team->ligaid)) {
					$XML_tmpteam = &$this->XML_tmpplayer->addChild('data');
	
					$tmpid = ($turnier->teamsize == 1) ? 'PT' : 'CT';
					$tmpid .= $this->tmpplayer++;
					$XML_tmpteam->addchild('tmpid', $tmpid);
					$XML_tmpteam->addchild('name', $this->char2xml($team->name));
	
					// soll eine neues wwcl team angemeldet werden?
					// -> hat der leader ne mail angegeben?
					$ligamail = false;
					foreach ($team->userlist as $userid => $flags) {
						if ($flags & TEAM2USER_LEADER) {
							if ($flags & TEAM2USER_LIGAMAIL) {
								$res = DB::query("select EMAIL from USER where USERID = '{$userid}'");
								$row = $res->fetch_assoc();
								$XML_tmpteam->addchild('email', $row['EMAIL']);
							} else {
								$XML_tmpteam->addchild('email');
							}
						}
					}
					$XML_data->addChild('id', $tmpid);
				} else {
					$XML_data->addChild('id', $team->ligaid);
				}
				
			}
		} elseif ($mode  == 'M2') { #if mode = M2
			$XML_game->addChild('maxplayer', $turnier->teamnum);
			$XML_ranking = &$XML_game->addChild('ranking'); 
			foreach ($ranking as $rank_tmp) {
				$XML_data = &$XML_ranking->addChild('data');
				$XML_data->addChild('rank', $rank_tmp['pos']);

				$team = Team::load($turnier->turnierid, $rank_tmp['teamid']);
	
				// team hat keine ligaid, also tmpplayer anlegen
				if (empty($team->ligaid)) {
					$XML_tmpteam = &$this->XML_tmpplayer->addChild('data');
	
					$tmpid = ($turnier->teamsize == 1) ? 'PT' : 'CT';
					$tmpid .= $this->tmpplayer++;
					$XML_tmpteam->addchild('tmpid', $tmpid);
					$XML_tmpteam->addchild('name', $this->char2xml($team->name));
	
					// soll eine neues wwcl team angemeldet werden?
					// -> hat der leader ne mail angegeben?
					$ligamail = false;
					foreach ($team->userlist as $userid => $flags) {
						if ($flags & TEAM2USER_LEADER) {
							if ($flags & TEAM2USER_LIGAMAIL) {
								$res = DB::query("select EMAIL from USER where USERID = '{$userid}'");
								$row = $res->fetch_assoc();
								$XML_tmpteam->addchild('email', $row['EMAIL']);
							} else {
								$XML_tmpteam->addchild('email');
							}
						}
					}
					$XML_data->addChild('id', $tmpid);
				} else {
					$XML_data->addChild('id', $team->ligaid);
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
		$doc = new DOMDocument('1.0');
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($this->XML_tree->asXML());
		$doc->formatOutput = true;
		echo $doc->saveXML();		
		#echo $this->XML_tree->asXML();
	}
}

?>
