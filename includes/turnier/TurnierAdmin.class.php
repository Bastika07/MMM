<?php
/**
 * @package turniersystem
 * @subpackage include
 */
require_once("turnier/t_constants.php");
require_once("turnier/Turnier.class.php");
require_once("t_compat.inc.php");

/**
 * Funktions Sammlung für TurnierAdmin Verwaltung
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/07/16 ore - initial version
 * @static
 */
class TurnierAdmin {

	/**
	 * checkt ob der user turnieradmin oder turnierleiter ist
	 * @param int $userid diesen User checken
	 * @param int $turnierid gegen dieses Turnier checken
	 * @return boolean true | false
	 */
	function isAdmin($userid, $turnierid) {
		// passendes Turnier laden (warscheinlich eh im cache)
		$turnier = Turnier::load($turnierid);

		if (TurnierAdmin::isTurnierLeitung($userid, $turnier->partyid))
			return true;

		$turnierlist = TurnierAdmin::getListByUser($userid);
		return in_array($turnierid, $turnierlist);
	}

	/**
	 * checkt ob der user turnierleiter dieser partyid ist
	 * @param int $userid Userid
	 * @param int $partyid Party
	 * @return boolean true | false
	 */
	function isTurnierLeitung($userid, $partyid) {
		static $cache = array();

		// ist uns dieses Tupel bekannt?
		if (isset($cache[$partyid])) {
			if (isset($cache[$partyid][$userid]))
				return $cache[$partyid][$userid];
		} else {
			$cache[$partyid] = array();
		}

		$mandantid = COMPAT::getMandantFromParty($partyid);
		$cache[$partyid][$userid] = COMPAT::hasMandantRight($mandantid, $userid, 'TURNIERLEITUNG');

		return $cache[$partyid][$userid];
	}

	/**
	 * Gibt eine Liste aller Turniere zurueck, wo der User Admin ist.
	 * wird gecached
	 * @param int $userid User fuer den die Liste geholt wird
	 * @param boolean $flush bei True wird cache geflushed
	 * @return mixed Liste von Turnierids
	 */
	function getListByUser($userid, $flush = false) {
		static $cache = array();

		if ($flush) {
			$cache = array();
			return TS_SUCCESS;
		}

		if (isset($cache[$userid]))
			return $cache[$userid];

		$sql = "SELECT turnierid FROM t_admin WHERE userid = '{$userid}'";
		$res = DB::query($sql);

		$retval = array();
		while ($row = mysql_fetch_assoc($res)) {
			array_push($retval, $row['turnierid']);
		}

		$cache[$userid] = $retval;
		return $retval;
	}


	/**
	 * Gibt fuer dieses Turnier alle Userids/Logins der Admins zurueck
	 * @param int $turnierid Turnier
	 * @return mixed Array von Userid=>Logins
	 */
	function getListByTourney($turnierid) {
		$sql = "SELECT ta.userid, u.LOGIN as login
			FROM t_admin ta
			LEFT JOIN USER u ON ta.userid = u.USERID
			WHERE turnierid = '{$turnierid}'";
		$res = DB::query($sql);

		$retval = array();
		while ($row = mysql_fetch_assoc($res))
			$retval[$row['userid']] = $row['login'];

		return $retval;
	}


	/**
	 * schreibt eine neue Liste von Admins fuer ein Turnier.
	 * flushed den cache von getListByUser()
	 * @param int $turnierid Turnierid
	 * @param mixed $newlist Array von Admins
	 * @return TS_SUCCESS
	 */
	function setListByTourney($turnierid, $newlist) {
		$sql = "SELECT userid FROM t_admin WHERE turnierid = '{$turnierid}'";
		$res = DB::query($sql);

		$oldlist = array();
		while ($row = mysql_fetch_assoc($res))
			array_push($oldlist, $row['userid']);

		/* neue eintraege erzeugen */
		$tmplist = array_diff($newlist, $oldlist);
		foreach ($tmplist as $userid) {
			$sql = "INSERT INTO t_admin SET
				turnierid = '{$turnierid}',
				userid = '{$userid}',
				wann_angelegt = '".time()."',
				wer_angelegt = '".COMPAT::currentID()."'";
			DB::query($sql);
		}

		/* nicht mehr vorhandene eintraege loeschen */
		$tmplist = array_diff($oldlist, $newlist);
		foreach ($tmplist as $userid) {
			$sql = "DELETE FROM t_admin
				WHERE turnierid = '{$turnierid}'
				AND userid = '{$userid}'";
			DB::query($sql);
		}

		/* flush cache */
		TurnierAdmin::getListbyUser(0, true);

		return TS_SUCCESS;
	}
}
?>
