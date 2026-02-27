<?php

class Post {
  public $id;
  public $parent;
  public $threadId;
  public $boardId;
  public $content;
  public $content_en;
  public $helperstring;
  public $authorName;
  public $authorId;
  public $time;
  public $lastEdited;
  public $hidden;
  public $hiddenBy;
  public $planned;
  public $timeplanned;
  
  function load($id) {
    $post = new Post();
    
    $sql = "SELECT
              parent, boardID, content, content_en, authorName, authorID, 
              time, lastEdited, hidden, hiddenBy, helperstring,planned,timeplanned
            FROM
              forum_content
            WHERE
              contentID = $id";
    $res = DB::query($sql);
    $row = $res->fetch_assoc();
    $post->id           = (int) $id;
    $post->parent       = (int) $row['parent'];
    $post->threadId     = ($post->parent == -1 ? $post->id : $post->parent);
    $post->boardId      = (int) $row['boardID'];
    $post->content      = (string) $row['content'];
    $post->content_en   = (string) $row['content_en'];
    $post->authorName   = (string) $row['authorName'];
    $post->authorId     = (int) $row['authorID'];
    $post->time         = (int) $row['time'];
    $post->lastEdited   = (int) $row['lastEdited'];
    $post->hidden       = (bool) $row['hidden'];
    $post->hiddenBy     = (int) $row['hiddenBy'];
    $post->helperstring = (string) $row['helperstring'];
    $post->planned      = (int) $row['planned'];
    $post->timeplanned  = (int) $row['timeplanned'];
    
    return $post;
  }
  
  function save() {
    if ($this != null) {
      $sql = "UPDATE 
                forum_content
              SET
                parent = '{$this->parent}',
                boardID = '{$this->boardId}', 
                content = '".DB::$link->real_escape_string($this->content)."', 
                content_en = '".DB::$link->real_escape_string($this->content_en)."', 
                authorID = '{$this->authorId}', 
                authorName = '".DB::$link->real_escape_string($this->authorName)."', 
                time = '{$this->time}',
                hidden = ".($this->hidden ? 1 : 0).",
                hiddenBy = '".intval($this->hiddenBy)."', 
                lastEdited = ".($this->lastEdited == null ? 'null' : $this->lastEdited).",
                helperstring = '".DB::$link->real_escape_string($this->helperstring)."',
				planned = '".intval($this->planned)."', 
				timeplanned = '{$this->timeplanned}'
              WHERE
                contentID = {$this->id}";
      return DB::query($sql);
    }
  }
  
  function create() {
    if ($this != null && !isset($this->id)) {
      $sql = "INSERT INTO
                forum_content
              SET
                parent = '{$this->parent}',
                boardID = '{$this->boardId}', 
                content = '".DB::$link->real_escape_string($this->content)."', 
                content_en = '".DB::$link->real_escape_string($this->content_en)."', 
                authorID = '{$this->authorId}', 
                authorName = '".DB::$link->real_escape_string($this->authorName)."', 
                time = '{$this->time}',
                hidden = ".($this->hidden ? 1 : 0).",
                hiddenBy = ".intval($this->hiddenBy).", 
                lastEdited = ".($this->lastEdited == null ? 'null' : $this->lastEdited).",
                helperstring = '".DB::$link->real_escape_string($this->helperstring)."'";
      $res = DB::query($sql);
      $this->id = DB::$link->insert_id;
    }
  }
}
?>