<?php
/**
 * @package turniersystem
 * @subpackage admin
 */
$iRecht = 'TURNIERADMIN';
define('MANDANTID', 'admin');

require('../controller.php');
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "turnier/t_constants.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/Round.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/TeamSystem.class.php";
require_once "turnier/TurnierRanking.class.php";
require_once "turnier/Jump.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require('admin/vorspann.php');

DB::connect();


function transferTeams() {

	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (false==TurnierSystem::existSubtourneys($turnier)) {  
		echo "Dieses Turnier hat keine Vorrunden!";
		return TS_ERROR;
	}

	if (!($turnier->flags & TURNIER_TREE_RUNDEN) || $turnier->flags & TURNIER_RUNDEN)
		return TS_ERROR;

	#alle vorrunden beendet?
	$sql = "SELECT turnierid
		FROM t_turnier
		WHERE pturnierid = '".$turnier->turnierid."'";
	$res= DB::query($sql);

	$teamlist_platz_1 = array();
	$teamlist_platz_2 = array();
	while ($row = mysql_fetch_assoc($res)) {
		
		$turnier_tmp = Turnier::load($row['turnierid']);
		if (!($turnier_tmp->status == TURNIER_STAT_FINISHED)) {
			$smarty = new PelasSmarty("turnier");
			$smarty->assign('notComplete', true);
			$smarty->display('turnier_transfer.tpl');
			die();
		}
		$ranking = TurnierRanking::getRankingRunden($turnier_tmp->turnierid);
		#da die teamid nicht Turnieruebergreifend ist, wird hier die Teamid im Hauptturnier ï¿½ber den Teamnamen gesucht
		##### erstplatzierten uebernehmen
		$sql = "SELECT teamid
			FROM t_team
			WHERE turnierid = '".$turnier->turnierid."' AND 
			name = (SELECT name FROM t_team WHERE turnierid = '".$turnier_tmp->turnierid."' AND teamid = ".$ranking[0][teamid].")";
		$res2 = DB::query($sql);
		$teamid_hauptturnier = mysql_fetch_assoc($res2);
		array_push($teamlist_platz_1, $teamid_hauptturnier['teamid']);

		##### zweitplatzierten uebernehmen
		$sql = "SELECT teamid
			FROM t_team
			WHERE turnierid = '".$turnier->turnierid."' AND 
			name = (SELECT name FROM t_team WHERE turnierid = '".$turnier_tmp->turnierid."' AND teamid = ".$ranking[1][teamid].")";
		$res2 = DB::query($sql);
		$teamid_hauptturnier = mysql_fetch_assoc($res2);
		array_unshift($teamlist_platz_2, $teamid_hauptturnier['teamid']);
	}

	#Damit immer ein erstplatzierter auf einen zweitplatzierten trifft, mï¿½ssen die nacheinander geseedet werden.
	$teamlist = array_merge($teamlist_platz_1, $teamlist_platz_2);
#var_dump($teamlist);
#var_dump($teamlist_platz_1);
#var_dump($teamlist_platz_2);

	foreach($teamlist as $team) {
		TurnierSystem::seedTeam($turnier, $team, true);
	}
	#Erste Runde auf Ready setzen
	TurnierSystem::startupTourney($turnier);

#	TurnierSystem::fillSeeding($turnier, $teamlist,true);
	TurnierSystem::flushCache($turnier->turnierid);
	header ("Location: /turnier/turnier_verwaltung_list.php");
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	default:	transferTeams();
			break;
}

?>
