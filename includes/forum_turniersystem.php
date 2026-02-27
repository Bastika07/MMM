<?php

require_once 'new_forum.php';

$turnierBoard = 6;




$forum = new forum($nPartyID, $_SERVER['PHP_SELF'], DESIGN_COMMENTS, BT_TURNIERCOMMENTS);

if (!isset($_REQUEST['action'])) {
  echo "Hier sind die krassen Kommentare:";
  if (!empty($commentID)) {
    $forum->showThread($commentID, DESIGN_COMMENTS);
    echo "<br><a href=\"$_SERVER[PHP_SELF]?action=addComment&thread=$commentID\">Kommentar abgeben</a>";    
  } else     
    echo "<br><a href=\"$_SERVER[PHP_SELF]?action=addComment&turnier=1&runde=2&spiel=3\">Kommentar abgeben</a>";    
} else {
  switch ($_REQUEST['action']) {
    case 'addComment':
      // wenn noch keine commentID bekannt ist, ist dies der erste Comment -> Thread erstellen
      if (empty($_REQUEST['thread']))
        $forum->form($turnierBoard, 0, array('turnier' => $_REQUEST['turnier'], 'runde' => $_REQUEST['runde'], 'spiel' => $_REQUEST['spiel']));
      else
        $forum->form(0, $_REQUEST['thread']);
      break;
    case 'submit':
      $board = isset($_REQUEST['board']) ? $_REQUEST['board'] : '';
      $thread = isset($_REQUEST['thread']) ? $_REQUEST['thread'] : '';

      $newID = $forum->submit($board, $thread, $_REQUEST['title'], $_REQUEST['content'], $nLoginID, $loginName);
      // wurde ein neuer Thread angelegt, ist folgendes der Fall:
      // $rc['newThread']=> ID des neuen Threads 
      // $rc['oldThread']=> -1
      // wurde nur ein Post angelegt, ist folgendes der Fall:
      // $rc['newThread']=> -1
      // $rc['oldThread']=> ID des Threads, zu dem der Post hinzugefügt wurde  
      break;
    default:
      echo "nicht unterstütze Action";
      break;
  }
}

?>