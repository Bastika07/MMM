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

/**
 * Funktions Sammlung für die Turnier Coverage
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @version 2004/12/03 ore - initial version
 */
class TurnierCoverage {
	/**
	 * array von turnieren
	 */
	var $arr = array();

	/**
	 * erzeugt ein array mit allen "wichtigen" infos fuer die coverage-uploads
	 * @param int $turnierid Turnier
	 * @return String serialisiertes Array
	 */
	function export($turnierid) {
		$turnier = Turnier::load($turnierid);
		if (!is_a($turnier, 'Turnier'))
			return TS_ERROR;

		$retval = array();
		$retval['turnier'] = $turnier;

		$rounds = Round::getRoundList($turnier->turnierid);
		foreach ($rounds as $roundid => $tmp)
			$retval['rounds'][$roundid] = Round::load($turnier->turnierid, $roundid);

		$teams = Team::getTeamNameList($turnier->turnierid);
		foreach ($teams as $teamid => $tmp)
			$retval['teams'][$teamid] = Team::load($turnier->turnierid, $teamid);

		$matches = Match::getMatchResultList($turnier->turnierid);
		foreach ($matches as $matchid => $tmp) {
			$retval['matches'][$matchid] = Match::load($turnier->turnierid, $matchid);
			$retval['events'][$matchid] = $retval['matches'][$matchid]->getEvents();
		}

		$admins = TurnierAdmin::getListByTourney($turnier->turnierid);
		$retval['admins'] = array();
		foreach ($admins as $userid => $login)
			array_push($retval['admins'], $userid);

		$this->arr[$turnier->turnierid] = $retval;
	}

	/**
	 * bildet die infos in dem array auf die lokale DB ab
	 * @param int $turnierid Turnier
	 * @param mixed $array Coverage export array
	 */
	function import($arr) {
		$turnier = $arr['turnier'];

		// todo: check ob turnieranmeldung noch offen ist, wenn ja -> abbrechen!

		Turnier::delete($turnier->turnierid);

		// turnier
		$turnier->create(true);

		// rounds
		Round::delete($turnier->turnierid);
		foreach ($arr['rounds'] as $roundid => $round)
			$round->create();

		// teams
		Team::delete($turnier->turnierid);
		foreach ($arr['teams'] as $teamid => $team)
				$team->create(true);

		// matches
		Match::delete($turnier->turnierid);
		Match::deleteEvents($turnier->turnierid);
		foreach ($arr['matches'] as $matchid => $match) {
			$match->create();
			foreach ($arr['matches']['events'][$matchid] as $eventid => $event)
				$match->createEvent($event['userid'], $event['text'], $event['time'], $event['flags'], $event['eventid']);
		}

		// admins
		TurnierAdmin::setListByTourney($turnier->turnierid, $arr['admins']);
	}

	function upload($host, $path) {
		$data = "";
		foreach ($this->arr as $turnierid => $tmp) {
			$data .= "t{$turnierid}[turnier]=".urlencode(serialize($this->arr[$turnierid]['turnier']))."&";

			foreach ($tmp['rounds'] as $roundid => $round)
				$data .= "t{$turnierid}[rounds][{$roundid}]=".urlencode(serialize($this->arr[$turnierid]['rounds'][$roundid]))."&";

			foreach ($tmp['teams'] as $teamid => $team)
				$data .= "t{$turnierid}[teams][{$teamid}]=".urlencode(serialize($this->arr[$turnierid]['teams'][$teamid]))."&";

			foreach ($tmp['matches'] as $matchid => $match)
				$data .= "t{$turnierid}[matches][{$matchid}]=".urlencode(serialize($this->arr[$turnierid]['matches'][$matchid]))."&";
		}
		$data .="end=end";
		//return $data;


//		$data = "upload=".urlencode(serialize($this->arr));
		echo "writing ".strlen($data)." bytes\n";
		$fp = fsockopen($host, 80);
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ". strlen($data) ."\r\n");
//		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data."\r\n");
		$res = "";
		while(!feof($fp))
			$res .= fgets($fp, 128);

		fclose($fp);
		return $res;
	}

}
?>
