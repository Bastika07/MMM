<?php
/**
 * Zeigt die Details zu einem Match an
 * @author Olaf Rempel <razzor@kopf-tisch.de>
 * @package turniersystem
 * @subpackage frontend
 * @todo Leader muss die Paarung auf "playing" setzen koennen
 */
require_once 'dblib.php';
require_once 't_compat.inc.php';
require_once "classes/PelasSmarty.class.php";

require_once "new_forum.php";
require_once "turnier/Tree.php";
require_once "turnier/Turnier.class.php";
require_once "turnier/TurnierAdmin.class.php";
require_once "turnier/TurnierSystem.class.php";
require_once "turnier/Match.class.php";
require_once "turnier/Round.class.php";
require_once "turnier/Team.class.php";
require_once "turnier/Jump.class.php";

DB::connect();

function editMatch() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['matchid']) || !is_numeric($_GET['matchid']))
		return;

	$match = Match::load($_GET['turnierid'], $_GET['matchid']);
	if (!is_a($match, 'Match'))
		return;

	switch ($_POST['subaction']) {
		case 'Accept':
			$ret = TurnierSystem::matchAcceptResult($match, COMPAT::currentID());
			break;

		case 'Enter':
		case 'Change':
			if (!isset($_POST['result1']) || !isset($_POST['result2']))
				break;

			if (!is_numeric($_POST['result1']) || !is_numeric($_POST['result2']))
				break;

			$ret = TurnierSystem::matchEnterResult($match, COMPAT::currentID(), $_POST['result1'], $_POST['result2']);
			break;

		case 'Random':
			$ret = TurnierSystem::matchRandomResult($match, COMPAT::currentID());
			break;

		case 'Reset Match':
			$ret = TurnierSystem::matchReset($match, COMPAT::currentID());
			break;

		case 'Spielbereit':
			$ret = TurnierSystem::matchSetReadyToPlay($match, COMPAT::currentID());
			break;

		default:
			break;
	}
	header ("Location: ?page=26&action=show&turnierid={$match->turnierid}&matchid={$match->matchid}");
}


function setFlags() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['matchid']) || !is_numeric($_GET['matchid']))
		return;

	if (!isset($_GET['flag']) || !is_numeric($_GET['flag']))
		return;

	$match = Match::load($_GET['turnierid'], $_GET['matchid']);
	if (!is_a($match, 'Match'))
		return;

	$ret = TurnierSystem::matchSetPenalty($match, COMPAT::currentID(), $_GET['flag']);

	header ("Location: ?page=26&action=show&turnierid={$match->turnierid}&matchid={$match->matchid}");
}


function hideEvent() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	if (!isset($_GET['matchid']) || !is_numeric($_GET['matchid']))
		return;

	if (!isset($_GET['eventid']) || !is_numeric($_GET['eventid']))
		return;

	if (!TurnierAdmin::isAdmin(COMPAT::currentID(), $_GET['turnierid']))
		return;

	$match = Match::load($_GET['turnierid'], $_GET['matchid']);
	if (!is_a($match, 'Match'))
		return;

	$match->hideEvent($match->turnierid, $_GET['eventid']);
	header ("Location: ?page=26&action=show&turnierid={$match->turnierid}&matchid={$match->matchid}");
}


function showMatch() {
	if (!isset($_GET['turnierid']) || !is_numeric($_GET['turnierid']))
		return;

	$turnier = Turnier::load($_GET['turnierid']);
	if (!is_a($turnier, 'Turnier'))
		return;

	if (!isset($_GET['matchid']) || !is_numeric($_GET['matchid']))
		return;

	$match = Match::load($turnier->turnierid, $_GET['matchid']);
	if (!is_a($match, 'Match'))
		return;

	$events = $match->getEvents();

	$round = Round::load($turnier->turnierid, $match->round);
	$team1 = Team::load($turnier->turnierid, $match->team1);
	$team2 = Team::load($turnier->turnierid, $match->team2);

	$jump = new Jump();
	$jump->size = $turnier->teamnum /2;
	$match->viewnum = $jump->calcReal($match->matchid);


	$admin = TurnierAdmin::isAdmin(COMPAT::currentID(), $turnier->turnierid);
	$ready = ($match->flags & MATCH_READY);
	$complete = ($match->flags & MATCH_COMPLETE);
	$leader = !($match->isLeader(COMPAT::currentID()) & TS_ERROR) ;
	$accept = (!($match->flags & MATCH_TEAM1_ACCEPT) ^ !($match->flags & MATCH_TEAM2_ACCEPT)) && ($leader | $admin);

	// sollen die edit felder angeziegt werden?
	$turnier_running = ($turnier->status == TURNIER_STAT_RUNNING) ||  ($turnier->status == TURNIER_STAT_PAUSED);

	$flags['admin'] = ($admin) && $turnier_running;
	$flags['showres'] = $complete || $accept;
	$flags['accept'] = (($leader | $admin) && $ready && !$complete && $accept) && $turnier_running;
	$flags['random'] = ($admin && !$accept) && $turnier_running;
	$flags['enter'] = ($admin || ($leader && $ready && !$complete)) && $turnier_running;
	$flags['readytoplay'] = $ready && !$complete && $turnier_running && ($team1->isMember(COMPAT::currentID()) || $team2->isMember(COMPAT::currentID()) || $team1->isLeader(COMPAT::currentID()) || $team2->isLeader(COMPAT::currentID()));

	$smarty = new PelasSmarty("turnier");
	$smarty->assign('intranet', (LOCATION == "intranet"));
	$smarty->assign('turnier', $turnier);
	$smarty->assign('round', $round);
	$smarty->assign('match', $match);
	$smarty->assign('team1', $team1);
	$smarty->assign('team2', $team2);
	$smarty->assign('events', $events);
	$smarty->assign('tmp', $flags);
	$smarty->displayWithFallback('match_detail.tpl');

	$mandantid = COMPAT::getMandantFromParty($turnier->partyid);
	$forum = new forum($mandantid, $_SERVER['PHP_SELF'], DESIGN_COMMENTS, BT_TURNIERCOMMENTS);

	// Kommentierkrams
	if ($match->threadid != -1) {
		$forum->showThread($match->threadid);
		echo "<table cellpadding='3' cellspacing='5' border='0'><tr><td class='forum_titel'>".
			"<a href=\"?page=26&action=add&thread=$match->threadid\" class=\"forumlink\">Kommentar erstellen</a>".
			"</td></tr></table>";

	} else {
		echo "<table cellpadding='3' cellspacing='5' border='0'><tr><td class='forum_titel'>".
			"<a href=\"?page=26&action=add&turnierid=".$match->turnierid."&matchid=".$match->matchid."\" class=\"forumlink\">Kommentar erstellen</a>".
			"</td></tr></table>";
	}
}

function addComment() {
	$turnier = Turnier::load($_GET['turnierid']);
	// wenn noch keine thread bekannt ist, ist dies der erste Comment -> Thread erstellen
	$mandantid = COMPAT::getMandantFromParty($turnier->partyid);
	$forum = new forum($mandantid, "?page=26", DESIGN_COMMENTS, BT_TURNIERCOMMENTS);
	if (empty($_REQUEST['thread'])) {
		$boardid = $forum->getBoardByType(BT_TURNIERCOMMENTS);
		$forum->form($boardid, NULL, NULL, array('turnierid' => $_GET['turnierid'], 'matchid' => $_GET['matchid']));
		echo "<!-- dada -->";
	} else {
		echo "<!-- hier -->";
		$forum->form(NULL, $_GET['thread'], NULL, NULL, isset($_GET['postToQuoteId']) ? $_GET['postToQuoteId'] : NULL);
	}
}

function submitComment() {
	global $nLoginID, $sLogin;
	$board = isset($_REQUEST['board']) ? Board::load($_REQUEST['board']) : NULL;
	$thread = isset($_REQUEST['threadid']) ? Thread::load($_REQUEST['threadid']) : NULL;
	$post = isset($_REQUEST['post']) ? Post::load($_REQUEST['post']) : NULL;

	$forum = new forum(0, $_SERVER['PHP_SELF'], DESIGN_COMMENTS, BT_TURNIERCOMMENTS);

	$newID = $forum->submit($board->id, $thread->id, $post->id, $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $sLogin);
	// wurde ein neuer Thread angelegt, ist folgendes der Fall:
	// $rc['newThread']=> ID des neuen Threads
	// $rc['oldThread']=> -1
	// wurde nur ein Post angelegt, ist folgendes der Fall:
	// $rc['newThread']=> -1
	// $rc['oldThread']=> ID des Threads, zu dem der Post hinzugefï¿½gt wurde
	if (isset($newID['newThread'])) {
		$match = Match::load($_POST['turnierid'], $_POST['matchid']);
		$match->threadid = $newID['newThread'];
		$match->save();
		echo "<br><br><p><a class=\"arrow\" href=\"?page=26&turnierid={$match->turnierid}&matchid={$match->matchid}\">Zurück</a></p>";

	} else {
		// Post zu bestehenden Thread wurde gemacht, Turnierid und match
		$threadId = ($post != NULL) ? $post->threadId : $thread->id;
		$match = Match::loadByThreadid($threadId);
		echo "<br><br><p><a class=\"arrow\" href=\"?page=26&action=show&turnierid={$match->turnierid}&matchid={$match->matchid}\">Zurück</a></p>";
	}
}

function editComment() {
	$forum = new forum(0, $_SERVER['PHP_SELF'], DESIGN_COMMENTS, BT_TURNIERCOMMENTS);
	$forum->edit($_REQUEST['post']);
}
// dispatcher
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
switch ($action) {
	case 'editMatch':
			editMatch();
			break;

	case 'setflag':
			setFlags();
			break;

	case 'add':
			addComment();
		        break;

	case 'submit':
			submitComment();
			break;

	case 'edit':
			editComment();
			break;

	case 'hideevent':
			hideEvent();
			break;

	default:
	case 'show':
			showMatch();
			break;
}
?>
