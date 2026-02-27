<?php

class Board {
  public $id;
  public $mandantId;
  public $name;
  public $description;
  public $threads;
  public $hiddenthreads;
  public $type;
  public $hidden;
  public $closed;
  public $posts;
  public $hiddenposts;
  public $lastpost;
  public $lastposterId;
  public $lastposterName;
  
  static function load($id) {
    $board = new Board();
    
    $sql = "SELECT
              mandantID, name, description, threads, hiddenthreads,
              type, hidden, closed, posts, hiddenposts, lastpost, lastposterID,
              lastposterName
            FROM
              forum_boards
            WHERE
              boardID = $id";
    $res = DB::query($sql);
    $row = $res->fetch_assoc();
    
    $board->id =             (int) $id;
    $board->mandantId =      (int) $row['mandantID'];
    $board->name =           (string) $row['name'];
    $board->description =    (string) $row['description'];
    $board->threads =        (int) $row['threads'];
    $board->hiddenthreads =  (int) $row['hiddenthreads'];
    $board->type =           (int) $row['type'];
    $board->hidden =         (bool) $row['hidden'];
    $board->closed =         (bool) $row['closed'];
    $board->posts =          (int) $row['posts'];
    $board->hiddenposts =    (int) $row['hiddenposts'];
    $board->lastpost =       (int) $row['lastpost'];
    $board->lastposterId =   (int) $row['lastposterID'];
    $board->lastposterName = (string) $row['lastposterName'];
   
    return $board;
  }
  
  function save() {
    if ($this != null) {
      $sql = "UPDATE 
                forum_boards
              SET
                mandantID = '{$this->mandantId}', 
                name = '".DB::$link->real_escape_string($this->name)."',
                description = '".DB::$link->real_escape_string($this->description)."',
                threads = '{$this->threads}', 
                hiddenthreads = '{$this->hiddenthreads}',
                type = '{$this->type}', 
                hidden = ".($this->hidden ? 1 : 0).", 
                closed = ".($this->closed ? 1 : 0).", 
                posts = '{$this->posts}', 
                hiddenposts = '{$this->hiddenposts}', 
                lastpost = ".($this->lastpost == null ? 'null' : $this->lastpost).", 
                lastposterID = ".($this->lastposterId == null ? 'null' : $this->lastposterId).", 
                lastposterName = '".DB::$link->real_escape_string($this->lastposterName)."'
              WHERE
                boardID = {$this->id}";
      return DB::query($sql);
    }
  }
  
  function loadLastPost() {
    /*
     * Load last Post
     */
    $sql = "SELECT 
              contentId
            FROM 
              `forum_content`
            WHERE 
               boardID = {$this->id}            
            ORDER BY 
              time DESC
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
  
  function loadLastNonHiddenPost() {
    /*
     * Load last non-hidden Post
     */
    $sql = "SELECT 
              contentId
            FROM 
              `forum_content`
            WHERE 
               boardID = {$this->id} AND
               hidden = 0
            ORDER BY 
              time DESC
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