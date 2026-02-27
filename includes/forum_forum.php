<?php

require_once 'dblib.php';
require_once 'new_forum.php';
if (LOCATION == 'internet')
  include_once "session.php";

$forum = new forum($nPartyID, $_SERVER['PHP_SELF']);
$forum->setPpp((isset($_GET['ppp']) ? $_GET['ppp'] : 15));
$forum->setTpp((isset($_GET['tpp']) ? $_GET['tpp'] : 30));

if (isset($_REQUEST['board'])) {
  if (!isset($_REQUEST['action'])) {
    $forum->showBoard($_REQUEST['board'], (isset($_GET['page_forum']) ? $_GET['page_forum'] : 0));
  } else {
    switch ($_REQUEST['action']) {
      case 'add':
        $forum->form($_REQUEST['board']);
        break;
      case 'submit': 
        $forum->submit($_REQUEST['board'], NULL, NULL, $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $sLogin);
        break;
      case 'close':
        $forum->close($_REQUEST['board'], $_REQUEST['thread']);
        break;
      case 'markAllThreadsRead':
        $forum->markAllThreadsReadInBoard($_REQUEST['board']);
        break;
      default:
        echo "nicht unterstütze Action";
    }
  }
} else if (isset($_REQUEST['thread'])) {
  if (!isset($_REQUEST['action'])) {
    $forum->showThread($_REQUEST['thread'], (isset($_GET['page_forum']) ? $_GET['page_forum'] : 0));
  } else {
    switch ($_REQUEST['action']) {
      case 'add':
        $forum->form(NULL, $_REQUEST['thread'], NULL, NULL, isset($_REQUEST['postToQuoteId']) ? $_REQUEST['postToQuoteId'] : NULL);
        break;
      case 'submit':
        $forum->submit(NULL, $_REQUEST['thread'], NULL, $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $sLogin);
        break;
      case 'moveThread':
        $forum->moveThread($_REQUEST['thread'], $_REQUEST['dstBoardID']);
        break;
      default: 
        echo "nicht unterstütze Action";
    }
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
} else
  if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'search') {
    $forum->search(trim($_POST['value']));
  } else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'markAllThreadsRead') {
    $forum->markAllThreadsRead();
  } else {
    $forum->listBoards();
  }
  
/*if ($nLoginID > 0 && User::hatRecht('FORENADMIN', $nLoginID, $nPartyID)) {
	echo "<!--\n";
	DB::outputQueryStatistic();
	echo "\n-->";
}*/
?>
