<?php
require('controller.php');
$iRecht = 'NEWSADMIN';
require_once 'dblib.php';
require_once 'new_forum.php';
include_once 'checkrights.php';
require_once 'classes/Board.class.php';
include_once 'admin/vorspann.php';

if (!isset($dbh))
	$dbh = DB::connect();

define('MANDANTID', 'admin');

$nLoginID = $loginID;

$sql = "SELECT m.MANDANTID, m.BESCHREIBUNG 
        FROM MANDANT m, RECHTZUORDNUNG r 
        WHERE r.USERID = " . $loginID . "
		AND r.MANDANTID = m.MANDANTID
		AND r.RECHTID = 'NEWSADMIN'";   
$res = DB::query($sql);         

while ($row = mysql_fetch_assoc($res)) {
  # Liste der Foren holen.
  $sql = 'SELECT name, boardID
          FROM forum_boards
          WHERE type = ' . BT_NEWS . '
	  	AND mandantID = ' . $row['MANDANTID'];
  $res2 = DB::query($sql);
  while ($row2 = mysql_fetch_assoc($res2))
    $boards[$row2['boardID']] = $row2['name'];
}

$forum = new forum($nPartyID, $_SERVER['PHP_SELF'], DESIGN_NEWSADMIN, BT_NEWS);
if (User::hatRecht('NEWSADMIN'))
  $forum->isAdmin = 1;

if (!isset($_REQUEST['action'])) {
	if ($_GET['showAll'] and $_GET['showAll'] == 'true')
	  $forum->adminNewsList($boards, true);
  else
    $forum->adminNewsList($boards, false);
} else if (isset($_REQUEST['post'])) {
  if (isset($_REQUEST['action'])) {
    switch($_REQUEST['action']) {
      case 'edit': 
        $forum->edit($_REQUEST['post']);
        break;
      case 'submit':      
        $forum->submit(NULL, NULL, $_REQUEST['post'], $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $login, array(), $_REQUEST['helperstring'], $_REQUEST['title_en'], $_REQUEST['content_en']);
        break;
      case 'changemode':
        $forum->changemode($_REQUEST['post'], $_REQUEST['mode']);
        break;
      default: 
        echo 'nicht unterstütze Action';
    }    
  }
} else {
  switch ($_REQUEST['action']) {
    case 'add':  
      $forum->form($_REQUEST['board']);
      break;
    case 'showComments':
      $forum->setDesign(DESIGN_NEWSCOMMENTS);
      $forum->showThread($_REQUEST['newsID'], (isset($_GET['ppp']) ? $_GET['ppp'] : 0), (isset($_GET['offset']) ? $_GET['offset'] : 0));
      break;
    case 'submit':      
      $forum->submit($_REQUEST['board'], NULL, NULL, $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $login, array(), $_REQUEST['helperstring'], $_REQUEST['title_en'], $_REQUEST['content_en']);
      break;
    default: 
      echo 'nicht unterstütze Action';
  }
}

?>
