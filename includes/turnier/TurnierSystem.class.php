<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once ("turnier/t_constants.php");
require_once ("turnier/Team.class.php");
require_once ("turnier/Match.class.php");
require_once ("turnier/Round.class.php");
require_once ("turnier/Turnier.class.php");
require_once ("turnier/TurnierAdmin.class.php");
require_once ("turnier/TeamSystem.class.php");
require_once ("turnier/Jump.class.php");
require_once ("turnier/Team.class.php");
require_once ("turnier/TurnierRanking.class.php");

/**
 * Funktions Sammlung für das Turniersystem
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/24 ore - initial version
 * @version 2004/08/10 ore - blub
 * @version 2007/03/27 mth - Umstellung wg. verbessertem Seeding
 * @static
 */
class TurnierSystem {
	/**
	* gibt eine Liste von Vorrundenturnieren zu einem Hauptturnier zurück
	* @param int $turnierid ID für das Vorrunden ausgelesen werden sollen
	* @return Turnier array
	**/
	function getSubtourneys($turnierid) {
		$sql = "SELECT turnierid FROM t_turnier WHERE pturnierid = '{$turnierid}' ORDER BY name ASC";
		$res = DB::query($sql);
		$subtourneys = array();
		while($row = mysql_fetch_array($res)) {
			array_push($subtourneys, TURNIER::load($row['turnierid']));
		}
		return $subtourneys;
	}


	/**
	 * Alle Vorrunden zu einem Turnier loeschen
	 * @param int $turnierid ID
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function delPrelim($turnierid) {
		#alle vorhandenen vorrunden löschen
		$sql = "SELECT turnierid
			FROM t_turnier
			WHERE pturnierid = '".$turnierid."'";
		$res= DB::query($sql);

		while ($row = mysql_fetch_assoc($res)) {
			#matches löschen
			Match::delete($row['turnierid']);
			Round::delete($row['turnierid']);
			Match::deleteEvents($row['turnierid']);
			TEAM::delete($row['turnierid']);
			TURNIER::delete($row['turnierid']);
			TurnierSystem::flushCache($row['turnierid']);
		}
	}

	/**
	 * erzeugt die Vorrundenturniere für 
	 * @param Turnier $turnier Turnier
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function createSubtourneys($turnier) {
		#Aufteilung der Gruppen
		$teamnum = TEAM::getTeamCount($turnier->turnierid);
		$groupsnum = 0;

		if ($teamnum < 16) {
			die("Vorrundenturnier ist bei dieser Teamanzahl nicht mehr sinnvoll!");
		} elseif($teamnum >= 16 && $teamnum <= 32) {
			$groupsnum = 4;
		} elseif($teamnum > 32 && $teamnum <= 64) {
			$groupsnum = 8;
		} elseif($teamnum > 64 && $teamnum <= 128) {
			$groupsnum = 16;
		} elseif($teamnum > 128) {
			die("Wenn das mal nicht zu viele Teilnehmer sind...");
		} 

		TurnierSystem::delPrelim($turnier->turnierid);

		$turnier_ids = array();
		#erzeugen der Vorrunden
		for($i = 1;$i<=$groupsnum;$i++) {
			#$vorrunde = new Turnier();
			$vorrunde = clone $turnier; # als default die Einstellungen des Hauptturniers übernehmen
			$vorrunde->turnierid = 0; # soll ja neu vergeben werden
			$vorrunde->flags = 0;
			$vorrunde->flags |= TURNIER_RUNDEN;
			$vorrunde->flags |= TURNIER_TREE_RUNDEN;
			$vorrunde->pturnierid = $turnier->turnierid;
			$vorrunde->name .= " Vorrunde ".$i;
			$vorrunde->coins = 0;
			$vorrunde->status = TURNIER_STAT_RUNNING;
			$vorrunde->create();
			$turnier_ids[$i-1] = $vorrunde->turnierid;
		}
		#Turnierbaum für Endrunde erzeugen
		$turnier->teamnum = $groupsnum * 2; # es kommen immer die rsten beiden Teams weiter
		$turnier->flags = 0;
		$turnier->flags |= TURNIER_SINGLE;
		$turnier->flags |= TURNIER_TREE_RUNDEN;

		#zuorden/kopieren der Teams
		$sql = "SELECT teamid
			FROM t_team
			WHERE turnierid = '".$turnier->turnierid."' 
			ORDER BY seedpos";
		$res= DB::query($sql);
		#die(var_dump($turnier_ids));
		$vorrunde_num = 0;
		while ($row = mysql_fetch_assoc($res)) {
			$team = TEAM::load($turnier->turnierid, $row[teamid]);
			$team->turnierid = $turnier_ids[$vorrunde_num];
			$team->teamid = 0;
			$team->markMembersAsNew();
			$team->create();
			#var_dump($vorrunde_num);
			$vorrunde_num = ($vorrunde_num == count($turnier_ids)-1) ? 0 : $vorrunde_num + 1;
		}
		#starten aller Vorrunden
		for($i = 0;$i<$groupsnum;$i++) {
			$turnier_tmp = TURNIER::load($turnier_ids[$i]);
			TURNIERSYSTEM::createRoundTourney($turnier_tmp);
			TurnierSystem::startupTourney($turnier_tmp);
			TURNIERSYSTEM::flushCache($turnier_tmp->turnierid);
		}
		#Hauptturnier erzeugen. teams kommen später rein

		// erstma alten muell loeschen
		Match::delete($turnier->turnierid);
		Match::deleteEvents($turnier->turnierid);
		Round::delete($turnier->turnierid);
	
		// und dann neu erzeugen
		TurnierSystem::createInternalTree($turnier);
		TurnierSystem::flushCache($turnier->turnierid);
	}


	/**
	 * erzeugt die Runden für ein Rundenturnier und fügt die Team ein
	 * @param Turnier $turnier Turnier das erzeugt werden soll.
	 * return int TS_SUCCESS | TS_ERROR
	 */
	function createRoundTourney($turnier) {
		#alle teams laden
		$teamlist = TEAM::getTeamNameList($turnier->turnierid);
		$teamnum = TEAM::getTeamCount($turnier->turnierid);
		if ($teamnum == 0)
			return TS_ERROR;

		#ungerade Anzahl Teams? -> Dann Freilos vormerken
		if($teamnum%2!=0) {
			$teamnum++;
			array_push($teamlist,array("teamid" => T_FREILOS));
		} 
		#Runden und matches generieren
		$last_flipped = true;
		$matchid = 0;
		$team1 = array_shift($teamlist);
		for($roundnum=1;$roundnum<$teamnum;$roundnum++) {
			$tisch_oben=array($team1);
			$tisch_unten=array();
			if($roundnum>1)
			{
				$team_temp = array_pop($teamlist);
				array_unshift($teamlist, $team_temp);
			}
			for($i=0;$i<$teamnum-1;$i++)
			{
				if($i+1<($teamnum/2))
				{
					array_push($tisch_oben,$teamlist[$i]);
				} else {
					array_push($tisch_unten,$teamlist[$i]);
				}
			}
			$tisch_unten=array_reverse($tisch_unten,0);
			if(!$last_flipped)
			{
				$temp = $tisch_oben;
				$tisch_oben = $tisch_unten;
				$tisch_unten = $temp;
			}
			$last_flipped = !$last_flipped;

			#runde erstellen
			$round = new Round();
			$round->turnierid = $turnier->turnierid;
			$round->roundid = $roundnum - 1; # in der Datenbank muss die Erste Runden die ID "0" haben...
			$round->name = "Round ".$roundnum;
			$round->create();
			#echo "Round ".$roundnum."<br>";

			#paarungen erstellen und der Runde hinzufügen
			for($i=0;$i<($teamnum/2);$i++)
			{
				$match = new Match();
				$match->turnierid = $turnier->turnierid;
				$match->matchid = $matchid++;
				$match->round = $round->roundid;
				$match->team1 = $tisch_oben[$i][teamid];
				$match->team2 = $tisch_unten[$i][teamid];
				$match->create();
				#echo "matchid ".$match->matchid."<br>";
				#echo "team1 ".$match->team1."<br>";
				#echo "team2 ".$match->team2."<br>";
			}
		}
	}

	/**
	 * Erzeugt Rounds & Matches fuer ein Turnier
	 * @param Turnier $turnier Turnier das "erzeugt" werden soll
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function createInternalTree($turnier) {
		$matches = $turnier->teamnum;
		$roundid = 0;
		$matchnr = 0;

		while ($matches > 1) {
			// Winnerbracket
			$round = new Round();
			$round->turnierid = $turnier->turnierid;
			$round->roundid = $roundid;
			$round->name = (($matches != 2) ? "Round ".($roundid +1) : (($turnier->flags & TURNIER_SINGLE) ? "Finale" : "WB Finale"));
			$round->create();
			for ($i = 0; $i < $matches/2; $i++) {
				$match = new Match();
				$match->turnierid = $turnier->turnierid;
				$match->matchid = $matchnr + $i;
				$match->round = $round->roundid;
				$match->create();
			}

			if (($turnier->flags & TURNIER_DOUBLE) && ($roundid > 0)) {
				// Loserbracket vs Loserbracket
				$round = new Round();
				$round->turnierid = $turnier->turnierid;
				$round->roundid = $roundid *2 -1 | 256;
				$round->name = "Round ".($roundid +1);
				$round->create();
				for ($i = 0; $i < $matches/2; $i++) {
					$match = new Match();
					$match->turnierid = $turnier->turnierid;
					$match->matchid = ($turnier->teamnum /2) + $matchnr + $i;
					$match->round = $round->roundid;
					$match->create();
				}

				// Loserbracket vs Winnerbracket
				$round = new Round();
				$round->turnierid = $turnier->turnierid;
				$round->roundid = $roundid *2 | 256;
				$round->name = (($matches > 2) ? "Round ".($roundid +1).".5" : "LB Finale");
				$round->create();
				for ($i = 0; $i < $matches/2; $i++) {
					$match = new Match();
					$match->turnierid = $turnier->turnierid;
					$match->matchid = ($turnier->teamnum) + $matchnr + $i;
					$match->round = $round->roundid;
					$match->create();
				}
			}
			$roundid++;
			$matches /= 2;
			$matchnr += $matches;
		}
		if ($turnier->flags & TURNIER_DOUBLE) {
			// overall finale
			$round = new Round();
			$round->turnierid = $turnier->turnierid;
			$round->roundid = $roundid;
			$round->name = "Overall Finale";
			$round->create();

			$match = new Match();
			$match->turnierid = $turnier->turnierid;
			$match->matchid = $turnier->teamnum -1;
			$match->round = $round->roundid;
			$match->create();
		} else {
			// spiel um platz 3
			$match = new Match();
			$match->turnierid = $turnier->turnierid;
			$match->matchid = $matchnr;
			$match->round = $round->roundid;
			$match->create();
		}

		TurnierSystem::flushCache($turnier->turnierid);
		return TS_SUCCESS;
	}


	/**
	 * Setzt ein Team in der ersten Runde
	 * @param Turnier $turnier Turnier in dem gesetzt wird
	 * @param int $teamid Team das gesetzt werden soll
	 * @param boolean $endrunde Bei der Endrunde muss doch das normale seeding greifen.
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function seedTeam($turnier, $teamid, $Endrunde = false) {
		if (!($turnier->flags & TURNIER_TREE_RUNDEN) || $Endrunde) { #wenn es kein Turnier mit Vorrunden ist und wenn nicht gerade die Endrunde automatisch geseedet wird.
			// matches der ersten Runde holen
			$matches = Match::getMatchResultList($turnier->turnierid, 0);
			if ( $turnier->flags & TURNIER_TREE_RUNDEN && $Endrunde) { #Es kann bei einer Endrunde keine Freilose geben. Da im Turnier noch alle teams sind (auch die, die nicht in die Endrunde gekommen sind, würde es sonst eine negative Anzahl Freilose geben!
				$Freilose = 0;
			} else {
				$Freilose = $turnier->teamnum  - Team::getTeamCount($turnier->turnierid);	
			}
			// max 50% seeden
			#for ($i = 0; $i < ($turnier->teamnum /2); $i++) {
			//alles seeden - ist zum beispiel noeting, wenn ein halb gespieltes Turnier in zwei Turniere geteilt wird(z.B. WWCL Quali)
			for ($i = 0; $i < ($turnier->teamnum); $i++) {
	
				// seedpos suchen
				$seed = Jump::seed($i, $turnier->teamnum, $Freilose);
	
				// team1 seeden
				if (!($seed & 0x1)) {
					// wenn diese position noch nicht besetzt ist...
					if (!($matches[$seed>>1]['flags'] & MATCH_TEAM1_SEEDED)) {
						$match = Match::load($turnier->turnierid, $seed>>1);
						$match->team1 = $teamid;
						$match->flags |= MATCH_TEAM1_SEEDED;
						$match->save();
						return TS_SUCCES;
					}

				// team2 seeden
				} else {
					// wenn diese position noch nicht besetzt ist...
					if (!($matches[$seed>>1]['flags'] & MATCH_TEAM2_SEEDED)) {
						$match = Match::load($turnier->turnierid, $seed>>1);
						$match->team2 = $teamid;
						$match->flags |= MATCH_TEAM2_SEEDED;
						$match->save();
						return TS_SUCCES;
					}
				}
			}
			return TS_ERROR;
		} else { # bei Vorrunden-Turnier
			#welcher Platz wurde als letztes geseedet?
			$sql = "SELECT max(seedpos) AS seed FROM t_team WHERE turnierid = '{$turnier->turnierid}'";
			$res = DB::query($sql);
			$result = mysql_fetch_array($res);

			$next_seed_pos = intval($result['seed']) + 1;

			$sql = "UPDATE t_team
				SET seedpos = $next_seed_pos
				WHERE turnierid = '{$turnier->turnierid}'
				AND teamid = $teamid";
			$res = DB::query($sql);
			return TS_SUCCES;
		}
	}


	/**
	 * fuegt die Teams in zufaelliger reihenfolge in den Tree ein
	 * und fuellt mit freilosen auf
	 * @param Turnier $turnier Das Turnier
	 * @param mixed $teamlist Array mit Teamids
	 * @return int TS_SUCCES | TS_ERROR
	 */
	function fillSeeding($turnier, $teamlist) {
		// matches der ersten Runde holen
		$matches = Match::getMatchResultList($turnier->turnierid, 0);

		$Freilose = $turnier->teamnum  - Team::getTeamCount($turnier->turnierid);

		$getnew = true;
		// alles positionen durchlaufen
		for ($i = 0; $i < ($turnier->teamnum); $i++) {

			if ($getnew) {
				// zufällige ID raussuchen
				if (count($teamlist) > 0) {
					$rand = rand(0, count($teamlist) -1);
					$teamid = $teamlist[$rand];
					unset($teamlist[$rand]);
					sort($teamlist);
				} else {
					$teamid = T_FREILOS;
				}
				$getnew = false;
			}

			// seedpos suchen
			$seed = Jump::seed($i, $turnier->teamnum, $Freilose);

			// team1 seeden
			if (!($seed & 0x1)) {
				// wenn diese position noch nicht geseeded wurde
				if (!($matches[$seed>>1]['flags'] & MATCH_TEAM1_SEEDED)) {
					$match = Match::load($turnier->turnierid, $seed>>1);
					$match->team1 = $teamid;
					$match->save();
					$getnew = true;
				}

			// team2 seeden
			} else {
				// wenn diese position noch nicht geseeded wurde
				if (!($matches[$seed>>1]['flags'] & MATCH_TEAM2_SEEDED)) {
					$match = Match::load($turnier->turnierid, $seed>>1);
					$match->team2 = $teamid;
					$match->save();
					$getnew = true;
				}
			}
		}

		return TS_SUCCESS;
	}


	/**
	 * ueberprueft ob die erste Runde fertig zum spielen ist (alle Paarungen belegt)
	 * @param Turnier $turnier Turnier
	 * @return boolean true wenn alles OK
	 */
	function checkFirstRound($turnier) {
		// matches der ersten Runde holen
		$matches = Match::getMatchResultList($turnier->turnierid, 0);

		if (count($matches) == 0)
			return false;

		foreach ($matches as $matchid => $match) {
			if (($match['team1'] == 0) || ($match['team2'] == 0))
				return false;
		}
		return true;
	}


	/**
	 * startet das Turnier, indem alle erstrunden-paarungen auf ready gesetzt werden
	 * @param Turnier $turnier Turnier
	 * @return int TS_SUCCES | TS_ERROR
	 */
	function startupTourney($turnier) {
		// matches der ersten Runde holen
		$matches = Match::getMatchResultList($turnier->turnierid, 0);

		foreach ($matches as $matchid => $tmp) {
			$match = Match::load($turnier->turnierid, $matchid);
			if ($match->setReady() == TS_SUCCESS)
				$match->addMessage(-1, "Match ready");
			$match->save();

			if (!($turnier->flags & TURNIER_RUNDEN)) { # bei Rundenturnieren keinen weiter setzen
				TurnierSystem::forwardTeam($match);
				TurnierSystem::kickTeam($match);
			}
		}
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);
		return TS_SUCCESS;
	}


	/**
	 * setzt einen Spieler auf "spielbereit"
	 * @param Match $match Paarung
	 * @param int $userid User der spielbereit ist
	 * @return int TS_ERROR | TS_SUCCESS
	 */
	function matchSetReadyToPlay($match, $userid) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		$team1 = Team::load($match->turnierid, $match->team1);
		$team2 = Team::load($match->turnierid, $match->team2);

		if (false == $team1->isMember(COMPAT::currentID()) && false == $team2->isMember(COMPAT::currentID()) && false == $team1->isLeader(COMPAT::currentID()) && false == $team2->isLeader(COMPAT::currentID()))
			return TS_ERROR;

		if ((!($match->flags & MATCH_READY)) || ($match->flags & MATCH_COMPLETE))
			return TS_ERROR;

		// turnier muss laufen
		if (($turnier->status != TURNIER_STAT_RUNNING) && ($turnier->status != TURNIER_STAT_PAUSED))
			return TS_TOURNEY_NOT_RUNNING;

		$match->addMessage($userid, "Spieler ist spielbereit");
		
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);

		return TS_SUCCESS;
	}

	
	/**
	 * setzt ein Match komplett zurueck
	 * @param Match $match Paarung
	 * @param int $userid User der zuruecksetzen will
	 * @return int TS_ERROR | TS_SUCCESS
	 */
	function matchReset($match, $userid) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// aufrufender muss Admin sein
		if (!TurnierAdmin::isAdmin($userid, $turnier->turnierid))
			return TS_NOT_ADMIN;

		// turnier muss laufen
		if (($turnier->status != TURNIER_STAT_RUNNING) && ($turnier->status != TURNIER_STAT_PAUSED))
			return TS_TOURNEY_NOT_RUNNING;

		// wenn folgende Runden schon gespielt worden sind, darf hier nicht einfach resettet werden
		if (TurnierSystem::isNextRoundPlayed($match) && !($turnier->flags & TURNIER_RUNDEN)) {
			return TS_ERROR;
		}

		$match->flags &= (MATCH_TEAM1_SEEDED | MATCH_TEAM2_SEEDED);
		$match->flags |= (($match->round == 0) ? MATCH_READY : MATCH_UNKNOWN);

		$match->result1 = 0;
		$match->result2 = 0;
		$match->save();

		$match->addMessage($userid, "Admin hat Match zurückgesetzt");
		
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);

		return TS_SUCCESS;
	}


	/**
	 * Bestaetigt das Ergebnis einer Paarung
	 * @param Match $match Paarung
	 * @param int $userid User der bestaetigt
	 * @return int TS_ERROR | TS_SUCCESS
	 */
	function matchAcceptResult($match, $userid) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// die paarung darf noch nicht eingetragen sein
		if ($match->flags & MATCH_COMPLETE)
			return TS_DUP_RESULT;

		// die paarung muss spielbar sein
		if (!($match->flags & MATCH_READY))
			return TS_MATCH_NOT_READY;

		// gegner muss vorhanden sein
		if ($match->team1 <= 0 || $match->team2 <= 0)
			return TS_NO_SUCH_TEAM;

		// Als Leader muss das turnier laufen
		if (!(($team = $match->isLeader($userid)) & TS_ERROR)) {
			if ($turnier->status != TURNIER_STAT_RUNNING)
				return TS_TOURNEY_NOT_RUNNING;

		// Als Turnieradmin muss das Turnier laufen oder pausiert sein
		} else if (TurnierAdmin::isAdmin($userid, $turnier->turnierid)) {

			if (($turnier->status != TURNIER_STAT_RUNNING) && ($turnier->status != TURNIER_STAT_PAUSED))
				return TS_TOURNEY_NOT_RUNNING;

			$team = T_ADMIN;

		// Als unbekannter darf man schonmal gar nichts
		} else {
			return TS_NOT_LEADER;
		}

		// wenn kein doppelter eintrag, speichern
		if (!($match->userResult($team, $match->result1, $match->result2) & TS_ERROR)) {

			// event hinzufuegen
			if ($team == T_ADMIN) {
			    $match->addMessage($userid, "Ergebnis durch Admin bestätigt ({$match->result1}:{$match->result2})");
			} else {
			    $match->addMessage($userid, "Ergebnis bestätigt ({$match->result1}:{$match->result2})");
			}
			$match->save();

			if (!($turnier->flags & TURNIER_RUNDEN)) { #bei Rundenturnieren wird keiner weiter gesetzt.
				// gewinner weiterschieben
				TurnierSystem::forwardTeam($match);

				// kick verlierer
				TurnierSystem::kickTeam($match);
			} else {
				TurnierSystem::checkRoundComplete($match);
			}
			// turnierbaeume flushen
			TurnierSystem::flushCache($turnier->turnierid);
		}
		return TS_SUCCESS;
	}


	/**
	 * Traegt ein Ergbnis fuer eine Paarung ein
	 * @param Match $match Paarung
	 * @param int $userid User der Ergebnis eintraegt
	 * @param int $result1 Ergebnis Team1
	 * @param int $result2 Ergebnis Team2
	 * @return int TS_ERROR | TS_SUCCESS
	 */
	function matchEnterResult($match, $userid, $result1, $result2) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// ergebnisbereich: -999 - 999
		$result1 = ($result1 < 1000) ? $result1 : 999;
		$result1 = ($result1 > -1000) ? $result1 : -999;
		$result2 = ($result2 < 1000) ? $result2 : 999;
		$result2 = ($result2 > -1000) ? $result2 : -999;

		// will jemand nen draw eintragen?
		if ($result1 == $result2)
			return TS_ERROR;

		// ergebniss doppelt eingetragen
		if (($match->result1 == $result1) && ($match->result2 == $result2))
			return TS_SUCCESS;

		// gegner muss vorhanden sein
		if ($match->team1 <= 0 || $match->team2 <= 0)
			return TS_NO_SUCH_TEAM;

			// man muss leader sein
		if (TurnierAdmin::isAdmin($userid, $turnier->turnierid)) {
			if (($turnier->status != TURNIER_STAT_RUNNING) && ($turnier->status != TURNIER_STAT_PAUSED))
				return TS_TOURNEY_NOT_RUNNING;

			// wenn folgende Runden schon gespielt worden sind, dann darf kein neues ergebnis eingetragen werden
			if (TurnierSystem::isNextRoundPlayed($match) && !($turnier->flags & TURNIER_RUNDEN)) {
				// TODO: ergebnis kann nicht eingetragen werden, und nu?
				return TS_DUP_RESULT;
			}

			// ergebnis eintragen
			$match->adminResult($result1, $result2);

			// event hinzufuegen
			$match->addMessage($userid, "Admin hat Ergebnis eingetragen ({$result1}:{$result2})");

		} else if (!(($team = $match->isLeader($userid)) & TS_ERROR)) {
			// und das turnier muss laufen
			if ($turnier->status != TURNIER_STAT_RUNNING)
				return TS_TOURNEY_NOT_RUNNING;

			// die paarung darf noch nicht eingetragen sein
			if (!($match->flags & MATCH_READY))
				return TS_MATCH_NOT_READY;

			// die paarung ist schon spielbar
			if ($match->flags & MATCH_COMPLETE)
				return TS_DUP_RESULT;

			// ergebnis eintragen
			$match->userResult($team, $result1, $result2);

			// event hinzufuegen
			$match->addMessage($userid, "Neues Ergebnis eingetragen ({$result1}:{$result2})");

		} else {
			return TS_NOT_LEADER;
		}

		$match->save();

		if (!($turnier->flags & TURNIER_RUNDEN)) { #bei Rundenturnieren wird keiner weiter gesetzt.
			// gewinner weiterschieben
			TurnierSystem::forwardTeam($match);
	
			// kick verlierer
			TurnierSystem::kickTeam($match);
		} else {
			TurnierSystem::checkRoundComplete($match);
		}
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);

		return TS_SUCCESS;
	}


	/**
	 * Überprüft bei einem Rundenturnier, ob einen Runde beendet ist und schaltet die nächste frei, bzw setzt Turnierende
	 * @param Match $match Paarung
	 * @return int TS_ERROR | TS_SUCCESS
	 */
	function checkRoundComplete($match) {
		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		if (!($turnier->flags & TURNIER_RUNDEN)) # bei keinem Rundenturnieren -> Fehler
			return TS_ERROR;

		#Alle Spiele der aktuellen Runde durch?
		$matches = Match::getMatchResultList($turnier->turnierid, $match->round);

		foreach($matches as $match_temp) {;
			if ((!($match_temp[flags] & MATCH_COMPLETE)) && (!($match_temp[team1] == T_FREILOS)) && (!($match_temp[team2] == T_FREILOS)))
				return TS_SUCCESS; # sobald ein Match noch nicht fertig gespielt wurde, raus springen.
		}

		$teamnum = TEAM::getTeamCount($turnier->turnierid);

		$teamnum = ($teamnum%2!=0) ? $teamnum + 1 : $teamnum;

		$nextRound = $match->round +1; TurnierSystem::flushCache($turnier->turnierid);	

		# war das schon die letzte Runde? Dann Turnierende setzten
		if ($nextRound == $teamnum-1) { # -1 weil runden bei 0 anfangen
			$turnier->status = TURNIER_STAT_FINISHED;
			$turnier->save();
			#allen Spielern die Coins zurück, die nicht weiter kommen.(erst und zweitplatzierte).
			#die coins müssen im Hauptturnier zurück gegeben werden!
			if($turnier->flags & TURNIER_TREE_RUNDEN) { # handelt es sich um eine Vorrundenturnier?
				$ranking = TurnierRanking::getRankingRunden($turnier->turnierid);
				#da die teamid nicht Turnierübergreifend ist, wird hier die Teamid im Hauptturnier über den Teamnamen gesucht
				foreach($ranking as $rank_entry) {
					if ($rank_entry[pos] > 2){
						$sql = "SELECT teamid
							FROM t_team
							WHERE turnierid = '".$turnier->pturnierid."' AND 
							name = (SELECT name FROM t_team WHERE turnierid = '".$turnier->turnierid
							."' AND teamid = ".$rank_entry[teamid].")";
						$res = DB::query($sql);
						$teamid_hauptturnier = mysql_fetch_assoc($res);
						$team_tmp = TEAM::load($turnier->pturnierid, $teamid_hauptturnier[teamid]);
						$team_tmp->flags &= ~(TEAM_IS_ACTIVE | TEAM_USE_COINS);
						$team_tmp->save();
					}
				}
			} #nur bei Vorrundenturnier
		} else {
			// matches der nächsten Runde holen
			$matches = Match::getMatchResultList($turnier->turnierid, $nextRound);
		
			foreach ($matches as $matchid => $tmp) {
				$match = Match::load($turnier->turnierid, $matchid);
				if ($match->setReady() == TS_SUCCESS)
					$match->addMessage(-1, "Match ready");
				$match->save();
			}
		}
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);
		return TS_SUCCESS;

	}
	/**
	 * Gibt der Paarung ein zufaelliges Ergebnis
	 * @param Match $match Paarung
	 * @param int $userid User der Ergebnis eintraegt
	 * @return int TS_ERROR | TS_SUCCESS
	 */
	function matchRandomResult($match, $userid) {
		// gegner muss vorhanden sein
		if ($match->team1 <= 0 || $match->team2 <= 0)
			return TS_NO_SUCH_TEAM;

		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// aufrufender muss Admin sein
		if (!TurnierAdmin::isAdmin($userid, $turnier->turnierid))
			return TS_NOT_ADMIN;

		// turnier muss laufen
		if (($turnier->status != TURNIER_STAT_RUNNING) && ($turnier->status != TURNIER_STAT_PAUSED))
			return TS_TOURNEY_NOT_RUNNING;

		// die paarung darf noch nicht eingetragen sein
		if (!($match->flags & MATCH_READY))
			return TS_MATCH_NOT_READY;

		// wenn folgende Runden schon gespielt worden sind, dann darf kein neues ergebnis eingetragen werden
		if (TurnierSystem::isNextRoundPlayed($match)  && !($turnier->flags & TURNIER_RUNDEN)) {
			// TODO: ergebnis kann nicht eingetragen werden, und nu?
			return TS_DUP_RESULT;
		}

		$match->randomResult();
		$match->addMessage($userid, "Random Ergebnis eingetragen ({$match->result1}:{$match->result2})");
		$match->save();

		if (!($turnier->flags & TURNIER_RUNDEN)) { #bei Rundenturnieren wird keiner weiter gesetzt.
			// gewinner weiterschieben
			TurnierSystem::forwardTeam($match);
	
			// kick verlierer
			TurnierSystem::kickTeam($match);
		} else {
			TurnierSystem::checkRoundComplete($match);
		}
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);

		return TS_SUCCESS;
	}


	/**
	 * Vergibt gelbe/rote Karten
	 * @param Match $match Paarung in der die Karten vergeben werden
	 * @param int $userid User der Strafe vergibt
	 * @param int $flag flags die gesetzt werden sollen (-1 fuer alle flags loeschen)
	 * @return int TS_ERROR | TS_SUCCESS
	 * @todo was tun, wenn (de-)disqualifizierung nicht moeglich, weil spaetere Paarungen schon gespielt?
	 * @todo dedisqualifizierung implementieren
	 */
	function matchSetPenalty($match, $userid, $flag) {
		// darf nicht auf leere matches oder welche mit freilosen angewandt werden
		if (($match->team1 <= 0) || ($match->team2 <= 0))
			return TS_NO_SUCH_TEAM;

		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// aufrufender muss Admin sein
		else if (!TurnierAdmin::isAdmin($userid, $turnier->turnierid))
			return TS_NOT_ADMIN;

		// turnier muss laufen oder pausiert sein
		else if (($turnier->status != TURNIER_STAT_RUNNING) && ($turnier->status != TURNIER_STAT_PAUSED))
			return TS_TOURNEY_NOT_RUNNING;

		// das match muss zumindest ready sein, gespielt werden, oder gespielt worden sein
		else if (!($match->flags & (MATCH_READY | MATCH_PLAYING | MATCH_COMPLETE)))
			// TODO: richtigen errorcode?
			return TS_ERROR;

		if ($flag != -1) {
			$team1 = Team::load($turnier->turnierid, $match->team1);
			$team2 = Team::load($turnier->turnierid, $match->team2);
			if (!is_a($team1, 'Team') || !is_a($team2, 'Team'))
				return TS_NO_SUCH_TEAM;

			// nur diese flags sind erlaubt
			$flag &= (MATCH_TEAM1_GELB | MATCH_TEAM1_ROT | MATCH_TEAM2_GELB | MATCH_TEAM2_ROT);

			// es haben sich keine flags geaendert
			if (($match->flags | $flag) == $match->flags)
				return TS_DUP_PENALTY;

			// es duerfen nicht beide teams disqualifiziert werden
			// TODO: oder doch?
			if ((($match->flags | $flag) & (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT)) == (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT))
				return TS_DUP_PENALTY;

			// bei disqualifizierungen
			if (($match->flags | $flag) & (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT)) {

				// disqualifizierung erlaubt? (naechste runde schon gespielt?)
				// eigentlich muss nur der Path des disqualifizierten gecheckt werden
				if (TurnierSystem::isNextRoundPlayed($match)  && !($turnier->flags & TURNIER_RUNDEN)) {
					// TODO: kann nicht disqualifiziert werde, und nu?
					return TS_ERROR;
				}

				// match ist noch nicht complete
				if (!($match->flags & MATCH_COMPLETE)) {
					$match->flags &= ~(MATCH_READY | MATCH_PLAYING | MATCH_USER_RESULT | MATCH_RANDOM_RESULT);
					$match->flags |= (MATCH_COMPLETE | MATCH_ADMIN_RESULT);
				}
			}

			$match->flags |= $flag;
			$match->save();

			switch ($flag) {
				case MATCH_TEAM1_GELB:
					$match->addMessage($userid, "Gelbe Karte für '{$team1->name}'");
					break;

				case MATCH_TEAM1_ROT:
					$match->addMessage($userid, "'{$team1->name}' wurde disqualifiziert");

					if (!($turnier->flags & TURNIER_RUNDEN)) { #bei Rundenturnieren wird keiner weiter gesetzt.
						// kick disqualifiziertes team
						TurnierSystem::forwardTeam($match);
				
						// anderes team weiterfuehren
						TurnierSystem::kickTeam($match);
					} else {
						TurnierSystem::checkRoundComplete($match);
					}
					break;

				case MATCH_TEAM2_GELB:
					$match->addMessage($userid, "Gelbe Karte für '{$team2->name}'");
					break;

				case MATCH_TEAM2_ROT:
					$match->addMessage($userid, "'{$team2->name}' wurde disqualifiziert");

					if (!($turnier->flags & TURNIER_RUNDEN)) { #bei Rundenturnieren wird keiner weiter gesetzt.
						// kick disqualifiziertes team
						TurnierSystem::forwardTeam($match);
				
						// anderes team weiterfuehren
						TurnierSystem::kickTeam($match);
					} else {
						TurnierSystem::checkRoundComplete($match);
					}

					break;
			}

		} else {

			$flags = $match->flags;
			//$flags &= ~(MATCH_TEAM1_GELB | MATCH_TEAM1_ROT | MATCH_TEAM2_GELB | MATCH_TEAM2_ROT);
			// rote karten koennen nicht zurueckgenommen werden
			$flags &= ~(MATCH_TEAM1_GELB | MATCH_TEAM2_GELB);

			if ($flags == $match->flags)
				return TS_DUP_PENALTY;

			// disqualifizierung erlaubt? (naechste runde schon gespielt?)
			// eigentlich muss nur der Path des disqualifizierten gecheckt werden
/*
			if (TurnierSystem::isNextRoundPlayed($match)) {
				// TODO: kann nicht de-disqualifiziert werde, und nu?
				return TS_ERROR;
			}
*/
			$match->flags = $flags;
			$match->save();

			$match->addMessage(COMPAT::currentID(), "Alle Strafen wurden entfernt");

			// TODO: de-disqualifizierung? (gegener naechste runde fixen)
		}
		// turnierbaeume flushen
		TurnierSystem::flushCache($turnier->turnierid);

		return TS_SUCCESS;
	}


	/**
	 * Schiebt das Winning Team einer Paarung weiter.
	 * und setzt die Folgerunden ggf. auf Ready
	 * @param Match $match Paarung die soeben bestritten wurde
	 * @return int TS_ERROR | TS_SUCCESS
	 * @todo disq in nicht gespielten matches
	 */
	function forwardTeam($match) {
		// match ergebniss muss feststehen
		if (!($match->flags & MATCH_COMPLETE))
			return TS_SUCCESS;

		// zustaendiges Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// turnier zuende? (DE + overall finale || SE + (finale || semifinale))
		if ((($turnier->flags & TURNIER_DOUBLE) && ($match->matchid == ($turnier->teamnum-1))) ||
			(($turnier->flags & TURNIER_SINGLE) && (($match->matchid == ($turnier->teamnum-2)) || ($match->matchid == ($turnier->teamnum-1))))) {

			// beiden teams das active flag entziehen
			// dem verlierer wird evtl. noch in kickTeam() das coin flag entzogen
			// der gewinner verbraucht immer die coins weiter
			$team1 = Team::load($turnier->turnierid, $match->team1);
			if (is_a($team1, 'Team')) {
				$team1->flags &= ~(TEAM_IS_ACTIVE);
				$team1->save();
			}

			$team2 = Team::load($turnier->turnierid, $match->team2);
			if (is_a($team2, 'Team')) {
				$team2->flags &= ~(TEAM_IS_ACTIVE);
				$team2->save();
			}
			// DE ist nun fertig
			if ($turnier->flags & TURNIER_DOUBLE) {
				$turnier->status = TURNIER_STAT_FINISHED;
				$turnier->save();
				return TS_SUCCESS;

			// bei SE muessen beide fertig sein (jeweils anderen laden)
			} else if ($match->matchid == ($turnier->teamnum-2)) {
				$match2 = Match::load($match->turnierid, $turnier->teamnum -1);

			} else if ($match->matchid == ($turnier->teamnum-1)) {
				$match2 = Match::load($match->turnierid, $turnier->teamnum -2);
			}

			if (!is_a($match2, 'Match'))
				return TS_ERROR;

			// ok beide im SE sind fertig
			if ($match2->flags & MATCH_COMPLETE) {
				$turnier->status = TURNIER_STAT_FINISHED;
				$turnier->save();
			}
			return TS_SUCCESS;
		}

		// neue positionen von winner und loser holen
		$jump = new Jump();
		$jump->size = $turnier->teamnum /2;

		// winner Match laden
		$winnerMatch = Match::load($match->turnierid, $jump->getNewWinnerPos($match->matchid));
		if (!is_a($winnerMatch, 'Match'))
			return TS_ERROR;

		// gewinner feststellen (oder der, der nicht disqualifiziert wurde)
		if ($match->flags & MATCH_TEAM1_ROT)
			$winnerTeamid = $match->team2;

		else if ($match->flags & MATCH_TEAM2_ROT)
			$winnerTeamid = $match->team1;

		else
			$winnerTeamid = (($match->result1 > $match->result2) ? $match->team1 : $match->team2);

		// evtl. wurde diese paarung bearbeitet. also flags checken/forcen
		if ($winnerTeamid > 0) {
			$winnerTeam = Team::load($turnier->turnierid, $winnerTeamid);
			if (!is_a($winnerTeam, 'Team'))
				return TS_ERROR;
			$winnerTeam->flags |= (TEAM_USE_COINS | TEAM_IS_ACTIVE);
			$winnerTeam->save();
		}

		// In welchem Team laden wir nach dem Forward?
		switch ($jump->winnerTeam($match->matchid)) {

			case T_TEAM1: 	// wurde das Match schon gespielt?
					if ($winnerMatch->flags & MATCH_COMPLETE) {
						// wenn gegen ein Freilos gespielt wurde, kann es ersetzt werden
						if ($winnerMatch->team2 != T_FREILOS)
							return TS_ERROR;
					}
					$winnerMatch->team1 = $winnerTeamid;
					break;

			case T_TEAM2:	// wurde das Match schon gespielt?
					if ($winnerMatch->flags & MATCH_COMPLETE) {
						// wenn gegen ein Freilos gespielt wurde, kann es ersetzt werden
						if ($winnerMatch->team1 != T_FREILOS)
							return TS_ERROR;
					}
					$winnerMatch->team2 = $winnerTeamid;
					break;
		}

		// match von unser seite als ready markieren
		if ($winnerMatch->setReady() == TS_SUCCESS)
			$winnerMatch->addMessage(-1, "Match ready");
		$winnerMatch->save();

		// pruefen ob nachfolgende Paarungen auch schon fertig sind (freilose usw.) www.rekursiv.de
		TurnierSystem::forwardTeam($winnerMatch);

		// bei SE sind wir fertig
		if ($turnier->flags & TURNIER_SINGLE) {
			// hack: wir sind SE und unser Verlierer -> Spiel um Platz 3
			if ( ($match->matchid == ($turnier->teamnum -4)) || ($match->matchid == ($turnier->teamnum -3)) ) {
				$loserMatch = Match::load($match->turnierid, $turnier->teamnum -1);
				// evil hack
				$tmp = ($match->matchid & 0x01) ? T_TEAM2 : T_TEAM1;

			// wir sind SE und somit fertig (gibt keine Verlierer im SE)
			} else {
				return TS_SUCCESS;
			}

		} else {
			// bei DE loserbracket durchgehen
			$loserMatch = Match::load($match->turnierid, $jump->getNewLoserPos($match->matchid));
			$tmp = $jump->loserTeam($match->matchid);
		}

		if (!is_a($loserMatch, 'Match'))
			return TS_ERROR;

		// verlierer feststellen (oder bei disqualifizierung ein freilos einfuegen)
		if ($match->flags & (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT))
			$loserTeamid = T_FREILOS;
		else
			$loserTeamid = (($match->result1 > $match->result2) ? $match->team2 : $match->team1);

		// evtl. wurde diese paarung bearbeitet. also flags checken/forcen
		// TODO: check ob das team nicht evtl. gerade rausfliegt
		if ($loserTeamid > 0) {
			$loserTeam = Team::load($turnier->turnierid, $loserTeamid);
			if (!is_a($loserTeam, 'Team'))
				return TS_ERROR;
			$loserTeam->flags |= (TEAM_USE_COINS | TEAM_IS_ACTIVE);
			$loserTeam->save();
		}

		// matchid eintragen
		switch ($tmp) {
			case T_TEAM1: 	// wurde das Match schon gespielt?
					if ($loserMatch->flags & MATCH_COMPLETE) {
						// wenn gegen ein Freilos gespielt wurde, kann es ersetzt werden
						if ($loserMatch->team2 != T_FREILOS)
							return TS_ERROR;
					}
					$loserMatch->team1 = $loserTeamid;
					break;

			case T_TEAM2:	// wurde das Match schon gespielt?
					if ($loserMatch->flags & MATCH_COMPLETE) {
						// wenn gegen ein Freilos gespielt wurde, kann es ersetzt werden
						if ($loserMatch->team1 != T_FREILOS)
							return TS_ERROR;
					}
					$loserMatch->team2 = $loserTeamid;
					break;
		}

		// match von unser seite als ready markieren
		if ($loserMatch->setReady() == TS_SUCCESS)
			$loserMatch->addMessage(-1, "Match ready");
		$loserMatch->save();

		// pruefen ob nachfolgende Paarungen auch schon fertig sind (freilose usw.) www.rekursiv.de
		TurnierSystem::forwardTeam($loserMatch);

		return TS_SUCCESS;
	}


	/**
	 * kickt ein Team aus dem Turnier.
	 * Entweder das Verlierer Team oder das Team das disqualifiziert wurde
	 * Die Teamflags werde gesetzt (coins rückgabe & nimmt am turnier teil).
	 * @param Match $match Paarung in der der Verlierer steckt
	 * @return int TS_ERROR | TS_SUCCESS
	 * @todo disq in nicht gespielten matches
	 */
	function kickTeam($match) {
		// das match muss natuerlich gespielt sein
		if (!($match->flags & MATCH_COMPLETE))
			return TS_SUCCESS;

		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		// wurde jemand disqualifiziert? dann dieses Team holen
		if ($match->flags & MATCH_TEAM1_ROT) {
			$team = Team::load($turnier->turnierid, $match->team1);

		} else if ($match->flags & MATCH_TEAM2_ROT) {
			$team = Team::load($turnier->turnierid, $match->team2);

		// sonst das Verliererteam
		} else {
			// im Winnerbracket eines DE Turniers fliegt noch kein verlierer raus
			if (($turnier->flags & TURNIER_DOUBLE) && ($match->matchid < ($turnier->teamnum -1)))
				return TS_SUCCESS;

			// bei SE in den matches deren Verlierer zu Spiel um Platz3 gehen, fliegt auch keiner
			if (($turnier->flags & TURNIER_SINGLE) && (($match->matchid == ($turnier->teamnum -4)) || ($match->matchid == ($turnier->teamnum -3))))
				return TS_SUCCESS;

			$team = Team::load($turnier->turnierid, ($match->result1 > $match->result2) ? $match->team2 : $match->team1);
		}

		// gueltiges Team?
		if (!is_a($team, 'Team'))
			return TS_ERROR;

		// event hinzufuegen
		$match->addMessage(-1, "Team '{$team->name}' scheidet aus dem Turnier aus.");

		// bei disqualifizierung gibts keine coins zurueck
		if ($match->flags & (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT)) {
			$team->flags &= ~(TEAM_IS_ACTIVE);

		// coinrueckgabe im winnerbracket
		} else if (($match->round < 256) && ($turnier->coinsback >= ($match->round & 0xFF))) {
			$team->flags &= ~(TEAM_IS_ACTIVE | TEAM_USE_COINS);

		// coinrueckgabe in loserbracket
		} else if (($match->round > 256) && (($turnier->coinsback *2) >= ($match->round & 0xFF))) {
			$team->flags &= ~(TEAM_IS_ACTIVE | TEAM_USE_COINS);

		// keine coins zurueckangeben
		} else {
			$team->flags &= ~(TEAM_IS_ACTIVE);
		}

		$team->save();

		return TS_SUCCESS;
	}


	/**
	 * Ueberprueft die jeweils folgenden Matches.
	 * Check ob das jetztige Match seine Teams anders gewinnen oder disqualifizieren lassen darf,
	 * ohne das alles explodiert.
	 * @param Match $match Jetztiges Match
	 * @return boolean true, Wenn die folgenden Matches gespielt werden, oder gespielt wurden
	 & @todo spiel um platz3 wird nicht beachtet
	 */
	function isNextRoundPlayed($match) {
		// passendes Turnier laden
		$turnier = Turnier::load($match->turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;	//TODO: !boolean

		// neue positionen von winner und loser holen
		$jump = new Jump();
		$jump->size = $turnier->teamnum /2;


		// hack für platz3 spiel
		if ($turnier->flags & TURNIER_SINGLE) {
			// hack: wir sind SE und unser Verlierer -> Spiel um Platz 3
			if ( ($match->matchid == ($turnier->teamnum -2)) || ($match->matchid == ($turnier->teamnum -1)) )
				return false;
		}


		$winnerMatch = Match::load($match->turnierid, $jump->getNewWinnerPos($match->matchid));
		if (!is_a($winnerMatch, 'Match'))
			return false;		// TODO: ist das richtig? (jump = -1)

		// wurde match schon gespielt?
		if ($winnerMatch->flags & MATCH_COMPLETE) {
			// und es waren keine freilose?
			if ($winnerMatch->team1 > 0 && $winnerMatch->team2 > 0)
				return true;

			// es war min. ein Freilos beteiligt. also tiefer graben
			if (TurnierSystem::isNextRoundPlayed($winnerMatch))
				return true;
		}

		// loser Match laden
		// bei SE sind wir (fast immer) fertig
		if ($turnier->flags & TURNIER_SINGLE) {
			// hack: wir sind SE und unser Verlierer -> Spiel um Platz 3
			if ( ($match->matchid == ($turnier->teamnum -4)) || ($match->matchid == ($turnier->teamnum -3)) ) {
				$loserMatch = Match::load($match->turnierid, $turnier->teamnum -1);

			// wir sind SE und somit fertig (gibt keine Verlierer im SE)
			} else {
				return false;
			}

		} else {
			// bei DE loserbracket durchgehen
			$loserMatch = Match::load($match->turnierid, $jump->getNewLoserPos($match->matchid));
		}

		if (!is_a($loserMatch, 'Match'))
			return false;		// TODO: ist das richtig? (jump = -1)

		// wurde match schon gespielt?
		if ($loserMatch->flags & MATCH_COMPLETE) {
			// und es waren keine freilose?
			if ($loserMatch->team1 > 0 && $loserMatch->team2 > 0)
				return true;

			// es war min. ein Freilos beteiligt. also tiefer graben
			if (TurnierSystem::isNextRoundPlayed($loserMatch))
				return true;
		}

		// weder im Winner noch im Loser weg wurde die naechste(n) runde(n) gespielt
		return false;
	}

	/*
	 * erzeugt ein array mit allen "wichtigen" infos fuer die coverage-uploads
	 * @param int $turnierid Turnier
	 * @return String serialisiertes Array
	 * @static
	 */
	function generateCoverage($turnierid) {
		$turnier = Turnier::load($turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		$retval = array();
		$turnier->regeln = "";
		$retval['turnier'] = $turnier;
		$retval['rounds'] = Round::getRoundList($turnierid);
		$retval['teams'] = Team::getTeamNameList($turnierid);
		$retval['matches'] = Match::getMatchResultList($turnierid);
		return serialize($retval);
	}

	/**
	 * loescht die smarty caches
	 * @param int $turnierid Turnier dess caches geflushed werden sollen
	 */
	function flushCache($turnierid) {
		$turnier = Turnier::load($turnierid);
		$mandantid = COMPAT::getMandantFromParty($turnier->partyid);

		// dirty dirty... (mandant vom admin ist "admin")
		$smarty = new PelasSmarty('turnier');
		$smarty->templateSubDir = $mandantid;
		$smarty->assembleCompileId();

		$smarty->clear_cache('turnier_tree.tpl', $turnierid);
		$smarty->clear_cache('turnier_table.tpl', $turnierid);
		$smarty->clear_cache('turnier_ranking.tpl', $turnierid);
	}

	/**
	 * erzeugt ein array mit Seedpositionen für die Teams. NUR Turniere mit Vorrunden
	 * @param int $turnierid Turnier
	 * @return array Team ID's mit Seedposition
	 * @static
	 */
	function getSeedList($turnier) {
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		if (!($turnier->flags & TURNIER_TREE_RUNDEN))
			return TS_ERROR;

		$sql = "SELECT teamid, seedpos
			FROM t_team
			WHERE turnierid = '{$turnier->turnierid}'";
		$res = DB::query($sql);
		$seed = array();

		while ($row = mysql_fetch_assoc($res)) {
			if($row['seedpos'] != 0)
				$seed[$row[teamid]] = $row['seedpos'];
		}
		#var_dump($seed); die($sql);
		return $seed;

	}
	/**
	 * ueberprueft ob schon vorrunden angelegt wurden
	 * @param Turnier $turnier Turnier für das Vorrunden erzeugt werden sollen
	 * @return 1 = vorrunden vorganden, sosnt 0
	 * @static
	 */
	function existSubtourneys($turnier) {
		$sql = "SELECT count(turnierid) anz
			FROM t_turnier
			WHERE pturnierid = '{$turnier->turnierid}'";
		$res = DB::query($sql);
		$row = mysql_fetch_array($res);
		return ($row['anz'] == 0) ? 0 : 1;
	}

	/**
	 * Gibt einen Errorcode als String zurueck
	 * @param int $err Errorcode
	 * @return string Errorstring
	 * @todo irgendwie von smarty machen lassen, gehoert nicht in code
	 */
	function getErrStr($err) {
		static $cache = array(
				TS_SUCCESS => "TS_SUCCESS",
				TS_ERROR => "TS_ERROR",
				TS_REG_CLOSED => "Anmeldung derzeit nicht möglich.",
				TS_NOT_LOGGED_IN => "User ist nicht eingelogged.",
				TS_NOT_PAYED => "User hat nicht bezahlt.",
				TS_ALREADY_REG => "User ist schon zu diesem Turnier angemeldet.",
				TS_TOO_FEW_COINS => "User hat zuwenig Coins für diese Turnier.",
				TS_TOO_MANY_TEAMS => "Turnier hat maximale Teamanzahl erreicht.",
				TS_NOT_LEADER => "User ist nicht der Leader dieses Teams.",
				TS_TEAM_FULL => "Das Team ist bereits voll.",
				TS_NOT_QUEUED => "User will nicht dem Team beitreten.",
				TS_NOT_MEMBER => "User gehört nicht zum Team.",
				TS_IS_LEADER => "Teamchef kann nicht entfernt werden.",
				TS_NO_SUCH_TEAM => "Unbekanntes Team.",
				TS_DUP_TEAM => "Teamname ist bereits vergeben.",
				TS_DUP_LEAGUE_ID => "Diese Liga ID ist bereits zu diesem Turnier angemeldet.",
				TS_NOT_ADMIN => "User ist kein Turnieradmin für dieses Turnier.",
				TS_DUP_PENALTY => "Strafe wurde bereits vergeben.",
				TS_DUP_RESULT => "Ergebnis wurde bereits eingetragen.",
				TS_MATCH_NOT_READY => "Paarung ist derzeit nicht Spielbar.",
				TS_TOURNEY_NOT_RUNNING => "Das Turnier läuft derzeit nicht.",
				TS_TOURNEY_RUNNING => "Das Turnier läuft derzeit.",
				TS_TEAMNAME_EMPTY => "Es muss ein Teamname angegeben werden.",
				TS_RESOLVE_IDS => "Bitte User ID eingeben und 'Resolve IDs' vor 'Create Team' waehlen!");

		return isset($cache[$err]) ? $cache[$err] : "Unbekannter Fehler '{$err}'";
	}
}
?>
