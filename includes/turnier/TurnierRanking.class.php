<?php

require_once 'turnier/t_constants.php';
require_once 'turnier/Turnier.class.php';
require_once 'turnier/Match.class.php';

define('T_RANK_WINNER', 0);
define('T_RANK_LOSER', 1);

class TurnierRanking {

	/**
	 * erzeugt internen aufbau und ranking
	 * @param int $type - single/double
	 * @param int $size - teamanzahl in der ersten runde
	 */
	function generateRanking($type, $size) {
		$retval = array();

		$matches = $size;
		$roundid = 0;
		$matchnr = 0;

		$wbrank = array(4 => array(-1),
				8 => array(5, -1),
				16 => array(9, 5, -1),
				32 => array(17, 9, 5, -1),
				64 => array(33, 17, 9, 5, -1),
				128 => array(65, 33, 17, 9, 5, -1),
				256 => array(129, 65, 33, 17, 9, 5, -1),
				);

		$lbrank = array(4 => array(5, 4, 3),
				8 => array(9, 7, 5, 4, 3),
				16 => array(17, 13, 9, 7, 5, 4, 3),
				32 => array(33, 25, 17, 13, 9, 7, 5, 4, 3),
				64 => array(65, 49, 33, 25, 17, 13, 9, 7, 5, 4, 3),
				128 => array(129, 97, 65, 49, 33, 25, 17, 13, 9, 7, 5, 4, 3),
				256 => array(257, 193, 129, 97, 65, 49, 33, 25, 17, 13, 9, 7, 5, 4, 3),
				);

		while ($matches > 1) {
			if (($type == TURNIER_SINGLE) && ($matches != 2)) {
				// wb match
				for ($i = 0; $i < $matches/2; $i++) {
					$matchid = $matchnr + $i;
					$retval[$matchid][T_RANK_WINNER] = -1;
					$retval[$matchid][T_RANK_LOSER] = $wbrank[$size][$roundid];
				}

			} else if (($type == TURNIER_DOUBLE) && ($roundid > 0)){
				// lb vs lb match
				for ($i = 0; $i < $matches/2; $i++) {
					$matchid = ($size /2) + $matchnr + $i;
					$retval[$matchid][T_RANK_WINNER] = -1;
					$retval[$matchid][T_RANK_LOSER] = $lbrank[$size][$roundid *2 -1];
				}

				// lb vs wb
				for ($i = 0; $i < $matches/2; $i++) {
					$matchid = $size + $matchnr + $i;
					$retval[$matchid][T_RANK_WINNER] = -1;
					$retval[$matchid][T_RANK_LOSER] = $lbrank[$size][$roundid *2];
				}
			}

			$roundid++;
			$matches /= 2;
			$matchnr += $matches;
		}

		if ($type == TURNIER_DOUBLE) {
			// overall finale
			$matchid = $size -1;
			$retval[$matchid][T_RANK_WINNER] = 1;
			$retval[$matchid][T_RANK_LOSER] = 2;

		} else if ($type == TURNIER_SINGLE) {
			// finale
			$matchid = $matchnr -1;
			$retval[$matchid][T_RANK_WINNER] = 1;
			$retval[$matchid][T_RANK_LOSER] = 2;

			// spiel um platz 3
			$matchid = $matchnr;
			$retval[$matchid][T_RANK_WINNER] = 3;
			$retval[$matchid][T_RANK_LOSER] = 4;
		}
		return $retval;
	}

	/**
	 * legt das ranking in der DB an
	 */
	function _not_used() {
		for ($i = 4; $i <= 256; $i *= 2) {
			$single = TurnierRanking::generateRanking(TURNIER_SINGLE, $i);
			$double = TurnierRanking::generateRanking(TURNIER_DOUBLE, $i);

			foreach ($single as $matchid => $tmp) {
				if ($tmp[T_RANK_WINNER] != -1) {
					$sql = "INSERT INTO t_ranking SET
						rank = '{$tmp[T_RANK_WINNER]}',
						size = '{$i}',
						type = '".TURNIER_SINGLE."',
						matchid = '{$matchid}',
						result = '".T_RANK_WINNER."'";
					DB::query($sql);
				}
				
				if ($tmp[T_RANK_LOSER] != -1) {
					$sql = "INSERT INTO t_ranking SET
						rank = '{$tmp[T_RANK_LOSER]}',
						size = '{$i}',
						type = '".TURNIER_SINGLE."',
						matchid = '{$matchid}',
						result = '".T_RANK_LOSER."'";
					DB::query($sql);
				}
			}

			foreach ($double as $matchid => $tmp) {
				if ($tmp[T_RANK_WINNER] != -1) {
					$sql = "INSERT INTO t_ranking SET
						rank = '{$tmp[T_RANK_WINNER]}',
						size = '{$i}',
						type = '".TURNIER_DOUBLE."',
						matchid = '{$matchid}',
						result = '".T_RANK_WINNER."'";
					DB::query($sql);
				}
				
				if ($tmp[T_RANK_LOSER] != -1) {
					$sql = "INSERT INTO t_ranking SET
						rank = '{$tmp[T_RANK_LOSER]}',
						size = '{$i}',
						type = '".TURNIER_DOUBLE."',
						matchid = '{$matchid}',
						result = '".T_RANK_LOSER."'";
					DB::query($sql);
				}
			}
		}
	}

        /**
	* erzeugt fuer ein Rundenturnier ein Ranking
	* @param int $turnierid - Turnier
	* @return mixed Array mit Ranking Infos
	*/
	function getRankingRunden($turnierid) {
		$retval = array();
		$sql = "SELECT m.team1, m.team2, m.flags, m.result1, m.result2
			FROM t_turnier t, t_match m
			WHERE t.turnierid = '{$turnierid}'
			AND m.turnierid = t.turnierid
			AND m.team1 <> '".T_FREILOS."'
			AND m.team2 <> '".T_FREILOS."'";

		$res = DB::query($sql);
		#$i = 0;
		while ($row = mysql_fetch_assoc($res)) {
			$tmp = Match::getResult($row['team1'], $row['team2'], $row['result1'], $row['result2'], $row['flags']);
			#echo $row['flags']." A".var_dump($row['flags'] & MATCH_TEAM1_ROT)." ";
			if(!isset($retval[$row['team1']])) {
				$retval[$row['team1']]['points'] = 0;
				$retval[$row['team1']]['round_diff'] = 0;
			}
			if(!isset($retval[$row['team2']])) {
				$retval[$row['team2']]['points'] = 0;
				$retval[$row['team2']]['round_diff'] = 0;
			}
			$retval[$row['team1']]['ROT'] = ($row['flags'] & MATCH_TEAM1_ROT) ? 1 : 0;
			$retval[$row['team2']]['ROT'] = ($row['flags'] & MATCH_TEAM2_ROT) ? 1 : 0;
			if ($tmp == T_TEAM1) {
				$retval[$row['team1']]['points'] += 3;
				$retval[$row['team2']]['points'] += 0;

                        } else if ($tmp == T_TEAM2) {
                                $retval[$row['team1']]['points'] += 0;
                                $retval[$row['team2']]['points'] += 3;

			} else {
                                $retval[$row['team1']]['points'] += 0;
								$retval[$row['team2']]['points'] += 0;
                        }
			#$retval[$row['team1']][result] += $row['result1'];
			#$retval[$row['team2']][result] += $row['result2'];
			$retval[$row['team1']]['round_diff'] += $row['result1'] - $row['result2'];
			$retval[$row['team2']]['round_diff'] += $row['result2'] - $row['result1'];
		}
		
		#sortierung der Rangliste:
		# 1. nach Punkten
		# 2. Rundendifferenz (Alle eigenen erspielen Punkte minus alle Punkte der gegener)
#var_dump($retval);
		arsort($retval);
		$i = 0;
		$retval2 = array();
		$last_points = 0;
		foreach($retval as $key => $val) {
			if ($val <> $last_points)
				$i++;
			array_push($retval2, array("pos" => $i, "points" => $retval[$key]['points'], "round_diff" => $retval[$key]['round_diff'], "teamid" => $key));
			$last_points = $val;
		}
		return $retval2;

	}


	/**
	 * erzeugt fuer ein Turnier ein Ranking
	 * @param int $turnierid - Turnier
	 * @return mixed Array mit Ranking Infos
	 */
	function getRanking($turnierid) {
		$retval = array();

		$sql = "SELECT m.team1, m.team2, m.flags, m.result1, m.result2, r.rank, r.result
			FROM t_turnier t, t_match m, t_ranking r
			WHERE t.turnierid = '{$turnierid}'
			AND r.type = (t.flags & '".(TURNIER_SINGLE | TURNIER_DOUBLE)."')
			AND r.size = t.teamnum
			AND m.turnierid = t.turnierid
			AND m.matchid = r.matchid
			ORDER BY r.rank";

		$res = DB::query($sql);
		$i = 0;
		while ($row = mysql_fetch_assoc($res)) {
			$tmp = Match::getResult($row['team1'], $row['team2'], $row['result1'], $row['result2'], $row['flags']);
			if ($tmp == T_TEAM1) {
				$winner = $row['team1'];
				$loser = $row['team2'];

			} else if ($tmp == T_TEAM2) {
				$winner = $row['team2'];
				$loser = $row['team1'];

			} else {
				$winner = 0;
				$loser = 0;
			}

			if ($row['result'] == T_RANK_WINNER) {
				$retval[$i]['pos'] = $row['rank'];
				$retval[$i]['teamid'] = ($winner != -1) ? $winner : 0x10000000;

			} else if ($row['result'] == T_RANK_LOSER) {
				$retval[$i]['pos'] = $row['rank'];
				$retval[$i]['teamid'] = ($loser != -1) ? $loser : 0x10000000;
			}
			$i++;
		}
		array_multisort($retval);

		foreach($retval as $key => $val)
			if ($val['teamid'] == 0x10000000)
				$retval[$key]['teamid'] = -1;
		#var_dump($retval);
		return $retval;
	}


	function getShortRanking($turnier) {
		$retval = array();
		$rank = array();
		if ($turnier['flags'] & TURNIER_RUNDEN)
			$rank = TurnierRanking::getRankingRunden($turnier['turnierid']);
		else
			$rank = TurnierRanking::getRanking($turnier['turnierid']);
		
		foreach($rank as $r) {
			$retval[ $r['pos'] ]['teamid'] = $r['teamid'];
			if ($r['teamid'] > 0)
			{
				$t = Team::load($turnier['turnierid'], $r['teamid'], false);
				$retval[ $r['pos'] ]['teamname'] = $t->name;
			}
		}
		return $retval;
	}


        /**
	* erzeugt fuer ein Rundenturnier ein Ranking inclusive der Vorrunden und den turnier_id's
	* Teilnehmer Ohne Platzierung (rote Karte) werden nicht ausgegeben.
	* @param int $turnierid - Haupttrnier
	* @return mixed Array mit Ranking Infos
	*/
	function getRankingRundenComplete($turnierid) {
		
		$haupt_ranking = array();
		$haupt_ranking_tmp = TurnierRanking::getRanking($turnierid);
		foreach($haupt_ranking_tmp as $haupt_ranking_tmp_entry) { # platz 0 = rote karte und nicht berücksichtigen
			if ($haupt_ranking_tmp_entry['pos'] > 0) {
				array_push($haupt_ranking, array('pos' => $haupt_ranking_tmp_entry['pos'], 
					"teamid" => $haupt_ranking_tmp_entry['teamid'], "turnierid" => $turnierid));
			}
		}

		#unterturniere raussuchen und dann einzelnd auswerten
		$sql = "SELECT turnierid
			FROM t_turnier
			WHERE pturnierid = '".$turnierid."'";
		$res= DB::query($sql);

		#den letzten Platz raus funden, ab da wird weiter gerechnet für die Vorrunden
		$last_place = $haupt_ranking[count($haupt_ranking)-1][pos]-2; # minus 2, da die ersten beiden platzierungen schon weiter gekommen sind
		#die(var_dump($last_place));
		#alle vorrunden durch gehen
		while ($row = mysql_fetch_assoc($res)) {
			$ranking_temp = TurnierRanking::getRankingRunden($row['turnierid']);
			#die ersten beiten plätze sind schon im hauptturnier
			foreach($ranking_temp as $ranking_temp_entry) {
				if($ranking_temp_entry['pos'] > 2) {
					array_push($haupt_ranking, array('pos' => ($ranking_temp_entry['pos']+$last_place), 
						"teamid" => $ranking_temp_entry['teamid'], "turnierid" => $row['turnierid']));
				}
			}
		}
		return $haupt_ranking;

	}


	function getLastPlace($turnierid) {
		$sql = "SELECT MAX(r.rank) as last
			FROM t_ranking r, t_turnier t
			WHERE t.turnierid = '{$turnierid}'
			AND r.type = (t.flags & '".(TURNIER_SINGLE | TURNIER_DOUBLE)."')
			AND r.size = t.teamnum";
		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);
		return $row['last'];
	}
}
?>
