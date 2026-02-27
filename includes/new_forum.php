<?php

// TODO: Admin-Funktions-Bestätigung mit Template

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once('dblib.php');

require_once('classes/PelasSmarty.class.php');


require_once('classes/Post.class.php');
require_once('classes/Thread.class.php');
require_once('classes/Board.class.php');

// Standard-Textkonstanten fürs Forum
define('T_NOTLOGGEDIN',         'Bitte logge dich zuerst ein! <br> <a href="?page=5"><img src="gfx/headline_pfeil.gif" border=0> Login</a>');
define('T_INVALIDTHREAD',       'Ungültiger Thread.');
define('T_INVALIDBOARD',        'Ungültiges Board.');
define('T_INVALIDPOST',         'Ungültiger Post.');
define('T_INVALIDMODE',         'Ungültiger Mode.');
define('T_THREADCLOSED',        'Dieser Thread ist geschlossen, keine weiteren Posts möglich.');
define('T_THREADHIDDEN',        'Dieser Thread ist versteckt, keine weiteren Posts möglich.');
define('T_NOTAUTHOR',           'Du bist nicht der Autor von diesem Post.');
define('T_BOARDCLOSED',         'Board gesperrt.');
define('T_ACCESSDENIED',        'Zugriff nicht erlaubt!');
define('T_NOTLASTPOSTINTHREAD', 'Der angegebene Post ist nicht der letzte im Thread.');
define('T_DSTBOARD_EQUALS_CURRENT_BOARD', 'Der Thread befindet sich schon im angegebenen Board.');
define('T_MOVE_BOARD_FAILED',   'Verschieben fehlgeschlagen');


// Config
// Markierungen, welcher Thread für den eingeloggten neu ist
define('C_READMARKS', true);
// Seitenaufteilung: wieviele am Anfang, wieviele am Ende auf jeden Fall anzeigen
define('C_PAGES_BEGIN', 1);
define('C_PAGES_END', 5);
// ab wieviel Seiten soll gestrichen werden
define('C_PAGES_THRESHOLD', 15);

class forum {
  var $mandantID;
  var $boardID;
  var $threadID;
  var $postID;
  // welche Boardtypen dürfen angezeigt werden?
  var $boardType;
  // z.B. forum.php
  // hier wird dann noch die ThreadID angehängt: forum.php?thread=25
  var $redirectBase;
  // Anpassungen für Forum und News, Newscomments
  var $design;
  var $smarty;
  var $icons;
  var $smileys;
  var $isAdmin;
  var $isNewsAdmin;
  var $userID;
  var $isLoggedIn;
  var $time;
  
  var $ppp = 15;
  var $tpp = 30;

  function forum($mandantID, $redirectBase, $design = DESIGN_FORUM, $boardType = BT_FORUM) {
    global $nLoginID;

    $this->time = time();
    $this->mandantID    = $mandantID;
    $this->redirectBase = $redirectBase;
    $this->design       = $design;
    $this->boardType    = $boardType;
    $this->userID       = $nLoginID;

    $this->isLoggedIn = ($this->userID > 0);
    $this->isAdmin    = ($this->isLoggedIn && User::hatRecht('FORENADMIN', $this->userID, $this->mandantID) ? true : false);
    $this->isNewsAdmin = ($this->isLoggedIn && User::hatRecht('NEWSADMIN', $this->userID, $this->mandantID) ? true : false);

    $this->smarty = new PelasSmarty('forum');

		# Hack für unterschiedliches Linkbuilding Admin / Frontend
		if (strpos($_SERVER['SCRIPT_FILENAME'], "admin") === true)
			$url = $_SERVER['PHP_SELF']."?g=1";
		else if (isset($_GET['page']) && $_GET['page'] == 2) // Quick Hack: Newskommentare setzen einen anderen Filename
			$url = $_SERVER['PHP_SELF']."?page=2";
		else if (isset($_GET['page']) && $_GET['page'] == 26) // Quick Hack: Turnierkommentare setzen einen anderen Filename
			$url = $_SERVER['PHP_SELF']."?page=26";
		else
			$url = $_SERVER['PHP_SELF']."?page=12";
    $this->smarty->assign('filename', $url);

	    $this->smarty->assign('admin',      $this->isAdmin);
    $this->smarty->assign('isAdmin',    $this->isAdmin);
    $this->smarty->assign('isLoggedIn', $this->isLoggedIn);
    $this->smarty->assign('userID',     $this->userID);
    $this->smarty->assign('mandantID',  $this->mandantID);

    $this->smarty->assign('pelasHost', PELASHOST);
    $this->smarty->assign('pelasDir',  PELASDIR);

    $this->smarty->assign('smileyDir', SMILEYDIR);
    $this->smarty->assign('smileySuffix', '?time='.$this->time);
    
    $this->initSmileys();

    $this->icons[0][0][0] = 'alter_thread.jpg';
    $this->icons[0][0][1] = 'alter_thread_closed.jpg';
    $this->icons[0][1][0] = 'alter_thread.jpg';
    $this->icons[0][1][1] = 'alter_thread_closed.jpg';
    $this->icons[1][0][0] = 'neuer_thread.jpg';
    $this->icons[1][0][1] = 'neuer_thread_closed.jpg';
    $this->icons[1][1][0] = 'neuer_thread.jpg';
    $this->icons[1][1][1] = 'neuer_thread_closed_hot.jpg';

    $this->smarty->assign('icons', $this->icons);
  }

  function setDesign($design) {
    /*
     * Setzt das Design fest
     */
    $this->design = $design;
  }

	function setPpp($ppp) {
		if (is_numeric($ppp) && $ppp > 0)
			$this->ppp = $ppp;
	}

	function setTpp($tpp) {
		if (is_numeric($tpp) && $tpp > 0)
			$this->tpp = $tpp;
	}
	
  function initSmileys() {
    $this->smileys = array(":)" => "sm1.gif",
                           ";)" => "sm2.gif",
                           ":D" => "sm3.gif",
                           ":\(" => "sm4.gif",
                           ":-X" => "sm5.gif",
                           ":-zzz" => "sm6.gif",
                           ":-rtfm" => "sm7.gif",
                           "%-)" => "sm8.gif",
                           ":-xxx" => "sm9.gif",
                           ":-no" => "sm10.gif",
                           ":-yes" => "sm11.gif",
                           ":-ah" => "sm12.gif",
                           ":-nixpeil" => "sm13.gif",
                           ":-grr" => "sm14.gif",
                           ":-zap" => "sm15.gif",
                           ":-hae" => "sm16.gif",
                           ":rolleyes:" => "sm17.gif",
                           ":P" => "sm18.gif",
                           ":gay:" => "sm19.gif",
                           ":ohh:" => "sm20.gif",
                           ":gaga:" => "sm21.gif",
                           ":kicker:" => "sm21.png",
                           ":kotz:" => "sm22.gif",
                           ":lach:" => "sm23.gif",
                           ":stink:" => "sm24.gif");
  }

  function search($value) {
    if (strlen($value) < 5) {
      $this->smarty->assign('error', "Suchwort muss mindestens 5 Zeichen lang sein.");
    } else {
      $value = str_replace('%', '\%', $value);
      $value = str_replace('_', '\_', $value);
      $this->smarty->assign('value', $value);

      $data[BT_FORUM] = $this->searchAndReturnResult($value, BT_FORUM);
      $data[BT_NEWS] = $this->searchAndReturnResult($value, BT_NEWS);
      $this->smarty->assign('data', $data);
    }
    $this->smarty->displayWithFallback('search.tpl');
  }

  function searchAndReturnResult($value, $type) {
      $sql = "SELECT
                c.contentId, c.content, IF (c.parent = -1, c.contentId, c.parent) parent,
                IF (c.parent = -1, c.title, c2.title) title, b.type
              FROM
                forum_content c
              LEFT JOIN
                forum_content c2 ON c.parent = c2.contentId
              LEFT JOIN
                forum_boards b ON c.boardId = b.boardId
              WHERE
                (
			c.content LIKE '%".DB::$link->real_escape_string($value)."%'
			OR
			c.title LIKE '%".DB::$link->real_escape_string($value)."%'
		) AND
		(c.parent = -1 && c.hidden = 0 || c2.hidden = 0) AND
		b.mandantId = {$this->mandantID} AND
 		b.type = $type
		ORDER BY
		  c.contentID DESC
                LIMIT 20";
      if (!$res = DB::query($sql))
        echo "DB-Fehler";
      else {
        $data = array();
        // Daten in Array einlesen, an Smarty übergeben
        while ($row = $res->fetch_assoc()) {
          if (strpos($row['content'], $value) === false) {
           // $value nicht im Content gefunden. Dies kann passieren, wenn das Suchwort im Titel,
           // aber nicht im Text gefunden wurde. Hier die ersten 100 Zeichen des Contents anzeigen
           $row['content'] = substr($row['content'], 0, 100).'...';
          } else {
            // escapen
          	$escapedValue = preg_quote($value, '/');          
            preg_match_all("/(.{0,50}$escapedValue.{0,50})/i", $row['content'], $m);
            $m = preg_replace("/($escapedValue)/i", "[b]\\1[/b]", $m[1]);
            $row['content'] = '...'.implode("...", $m).'...';
          }
          $row['pageForPost'] = $this->threadPageForPost($row['parent'], $row['contentId'], $this->ppp);
          $data[] = $row;
        }
      }
    return $data;
  }

  function listBoards() {
    /*
     *  Boards des Mandanten $mandantID anzeigen
     */

    $sql = "SELECT
              boardID, name, description, threads, posts, lastpost, lastposterID, lastposterName
            FROM
              forum_boards
            WHERE
              mandantID = $this->mandantID AND
              closed = 0 AND
              hidden = 0 AND
              type = ".BT_FORUM."
            ORDER BY
              boardID";
    if (!$res = DB::query($sql))
      echo "DB-Fehler";
    else {
      $data = array();
      $boardIds = array();
      // Daten in Array einlesen, an Smarty übergeben
      while ($row = $res->fetch_assoc()) {
       	//$row['new'] = $this->newThreadInBoard($row['boardID']);
       	$boardIds[] = $row['boardID'];
        array_push($data, $row);
      }
      
      $boardsNewArray = $this->boardsAreNew($boardIds);
      
      foreach ($data as $key => $row) { 	
        	$data[$key]['new'] = $boardsNewArray[$row['boardID']];
      }
      
      $this->smarty->assign('data', $data);
      $this->smarty->displayWithFallback('forum.tpl');
    }
  }

  function adminNewsList($boardList = NULL, $showAll = false) {
    $data = array();

    if ($boardList == NULL) {
      // Liste der Boards vom Typ "BT_NEWS" holen
      $boardList = $this->boardList(BT_NEWS);
    }

    // Für jedes Board die News holen
    foreach($boardList as $boardID => $boardName) {
      $sql = "SELECT
                c.*, m.REFERER
              FROM
                forum_content AS c, forum_boards AS b, MANDANT AS m
              WHERE
                c.boardID = b.boardID AND
                b.mandantID = m.MANDANTID AND
                c.parent = -1 AND
                c.boardID = $boardID
              ORDER BY
                c.time DESC";
      if (!$showAll)
        $sql .= " LIMIT 5";
      if ($res = DB::query($sql)) {
        while ($row = $res->fetch_assoc()) {
          if (!isset($data[$boardID])) {
            $data[$boardID] = array();
          }
          array_push($data[$boardID], $row);
        }
      }
    }
		$this->smarty->assign('showAll', $showAll);
    $this->smarty->assign('data', $data);
    $this->smarty->assign('boardList', $boardList);
    $this->smarty->displayWithFallback('newsadmin.tpl');
  }

  function showNewsOverview($short = 150) {
    /*
     * Zeigt aus allen 4 Newskategorien (Allgemein, Animation, Turniere, Technik)
     * die aktuellste News an
     * $short: Wieviele Zeichen jeder News sollen maximal angezeigt werden?
     */

    $data = array();

    // Liste der Boards vom Typ "BT_NEWS" holen
    $boardList = $this->boardList(BT_NEWS);

    // Für jedes Board die aktuellste News holen
    foreach($boardList as $boardID => $boardName) {
      $sql = "SELECT
                *
              FROM
                forum_content
              WHERE
                parent = -1 AND
                hidden = 0 AND
                boardID = $boardID
              ORDER BY
                time DESC
              LIMIT 1";
      if (!($res = DB::query($sql)) || $res->num_rows != 1) {
        // DB-Fehler oder keine entsprechende News für die Kategorie
        $this->smarty->assign($boardName, array('hidden' => 1, 'boardID' => $boardID));
      } else {
        $row = $res->fetch_assoc();
        $this->smarty->assign($boardName, $row);
      }
    }
    $this->smarty->assign('short', $short);
    $this->smarty->assign('boardList', $boardList);
    $this->smarty->displayWithFallback('newsoverview.tpl');
  }

  function boardList($type = BT_FORUM) {
    /*
     * Array mit Namen und boardID der Boards des typs $type zurückgeben
     */
    $rc = array();
    $sql = "SELECT
              boardID, name
            FROM
              forum_boards
            WHERE
              type = '$type' AND
              mandantID = '$this->mandantID'
            ORDER BY
              boardID";
    if (!$res = DB::query($sql))
      echo "DB-Fehler";
    else {
      while ($row = $res->fetch_assoc()) {
        $rc[$row['boardID']] = $row['name'];
      }
    }
    return $rc;
  }


  function showBoardsOfType($type, $limit = 0, $short = 0) {
    /*
     * Zeigt Kurzform der Boards an:
     * $limit: Anzahl der Threads
     * $short: Anzahl der anzuzeigenden Zeichen
     */
    $sql = "SELECT
              boardID, name
            FROM
              forum_boards
            WHERE
              type = '$type'
            ORDER BY
              boardID";
    $res = DB::query($sql);
    $boards = array();
    $data = array();
    while ($row = $res->fetch_row())
      array_push($boards, $row);
    foreach ($boards as $val) {
      $sql = "SELECT
                c.contentID, c.title, c.content, c.authorID,
                c.authorName, c.posts, c.time, c.hidden
              FROM
                forum_content AS c, forum_boards AS b
              WHERE
                c.boardID = '$val[0]' AND
                b.boardID = c.boardID AND
                b.mandantID = $this->mandantID AND
                c.parent = -1
              ORDER BY
                c.boardID ASC, time DESC ";
      if ($limit)
        $sql .= "LIMIT $limit";
      if (!$res = DB::query($sql)) {
        echo "DB-Fehler";
      } else {
        while ($row = $res->fetch_assoc()) {
          $row['boardName'] = $val[1];
          array_push($data, $row);
        }
      }
    }
    $this->smarty->assign('short', $short);
    $this->smarty->assign('data', $data);
    if ($this->design == DESIGN_NEWS)
      $this->smarty->displayWithFallback('news_quad.tpl');
    else if ($this->design == DESIGN_NEWSADMIN)
      $this->smarty->displayWithFallback('newsadmin.tpl');
  }

  function showBoard($boardID, $currentPage = 0, $limit = false) {
    /*
     *  Board $boardID anzeigen
     */
    if (!$boardID || (!is_numeric($boardID) || !$this->boardExists($boardID))) {
      $emsg = T_INVALIDBOARD;
    } else if ($this->boardStatus($boardID) & BOARD_CLOSED) {
      $emsg = T_BOARDCLOSED;
      // es dürfen nur Boards angezeigt werden, die dem aktuellen Type entsprechen
    } else if ($this->boardType($boardID) != $this->boardType) {
      $emsg = T_ACCESSDENIED;
    } else {
      $sql = "SELECT
                c.contentID, c.title, c.title_en, c.content, c.content_en, c.authorID,
                c.authorName, c.posts, c.time, c.lastPost, c.sticky, c.hidden, c.closed,
                c.lastposterName, c.lastposterID, c.helperstring
              FROM
                forum_content AS c, forum_boards AS b
              WHERE
                c.boardID = '$boardID' AND
                b.boardID = c.boardID AND
                b.mandantID = $this->mandantID AND
                c.parent = -1
                ".(!$this->isAdmin || $this->design == DESIGN_NEWS ? ' AND c.hidden = 0 AND (c.planned = 0 OR (c.planned = 1 AND UNIX_TIMESTAMP() >= c.timeplanned)) ' : ' ')."
              ORDER BY ";
      if ($this->design == DESIGN_FORUM)
        $sql .= "sticky DESC, lastPost DESC ";
      else
        $sql .= "time DESC ";

			if ($limit > 0)
				$sql .= "LIMIT ".intval($limit)." ";

			// Blättern. Aber nur, wenn kein festes Limit angegeben wurde
      if ($limit <= 0 && $this->tpp && ($threadCount = $this->threadCount($boardID, !$this->isAdmin)) > $this->tpp) {
        // Seiteneinteilung
        $pageCount = ceil($threadCount / $this->tpp);
        // Wurde keine Seite oder eine ungültige Seite angegeben, wird auf die erste Seite gesprungen
        if ($currentPage == 0 || $currentPage > $pageCount || $currentPage < 1)
          $currentPage = 1;
        $offset = ($currentPage-1) * $this->tpp;
				$sql .= "LIMIT $offset, {$this->tpp}";

        $pagesBorder = 4;
        $pagesBuffer = 3;

        $pages = $this->createPagePartition($pageCount, $currentPage, $pagesBorder, $pagesBuffer);

        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('pageCount', $pageCount);
        $this->smarty->assign('currentPage', $currentPage);
				
      }

      if (!$res = DB::query($sql))
        echo "DB-Fehler";
      else {
        $data = array();
        $threadIds = array();
        // Daten in Array einlesen, an Smarty übergeben
        while ($row = $res->fetch_assoc()) {
          // Ist der Thread ungelesen?
          $threadIds[] = $row['contentID'];
          // Ist der Thread "hot"?
          $row['hot'] = ($row['posts'] >= 25) ? 1 : 0;

          $postCount = $this->isAdmin && isset($row['hiddenposts']) ? $row['posts'] + $row['hiddenposts'] : $row['posts'];
          $pageCount = ceil($postCount / $this->ppp);

          $pagesBorder = 4;
          $pagesBuffer = 3;

          $row['pages'] = $this->createPagePartition($pageCount, $pageCount, $pagesBorder, $pagesBuffer);

          array_push($data, $row);
        }
        
        $threadNewArray = $this->threadsAreNew($threadIds);
        
        
				foreach ($data as $key => $row) { 	
        	$data[$key]['new'] = $threadNewArray[$row['contentID']];
      	}

        $this->smarty->assign('data', $data);
        $this->smarty->assignByRef('board', Board::load($boardID));        
        
        if ($this->design == DESIGN_FORUM) {
          $this->smarty->displayWithFallback('forum_board.tpl');
        } else if ($this->design == DESIGN_NEWS) {
            $this->smarty->assign('boardList', $this->boardList(BT_NEWS));
            $this->smarty->displayWithFallback('news.tpl');
        }

      }
    }
    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
  }

  function showBoardNew($boardID, $currentPage = 0, $limit = false) {
    /*
     *  Board $boardID anzeigen
     */
    if (!$boardID || (!is_numeric($boardID) || !$this->boardExists($boardID))) {
      $emsg = T_INVALIDBOARD;
    } else if ($this->boardStatus($boardID) & BOARD_CLOSED) {
      $emsg = T_BOARDCLOSED;
      // es dürfen nur Boards angezeigt werden, die dem aktuellen Type entsprechen
    } else if ($this->boardType($boardID) != $this->boardType) {
      $emsg = T_ACCESSDENIED;
    } else {
      $sql = "SELECT
                c.contentID, c.title, c.title_en, c.content, c.content_en, c.authorID,
                c.authorName, c.posts, c.time, c.lastPost, c.sticky, c.hidden, c.closed,
                c.lastposterName, c.lastposterID, c.helperstring
              FROM
                forum_content AS c, forum_boards AS b
              WHERE
                c.boardID = '$boardID' AND
                b.boardID = c.boardID AND
                b.mandantID = $this->mandantID AND
                c.parent = -1
                ".(!$this->isAdmin || $this->design == DESIGN_NEWS ? ' AND c.hidden = 0 ' : ' ')."
              ORDER BY ";
      if ($this->design == DESIGN_FORUM)
        $sql .= "sticky DESC, lastPost DESC ";
      else
        $sql .= "time DESC ";

			if ($limit > 0)
				$sql .= "LIMIT ".intval($limit)." ";

			// Blättern. Aber nur, wenn kein festes Limit angegeben wurde
      if ($limit <= 0 && $this->tpp && ($threadCount = $this->threadCount($boardID, !$this->isAdmin)) > $this->tpp) {
        // Seiteneinteilung
        $pageCount = ceil($threadCount / $this->tpp);
        // Wurde keine Seite oder eine ungültige Seite angegeben, wird auf die erste Seite gesprungen
        if ($currentPage == 0 || $currentPage > $pageCount || $currentPage < 1)
          $currentPage = 1;
        $offset = ($currentPage-1) * $this->tpp;
				$sql .= "LIMIT $offset, {$this->tpp}";

        $pagesBorder = 4;
        $pagesBuffer = 3;

        $pages = $this->createPagePartition($pageCount, $currentPage, $pagesBorder, $pagesBuffer);

        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('pageCount', $pageCount);
        $this->smarty->assign('currentPage', $currentPage);
				
      }

      if (!$res = DB::query($sql))
        echo "DB-Fehler";
      else {
        $data = array();
        $threadIds = array();
        // Daten in Array einlesen, an Smarty übergeben
        while ($row = $res->fetch_assoc()) {
          // Ist der Thread ungelesen?
          $threadIds[] = $row['contentID'];
          // Ist der Thread "hot"?
          $row['hot'] = ($row['posts'] >= 25) ? 1 : 0;

          $postCount = $this->isAdmin && isset($row['hiddenposts']) ? $row['posts'] + $row['hiddenposts'] : $row['posts'];
          $pageCount = ceil($postCount / $this->ppp);

          $pagesBorder = 4;
          $pagesBuffer = 3;

          $row['pages'] = $this->createPagePartition($pageCount, $pageCount, $pagesBorder, $pagesBuffer);

          array_push($data, $row);
        }
        
        $threadNewArray = $this->threadsAreNew($threadIds);
        
        
				foreach ($data as $key => $row) { 	
        	$data[$key]['new'] = $threadNewArray[$row['contentID']];
      	}

        $this->smarty->assign('data', $data);
        $this->smarty->assignByRef('board', Board::load($boardID));        
        
        if ($this->design == DESIGN_FORUM) {
          $this->smarty->displayWithFallback('forum_board.tpl');
        } else if ($this->design == DESIGN_NEWS) {
            $this->smarty->assign('boardList', $this->boardList(BT_NEWS));
            $this->smarty->displayWithFallback('news.tpl');
        }

      }
    }
    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
  }

  function showThread($threadID, $currentPage = 0) {
    /*
     *  Thread $threadID anzeigen
     */

//    if (empty($threadID) || $threadID && (!is_numeric($threadID) || !$this->threadExists($threadID))) {
    if (empty($threadID) || !is_numeric($threadID) || ($thread = Thread::load($threadID)) == null) {
      $emsg = T_INVALIDTHREAD;
    } else {
      $board = Board::load($thread->boardId);
      if ($this->boardType != $board->type) {
        // Thread wird im falschen Context angezeigt (z.B. news im Forum)
        $emsg = T_ACCESSDENIED;
      } else if ($thread->hidden && ($board->type != BT_NEWS && !$this->isAdmin || $board->type == BT_NEWS && !$this->isNewsAdmin)) {
        // Admin darf auch gucken, wenn versteckt ist
        $emsg = T_ACCESSDENIED;
      } else if ($board->mandantId != $this->mandantID) {
        $emsg = T_ACCESSDENIED;
      } else {
            // Kopfdaten an Smarty übergeben
            $this->smarty->assignByRef('thread', $thread);
            $this->smarty->assignByRef('board', $board);

            // Der Admin kriegt alle Boards zur Auswahl um Threads verschieben zu können
            if ($this->isAdmin) {
              $this->smarty->assign('boards', $this->boardList());
            }


            $postCount = $this->isAdmin ? $thread->posts + $thread->hiddenposts : $thread->posts;
            $pageCount = ceil($postCount / $this->ppp);

            if ($currentPage == 0) {
              // zum neuesten Post für den User springen. Ist er nicht eingeloggt, zum letzten Post gehen
              $firstNewPostId = $this->threadFirstNewPost($thread->id);
              if ($firstNewPostId == 0)
                $currentPage = $pageCount;
              else
                $currentPage = $this->threadPageForPost($thread->id, $firstNewPostId);
			} else {
				$firstNewPostId = 0;
			}

            // Nur Startpost vorhanden, dann ist die Anzahl der Antworten
            // gleich 0 und es existiert nur die erste Seite
            if ($currentPage == 0 && $postCount == 1) {
              $currentPage = 1;
              $pages = array(1 => true);
              $pageCount = 1;
            } else {
               // Wurde keine Seite angegeben, wird auf die letzte Seite gesprungen
              if ($currentPage == 0)
                $currentPage = $pageCount;

              $pagesBorder = 4;
              $pagesBuffer = 3;

              $pages = $this->createPagePartition($pageCount, $currentPage, $pagesBorder, $pagesBuffer);

            }
            $this->smarty->assign('pages', $pages);
            $this->smarty->assign('currentPage', $currentPage);
            $this->smarty->assign('pageCount', $pageCount);

            $this->smarty->assign('ppp', $this->ppp);
            $offset = ($currentPage-1) * $this->ppp;


            // Posts lesen

            if ($board->type == BT_NEWS && $this->isNewsAdmin)
              $showHidden = true;
            else if ($this->isAdmin)
              $showHidden = true;
            else
              $showHidden = false;

            $sql = "SELECT
                      c.contentID, c.title, c.title_en, c.content, c.content_en, c.authorID, c.authorName, c.time,
                      c.lastEdited, c.hidden, u.LOGIN AS 'hiddenBy'
                    FROM
                      forum_content AS c
                    LEFT JOIN
                      USER AS u ON c.hiddenBy = u.USERID
                    WHERE
                      (c.parent = -1 && c.contentID = ".$thread->id." || c.parent = $threadID)
                    ".($showHidden ? '' : ' AND c.hidden = 0')."
                    ORDER BY
                      c.time ASC";

            // Bei News ist der erste Post die News, also Offset einen weitersetzen
            if ($this->boardType == BT_NEWS && $offset == 0)
              $offset = 1;

            if ($this->boardType != BT_NEWS)
              $sql .= "\nLIMIT $offset, {$this->ppp}";

            if (!$res = DB::query($sql))
              echo "DB-Fehler";
            else if ($res->num_rows == 0){
              echo "Diese Seite existiert nicht";
            } else {
              $data = array();
              // array für caching
              $avatar = array();
              $authorClass = array();

              // Daten in Array einlesen, an Smarty übergeben
              while ($row = $res->fetch_assoc()) {
                // Userklasse bestimmen
                if (!isset($authorClass[$row['authorID']])) {
                  if (User::hatRecht("GASTADMIN", $row['authorID'], $this->mandantID))
                    $authorClass[$row['authorID']] = 'Gastadmin';
                  else if (User::hatRecht("TEAMMEMBER", $row['authorID'], $this->mandantID))
                    $authorClass[$row['authorID']] = User::orgaTeam($row['authorID']);
  	              else
                    $authorClass[$row['authorID']] = 'Registrierter Benutzer';
                }

                $row['authorClass'] = $authorClass[$row['authorID']];
  		
				// keiner der posts ist editierbar, bis auf den letzten
				$row['edit'] = 0;

		  		// Auf Avatar prüfen mit Caching
				if (isset($avatar[$row['authorID']])) {
		  		  $row['avatar'] = $avatar[$row['authorID']];
		  		} else {
		  		  $row['avatar'] = $avatar[$row['authorID']] = file_exists(PELASDIR."userbild/$row[authorID].jpg") ? 1 : 0;
		  		}
				$row['passes'] = $this->getSupporterPasses($row['authorID']);

		  		// Post neu?
		  		if ($firstNewPostId != 0 && $firstNewPostId <= $row['contentID'])
		  		  $row['isNew'] = true;
		  		else
		  		  $row['isNew'] = false;

		  		$data[] = $row;
              }
              // Author des letzten Posts ist Author, darf editieren
              if ($this->userID == $data[count($data)-1]['authorID'] &&
                  $thread->lastPostID == $data[count($data)-1]['contentID'])
                $data[count($data)-1]['edit'] = 1;

		// letzter Post wird entsprechend markiert
		if ($thread->lastPostID == $data[count($data)-1]['contentID'])
                  $data[count($data)-1]['isLast'] = 1;

              // Thread als gelesen markieren, bis letzten angezeigten Post
              $this->threadMarkReadToPost($thread->id, $data[count($data)-1]['contentID']);

              $this->smarty->assign('data', $data);

              if ($this->design == DESIGN_FORUM) {
                $template = 'forum_thread.tpl';
              } else if ($this->design == DESIGN_NEWSCOMMENTS) {
              	$this->smarty->assign('boardList', $this->boardList(BT_NEWS));
                $template = 'news_comments.tpl';
              } else if ($this->design == DESIGN_COMMENTS) {
                $template = 'comments.tpl';
              }
              $this->smarty->displayWithFallback($template);
            }
        }
      }

    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
  }

  function edit($contentID) {
    /*
     *  Editieren von $contentID
     */

    if (!isset($contentID) || !is_numeric($contentID)) {
      $emsg = T_INVALIDPOST;
    } else if (!$this->isAdmin && $this->lastPostInThread($this->threadIDByPost($contentID)) != $contentID && $this->design != DESIGN_NEWSADMIN) {
      $emsg = T_NOTLASTPOSTINTHREAD;
    } else if (!$this->isAdmin && $this->postAuthor($contentID) != $this->userID && $this->design != DESIGN_NEWSADMIN) {
      $emsg = T_NOTAUTHOR;
    } else {
      $this->form(NULL, NULL, $contentID);
    }

    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
  }

  function form($boardId = NULL, $threadId = NULL, $postId = NULL, $data = array(), $postToQuoteId = NULL) {
    /*
     *  Gibt Formular für add und edit von Posts und Threads
     *  $tb: ID des Boards bzw Threads, in dem der Thread bzw Post angelegt werden soll
     *  $board wird angegeben, wenn noch kein Thread bekannt ist, also einer gestartet werden soll
     *  $thread wird angegeben, wenn der Thread bekannt ist, und somit folgeposts enstehen sollen
     *  $data: Variablen, die mit durchgegeben werden, damit später eine Verbindung zwischen neuem Thread
     *     und Entity, zu der der Kommentar abgegeben wurde, hergestellt werden kann
     */

    if (!$this->isLoggedIn) {
      $emsg = T_NOTLOGGEDIN;
    } else if (isset($threadId) && (!is_numeric($threadId) || !$this->threadExists($threadId))) {
      $emsg = T_INVALIDTHREAD;
    } else if (isset($boardId) && (!is_numeric($boardId) || !$this->boardExists($boardId))) {
      $emsg = T_INVALIDBOARD;
    } else if (isset($postId) && (!is_numeric($postId) || !$this->postExists($postId))) {
      $emsg = T_INVALIDPOST;
    } else if (isset($threadId) && $this->threadIsClosed($threadId) && ($this->boardType != BT_NEWS && !$this->isAdmin || $this->boardType == BT_NEWS && !$this->isNewsAdmin)) {
      $emsg = T_THREADCLOSED;
    } else if (isset($threadId) && $this->threadIsHidden($threadId) && ($this->boardType != BT_NEWS && !$this->isAdmin || $this->boardType == BT_NEWS && !$this->isNewsAdmin)) {
      $emsg = T_THREADHIDDEN;
    //} else if ($post && $this->postAuthor($post) != $this->userID) {
    //  $emsg = T_NOTAUTHOR;
    } else if (isset($boardId) && $this->boardStatus($boardId) & BOARD_CLOSED) {
      $emsg = T_BOARDCLOSED;
    } else if (isset($boardId) && $this->boardType($boardId) == BT_NEWS && !$this->isAdmin) {
      // News dürfen nur vom Admin geschrieben werden
      $emsg = T_ACCESSDENIED;
    } else {
      if (isset($boardId)) {
        // neuer Thread
        // Variablen aus $data in Hidden-Felder packen
        $board = Board::load($boardId);

        $this->smarty->assign('data', $data);
        $this->smarty->assign('mode', 'newThread');

        if ($this->boardType == BT_FORUM || $this->boardType == BT_NEWS)
          // im normalen Forum-Modus, wenn ein neuer Thread eröffnet wird, kann man einen Title auswählen.
          $this->smarty->assign('title_field', 1);
        else
          $this->smarty->assign('title_field', 0);
      } else if (isset($threadId)) {
        // neuer Post
        $thread = Thread::load($threadId);
        $board = Board::load($thread->boardId);

        // Kein Eingabefeld für den Titel
        $this->smarty->assign('title_field', 0);
        $this->smarty->assign('mode', 'newPost');
        $this->smarty->assignByRef('thread', $thread);

        // ist $postToQuoteId angegeben, soll ein Post in den Text eingefügt werden.
        if ($postToQuoteId != NULL && is_numeric($postToQuoteId) && ($postToQuote = Post::load($postToQuoteId)) != null) {
          $this->smarty->assignByRef('postToQuote', $postToQuote);
        }
      } else if (isset($postId)) {
        // edit
        $post = Post::load($postId);
        $thread = Thread::load($post->threadId);
        $board = Board::load($thread->boardId);

        if ($this->isFirstPostInThread($post->id))
	        $this->smarty->assign('title_field', 1);
        else
          $this->smarty->assign('title_field', 0);

        $this->smarty->assign('mode', 'editPost');
        $this->smarty->assignByRef('post', $post);
        $this->smarty->assignByRef('thread', $thread);
      }

      $this->smarty->assignByRef('board', $board);

      $this->smarty->assign('showThreadName', ($this->design == DESIGN_COMMENTS || $boardId) ? 0 : 1);
      $this->smarty->assign('smileys', $this->smileys);

      if ($this->design == DESIGN_NEWSADMIN) {
        // Sind wir im Admin, entsprechendes Bilder-Array mit zurückgeben
        $pattern = NEWSBILD_DIR.$board->mandantId.'_*';
        $files = glob($pattern);
        $images = array();
        if (is_array($files)) {
          foreach ($files as $val) {
            // Bilder ins Array. Nur die Dateinamen, keine Verzeichnisse
            $picName = substr($val, strrpos($val, '/') + 1);
            $images[NEWSBILD_PATH.$picName] = $picName;
          }
        }
        $this->smarty->assign('images', $images);
      }

      if ($this->boardType == BT_TURNIERCOMMENTS)
        $this->smarty->displayWithFallback('comments_form.tpl');
      else
        $this->smarty->displayWithFallback('form.tpl');
    }

    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
  }

  function submit($boardId = NULL, $threadId = NULL, $postId = NULL, $title, $content, $authorID, $authorName, $data = array(), $helperstring = NULL, $title_en = NULL, $content_en = NULL,$planned = NULL) {
    /*
     *  Legt Thread/Post an
     *  $tb: ID des Boards bzw Threads, in dem der Thread bzw Post angelegt werden soll
     *  $title, $content, $authorID, $authorName: inhaltsbezogene Daten
     *
     *  Soll ein neuer Thread angelegt werden, ist $board != -1, soll ein neuer Post angelegt werden, ist
     *  $thread != -1
     *
     *  Gibt die ID des neuen Threads zurück. Wurde kein neuer Thread angelegt, sondern ein Post, wird
     *  -1 zurückgegeben
     */

    // Überprüfung auf gültigen Thread / gültiges Board
    if (!$this->isLoggedIn) {
      $emsg = T_NOTLOGGEDIN;
    } else if (isset($threadId) && (!is_numeric($threadId) || !$this->threadExists($threadId))) {
      $emsg = T_INVALIDTHREAD;
    } else if (isset($boardId) && (!is_numeric($boardId) || !$this->boardExists($boardId))) {
      $emsg = T_INVALIDBOARD;
    } else if (isset($postId) && (!is_numeric($postId) || !$this->postExists($postId))) {
      $emsg = T_INVALIDPOST;
    } else if (isset($threadId) && $this->threadIsClosed($threadId) && !$this->isAdmin) {
      $emsg = T_THREADCLOSED;
    } else if (!$this->isAdmin && isset($postId) && $this->postAuthor($postId) != $this->userID && $this->design != DESIGN_NEWSADMIN) {
      $emsg = T_NOTAUTHOR;
    } else if (empty($title) || empty($content)) {
      // ist Title oder Content leer, Formular erneut anzeigen
      $this->form($boardId, $threadId, $postId, $data);
      $rc = 0;
    } else {
      if (isset($postId)) {
        // Post wurde übergeben, dies ist ein Edit
        $action = 'changed';
        $what = 'post';
        
        $post   = Post::load($postId);
        $thread = Thread::load($post->threadId);
        $board  = Board::load($thread->boardId);

        $post->content      = stripslashes($content);
        $post->content_en   = stripslashes($content_en);
        $post->helperstring = $helperstring;         
    

	// Ist der edit von einem Admin durchgeführt worden 
	// und es ist nicht der eigene Post des admins, wird 
	// lastedit nicht gesetzt
	if (!($this->isAdmin && $post->authorId != $this->userID))
	  $post->lastEdited   = $this->time;
        if ($this->isFirstPostInThread($postId)) {
          $thread->title = $title;
          $thread->title_en = $title_en;
          $thread->save();
        }

        $post->content    = $this->checkImagesForSize($post->content);
        $post->content_en = $this->checkImagesForSize($post->content_en);

        $post->save();

        $newID = $thread->id;
      } else if (isset($threadId)) {
        // Post anlegen
        $action = 'created';
        $what = 'post';
        
        $post   = new Post();
        $thread = Thread::load($threadId);
        $board  = Board::load($thread->boardId);

        $post->boardId    = $thread->boardId;
        $post->parent     = $thread->id;
        $post->title      = stripslashes($title);
        $post->title_en   = stripslashes($title_en);
        $post->content    = stripslashes($content);
        $post->content_en = stripslashes($content_en);
        $post->authorId   = $authorID;
        $post->authorName = stripslashes($authorName);
        $post->time       = $this->time;
        $post->hidden     = false;
        $post->posts      = 0;

        $post->content    = $this->checkImagesForSize($post->content);
        $post->content_en = $this->checkImagesForSize($post->content_en);

        $post->create();

        $newID = $thread->id;

        $board->posts           += 1;
        $board->lastpost        = $this->time;
        $board->lastposterId    = $authorID;
        $board->lastposterName  = stripslashes($authorName);

        $thread->posts          += 1;
        $thread->lastPost       = $this->time;
        $thread->lastposterId   = $authorID;
        $thread->lastPostID     = $post->id;
        $thread->lastposterName = stripslashes($authorName);

        $board->save();
        $thread->save();        
      } else if (isset($boardId)) {
        // Thread anlegen
        $action = 'created';
        $what = 'thread';
        
        $board = Board::load($boardId);

        $thread = new Thread();

        $thread->boardId    = $board->id;
        $thread->parent     = -1;
        $thread->title      = stripslashes($title);
        $thread->title_en   = stripslashes($title_en);
        $thread->content    = stripslashes($content);
        $thread->content_en = stripslashes($content_en);
        $thread->authorId   = $authorID;
        $thread->authorName = stripslashes($authorName);
        $thread->time       = $this->time;
        $thread->helperstring = $helperstring;

        // Neues News' sind erstmal versteckt
        $thread->hidden     = $this->boardType == BT_NEWS ? true : false;
        $thread->posts      = 1;
        $thread->lastpost       = $this->time;
        $thread->lastposterId   = $authorID;
        $thread->lastposterName = stripslashes($authorName);

	      $thread->content    = $this->checkImagesForSize($thread->content);
	      $thread->content_en = $this->checkImagesForSize($thread->content_en);
	      

        $thread->create();

        $newID = $rc['newThread'] = $thread->id;

        if ($this->boardType == BT_NEWS) {
          $board->hiddenthreads += 1;
          $board->hiddenposts   += 1;
        } else {
          $board->threads        += 1;
          $board->posts          += 1;
          $board->lastpost       = $this->time;
          $board->lastposterId   = $authorID;
          $board->lastposterName = stripslashes($authorName);
        }
        $board->save();
      }


        // $data wird auch wieder mit zurückgegeben
        $rc['data'] = $data;

        // Thread für alle als neu markieren
        //$this->threadMarkUnread($thread->id);
        // Thread als gelesen markieren
        $this->threadMarkRead($thread->id, $post->id);

        $rc['oldThread'] = $threadId;
        $rc['editedPost'] = $postId;

        if (isset($post)) 
          $this->smarty->assignByRef('post', $post);
        $this->smarty->assignByRef('thread', $thread);
        $this->smarty->assignByRef('board', $board);
        $this->smarty->assign('action', $action);
        $this->smarty->assign('what', $what);
        $this->smarty->assign('jumpToPost', $jumpToPost);

        if ($this->design == DESIGN_FORUM) {
          $this->smarty->displayWithFallback('forum_submit.tpl');
        } else if ($this->design == DESIGN_NEWS) {
          $this->smarty->displayWithFallback('news_submit.tpl');
        } else if ($this->design == DESIGN_COMMENTS) {
          if (!strstr($this->redirectBase, '?'))
            $redirectTarget = "$this->redirectBase?thread=$newID";
          else
            $redirectTarget = "$this->redirectBase&thread=$newID";
          echo "Post erfolgreich angelegt/geändert. ";
        } else if ($this->design == DESIGN_NEWSADMIN) {
          echo "Erfolgreich.<br><a href=\"$this->redirectBase\">zurück</a>";
        }
    }
    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
    return $rc;
  }

  function changemode($postId, $mode) {
    /*
     * Ändert den Modes eines Thread/posts: hidden, closed, sticky
     */

    // TODO: lastpost, lastposterName, etc ändern wenn post/thread versteckt wird

    $validModes = array('hidden', 'closed', 'sticky');
    if (!in_array($mode, $validModes)) {
      $emsg = T_INVALIDMODE;
    } else if (!$this->isAdmin) {
      $emsg = T_ACCESSDENIED;
    } else if (!$this->postExists($postId)) {
      $emsg = T_INVALIDPOST;
    } else {
      // aktuelle Einstellung lesen
      $post = Post::load($postId);
      $parentPostId = $this->postParent($postId);
      $thread = Thread::load($parentPostId);
      $board = Board::load($thread->boardId);

      if ($mode == 'closed') {
        $thread->closed = !$thread->closed;
        $thread->closedBy = $this->userID;
      } else if ($mode == 'sticky') {
        $thread->sticky = !$thread->sticky;
        $thread->stickyBy = $this->userID;
      } else if ($mode == 'hidden') {
        // Ist $thread->Id == $post->id, dann ist $post der erste Post im Thread.
        // Damit wird beim Verstecken der gesamte Thread versteckt und es muss
        // die Anzahl der Threads im Board verändert werden
        if ($thread->id == $post->id) {
          if ($thread->hidden) {
            // show
            $board->threads       += 1;
            $board->hiddenthreads -= 1;
            $board->posts         += $thread->posts;
            $board->hiddenposts   -= $thread->hiddenposts;
            $thread->hidden = false;
            $thread->hiddenBy = 0;
          } else {
            // hide
            $board->threads       -= 1;
            $board->hiddenthreads += 1;
            $board->posts         -= $thread->posts;
            $board->hiddenposts   += $thread->hiddenposts;
            $thread->hidden = true;
            $thread->hiddenBy = $this->userID;
          }
        } else {
          if ($post->hidden) {
            // show
            $thread->posts       += 1;
            $thread->hiddenposts -= 1;
            if (!$thread->hidden) {
              // Angaben im Board nur verändern, wenn Thread nicht versteckt ist!
              $board->posts        += 1;
              $board->hiddenposts  -= 1;
            }
            $post->hidden = false;
            $post->hiddenBy = 0;
          } else {
            // hide
            $thread->posts       -= 1;
            $thread->hiddenposts += 1;
            if (!$thread->hidden) {
              // Angaben im Board nur verändern, wenn Thread nicht versteckt ist!
              $board->posts        -= 1;
              $board->hiddenposts  += 1;
            }
            $post->hidden = true;
            $post->hiddenBy = $this->userID;
          }
          // save all
          if (isset($post))
            $post->save();
          if (isset($thread))
            $thread->save();
          if (isset($board))

            $board->save();
          // Information, welches der letzte Poster im Thread ist neu setzen
          $threadLastPost = $thread->loadLastNonHiddenPost();
          $thread->lastPost =       $threadLastPost->time;
          $thread->lastposterId =   $threadLastPost->authorId;
          $thread->lastposterName = $threadLastPost->authorName;
          $thread->save();
        }
        // save all
        if (isset($post))
          $post->save();
        if (isset($thread))
          $thread->save();
        if (isset($board))
          $board->save();
        // Auf jeden Fall müssen die Informationen, welches der letzte Post ist im Board neu gesetzt werden
        $boardLastPost = $board->loadLastNonHiddenPost();
        if ($boardLastPost == null) {
          $board->lastpost =       null;
          $board->lastposterId =   null;
          $board->lastposterName = null;
        } else {
          $board->lastpost =       $boardLastPost->time;
          $board->lastposterId =   $boardLastPost->authorId;
          $board->lastposterName = $boardLastPost->authorName;
        }
        $board->save();
      }

      if (isset($post))
        $post->save();
      if (isset($thread))
        $thread->save();
      if (isset($board))
        $board->save();

      if ($this->design == DESIGN_FORUM) {
        if (!strstr($this->redirectBase, '?'))
          $redirectTarget = "$this->redirectBase?thread=".$this->threadIDByPost($post->id);
        else
          $redirectTarget = "$this->redirectBase&thread=".$this->threadIDByPost($post->id);
      } else if ($this->design == DESIGN_NEWSADMIN) {
        $redirectTarget = $this->redirectBase;
      } else {
        if (!strstr($this->redirectBase, '?'))
          $redirectTarget = "$this->redirectBase?newsID=".$this->threadIDByPost($post->id);
        else
          $redirectTarget = "$this->redirectBase&newsID=".$this->threadIDByPost($post->id);
      }


      if ($this->design != DESIGN_COMMENTS) {
				$this->smarty->assignByRef('post', $post);
				$this->smarty->assignByRef('thread', $thread);
				$this->smarty->assignByRef('board', $board);
      	$this->smarty->displayWithFallback('forum_mode_changed.tpl');
        //echo "mode erfolgreich geändert. <a href=\"$redirectTarget\">Zurück</a>";
      } else
        echo "mode erfolgreich geändert. ";
    }
    // Fehlermeldung ausgeben
    if (isset($emsg)) {
      $this->smarty->assign('emsg', $emsg);
      $this->smarty->displayWithFallback('error.tpl');
    }
  }

  function forumActivity($count) {
    /*
     * Gibt die IDs der $count neuesten Threads (lastpost) zurück, die nicht versteckt sind
     * Beim Boardtyp BT_NEWS wird nach "time" (also Zeitpunkt des Newsposts sortiert)
     */
    $sql = "SELECT
          c.boardID, c.contentID, c.title, c.title_en, c.lastpost, c.lastposterID, c.lastposterName, c.time, c.authorName
        FROM
          forum_content c, forum_boards b
        WHERE
          b.mandantID = {$this->mandantID} AND
          c.boardID = b.boardID AND
          b.type = {$this->boardType} AND
          parent = -1 AND
          c.hidden = 0
        ORDER BY
          ".($this->boardType == BT_NEWS ? 'time' : 'lastpost')." DESC
        LIMIT
          $count";
    $res = DB::query($sql);
    $rc = false;
    while ($row = $res->fetch_assoc()) {
      $rc[] = $row;
    }
    return $rc;
  }

  function markBoardRead($board) {
    $sql = "SELECT
              contentID
            FROM
              forum_content
            WHERE
              parent=-1 AND
              boardID = '$board->id'";
    $res = DB::query($sql);
    $threadIds = array();
    while ($row = $res->fetch_row()) {
      $threadIds[] = $row[0];
    }

    // Überhaupt Threads da?
    if ($res->num_rows > 0) {
      $sql = "REPLACE INTO
                forum_readmarks
              (threadID, postID, userID)
              VALUES\n";
      foreach ($threadIds as $val) {
        $lastPostId = $this->lastPostInThread($val);
        $sql .= "($val, $lastPostId, {$this->userID}), ";
      }
      $sql = substr($sql, 0, strlen($sql)-2);
      DB::query($sql);
    }
  }

  function markAllThreadsRead() {
    $boards = $this->boardList() + $this->boardList(BT_NEWS);
    foreach ($boards as $boardId => $val) {
      $board = Board::load($boardId);
      $this->markBoardRead($board);
    }
    $this->smarty->displayWithFallback('forum_marked_as_read.tpl');
  }

  function markAllThreadsReadInBoard($boardId) {
    if (!is_numeric($boardId) || $boardId < 1 || !$this->boardExists($boardId))
      $error = T_INVALIDBOARD;
    else if (!$this->isLoggedIn)
      $error = T_NOTLOGGEDIN;
    else {
      $board = Board::load($boardId);
      $this->markBoardRead($board);
      $this->smarty->assignByRef('board', $board);
    }

    if (isset($error))
      $this->smarty->assign('error', $error);

    $this->smarty->displayWithFallback('forum_board_marked_as_read.tpl');
  }

  function moveThread($threadId, $dstBoardId) {
    if (!is_numeric($threadId) || $threadId < 1 || !$this->threadExists($threadId))
      $error = T_INVALIDTHREAD;
    else if (!is_numeric($dstBoardId) || $dstBoardId < 1 || !$this->boardExists($dstBoardId))
      $error = T_INVALIDBOARD;
    else {
      $thread = Thread::load($threadId);
      $dstBoard = Board::load($dstBoardId);

      $this->smarty->assignByRef('thread', $thread);
      $this->smarty->assignByRef('board', $dstBoard);

      // altes Board == neues Board?
      if ($thread->boardId == $dstBoardId) {
        $error = 'dstboardEqualsCurrentBoard';
      } else if (!$thread->moveToBoard($dstBoardId)) {
        $error = 'moveBoardFailed';
      }
    }
    if (isset($error))
      $this->smarty->assign('error', $error);

    $this->smarty->displayWithFallback('forum_thread_moved.tpl');
  }

  function createPagePartition($pageCount, $currentPage, $pagesBorder, $pagesBuffer) {
    // erste und letzte Seite immer anzeigen
    $pages[1] = true;
    $pages[$pageCount] = true;

    if ($currentPage == 1) {
      // erste Seite ausgewählt
      for ($i = 2; $i <= 1 + $pagesBorder && $i <= $pageCount; $i++) {
        $pages[$i] = true;
      }
      $pages[$i] = false;
    } else if ($currentPage != $pageCount) {
      // aktuelle Seite irgendwo in der Mitte
      $pages[$currentPage - ($pagesBuffer + 1)] = false;

      for ($i = $pagesBuffer; $i > 0 ; $i--) {
        $pages[$currentPage - $i] = true;
      }

      $pages[$currentPage] = true;

      for ($i = $pagesBuffer; $i > 0 ; $i--) {
        $pages[$currentPage + $i] = true;
      }

      $pages[$currentPage + ($pagesBuffer + 1)] = false;
    } else {
      // letzte Seite ausgewählt
      $pages[$pageCount - $pagesBorder] = false;
      for ($i = $pageCount - $pagesBorder + 1; $i < $pageCount; $i++) {
        $pages[$i] = true;
      }
    }

    // bereinigen
    foreach($pages as $key => $val) {
      if ($key < 1 || $key > $pageCount)
        unset($pages[$key]);
      if (!$val &&
          isset($pages[$key-1]) && $pages[$key-1]
          &&
          isset($pages[$key+1]) && $pages[$key+1])
        $pages[$key] = true;
    }

    // erste und letzte Seite immer anzeigen
    $pages[1] = true;
    $pages[$pageCount] = true;

    ksort($pages);
    return $pages;
  }


  function threadIDByPost($contentID) {
    /*
     * Gibt threadID des Posts $contentID zurück
     */
    $sql = "SELECT
              parent
            FROM
              forum_content
            WHERE
              contentID = '$contentID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    return ($row[0] == -1) ? $contentID : $row[0];
  }

  function boardIDByThread($threadID) {
    /*
     * Gibt BoardID des Thread $threadID zurück
     */
    $sql = "SELECT
              boardID
            FROM
              forum_content
            WHERE
              contentID = '$threadID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    return $row[0];
  }

  function boardExists($boardID) {
    /*
     * Existiert das Board $boardID?
     */
    $sql = "SELECT
              COUNT(*)
            FROM
              forum_boards
            WHERE
              boardID = '$boardID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    $rc = ($row[0] == 1) ? true : false;
    return $rc;
  }

  function boardStatus($boardID) {
    /*
     * Board ist open, closed, hidden, inline?
     */
     $sql = "SELECT
               closed, hidden
             FROM
               forum_boards
             WHERE
               boardID = '$boardID'";
     $res = DB::query($sql);
     $row = $res->fetch_assoc();
     $rc = 0;
     if ($row['closed'] == 1)
       $rc |= BOARD_CLOSED;
     if ($row['hidden'] == 1)
       $rc |= BOARD_HIDDEN;
     return $rc;
  }

  function boardType($boardID) {
    /*
     * Board ist BT_FORUM, BT_NEWS, BT_TURNIERCOMMENTS?
     */
     $sql = "SELECT
               type
             FROM
               forum_boards
             WHERE
               boardID = '$boardID'";
     $res = DB::query($sql);
     $row = $res->fetch_row();
     $rc = $row[0];
     return $rc;
  }

  function threadExists($threadID) {
    /*
     * Existiert der Thread $threadID?
     */
    $sql = "SELECT
              COUNT(*)
            FROM
              forum_content
            WHERE
              contentID = '$threadID' AND
              parent = -1";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    $rc = ($row[0] == 1) ? true : false;
    return $rc;
  }


  ///////////////////////////////////////////////// NEW



	function boardsAreNew($boardIds) {
		/*
		 * Sind die boards neu für den aktuellen Benutzer?
		 */
		$data = array();
    if ($this->isLoggedIn && C_READMARKS) {
			$idString = implode(',', $boardIds);
     	$sql = "SELECT 
     						count(boardID), boardID
							FROM 
								forum_content c
							LEFT JOIN 
								forum_readmarks rm ON(
									rm.threadID = c.contentID AND
									rm.userID = {$this->userID} AND
									rm.postID = c.lastPostID
								)
							WHERE 
								c.boardID IN($idString) AND 
								c.parent = -1 AND 
								rm.threadID IS NULL
							GROUP BY 
								boardID
							LIMIT 0, 30";
                
			$res = DB::query($sql);
			$data = array();
			foreach ($boardIds as $boardId)
				$data[$boardId] = 0;
			while ($row = $res->fetch_assoc()) {
				$data[$row['boardID']] = 1;
			}
    }	else {
    	// nicht eingeloggt, oder readmarks aus --> alles ungelesen
			foreach ($boardIds as $boardId)
				$data[$boardId] = 1;
		}
    return $data;
	}


  function newThreadInBoard($boardId) {
    /*
     * Ist in diesem Board ein ungelesener Thread für den aktuellen Benutzer?
     */


    if (!$this->isLoggedIn || !C_READMARKS) {
      // nicht eingeloggt, oder Feature disabled
      // dann ist alles neu
      $rc = true;
    } else {
	$sql = "SELECT 
  		  count(*)
		FROM 
  		  forum_content c
		LEFT JOIN 
		  forum_readmarks rm ON 
		  (rm.threadID = c.contentID AND 
		  rm.userID = {$this->userID} AND rm.postID = c.lastPostID)
		WHERE 
		  c.boardID = $boardId AND
		  c.parent = -1 AND
		  rm.threadID IS NULL";
      $time_start = microtime_float();
      $res = DB::query($sql);
      $row = $res->fetch_row();
      $time_end = microtime_float();
//if ($this->isAdmin) echo "newThreadInBoard: ". ($time_end - $time_start)."<br>";
      // 0.3899 auf live
      
      $rc = ($row[0] != 0);
    }
    return $rc;
  }


	function threadsAreNew($threadIds) {
		/*
		 * Sind die threads neu für den aktuellen Benutzer?
		 */
		$data = array();
		// nicht eingeloggt oder readmarks aus --> alles ungelesen
		foreach ($threadIds as $threadId)
			$data[$threadId] = 1;
    if ($this->isLoggedIn && C_READMARKS && !empty($threadIds)) {
			$idString = implode(',', $threadIds);

     	$sql = "SELECT
              	threadID, postID != lastPostID as new
              FROM
                forum_content c, forum_readmarks rm
              WHERE
                c.contentID  IN ($idString) AND
                rm.threadID = c.contentID AND
                rm.userID = {$this->userID} AND
                c.lastPostID = rm.postID";
			$res = DB::query($sql);
			while ($row = $res->fetch_assoc()) {
				$data[$row['threadID']] = 0;
			}			
    }
    return $data;
	}

  function threadIsNew($threadID) {
    /*
     * Ist der Thread $threadID neu für den aktuellen Benutzer?
     */
    $rc = true;
    if ($this->isLoggedIn && C_READMARKS) {
      $time_start = microtime_float();
            
	$sql = "SELECT 
  		  count(*)
		FROM 
  		  forum_content c, forum_readmarks rm
		WHERE 
  		  c.contentID = '$threadID' AND 
		  rm.threadID = c.contentID AND 
		  rm.userID = {$this->userID} AND 
		  c.lastPostID = rm.postID";
	$res = DB::query($sql);
	$row = $res->fetch_row();
        $rc = ($row[0] == 0);      
      $time_end = microtime_float();
//if ($this->isAdmin) echo "threadIsNew: ". ($time_end - $time_start)."<br>";
    }
    return $rc;
  }

	function threadPageForPost($threadID, $postID) {
		/*
		 * Seite auf der der angegebene Post ist zurückgeben
		 */
		$sql = "SELECT
			  CEIL(COUNT(contentID) / {$this->ppp})
			FROM
			  forum_content
			WHERE
			  (parent = '$threadID' || (parent = -1 && contentID = '$threadID')) AND
			  contentID <= $postID";
		if (!$this->isAdmin)
			$sql .= " AND hidden = 0";

		$time_start = microtime_float();
		$res = DB::query($sql);
		$time_end = microtime_float();
//if ($this->isAdmin) echo "threadPageForPost: ". ($time_end - $time_start)."<br>";
		// 0.06 auf live
		$row = $res->fetch_row();
		return (int) $row[0];
	}

  function threadLastReadPostId($threadID) {
    if ($this->isLoggedIn && C_READMARKS) {
      $sql = "SELECT
                postID
              FROM
                forum_readmarks
              WHERE
                threadID = '$threadID' AND
                userID = '$this->userID'";
      if (!$res = DB::query($sql)) {
      	$rc = 0;
      } else {
        $row = $res->fetch_row();
        $rc = $row[0];
      }
    } else {
    	$rc = $this->lastPostInThread($threadID);
	//$rc = $threadID;
    }
    return (int) $rc;
  }

	function threadFirstNewPost($threadID) {
		if ($this->isLoggedIn && C_READMARKS) {
			$lastReadPostId = $this->threadLastReadPostId($threadID);

			$sql = "SELECT
				  contentID
				FROM
				  forum_content
				WHERE
				  (parent = '$threadID' || (parent = -1 && contentID = '$threadID')) AND
				  contentID > $lastReadPostId";
			if (!$this->isAdmin)
				$sql .= " AND hidden = 0";
			$sql .= " LIMIT 1";
      			$time_start = microtime_float();
		$res = DB::query($sql);
		$time_end = microtime_float();
//if ($this->isAdmin) echo "threadFirstNewPost: ". ($time_end - $time_start)."<br>";
		// 0.06 auf live
        		$row = $res->fetch_row();
        		$rc = $row[0];
			// sind alle Posts gelesen, wird 0 zurückgegeben
      			return (int) ($rc) ? $rc : 0;
  		}
  		return $this->lastPostInThread($threadID);
  	}

  function threadMarkReadToPost($threadID, $postID) {
    /*
     * Thread $threadID bis $postID für eingeloggten als gelesen markieren,
     * allerdings nur, wenn $postID > postID aus DB
     */

    // geht nur wenn eingeloggt und Feature aktiviert
    if ($this->isLoggedIn && C_READMARKS && (
        !$postID || ($postID > $this->threadLastReadPostId($threadID)))) {
      $sql = "REPLACE INTO
                forum_readmarks
                (threadID, postID, userID)
              VALUES ('$threadID', '$postID', '{$this->userID}')";
      if (!$res = DB::query($sql))
        echo "DB-Fehler";
    }
  }
  
  ///////////////////////////////////////////////// NEW

  function threadMarkRead($threadID) {
    /*
     * Thread $threadID für eingeloggten als gelesen markieren
     */

    // geht nur wenn eingeloggt und Feature aktiviert
    if ($this->isLoggedIn && C_READMARKS) {
      $lastPostId = $this->lastPostInThread($threadID);
      $sql = "REPLACE INTO
                forum_readmarks
                (threadID, postID, userID)
              VALUES ('$threadID', '$lastPostId', '{$this->userID}')";
      if (!$res = DB::query($sql))
        echo "DB-Fehler";
    }
  }

  function threadMarkUnread($threadID) {
    /*
     * Thread $threadID für alle als neu markieren
     */

    // geht nur wenn eingeloggt und Feature aktiviert
    if ($this->isLoggedIn && C_READMARKS) {
      $sql = "DELETE FROM
                forum_readmarks
              WHERE
                threadID = '$threadID'";
      if (!$res = DB::query($sql))
        echo "DB-Fehler";
    }
  }

  function threadCount($boardID, $countVisibleOnly = false) {
    /*
     * Anzahl Threads in diesem Board
     * $countVisibleOnly: nur die sichtbaren (hidden = 0) anzeigen?
     */
    $sql = "SELECT
              threads ".($countVisibleOnly ? '' : '- hiddenthreads' )."
            FROM
              forum_boards
            WHERE
              boardID = '$boardID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    return $row[0];
  }

  function threadIsClosed($threadID) {
    /*
     * Thread $threadID closed?
     */
    $sql = "SELECT
              closed
            FROM
              forum_content
            WHERE
              contentID = '$threadID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    $rc = ($row[0] == 1) ? true : false;
    return $rc;
  }

  function threadIsHidden($threadID) {
    /*
     * Thread $threadID hidden?
     */
    $sql = "SELECT
              hidden
            FROM
              forum_content
            WHERE
              contentID = '$threadID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    $rc = ($row[0] == 1) ? true : false;
    return $rc;
  }

  function lastPostInThread($threadID) {
    /*
     * gibt die ID des letzten Posts im Thread $threadID
     */
    $sql = "SELECT
              lastPostID
            FROM
              forum_content
            WHERE
              contentID = '$threadID'";
		$time_start = microtime_float();
		$res = DB::query($sql);
		$time_end = microtime_float();
//if ($this->isAdmin) echo "lastPostInThread: ". ($time_end - $time_start)."<br>";
		// 0.08 auf live
    $row = $res->fetch_row();
    return (int) $row[0];
  }

  function postExists($postID) {
    /*
     * Existiert der Post $postID?
     */
    if (!is_numeric($postID) || $postID <= 0)
      return false;
    $sql = "SELECT
              COUNT(*)
            FROM
              forum_content
            WHERE
              contentID = '$postID'";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    $rc = ($row[0] == 1) ? true : false;
    return $rc;
  }

  function postAuthor($postID) {
    /*
     * Wer ist der Author des Posts $postID?
     */
    if (!is_numeric($postID) || $postID <= 0) {
      $rc = 0;
    } else {
      $sql = "SELECT
                authorID
              FROM
                forum_content
              WHERE
                contentID = '$postID'";
      if (!$res = DB::query($sql))
        $rc = 0;
      else {
        $row = $res->fetch_row();
        $rc = $row[0];
      }
    }
    return $rc;
  }

  function isFirstPostInThread($postID) {
    /*
     * Ist der Post $postID der erste im Thread??
     */
    $sql = "SELECT
              COUNT(*)
            FROM
              forum_content
            WHERE
              contentID = '$postID' AND
              parent = -1";
    $res = DB::query($sql);
    $row = $res->fetch_row();
    $rc = ($row[0] == 1) ? true : false;
    return $rc;
  }

  function postCount($thread, $countVisibleOnly = false) {
    /*
     * Wieviele Posts sind in diesem Thread?
     * $countVisibleOnly: nur die sichtbaren (hidden = 0) anzeigen?
     */
    if (!is_numeric($thread) || $thread <= 0) {
      $rc = 0;
    } else if (!$this->threadExists($thread)) {
      $rc = 0;
    } else {
      $sql = "SELECT
                posts ".($countVisibleOnly ? '- hidden' : '' )."
              FROM
                forum_content
              WHERE
                contentID = '$thread'";
      if (!$res = DB::query($sql)) {
        echo "DB-Fehler";
        $rc = 0;
      } else {
        $row = $res->fetch_row();
        $rc = $row[0];
      }
    }
    return $rc;
  }

  function postParent($post) {
    /*
     *  Gibt die ID des Parent-Posts zurück. $rc == $post wenn übergebener Post selbst der Parent ist
     */
    $sql = "SELECT
              parent
            FROM
              forum_content
            WHERE
              contentID = '$post'";
    if (!$res = DB::query($sql)) {
      echo "DB-Fehler";
      $rc = 0;
    } else {
      $row = $res->fetch_row();
      $rc = ($row[0] == -1) ? $post : $row[0];
    }
    return $rc;
  }

	function getBoardByType($type) {
		/*
		 * Gibt die board-id zurueck die den gewünschten Boardtype hat
		 */
		$sql = "SELECT 
			  boardID
			FROM 
			  forum_boards
			WHERE 
			  mandantID = '{$this->mandantID}' AND
			  type = '{$type}'";
		$res = DB::query($sql);
		$row = $res->fetch_assoc();
		return $row['boardID'];
	}

	function checkImagesForSize($content) {
		$pattern = "#\[img(.*)\](.*)\[/img\]#i";
		$content = preg_replace_callback($pattern, 'replaceImageCallback', $content);		
		return $content;
	}

	
	function getSupporterPasses($userId) {
		/*
		 * Holt die Supporter-Pässe für den User
		 */
		$sql = "select
				count(s.passId) as count,
				p.supporterPassPicSmall as picSmall
			from
				party p,
				acc_supporterpass s
			where
				s.mandantId = '{$this->mandantID}' and
				s.ownerId = '$userId' and
				s.partyId = p.partyId and
				s.statusId = ".ACC_STATUS_BEZAHLT."
			group by
				p.partyId
			order by
				p.partyId desc";
		$res = DB::query($sql);
		$passes = array();
		while ($row = $res->fetch_assoc()) {
			$passes[] = $row;
		}
		return $passes;
	}

}
function replaceImageCallback($matches) {
	$imageUrl = $matches[2];
	
	// default
	$retImage = "[img{$matches[1]}]{$imageUrl}[/img]";
	// tmp-name for saved image
	$tmpfname = tempnam("/tmp", "forumImage");
	
	$cmd = "wget ".escapeshellarg($imageUrl)." -O ".escapeshellarg($tmpfname);	
	
	exec($cmd, $output, $retval);
	
	if ($retval == 0) {			
		$size = @getimagesize($tmpfname);	
		unlink($tmpfname);
		if ($size !== false) {
			if ($size[0] > 400) {
				$ratio = 400 / $size[0];
				$width = 400;
				$height = round($size[1] * $ratio, 0);
			} else {
				$width = $size[0];
				$height = $size[1];
			}
			$retImage = "[img={$width}x{$height}]{$imageUrl}[/img]";
		}
	}
	return $retImage;
	
	/*
	$ch = curl_init($image);
	$tmpfname = tempnam("/tmp", "forumImage");
	$fp = fopen($tmpfname, "w");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($handle);
*/
	
	
}
?>
