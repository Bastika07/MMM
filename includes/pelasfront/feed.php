<?php
include_once "dblib.php";
include_once "format.php";
if (LOCATION == 'internet')
  include_once "session.php";
include_once 'classes/PelasSmarty.class.php';
include_once 'classes/User2BeamerMessage.class.php';
include_once 'new_forum.php';

$smarty = new PelasSmarty();
$smarty->appName = 'feed';


switch($_GET['content']) {
  case 'news':
    $forum = new forum(MANDANTID, '', DESIGN_NEWS, BT_NEWS);
    $news = $forum->forumActivity(12);

    header('Content-type: application/atom+xml; charset=UTF-8');

    $smarty->assign('baseUrl', 'http://batterix.dyndns.org:81/');
    $smarty->assign('newsArray', $news);
    $smarty->assign('updated', $news[0]['time']);
    $smarty->displayWithFallback('news.tpl');
  break;
  case 'user2beamer':
    $messages = User2BeamerMessage::loadAll();    
  
    header('Content-type: application/atom+xml; charset=UTF-8');

    $smarty->assign('baseUrl', 'http://batterix.dyndns.org:81/');
    $smarty->assign('messages', $messages);
    $smarty->assign('updated', $messages[0]->approvedAt);
    $smarty->displayWithFallback('user2beamer.tpl');

  break;
}

?>
