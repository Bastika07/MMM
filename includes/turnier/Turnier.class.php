<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once ("turnier/t_constants.php");

/**
 * Turnier bezogene Funktionen
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/14 ore - initial version
 * @version 2004/07/18 ore - caching, typsicherheit
 * @todo regeln, icons und ircchannel beim load immer holen?
 */
class Turnier {
	/**
	 * @var int TurnierID (primary key)
	 */
	var $turnierid = 0;
	/**
	 * @var int PartyID
	 */
	var $partyid = 0;
	/**
	 * @var int Turniergruppe (zum Sortieren in der Uebersicht)
	 */
	var $groupid = 0;
	/**
	 * @var int TurnierID des Hauptturniers bei Vorrundenturnieren
	 */
	var $pturnierid = 0;
	/**
	 * @var string Name des Turniers
	 */
	var $name = '';
	/**
	 * @var int Flags des Turniers
	 */
	var $flags = 0;
	/**
	 * @var string Informative Startzeit
	 */
	var $startzeit;
	/**
	 * @var int ligagame typ des Turniers
	 */
	var $gameid;
	/**
	 * @var int ligatyp des Turniers
	 * @deprecated
	 */
	var $liga = 0;
	/**
	 * @var int id innerhalb der liga des Turniers
	 * @deprecated
	 */
	var $ligaid = "error";
	/**
	 * @var int Kosten des Turniers
	 */
	var $coins;
	/**
	 * @var int Teamanzahl
	 */
	var $teamnum;
	/**
	 * @var int Teamgroesse
	 */
	var $teamsize;
	/**
	 * @var int Status des Turniers
	 */
	var $status;
	/**
	 * @var string Regeln
	 */
	var $regeln;
	/**
	 * @var string kleines Icon des Turniers
	 */
	var $icon;
	/**
	 * @var string grosses Icon des Turniers
	 */
	var $icon_big;
	/**
	 * @var string irc channel waehrend der Veranstaltung
	 */
	var $ircchannel;
	/**
	 * @var int bis welche runde werden coins zurueckgegeben (Default: Bis Turnierende)
	 */
	var $coinsback = 32;


	/**
	 * laed ein Turnier-Objekt aus der Datenbank.
	 * das Objekt wird gecached.
	 * @param int $turnierid Turnier
	 * @param boolean $flush True um ein Objekt aus dem cache zu loeschen
	 * @return mixed Match | TS_ERROR
	 * @static
	 */
	function load($turnierid, $flush = FALSE) {
		static $cache = array();

		if (!isset($turnierid))
			return TS_ERROR;

		/* eintrag aus cache loeschen */
		if ($flush) {
			unset($cache[$turnierid]);
			return TS_SUCCESS;
		}

		/* eintrag aus cache holen */
		if (isset($cache[$turnierid]))
			return $cache[$turnierid];

		$retval = new Turnier();

		$sql = "SELECT * FROM t_turnier WHERE turnierid = '{$turnierid}'";
		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);

		$retval->turnierid	= (int)$row['turnierid'];
		$retval->pturnierid	= (int)$row['pturnierid'];
		$retval->partyid	= (int)$row['partyid'];
		$retval->groupid	= (int)$row['groupid'];
		$retval->name		= (string)$row['name'];
		$retval->mindestalter		= (int)$row['mindestalter'];
		$retval->flags		= (int)$row['flags'];
		$retval->startzeit	= (string)$row['startzeit'];
		$retval->gameid		= (int)$row['gameid'];
		$retval->coins		= (int)$row['coins'];
		$retval->teamnum	= (int)$row['teamnum'];
		$retval->teamsize	= (int)$row['teamsize'];
		$retval->regeln		= (string)$row['regeln'];
		$retval->htmltree		= (string)$row['htmltree'];
		$retval->htmlranking		= (string)$row['htmlranking'];
		$retval->status		= (int)$row['status'];
		$retval->icon		= (string)$row['icon'];
		$retval->icon_big	= (string)$row['icon_big'];
		$retval->ircchannel	= (string)$row['ircchannel'];
		$retval->coinsback	= (int)$row['coinsback'];

		$cache[$turnierid] = $retval;
		return $retval;
	}


	/**
	 * Sichert ein Turnier-Objekt in der Datenbank.
	 * flushed cache von load()
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function save() {
		$sql = "UPDATE t_turnier SET
				partyid = '{$this->partyid}',
				groupid = '{$this->groupid}',
				pturnierid = '{$this->pturnierid}',
				name = '".mysql_escape_string($this->name)."',
				mindestalter = '{$this->mindestalter}',
				flags = '{$this->flags}',
				startzeit = '".mysql_escape_string($this->startzeit)."',
				gameid = '{$this->gameid}',
				coins = '{$this->coins}',
				teamnum = '{$this->teamnum}',
				teamsize = '{$this->teamsize}',
	 			regeln = '$this->regeln',
	 			htmltree = '$this->htmltree',
	 			htmlranking = '$this->htmlranking',
				status = '{$this->status}',
				icon = '".mysql_escape_string($this->icon)."',
				icon_big = '".mysql_escape_string($this->icon_big)."',
				ircchannel = '".mysql_escape_string($this->ircchannel)."',
				coinsback = '{$this->coinsback}',
				wann_geaendert = '".time()."',
				wer_geaendert = '".COMPAT::currentID()."'
			WHERE
				turnierid = '{$this->turnierid}'";

/* Disabled mysql_real_escape_string, because magic_quotes_gpc ist enabled. */
/*                              regeln = '".mysql_real_escape_string($this->regeln)."', */
		if (!DB::query($sql))
			return TS_ERROR;
		/* Flush cache */
		Turnier::load($this->turnierid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * Erzeugt ein neues Objektabbild in der Datenbank.
	 * @param boolean $force - forced mit einer bestimmten Turnierid (Coverage import)
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function create($force = FALSE) {
		if (!$force) {
			// leeres objekt
			if ($this->turnierid != 0)
				return TS_ERROR;

			// naechste turnierid berechnen
			$sql = "SELECT MAX(turnierid) % 1000 AS lastid FROM t_turnier WHERE partyid = '{$this->partyid}'";
			$res = DB::query($sql);
			$row = mysql_fetch_assoc($res);
			$this->turnierid = ($this->partyid * 1000) + ((isset($row['lastid'])) ? $row['lastid'] +1 : 1);
		}

		$sql = "INSERT INTO t_turnier SET
				turnierid = '{$this->turnierid}',
				partyid = '{$this->partyid}',
				groupid = '{$this->groupid}',
				pturnierid = '{$this->pturnierid}',
				name = '".mysql_escape_string($this->name)."',
				mindestalter = '{$this->mindestalter}',
				flags = '{$this->flags}',
				startzeit = '".mysql_escape_string($this->startzeit)."',
				gameid = '{$this->gameid}',
				coins = '{$this->coins}',
				teamnum = '{$this->teamnum}',
				teamsize = '{$this->teamsize}',
				regeln = '{$this->regeln}',
				status = '{$this->status}',
				icon = '".mysql_escape_string($this->icon)."',
				icon_big = '".mysql_escape_string($this->icon_big)."',
				ircchannel = '".mysql_escape_string($this->ircchannel)."',
				coinsback = '{$this->coinsback}',
				wann_angelegt = '".time()."',
				wer_angelegt = '".COMPAT::currentID()."'";
		if (!DB::query($sql))
			return TS_ERROR;

		/* Flush cache */
		Turnier::load($this->turnierid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * Loescht ein Turnier
	 * (alle anderen Tables sollten vorher geloescht werden)
	 * @param int $turnierid Turnier
	 */
	function delete($turnierid) {
		$sql = "DELETE FROM t_turnier WHERE turnierid = '{$turnierid}'";
		DB::query($sql);
	}
	

	/**
	 * Liste aller Turniere fuer diese Party holen
	 * @param int $partyid Party
	 * @return mixed Array mit Turnieren
	 */
	static function getTourneyList($partyid) {
		$sql = "SELECT t.*, COUNT(t_team.teamid) as teams
			FROM t_turnier t
			LEFT JOIN t_team ON t.turnierid = t_team.turnierid
			LEFT JOIN t_group g ON t.groupid = g.groupid
			WHERE t.partyid = '{$partyid}'
			GROUP BY turnierid
			ORDER BY g.value, t.name";
		$res= DB::query($sql);

		$retval = array();
		while ($row = mysql_fetch_assoc($res))
			$retval[$row['turnierid']] = $row;
		return $retval;
	}

	/**
	 * Liste aller Turniere fuer diese Party, für diesen User holen
	 * @param int $partyid Party
	 * @param int $userid User
	 * @return mixed Array mit Turnieren
	 */
	static function getTourneyListForUser($partyid, $userid) {
		$sql = "SELECT t.turnierid, team.teamid, team.name, t.coins, team.flags
			FROM t_turnier t
			LEFT JOIN t_team team USING (turnierid)
			LEFT JOIN t_team2user t2u ON team.teamid = t2u.teamid
			AND team.turnierid = t2u.turnierid
			WHERE t.partyid = '$partyid'
			AND t2u.userid = '$userid'";
		$res= DB::query($sql);
		$retval = array();
		while ($row = mysql_fetch_assoc($res))
			$retval[$row['turnierid']] = $row;
		return $retval;
	}


	/**
	 * Gibt ein Array mit den Stati eines Turniers zurueck
	 * @return mixed Stringarray
	 * @todo irgendwie von smarty machen lassen, gehoert nicht in code
	 */
	static function getStatusArr() {
		static $cache = array(TURNIER_STAT_RES_NOT_OPEN => 'Anmeldung noch nicht eröffnet',
					TURNIER_STAT_RES_OPEN => 'Anmeldung eröffnet',
					TURNIER_STAT_RES_CLOSED => 'Anmeldung geschlossen',
					TURNIER_STAT_SEEDING => 'Turnier wird zugelost',
					TURNIER_STAT_RUNNING => 'Turnier läuft',
					TURNIER_STAT_PAUSED => 'Turnier unterbrochen',
					TURNIER_STAT_FINISHED => 'Turnier beendet',
					TURNIER_STAT_CANCELED => 'Turnier abgesagt');
		return $cache;
	}
}
?>