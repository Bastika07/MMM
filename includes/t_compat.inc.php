<?php
/**
 * Systemfunktionen fuer das turniersystem
 */
class COMPAT {
	/**
	 * check ob user eingeloggt ist
	 * @return boolean true wenn User eingeloggt ist
	 */
	static function sessionIsValid() {
		global $nLoginID;
		return isset($nLoginID) && $nLoginID > 0;
	}

	/**
	 * Gibt den Login des eingeloggten Users zurueck
	 * @return string Loginname
	 */
	function currentLogin() {
		return User::name();
	}

	/**
	 * Gibt die userid des eingeloggten Users zurueck
	 * @return int Userid
	 */
	static function currentID() {
		global $nLoginID, $loginID;
		return (int) (isset($nLoginID) ? $nLoginID : $loginID);
	}

	/**
	 * Holt zu einer Userid einen Login
	 * @param int $userid Userid
	 * @return string Loginname
	 */
	function getLoginByID($userid) {
		return User::name($userid);
	}

	/**
	 * Holt den bezahltstatus fuer diesen User
	 * @param int $userid Userid
	 * @param int $partyid Partyid
	 * @return boolean true wenn user bezahlt hat
	 */
	function userHasPayed($userid, $partyid) {
		$mandantid = COMPAT::getMandantFromParty($partyid);
		return User::hatBezahlt($userid, $mandantid);
	}

	/**
	 * holt zu einer partyid die passende mandantid
	 * @param int $partyid Partyid
	 * @return int Mandantid
	 * @intern derzeit ist Partyid == Mandantid
	 */
	function getMandantFromParty($partyid) {
		$sql = "select
			  mandantId
			from
			  party
			where
			  partyId = '$partyid'
			";

		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);
		return $row['mandantId'];
	}

	/**
	 * Gibt eine Liste von userids mit passenden rechten zurueck.
	 * @param int $mandantid Mandant
	 * @param string $right Recht
	 * @return mixed Array von Userids
	 */
	function getUserListByRight($mandantid, $right) {
		$sql = "select r.USERID, u.LOGIN
			from RECHTZUORDNUNG r, USER u
			where r.USERID = u.USERID
			and r.MANDANTID = '{$mandantid}'
			and r.RECHTID = '{$right}'";
		$res = DB::query($sql);
		$retval = array();
		while ($row = mysql_fetch_assoc($res))
			$retval[$row['USERID']] = $row['LOGIN'];

		return $retval;
	}

	/**
	 * Gibt eine Liste von Mandanten zurueck in denen der user das recht hat
	 * @param int $userid Userid
	 * @param string $right Recht
	 * @return mixed Array von Mandanten
	 */
	function getMandantArrFromRight($userid, $right) {
		$sql = "select
			  m.MANDANTID as mandantid,
			  m.BESCHREIBUNG as beschreibung,
			  p.partyId as partyid,
			  p.beschreibung as partyname
			from
			  MANDANT m,
			  RECHTZUORDNUNG r,
			  party p
			where
			  r.USERID = '$userid' and
			  m.MANDANTID = r.MANDANTID and
			  p.mandantId = m.MANDANTID and
			  p.aktiv = 'J' and
			  r.RECHTID = '$right'
			";

		$res = DB::query($sql);
		$retVal= array();
		while ($row = mysql_fetch_assoc($res))
			$retVal[$row['mandantid']] = $row;

		return $retVal;
	}

	/**
	 * Checkt ob der User in in dem Mandanten das recht hat
	 * @param int $mandantid Mandant
	 * @param int $userid User
	 * @param string $right Recht
	 * @return boolean true wenn das recht vorhanden ist
	 */
	function hasMandantRight($mandantid, $userid, $right) {
		if (LOCATION == "intranet")
			return User::hatRecht($right, $userid);
		else
			return User::hatRecht($right, $userid, $mandantid);
	}

	/**
	 * gibt eine Liste von Clanmembern des Users zurueck
	 * @param int $mandantid Mandant
	 * @param int $userid User
	 * @return mixed Array von ClanMembern
	 */
	function getClanMemberOf($mandantid, $userid) {
		$sql = "select CLANID FROM USER_CLAN
			where MANDANTID = '{$mandantid}'
			and USERID = '{$userid}'";
		$res = DB::query($sql);

		// user ist in keinem clan
		if (mysql_num_rows($res) == 0)
			return array();

		$row = mysql_fetch_assoc($res);

		$sql = "select c.USERID, u.LOGIN
			from USER_CLAN c
			left join USER u on u.USERID = c.USERID
			where c.MANDANTID = '{$mandantid}'
			and c.CLANID = '{$row['CLANID']}'
			and c.AUFNAHMESTATUS = 2";
		$res = DB::query($sql);
		$retval = array();
		while ($row = mysql_fetch_assoc($res))
			$retval[$row['USERID']] = $row['LOGIN'];

		return $retval;
	}

	/**
	 * gibt den sitzplatz des users zurueck
	 * @param int $mandantid Mandant
	 * @param int $userid User
	 * @return mixed Array reihe/platz/ebene
	 */
	function getSeat($partyid, $userid) {
		// Altes System
		/*
		$sql = "select s.REIHE, s.PLATZ, sd.EBENE
			from SITZ as s, SITZDEF as sd
			where s.USERID = '{$userid}'
			and s.MANDANTID = '{$mandantid}'
			and s.MANDANTID = sd.MANDANTID
			and s.REIHE = sd.REIHE
			and (s.RESTYP = 1 or s.RESTYP = 3)";
		*/
		// Neues System
		$sql = "select
			  t.sitzReihe,
			  t.sitzPlatz,
			  sd.EBENE
			from
			  acc_tickets t,
			  SITZDEF sd
			where
			  sd.REIHE    = t.sitzReihe and
			  t.userId    = '$userid' and
			  t.partyId   = '$partyid'
			";
		$res = DB::query($sql);
		$row = mysql_fetch_assoc($res);
		if (mysql_num_rows($res) == 0)
			return "";

		return array('reihe' => $row['sitzReihe'], 'platz' => $row['sitzPlatz'], 'ebene' => $row['EBENE']);
	}
}


class HELPER {
	function error($str) {
		echo '<p class="fehler">'.$str.'</p>'."\n";
	}

	function confirm($str) {
		echo '<p class="confirm">'.$str.'</p>'."\n";
	}

	function show($str) {
		echo '<pre>';
		print_r($str);
		echo '</pre>'."\n";
	}

	function show2($str) {
		echo '<pre>';
		var_dump($str);
		echo '</pre>'."\n";
	}
}

