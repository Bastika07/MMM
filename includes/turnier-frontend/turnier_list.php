<?php
/**
 * Zeigt die Turnierliste an
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 */


require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "turnier/Turnier.class.php";

require_once "turnier/TeamSystem.class.php";

require_once "turnier/TurnierGroup.class.php";


DB::connect();


$mandantid = $nPartyID;
$partyid = PELAS::mandantAktuelleParty($mandantid);

$turniere = Turnier::getTourneyList($partyid);
$personal_tourneys = Turnier::getTourneyListForUser($partyid, COMPAT::currentID());
$t_stat = Turnier::getStatusArr();

foreach ($turniere as $turnierid => $turnier) {
	$turniere[$turnierid]['statusstr'] = $t_stat[$turniere[$turnierid]['status']];
	if (isset($personal_tourneys[$turnierid])) {
	  $turniere[$turnierid]['isSignedUp'] = true;
	  $turniere[$turnierid]['ownTeamId'] = $personal_tourneys[$turnierid]['teamid'];
	}
}

$smarty = new PelasSmarty("turnier");
$smarty->assign('intranet', (LOCATION == "intranet"));
$smarty->assign('turniere', $turniere);
$smarty->assign('userId', COMPAT::currentID());
if (COMPAT::sessionIsValid())
  $smarty->assign('coins', TeamSystem::calcCoins($partyid, COMPAT::currentID()));
$smarty->assign('maxCoins', TeamSystem::getMaxCoins($mandantid, COMPAT::currentID()));

$smarty->assign('groupid', 0);
$smarty->assign('groups', TurnierGroup::getGroups());

$smarty->assign('pelashost', PELASHOST);

$smarty->displayWithFallback('turnier_list.tpl');


?>
