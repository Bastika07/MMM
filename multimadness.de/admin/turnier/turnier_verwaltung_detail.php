<?php
/**
 * @package turniersystem
 * @subpackage admin
 */

ob_start();

define('MANDANTID', 'admin');
$iRecht = 'TURNIERADMIN';

require('../controller.php');
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "turnier/t_constants.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierGroup.class.php";
require_once "turnier/TurnierLiga.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Team.class.php";
require_once "classes/PelasSmarty.class.php";
require_once "checkrights.php";
require_once "turnier/TeamSystem.class.php";
require('admin/vorspann.php');

DB::connect();

function _getFilledObj() {
	if (!isset($_POST['form']) || !is_array($_POST['form']))
	    return;

	$form = $_POST['form'];

	// Sollte es das Turnier schon geben, die Werte Vorbelegen, da nicht alle Werte im Formular ersetzt werden.
	if (isset($form['turnierid']) && is_numeric($form['turnierid'])) {
	    $turnier = Turnier::load($form['turnierid']);
	    # Flags zurueck setzten, da die nur ber AND hinzugefügt werden.
	    $turnier->flags = 0;
	    if (!is_a($turnier, 'Turnier'))
		return;
	} else {
	    $turnier = new Turnier();
	}

	$turnier->turnierid = $form['turnierid'];
	$turnier->mindestalter = $form['mindestalter'];
	$turnier->groupid = $form['groupid'];
	$turnier->partyid = $form['partyid'];
	$turnier->name = $form['name'];

	if (isset($form['flags']))
		foreach ($form['flags'] as $key => $val)
			$turnier->flags |= $val;

	$turnier->startzeit = $form['startzeit'];
	$turnier->gameid = $form['gameid'];
	$turnier->coins = $form['coins'];
	$turnier->teamnum = $form['teamnum'];
	$turnier->teamsize = $form['teamsize'];
	$turnier->regeln = $form['regeln'];
	$turnier->htmltree = $form['htmltree'];
	$turnier->htmlranking = $form['htmlranking'];
	$turnier->ircchannel = $form['ircchannel'];
	$turnier->coinsback = $form['coinsback'];
	$turnier->icon = $form['icon'];
	$turnier->icon_big = $form['icon_big'];

	return $turnier;
}


function _checkObj($turnier) {
	if (empty($turnier->name))
		return 'Das Turnier muss einen Namen haben.';

	if (empty($turnier->startzeit))
		return 'Das Turnier muss eine Startzeit haben.';

	$ligagame = TurnierLiga::load($turnier->gameid);
	if (($ligagame->teamsize != 0) && ($ligagame->teamsize != $turnier->teamsize))
		return 'Die Liga gibt für diese Spiel eine andere Teamsize vor';

	return;
}


function _createSmarty() {
	global $MINDESTALTER;
	$smarty = new PelasSmarty("turnier");
	$smarty->assign('statusArr', Turnier::getStatusArr());
	$smarty->assign('coinArr', array(0=>0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15));
	$smarty->assign('teamArr', array(4=>4, 8=>8, 16=>16, 32=>32, 64=>64, 96=>96, 128=>128, 256=>256));
	$smarty->assign('teamSizeArr', array(1=>1,2,3,4,5,6,7,8,9,10,11,12,13,14,15));

	$groups = TurnierGroup::getGroups();
	$groupArr = array();
	foreach($groups as $groupid => $group)
		$groupArr[$groupid] = $group['name'];

	$smarty->assign('groupArr', $groupArr);

	$gamelist = TurnierLiga::getGameList();
	$gameidArr = array();
	foreach($gamelist as $id => $game)
		$gameidArr[$id] = $game['fullname'];

	$smarty->assign('gameidArr', $gameidArr);

	$smarty->assign('mindestalterArr', $MINDESTALTER);

	return $smarty;
}


function createTourney() {
	if (!isset($_POST['form'])) {
		if (isset($_GET['template'])) {
			$turnier = Turnier::load($_GET['template']);
			if (!is_a($turnier, 'Turnier'))
				return;
			$turnier->turnierid = 0;
			$formerr = "";

		} else {
			if (!isset($_GET['partyid']) || !is_numeric($_GET['partyid']))
				return;

			$turnier = new Turnier();
			$turnier->partyid = $_GET['partyid'];
			$formerr = "";
		}

	} else {
		$turnier = _getFilledObj();
		unset($turnier->turnierid);

		if (!TurnierAdmin::isTurnierLeitung(COMPAT::currentID(), $turnier->partyid))
			return;

		$formerr = _checkObj($turnier);
		if (!isset($formerr)) {
			$turnier->create();
			header ("Location: turnier_verwaltung.php?partyid={$turnier->partyid}");
			die();
		}
	}

	$smarty = _createSmarty();
	$smarty->assign('action', 'new');
	$smarty->assign('formerr', $formerr);
	$smarty->assign('turnier', $turnier);
	$smarty->display('turnier_verwaltung_detail.tpl');
}

function editTourney() {
	if (!isset($_POST['form'])) {
		if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
			return;

		$turnier = Turnier::load($_GET['turnierid']);
		if (!is_a($turnier, 'Turnier'))
			return;
		$formerr = "";

	} else {
		$turnier = _getFilledObj();
		$formerr = _checkObj($turnier);
		if (!isset($formerr)) {

			if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
				return;

			$turnier->save();
			if (!empty($turnier->htmltree)) {
				TurnierSystem::flushCache($turnier->turnierid);
			}
			header("Location: turnier_verwaltung_list.php?partyid={$turnier->partyid}");
			die();
		}
	}

	$smarty = _createSmarty();
	$smarty->assign('action', 'edit');
	$smarty->assign('formerr', $formerr);
	$smarty->assign('turnier', $turnier);
	$smarty->display('turnier_verwaltung_detail.tpl');
}

/**
 * wandelt ein Turnier in ein MIXED XonX um
 */  
function mixTourney() {

  if (!isset($_REQUEST['turnierid']) || !is_numeric($_REQUEST['turnierid']))
  	return;
  $turnier = Turnier::load($_REQUEST['turnierid']);

	if (!is_a($turnier, 'Turnier'))
		return;
	$formerr = "";

	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid))
		return;
  
  #turnier geschlossen?
  if ($turnier->status <> TURNIER_STAT_RES_CLOSED)  
    $formerr = "Die Anmeldung muss geschlossen sein.";

 	if (isset($_POST['mixit']) && !$formerr) {

    #neue Teamgroesse ermitteln
    $teamsize = intval($_POST['teamsize']);
    if ($teamsize == 0)
      return;
    
    #alte teams holen
    $teamlist = Team::getTeamNameList($turnier->turnierid);
   
    #alle Spieler in eine liste
    $userlist = array(); 
   
    foreach ($teamlist as $team){
      $current_team = Team::load($turnier->turnierid, $team['teamid']);
      foreach ($current_team->namelist as $userid => $username) {
        $userlist[$userid] = $username;
      }
    }
  
    #In $userlist sind nun alle Spieler drin die gelost werden sollen
    if((count($userlist) % $teamsize) <> 0) {
      $formerr = "Teamgr&ouml;&szlig;e nicht m&ouml;glich, da es unvollst&auml;ndige Teams geben w&uuml;rde.(".count($userlist)." Spieler sind angemeldet)";
    } else {
      
      #neue teamsize fuer das turnier setzen
      $turnier->teamsize = $teamsize;
      
      #user mischen
      $userids = array_keys($userlist);
      srand((float)microtime() * 1000000);
      shuffle($userids);

      #alle alten Teams loeschen
      foreach ($teamlist as $team){
        Team::delete($turnier->turnierid, $team['teamid']);
      }
      
      $i = 0;
      foreach ($userids as $userid){  
        if ($i % $teamsize == 0) {
      		// Ligaid von Leader holen
		      $ligaid = TeamSystem::getLigaID($turnier, $userid);
          # neues Team erstellen(Wie TeamSystem::createTeam - nur ohne die ganzen Pruefungen.
          $team = new Team();
      		$team->turnierid = $turnier->turnierid;
      		$team->addUser($userid);
      		$team->name = "Team ".$userlist[$userid];
      		$team->ligaid = $ligaid;
      		$team->flags = (TEAM_USE_COINS | TEAM_IS_ACTIVE);
      		$team->create();
          
        } else {
          #spieler zum letzten Team hinzufuegen
          $team->addUser($userid);
          $team->save();
        }
        $i++;
      }        

      // anzahl teams
    	$teamcount = Team::getTeamCount($turnier->turnierid);
    
    	// teamgroesse festlegen(Turnier kann theoretisch auch groesser sein als vorher!)
    	$turnier->teamnum = 512;
    	
      while ($turnier->teamnum/2 >= $teamcount)
    		$turnier->teamnum /= 2;
    	
      $turnier->save();
      $confirm = "Turnier wurde konvertiert!";
    }
	}#if $_POST
	

	$smarty = _createSmarty();
	$smarty->assign('action', 'mixit');
	$smarty->assign('formerr', $formerr);
	$smarty->assign('confirm', $confirm);
	$smarty->assign('turnier', $turnier);
	$smarty->display('turnier_verwaltung_mixit.tpl');
}

// dispatcher
$action = (isset($_GET['action']) ? $_GET['action'] : '');
switch ($action) {
	case 'new':	createTourney();
			break;

	case 'import':	importTourney();
			break;

	case 'mixit':	mixTourney();
			break;

	case 'edit':	editTourney();
			break;
}

ob_flush();

?>