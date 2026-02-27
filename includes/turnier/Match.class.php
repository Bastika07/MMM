<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once("turnier/t_constants.php");
require_once("turnier/TurnierAdmin.class.php");
require_once("turnier/Team.class.php");

/**
 * Match bezogene Funktionen
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/15 ore - initial version
 * @version 2004/07/17 ore - setReady() / testing
 * @version 2004/07/18 ore - caching
 * @todo unterschiedliche Events?
 */
class Match {
	/**
	 * @var int TurnierID (primary key)
	 */
	var $turnierid = 0;
	/**
	 * @var int MatchID (primary key)
	 */
	var $matchid = 0;
	/**
	 * @var int rundennummer
	 */
	var $round = 0;
	/**
	 * @var int flags
	 */
	var $flags = 0;
	/**
	 * @var int TeamID
	 */
	var $team1 = 0;
	/**
	 * @var int TeamID
	 */
	var $team2 = 0;
	/**
	 * @var int Ergebnis Team1
	 */
	var $result1 = 0;
	/**
	 * @var int Ergebnis Team2
	 */
	var $result2 = 0;
	/**
	 * @var int ThreadID im Forum
	 */
	var $threadid = -1;


	/**
	 * Marrkiert das Match als spielbar.
	 * Beide Teams vorhanden, Freilose werden eliminiert
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function setReady() {
		/* keine teams eingetragen */
		if ($this->team1 == 0 || $this->team2 == 0) {
			return TS_ERROR;
		}

		/* match ist schon schon complete */
		if ($this->flags & MATCH_COMPLETE)
			return TS_ERROR;

		/* team1 ist ein freilos -> team2 kommt weiter */
		if ($this->team1 == T_FREILOS && $this->team2 != 0) {
			$this->flags &= ~(MATCH_READY | MATCH_PLAYING);
			$this->flags |= MATCH_COMPLETE;
			$this->result1 = 0;
			$this->result2 = 1;
			return TS_SUCCESS;
		}

		/* team2 ist ein freilos -> team1 kommt weiter */
		if ($this->team1 != 0 && $this->team2 == T_FREILOS) {
			$this->flags &= ~(MATCH_READY | MATCH_PLAYING);
			$this->flags |= MATCH_COMPLETE;
			$this->result1 = 1;
			$this->result2 = 0;
			return TS_SUCCESS;
		}

		/* doppelt muss nicht sein */
		if ($this->flags & MATCH_READY)
			return TS_ERROR;

		/* beide Teams vorhanden, keine Freilose -> set ready */
		$this->flags |= MATCH_READY;
		return TS_SUCCESS;
	}


	/**
	 * prueft ob User Leader einer der beiden Teams ist
	 * @param int $userid vermeintlicher Leader
	 * @return int T_TEAM1 | T_TEAM2 | TS_ERROR
	 */
	function isLeader($userid) {
		$team1 = Team::load($this->turnierid, $this->team1);
		if (!is_a($team1, 'Team'))
			return TS_ERROR;

		if ($team1->isLeader($userid))
			return T_TEAM1;

		$team2 = Team::load($this->turnierid, $this->team2);
		if (!is_a($team2, 'Team'))
			return TS_ERROR;

		if ($team2->isLeader($userid))
			return T_TEAM2;

		return TS_ERROR;
	}


	/**
	 * setzt die Paarung auf playing
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function startMatch() {
		/* wenn spiel bereits laeuft */
		if ($this->flags & (MATCH_PLAYING | MATCH_COMPLETE))
			return TS_SUCCESS;

		/* wenn spiel noch nicht spielbar ist */
		if (!($this->flags & MATCH_READY))
			return TS_ERROR;

		$this->flags &= ~MATCH_READY;
		$this->flags |= MATCH_PLAYING;
		return TS_SUCCESS;
	}


	/**
	 * Userergebnis eintragen und/oder bestaetigen
	 * @param int $team T_TEAM1 | T_TEAM2 | T_ADMIN (bestaetigt immer)
	 * @param int $result1 Ergebnis Team1
	 * @param int $result2 Ergebnis Team2
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function userResult($team, $result1, $result2) {
		if ($result1 == $result2)
			return TS_ERROR;

		// neues/anderes ergebnis, zustimmungen loeschen
		if (($this->result1 != $result1) || ($this->result2 != $result2)) {
			$this->result1 = $result1;
			$this->result2 = $result2;
			$this->flags &= ~(MATCH_TEAM1_ACCEPT | MATCH_TEAM2_ACCEPT);
		}

		// dem ergebnis zustimmen
		switch ($team) {
			case T_TEAM1:	if ($this->flags & MATCH_TEAM1_ACCEPT)
						return TS_DUP_RESULT;
					$this->flags |= MATCH_TEAM1_ACCEPT;
					break;

			case T_TEAM2:	if ($this->flags & MATCH_TEAM2_ACCEPT)
						return TS_DUP_RESULT;
					$this->flags |= MATCH_TEAM2_ACCEPT;
					break;

			case T_ADMIN:	$this->flags |= (MATCH_TEAM1_ACCEPT | MATCH_TEAM2_ACCEPT);
					break;

			default:	return TS_ERROR;
					break;
		}

		/* wenn beide zugestimmt haben, paarung schliessen */
		if (($this->flags & (MATCH_TEAM1_ACCEPT | MATCH_TEAM2_ACCEPT)) == (MATCH_TEAM1_ACCEPT | MATCH_TEAM2_ACCEPT)) {
			$this->flags &= ~(MATCH_READY | MATCH_PLAYING | MATCH_ADMIN_RESULT | MATCH_RANDOM_RESULT);
			$this->flags |= (MATCH_COMPLETE | MATCH_USER_RESULT);
		}
		return TS_SUCCESS;
	}


	/**
	 * Admin Ergebnis eintragen
	 * @param int $result1 Ergebnis Team1
	 * @param int $result2 Ergebnis Team2
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function adminResult($result1, $result2) {
		if ($result1 == $result2)
			return TS_ERROR;

		$this->result1 = $result1;
		$this->result2 = $result2;

		/* als ADMIN_RESULT speichern */
		$this->flags &= ~(MATCH_READY | MATCH_PLAYING | MATCH_USER_RESULT | MATCH_RANDOM_RESULT);
		$this->flags |= (MATCH_COMPLETE | MATCH_ADMIN_RESULT);
		return TS_SUCCESS;
	}


	/**
	 * Zufï¿½lliges Ergebnis eintragen.
	 * Freilose verlieren immer
	 * @return int TS_SUCCESS
	 */
	function randomResult() {
		// freilose verlieren immer
		if ($this->team1 > 0 && $this->team2 == T_FREILOS) {
			$this->result1 = 1;
			$this->result2 = 0;

		// freilose verlieren immer
		} else if ($this->team1 == T_FREILOS && $this->team2 > 0) {
			$this->result1 = 0;
			$this->result2 = 1;

		} else if (rand(1, 100) <= 50) {
			$this->result1 = 1;
			$this->result2 = 0;

		} else {
			$this->result1 = 0;
			$this->result2 = 1;
		}

		/* als RANDOM_RESULT speichern */
		$this->flags &= ~(MATCH_READY | MATCH_PLAYING | MATCH_USER_RESULT | MATCH_ADMIN_RESULT);
		$this->flags |= (MATCH_COMPLETE | MATCH_RANDOM_RESULT);
		return TS_SUCCESS;
	}


	/**
	 * laed ein MatchObjekt aus der Datenbank.
	 * das Objekt wird gecached.
	 * @param int $turnierid Turnier
	 * @param int $matchid Match
	 * @param boolean $flush True um ein Objekt aus dem cache zu loeschen
	 * @return mixed Match | TS_ERROR
	 * @static
	 */
	function load($turnierid, $matchid, $flush = FALSE) {
		static $cache = array();

		if (!isset($turnierid) || !isset($matchid) || $matchid == -1)
			return TS_ERROR;

		/* eintrag aus cache loeschen */
		if ($flush && isset($cache[$turnierid][$matchid])) {
			unset($cache[$turnierid][$matchid]);
			return TS_SUCCESS;
		}

		/* eintrag aus cache holen */
		if (isset($cache[$turnierid][$matchid])) {
			return $cache[$turnierid][$matchid];
		}

		$retval = new Match();

		$sql = "SELECT * FROM t_match
			WHERE turnierid = '{$turnierid}'
			AND matchid = '{$matchid}'";
		$res = DB::query($sql);
		if ($res->num_rows < 1)
			return TS_ERROR;

		$row = $res->fetch_assoc();

		$retval->turnierid	= (int)$row['turnierid'];
		$retval->matchid	= (int)$row['matchid'];
		$retval->round		= (int)$row['round'];
		$retval->flags		= (int)$row['flags'];
		$retval->team1		= (int)$row['team1'];
		$retval->team2		= (int)$row['team2'];
		$retval->result1	= (int)$row['result1'];
		$retval->result2	= (int)$row['result2'];
		$retval->threadid	= (int)$row['threadid'];

		/* insert in cache */
		if (!isset($cache[$turnierid]))
			$cache[$turnierid] = array();

		$cache[$turnierid][$matchid] = $retval;

		return $retval;
	}

	/**
	 * laed ein MatchObjekt aus der Datenbank.
	 * @param int $turnierid Turnier
	 * @param int $matchid Match
	 * @return mixed Match | TS_ERROR
	 * @static
	 */
	function loadByThreadid($threadid) {
		if (!isset($threadid))
			return TS_ERROR;

		$retval = new Match();

		$sql = "SELECT * FROM t_match
			WHERE threadid = '{$threadid}'";
		$res = DB::query($sql);
		if ($res->num_rows < 1)
			return TS_ERROR;

		$row = $res->fetch_assoc();

		$retval->turnierid	= (int)$row['turnierid'];
		$retval->matchid	= (int)$row['matchid'];
		$retval->round		= (int)$row['round'];
		$retval->flags		= (int)$row['flags'];
		$retval->team1		= (int)$row['team1'];
		$retval->team2		= (int)$row['team2'];
		$retval->result1	= (int)$row['result1'];
		$retval->result2	= (int)$row['result2'];
		$retval->threadid	= (int)$row['threadid'];

		return $retval;
	}


	/**
	 * Sichert ein Match-Objekt in der Datenbank.
	 * flushed cache von load()
	 * @return int TS_SUCCESS
	 */
	function save() {
		$sql = "UPDATE t_match SET
				round = '{$this->round}',
				flags = '{$this->flags}',
				team1 = '{$this->team1}',
				team2 = '{$this->team2}',
				result1 = '{$this->result1}',
				result2 = '{$this->result2}',
				threadid = '{$this->threadid}',
				wann_geaendert = '".time()."',
				wer_geaendert = '".COMPAT::currentID()."'
			WHERE
				turnierid = '{$this->turnierid}' AND
				matchid = '{$this->matchid}'";
		if (!DB::query($sql))
			return TS_ERROR;

		/* flush cache */
		Match::load($this->turnierid, $this->matchid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * Erzeugt ein neues Objektabbild in der Datenbank.
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function create() {
		if (!isset($this->turnierid) || !isset($this->matchid))
			return TS_ERROR;

		$sql = "INSERT INTO t_match SET
				turnierid = '{$this->turnierid}',
				matchid = '{$this->matchid}',
				round = '{$this->round}',
				flags = '{$this->flags}',
				team1 = '{$this->team1}',
				team2 = '{$this->team2}',
				result1 = '{$this->result1}',
				result2 = '{$this->result2}',
				threadid = '{$this->threadid}',
				wann_angelegt = '".time()."',
				wer_angelegt = '".COMPAT::currentID()."'";
		if (!DB::query($sql)) {
			return TS_ERROR;
		}

		/* flush cache */
		Match::load($this->turnierid, $this->matchid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * loescht ein Match aus dem Turnier
	 * @param int $turnierid Turnier
	 * @param int $matchid Match (optional)
	 * @static
	 */
	function delete($turnierid, $matchid = -1) {
		$sql = "DELETE FROM t_match WHERE turnierid = '{$turnierid}'";

		if ($matchid != -1)
			$sql .= " AND matchid = '{$matchid}'";

		DB::query($sql);
		return TS_SUCCESS;
	}


	/**
	 * Holt eine Liste von Matches mit Team-IDs und Ergebnissen.
	 * @param int $turnierid Turnier fuer das die Ergebnisse geholt werden sollen
	 * @param int $roundid Runde fuer die die Ergebnisse geholt werden sollen (default: alle)
	 * @return mixed Ergebnissarray
	 * @static
	 */
	function getMatchResultList($turnierid, $roundid = -1) {
		$sql = "SELECT matchid, flags, team1, team2, result1, result2, round
			FROM t_match
			WHERE turnierid = '{$turnierid}'".
			(($roundid != -1) ? " AND round = '{$roundid}'" : "")."
			ORDER BY matchid";

		$res = DB::query($sql);

		$retval = array();
		while ($row = $res->fetch_assoc()) {
			$retval[$row['matchid']] = $row;
			$retval[$row['matchid']]['result'] = Match::getResult($row['team1'], $row['team2'], $row['result1'], $row['result2'], $row['flags']);
		}

		return $retval;
	}


	/**
	 * Holt eine Liste von Matches fuer ein Team mit Ergebnissen
	 * @param int $turnierid Turnier fuer das die Ergebnisse geholt werden sollen
	 * @param int $teamid Team fuer das die Ergebnisse geholt werden sollen
	 * @return mixed Ergebnissarray
	 * @static
	 */
	function getTeamResultList($turnierid, $teamid) {
		$sql = "SELECT m.matchid, m.flags, m.team1, t1.name as team1name, t2.name as team2name, m.team2, m.result1, m.result2
			FROM t_match m
			LEFT JOIN t_team t1 ON m.team1 = t1.teamid AND m.turnierid = t1.turnierid
			LEFT JOIN t_team t2 ON m.team2 = t2.teamid AND m.turnierid = t2.turnierid
			WHERE m.turnierid = '{$turnierid}'
			AND (m.team1 = '{$teamid}' OR m.team2 = '{$teamid}')
			ORDER BY m.round";
		$res = DB::query($sql);

		$retval = array();
		while ($row = $res->fetch_assoc()) {
			$retval[$row['matchid']] = $row;
			$retval[$row['matchid']]['result'] = Match::getResult($row['team1'], $row['team2'], $row['result1'], $row['result2'], $row['flags']);
		}

		return $retval;
	}


	/**
	 * Setzt die Matches einer Turnierrunde/Turniers zurueck
	 * @param int $turnierid Turnier
	 * @param int $roundid Runde (default: alle)
	 * @return int TS_SUCCESS
	 * @static
	 */
	function resetMatches($turnierid, $roundid = -1) {
		$sql = "UPDATE t_match SET flags = 0, team1 = 0, team2 = 0,
			result1 = 0, result2 = 0
			WHERE turnierid = '{$turnierid}'".
			(($roundid != -1) ? " AND round = '{$roundid}'" : "");
		DB::query($sql);
		return TS_SUCCESS;
	}


	/**
	 * fuegt dem Match eine Message hinzu
	 * @param int $userid User der die Message eintraegt
	 * @param string $msg Nachricht
	 * @return int TS_SUCCESS
	 */
	function addMessage($userid, $msg) {
		return $this->createEvent($userid, $msg, time(), EVENT_MSG);
	}


	/**
	 * fuegt dem Match eine Message hinzu
	 * @param int $userid User der die Message eintraegt
	 * @param string $msg Nachricht
	 * @param int $time zeitpunkt des events
	 * @param int $flags flags des events
	 * @param int $eventid Eventid (optional fuer import)
	 * @return int TS_SUCCESS
	 * @static
	 */
	function createEvent($userid, $msg, $time, $flags, $eventid = -1) {
		if ($eventid == -1) {
			$sql = "SELECT MAX(eventid) as lastid FROM t_events
				WHERE turnierid = '{$this->turnierid}'
				AND matchid = '{$this->matchid}'";
			$res = DB::query($sql);
			$row = $res->fetch_assoc();
			$eventid = (isset($row['lastid'])) ? ($row['lastid'] +1) : 1;
		}

		$sql = "INSERT INTO t_events SET
			eventid = '{$eventid}',
			turnierid = '{$this->turnierid}',
			matchid = '{$this->matchid}',
			time = '".$time."',
			userid = '{$userid}',
			flags = '".$flags."',
			text = '".DB::$link->real_escape_string($msg)."'";

		DB::query($sql);
		return TS_SUCCESS;
	}


	/**
	 * holt alle events zu diesem match
	 * @return mixed array mit Events
	 */
	function getEvents() {
		$sql = "SELECT e.time, e.eventid, e.userid, u.LOGIN as login, e.flags, e.text
			FROM t_events e
			LEFT JOIN USER u ON e.userid = u.USERID
			WHERE turnierid = '{$this->turnierid}'
			AND matchid = '{$this->matchid}'
			ORDER BY e.time, e.eventid";
		$res = DB::query($sql);

		$retval = array();
		while ($row = $res->fetch_assoc())
			$retval[$row['eventid']] = $row;

		return $retval;
	}


	/**
	 * verbirgt einen Event aus dem Turnier
	 * @param int $turnierid Turnier
	 * @param int $eventid Event
	 * @return int TS_SUCCESS
	 * @static
	 */
	function hideEvent($turnierid, $eventid) {
		$sql = "UPDATE t_events
			SET flags = (flags | '".EVENT_HIDDEN."')
			WHERE turnierid = '{$this->turnierid}'
			AND matchid = '{$this->matchid}'
			AND eventid = '{$eventid}'";
		DB::query($sql);
		return TS_SUCCESS;
	}


	/**
	 * loescht alle Events die zu einem Turnier gehoeren
	 * @param int $turnierid Turnier
	 * @param int $matchid Match (optional)
	 * @param int $eventid Event (optional)
	 * @return int TS_SUCCESS
	 * @static
	 */
	function deleteEvents($turnierid, $matchid = -1, $eventid = -1) {
		$sql = "DELETE FROM t_events
			WHERE turnierid = '{$turnierid}'";

		if ($matchid != -1)
			$sql .= " AND matchid = '{$matchid}'";

		if ($eventid != -1)
			$sql .= " AND eventid = '{$eventid}'";

		DB::query($sql);
		return TS_SUCCESS;
	}


	/**
	 * gibt den Gewinner zurueck
	 * @param int $team1 Team1
	 * @param int $team2 Team2
	 * @param int $result1 Ergebnis Team1
	 * @param int $result2 Ergebnis Team2
	 * @param int $flags Match Flags
	 * @return int T_TEAM1 | T_TEAM2 | TS_ERROR
	 * @static
	 */
	function getResult($team1, $team2, $result1, $result2, $flags) {
		// match muss gespielt sein
		if (!($flags & MATCH_COMPLETE))
			return TS_ERROR;

		// zwei disqualifizierungen
		else if (($flags & (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT)) == (MATCH_TEAM1_ROT | MATCH_TEAM2_ROT))
			return T_ERROR;
		
		// team1 war ein freilos oder wurde disqualifiziert
		else if (($team1 == T_FREILOS) || ($flags & MATCH_TEAM1_ROT))
			return T_TEAM2;

		// team2 war ein freilos oder wurde disqualifiziert
		else if (($team2 == T_FREILOS) || ($flags & MATCH_TEAM2_ROT))
			return T_TEAM1;

		// team1 war besser als team2
		else if ($result1 > $result2)
			return T_TEAM1;

		// team2 war besser als team1
		else if ($result2 > $result1)
			return T_TEAM2;

		// gleichstand
		return T_DRAW;
	}
}
?>
