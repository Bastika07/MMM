<?php

require_once 'dblib.php';
require_once 'new_forum.php';
include_once "getsession.php";

if (!isset($dbh))
	$dbh = DB::connect();

$forum = new forum($nPartyID, $_SERVER['PHP_SELF'].'?action=showComments', DESIGN_NEWS, BT_NEWS);
$forum->setPpp((isset($_GET['ppp']) ? $_GET['ppp'] : 5));
$forum->setTpp((isset($_GET['tpp']) ? $_GET['tpp'] : 5));

// ppp/tpp override
if (isset($ppp)) {
  $forum->setPpp($ppp);
}

if (isset($tpp)) {
  $forum->setTpp($tpp);
}

$noNewsQuad = true;
if (LOCATION == 'intranet' && (!isset($noNewsQuad) || !$noNewsQuad)) {
  // Im Intranet gibt es die Möglichkeit, News als Quad-Anzeige darzustellen
  
  // Liste der Foren holen
  $boards = $forum->boardList(BT_NEWS);

  // übergebenes Board in den anzeigbaren Boards?    
  if (isset($_GET['newsBoard']) && is_numeric($_GET['newsBoard']) && isset($boards[$_GET['newsBoard']]))
    $newsBoard = $_GET['newsBoard'];   
  else if (isset($_GET['newsID']) && is_numeric($_GET['newsID']) && $forum->threadExists($_GET['newsID'])) {
    // News ausgewählt, boardId holen  
    $news = Thread::load($_GET['newsID']);
    $newsBoard = Board::load($news->boardId);
  }

  // Foren als Leiste anzeigen
  $smarty = new PelasSmarty('forum');
  
  $smarty->assign('boards', $boards);
  $smarty->assign('currentBoard', (isset($newsBoard) ? $newsBoard : 0));
  $smarty->display('news_boardbar.tpl');

  echo "<br><br>";
}

if (!isset($_REQUEST['action'])) {
  if (LOCATION == 'internet') {
	
    $forum->showBoard($newsBoard, (isset($_GET['page_forum']) ? $_GET['page_forum'] : 0), (isset($newsLimiter) ? $newsLimiter : 0));
  } else if (!isset($newsBoard)) {
    $forum->showNewsOverview();
  } else {
    $forum->showBoard($newsBoard);
  }
} else if (isset($_REQUEST['post'])) {
  if (isset($_REQUEST['action'])) {
    switch($_REQUEST['action']) {
      case 'edit':
        $forum->edit($_REQUEST['post']);
        break;
      case 'submit':
        $forum->submit(NULL, NULL, $_REQUEST['post'], $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $sLogin);
        break;
      case 'changemode':
        $forum->changemode($_REQUEST['post'], $_REQUEST['mode']);
        break;
      default:
        echo "nicht unterstütze Action";
    }
  }
} else {  
  switch ($_REQUEST['action']) {
    case 'showComments':
      $forum->setDesign(DESIGN_NEWSCOMMENTS);
      $forum->showThread($_REQUEST['newsID'], (isset($_GET['offset']) ? $_GET['offset'] : 0));
      break;
    case 'addComment':
        $forum->form(NULL, $_REQUEST['newsID'], NULL, NULL, isset($_REQUEST['postToQuoteId']) ? $_REQUEST['postToQuoteId'] : NULL);
      //$forum->form(0, $_REQUEST['newsID']);
      break;
    case 'submit':
      $forum->submit(NULL, $_REQUEST['thread'], NULL, $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $sLogin);
      break;
    default:
      echo "nicht unterstütze Action";
  }
}

?>
