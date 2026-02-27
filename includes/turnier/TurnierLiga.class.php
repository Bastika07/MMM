<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once ("turnier/t_constants.php");


/**
 * TurnierLiga bezogene Funktionen
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2005/07/16 ore - initial version
 */
class TurnierLiga {
	/**
	 * @var int GameID (primary key)
	 */
	var $gameid = 0;
	/**
	 * @var int Liga
	 */
	var $liga = 0;
	/**
	 * @var string Ligaid innerhalb der Liga (NGL: shortname, WWCL: number)
	 */
	var $shortname = '';
	/**
	 * @var string Name des Spiels
	 */
	var $name = '';
	/**
	 * @var string URL zu den Regeln (oder NULL)
	 */
	var $regelurl = '';

	/**
	 * @var int Teamgroesse
	 */

	/**
	 * laed ein TurnierLiga-Objekt aus der Datenbank.
	 * das Objekt wird gecached.
	 * @param int $gameid
	 * @param boolean $flush True um ein Objekt aus dem cache zu loeschen
	 * @return mixed | TS_ERROR
	 * @static
	 */
	function load($gameid, $flush = FALSE) {
		static $cache = array();

		if (!isset($gameid))
			return TS_ERROR;

		/* eintrag aus cache loeschen */
		if ($flush) {
			unset($cache[$gameid]);
			return TS_SUCCESS;
		}

		/* eintrag aus cache holen */
		if (isset($cache[$gameid]))
			return $cache[$gameid];

		$retval = new TurnierLiga();

		$sql = "SELECT * FROM t_ligagame WHERE gameid = '{$gameid}'";
		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);

		$retval->gameid		= (int)$row['gameid'];
		$retval->liga		= (int)$row['liga'];
		$retval->shortname	= (string)$row['shortname'];
		$retval->name		= (string)$row['name'];
		$retval->regelurl	= (string)$row['regelurl'];
		$retval->teamsize	= (int)$row['teamsize'];

		$cache[$gameid] = $retval;
		return $retval;
	}


	/**
	 * Sichert ein TurnierLiga-Objekt in der Datenbank.
	 * flushed cache von load()
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function save() {
		$sql = "UPDATE t_ligagame SET
				liga = '{$this->liga}',
				shortname = '".mysql_escape_string($this->shortname)."',
				name = '".mysql_escape_string($this->name)."',
				regelurl = '".mysql_escape_string($this->regelurl)."',
				teamsize = '{$this->teamsize}',
				wann_geaendert = '".time()."',
				wer_geaendert = '".COMPAT::currentID()."'
			WHERE
				gameid = '{$this->gameid}'";
		if (!DB::query($sql))
			return TS_ERROR;

		/* Flush cache */
		TurnierLiga::load($this->gameid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * Erzeugt ein neues Objektabbild in der Datenbank.
	 * @return int TS_SUCCESS | TS_ERROR
	 */
	function create($force = FALSE) {
		// leeres objekt
		if ($this->turnierid != 0)
			return TS_ERROR;

		$sql = "INSERT INTO t_ligagame SET
				liga = '{$this->liga}',
				shortname = '".mysql_escape_string($this->ligaid)."',
				name = '".mysql_escape_string($this->name)."',
				regelurl = '".mysql_escape_string($this->regelurl)."',
				teamsize = '{$this->teamsize}',
				wann_angelegt = '".time()."',
				wer_angelegt = '".COMPAT::currentID()."'";
		if (!DB::query($sql))
			return TS_ERROR;

		$this->gameid = mysql_insert_id();

		/* Flush cache */
		TurnierLiga::load($this->gameid, TRUE);
		return TS_SUCCESS;
	}


	/**
	 * Liste aller Spiele einer Liga
	 * @param int $liga
	 * @return mixed Array mit Spielen
	 */
	function getGameList($liga = false) {
		if (isset($liga) && is_numeric($liga)) {
			$sql = "SELECT gameid, liga, shortname, name, regelurl, teamsize
				FROM t_ligagame
				WHERE liga = '{$liga}'
				ORDER BY name";

		} else {
			$sql = "SELECT gameid, liga, shortname, name, regelurl, teamsize
				FROM t_ligagame
				ORDER BY liga, name";
		}

		$res = DB::query($sql);
		$retval = array();
		$ligaArr = TurnierLiga::getLigaArr();
		while ($row = mysql_fetch_assoc($res)) {
			$retval[$row['gameid']] = $row;
			$retval[$row['gameid']]['liganame'] = $ligaArr[$row['liga']];
			$retval[$row['gameid']]['fullname'] = $ligaArr[$row['liga']].": ".$row['name'];
		}
		return $retval;
	}


	/**
	 * Gibt ein Array mit den verschiedenen Turnierarten/Ligen zurueck
	 * @return mixed Stringarray
	 * @todo irgendwie von smarty machen lassen, gehoert nicht in code
	 */
	function getLigaArr() {
		static $cache = array(TURNIER_LIGA_NORMAL => 'Normal',
					TURNIER_LIGA_FUN => 'Fun',
					TURNIER_LIGA_WWCL => 'WWCL',
					TURNIER_LIGA_NGL => 'NGL');
		return $cache;
	}
}
?>
