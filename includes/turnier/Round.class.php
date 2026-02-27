<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once("turnier/t_constants.php");

/**
 * Runden bezogene Funktionen
 *
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/16 ore - initial version
 * @version 2004/07/18 ore - caching
 */
class Round {
	/**
	 * @var int Turnierid (primary key)
	 */
	var $turnierid = 0;
	/**
	 * @var int Roundid (primary key)
	 */
	var $roundid = 0;
	/**
	 * @var string Roundname
	 */
	var $name = '';
	/**
	 * @var string Beginnzeitpunkt der Runde (nur informativ)
	 */
	var $begins = '';
	/**
	 * @var string Endzeitpunkt der Runde (nur informativ)
	 */
	var $ends = '';
	/**
	 * @var string Info zu dieser Runde (nur informativ)
	 */
	var $info = '';
	/**
	 * @var int Flags
	 * @todo wird das ueberhaupt gebraucht?
	 */
	var $flags = 0;


	/**
	 * laed ein Rundenobjekt auf der Datenbank.
	 * Objekte werden gecached
	 * @param int $turnierid Turnierid
	 * @param int $roundid Roundid
	 * @param boolean $flush Wenn True wird der Cache fuer ein Objekt geflusht
	 * @return mixed Round | TS_ERROR
	 * @static
	 */
	function load($turnierid, $roundid, $flush = FALSE) {
		static $cache = array();

		if (!isset($turnierid) || !isset($roundid))
			return TS_ERROR;

		/* eintrag aus cache loeschen */
		if ($flush && isset($cache[$turnierid][$roundid])) {
			unset($cache[$turnierid][$roundid]);
			return TS_SUCCESS;
		}

		/* eintrag aus cache holen */
		if (isset($cache[$turnierid][$roundid]))
			return $cache[$turnierid][$roundid];


		$retval = new Round();

		$sql = "SELECT * FROM t_rounds
			WHERE turnierid = '{$turnierid}'
			AND roundid = '{$roundid}'";
		$res = DB::query($sql);
		$row = $res->fetch_assoc();

		$retval->turnierid	= (int)$row['turnierid'];
		$retval->roundid	= (int)$row['roundid'];
		$retval->name		= (string)$row['name'];
		$retval->begins		= (string)$row['begins'];
		$retval->ends		= (string)$row['ends'];
		$retval->info		= (string)$row['info'];
		$retval->flags		= (int)$row['flags'];

		/* insert in cache */
		if (!isset($cache[$turnierid]))
			$cache[$turnierid] = array();

		$cache[$turnierid][$roundid] = $retval;
		return $retval;
	}


	/**
	 * Speichert ein Round-Objekt in der Datenbank.
	 * Flushed den cache in load()
	 * @return int TS_SUCCESS
	 */
	function save() {
		$sql = "UPDATE t_rounds SET
				name = '".DB::$link->real_escape_string($this->name)."',
				begins = '".DB::$link->real_escape_string($this->begins)."',
				ends = '".DB::$link->real_escape_string($this->ends)."',
				info = '".DB::$link->real_escape_string($this->info)."',
				flags = '{$this->flags}',
				wann_geaendert = '".time()."',
				wer_geaendert = '".COMPAT::currentID()."'
			WHERE
				turnierid = '{$this->turnierid}' AND
				roundid = '{$this->roundid}'";
		if (!DB::query($sql))
			return TS_ERROR;

		/* flush cache */
		Round::load($this->turnierid, $this->roundid, TRUE);

		return TS_SUCCESS;
	}


	/**
	 * Erzeugt Objektabbild in der Datenbank
	 * return int TS_SUCCESS | TS_ERROR
	 */
	function create() {
		$sql = "INSERT INTO t_rounds SET
				turnierid = '{$this->turnierid}',
				roundid = '{$this->roundid}',
				name = '".DB::$link->real_escape_string($this->name)."',
				begins = '".DB::$link->real_escape_string($this->begins)."',
				ends = '".DB::$link->real_escape_string($this->ends)."',
				info = '".DB::$link->real_escape_string($this->info)."',
				flags = '{$this->flags}',
				wann_angelegt = '".time()."',
				wer_angelegt = '".COMPAT::currentID()."'";
		if (!DB::query($sql))
			return TS_ERROR;

		/* flush cache */
		Round::load($this->turnierid, $this->roundid, TRUE);

		return TS_SUCCESS;
	}


	/**
	 * loescht eine runde aus einem Turnier
	 * @param int $turnierid Turnier
	 * @param int $roundid Runde (optional)
	 * @return TS_SUCCESS
	 * @static
	 */
	function delete($turnierid, $roundid = -1) {
		$sql = "DELETE FROM t_rounds WHERE turnierid = '{$turnierid}'";

		if ($roundid != -1)
			$sql .= " AND roundid = '{$roundid}'";

		DB::query($sql);
		return TS_SUCCESS;
	}


	/**
	 * Holt Rundenliste aus der DB
	 * @param int $turnierid Turnier
	 * @return mixed Array mit Rundenliste
	 * @static
	 */
	function getRoundList($turnierid) {
		$retval = array();

		if (!isset($turnierid))
			return $retval;

		$sql = "SELECT roundid, name, begins, ends, flags, info
			FROM t_rounds
			WHERE turnierid = '{$turnierid}'
			ORDER BY roundid";

		$res = DB::query($sql);

		while ($row = $res->fetch_assoc())
			$retval[$row['roundid']] = $row;

		return $retval;
	}
}
?>
