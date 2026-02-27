<?php

class Thread {
  public $id;
  public $boardId;
  public $title;
  public $title_en;
  public $content;
  public $content_en;
  public $contentId;
  public $authorId;
  public $authorName;
  public $time;
  public $helperstring;
  public $posts;
  public $hiddenposts;
  
  public $closed;
  public $hidden;
  public $sticky;
  
  public $closedBy;
  public $hiddenBy;
  public $stickyBy;
  
  public $closedByName;
  public $hiddenByName;
  public $stickyByName;
  
  public $lastPost;
  public $lastposterId;
  public $lastposterName;
  public $lastPostID;
  
  public $postsArray;
  
  function load($id) {
    $thread = new Thread();
    
    $sql = "SELECT
              boardID, title, title_en, content, content_en, contentID, authorID, authorName, `time`, helperstring, posts, hiddenposts, 
              closed, hidden, sticky, closedBy, hiddenBy, stickyBy, 
              u1.LOGIN AS 'hiddenByName', u2.LOGIN AS 'closedByName', u3.LOGIN AS 'stickyByName',
              lastPost, lastposterID, lastposterName, lastPostID
            FROM
              forum_content c
            LEFT JOIN
              USER AS u1 ON c.hiddenBy = u1.USERID
            LEFT JOIN
              USER AS u2 ON c.closedBy = u2.USERID
            LEFT JOIN
              USER AS u3 ON c.stickyBy = u3.USERID
            WHERE
              contentID = $id";
    $res = DB::query($sql);
    $row = $res->fetch_assoc();
    
    $thread->id =          (int) $id;
    $thread->boardId =     (int) $row['boardID'];
    $thread->title =       (string) $row['title'];
    $thread->title_en =       (string) $row['title_en'];
    $thread->content =     (string) $row['content'];
    $thread->content_en =     (string) $row['content_en'];
    $thread->contentId =   (int) $row['contentID'];
    $thread->authorId =    (int) $row['authorID'];
    $thread->authorName =  (string) $row['authorName'];   
    $thread->time =        (int) $row['time'];
    $thread->helperstring =(string) $row['helperstring'];
    $thread->posts =       (int) $row['posts'];
    $thread->hiddenposts = (int) $row['hiddenposts'];
    
    $thread->closed = (bool) $row['closed'];
    $thread->hidden = (bool) $row['hidden'];
    $thread->sticky = (bool) $row['sticky'];
    
    $thread->closedBy = (int) $row['closedBy'];
    $thread->hiddenBy = (int) $row['hiddenBy'];
    $thread->stickyBy = (int) $row['stickyBy'];
    
    $thread->closedByName = (string) $row['closedByName'];
    $thread->hiddenByName = (string) $row['hiddenByName'];
    $thread->stickyByName = (string) $row['stickyByName'];
    
    $thread->lastPost       = (int) $row['lastPost'];
    $thread->lastposterId   = (int) $row['lastposterID'];
    $thread->lastposterName = (string) $row['lastposterName'];
    $thread->lastPostID     = (int) $row['lastPostID'];
    
    return $thread;
  }
  
  function save() {
    if ($this != null) {
      $sql = "UPDATE 
                forum_content
              SET
                boardID = '{$this->boardId}',
                title = '".DB::$link->real_escape_string($this->title)."', 
                title_en = '".DB::$link->real_escape_string($this->title_en)."', 
                content = '".DB::$link->real_escape_string($this->content)."', 
                content_en = '".DB::$link->real_escape_string($this->content_en)."', 
                authorID = '{$this->authorId}', 
                authorName = '".DB::$link->real_escape_string($this->authorName)."', 
                `time` = '{$this->time}', 
                helperstring = '".DB::$link->real_escape_string($this->helperstring)."',
                posts = '{$this->posts}', 
                hiddenposts = '".intval($this->hiddenposts)."', 
                closed = ".($this->closed ? 1 : 0).", 
                hidden = ".($this->hidden ? 1 : 0).", 
                sticky = ".($this->sticky ? 1 : 0).", 
                closedBy = '".intval($this->closedBy)."', 
                hiddenBy = '".intval($this->hiddenBy)."', 
                stickyBy = '".intval($this->stickyBy)."', 
                lastPost = '{$this->lastPost}', 
                lastposterID = '{$this->lastposterId}', 
                lastposterName = '".DB::$link->real_escape_string($this->lastposterName)."',
                lastPostID = '{$this->lastPostID}'
              WHERE
                contentID = {$this->id}";
      return DB::query($sql);
    }
  }
  
  function create() {
    if ($this != null && !isset($this->id)) {
      $sql = "INSERT INTO
                forum_content
              (boardID, parent, title, title_en, content, content_en, authorID, authorName, 
                lastPost, lastposterID, lastposterName, lastPostID, `time`, helperstring, hidden, posts)
              VALUES
                ('{$this->boardId}', -1, '".DB::$link->real_escape_string($this->title)."', '".DB::$link->real_escape_string($this->title_en)."',
                '".DB::$link->real_escape_string($this->content)."', 
                '".DB::$link->real_escape_string($this->content_en)."', '{$this->authorId}',
                '".DB::$link->real_escape_string($this->authorName)."', '".intval($this->lastPost)."', 
                '{$this->authorId}', '".DB::$link->real_escape_string($this->authorName)."', '".intval($this->lastPostID)."', 
                '{$this->time}', '".DB::$link->real_escape_string($this->helperstring)."', ".($this->hidden ? 1 : 0).",
                '{$this->posts}')";
      $res = DB::query($sql);
      $this->id = DB::$link->insert_id;
      // ugly hack: nach dem anlegen ist der erste post auch der letzte
      $this->lastPostID = $this->id;
      $this->lastPost = $this->time;
      $this->save();
    }
  }
  
  function moveToBoard($dstBoardId) {
    $dstBoard = Board::load($dstBoardId);
    $srcBoard = Board::load($this->boardId);
                
    // thread: boardID ändern
    $this->boardId = $dstBoardId;   
    $this->save();
    
    // beim alten Board: thread und post zahlen verringern (auf hidden / non hidden achten)
    //                    lastpost/lastposter ändern
    if ($this->hidden)
      $srcBoard->hiddenthreads -= 1;
    else 
      $srcBoard->threads -= 1;
     
     
    $srcBoard->posts -=       $this->posts;
    $srcBoard->hiddenposts -= $this->hiddenposts;
    
    
    $srcBoardLastPost = $srcBoard->loadLastPost();
    if ($srcBoardLastPost == null) {
      $srcBoard->lastpost =       null;
      $srcBoard->lastposterId =   null;
      $srcBoard->lastposterName = null;
    } else {
      $srcBoard->lastpost =       $srcBoardLastPost->time;
      $srcBoard->lastposterId =   $srcBoardLastPost->authorId;
      $srcBoard->lastposterName = $srcBoardLastPost->authorName;
    }
    
    
    // beim neuen Board:  thread und post zahlen erhöhen (auf hidden / non hidden achten)
    //                    lastpost/lastposter ändern          
    
    if ($this->hidden)
      $dstBoard->hiddenthreads += 1;
    else 
      $dstBoard->threads += 1;
     
     
    $dstBoard->posts       += $this->posts;
    $dstBoard->hiddenposts += $this->hiddenposts;
    
    
    $dstBoardLastPost = $dstBoard->loadLastPost();    
    if ($srcBoardLastPost == null) {
      $dstBoard->lastpost =       null;
      $dstBoard->lastposterId =   null;
      $dstBoard->lastposterName = null;
    } else {
      $dstBoard->lastpost =       $dstBoardLastPost->time;
      $dstBoard->lastposterId =   $dstBoardLastPost->authorId;
      $dstBoard->lastposterName = $dstBoardLastPost->authorName;
    }
    
    return ($srcBoard->save() && $dstBoard->save());
  }
  
  function loadLastPost() {
    /*
     * Load last Post
     */
    return Post::load($this->lastPostID);
  }
  
  function loadLastNonHiddenPost() {
    /*
     * Load last non-hidden Post
     */
    $sql = "SELECT 
              contentId
            FROM 
              `forum_content`
            WHERE 
               (parent = {$this->id} OR
               contentID = {$this->id}) AND
               hidden = 0
            ORDER BY 
              `time` DESC
            LIMIT 
              1";
    $res = DB::query($sql);
    if ($res->num_rows == 0) {
      return null;
    } else {
      $row = $res->fetch_row();
      $post = Post::load($row[0]);
      return $post;
    }
  }
}
?>
