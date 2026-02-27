<?php

class User2BeamerMessage {
  var $id;
  var $mandantId;
  var $userId;
  var $userName;
  var $message;
  var $createdAt;
  var $approvedAt;
  
  function load($id) {
    $message = new User2BeamerMessage();
    
    $sql = "SELECT
              u2bm.mandantId, u2bm.userId, u2bm.message, u2bm.createdAt, u2bm.approvedAt, u.LOGIN userName
            FROM
              user2beamer_messages u2bm, USER u
            WHERE
              messageId = $id AND
              u.USERID = u2bm.userId";
    $res = DB::query($sql);
    $row = mysql_fetch_assoc($res);
    
    $message->id =             (int) $id;
    $message->mandantId =      (int) $row['mandantId'];
    $message->userId =         (int) $row['userId'];
    $message->userName =       (string) $row['userName'];
    $message->createdAt =      (int) $row['createdAt'];
    $message->approvedAt =     (int) $row['approvedAt'];
    $message->message =        (string) $row['message'];
    
    return $message;
  }
  
  function save() {
    if ($this != null) {
      $sql = "UPDATE 
                user2beamer_messages
              SET
                mandantId = '{$this->mandantId}', 
                userId = '{$this->userId}', 
                message = '".mysql_escape_string($this->message)."'
              WHERE
                messageID = {$this->id}";
      return DB::query($sql);
    }
  }
  
   function create() {
    if ($this != null && !isset($this->id)) {
      $this->createdAt = time();
      $sql = "INSERT INTO
                user2beamer_messages
              (mandantId, userId, message, createdAt)
              VALUES
                ('{$this->mandantId}', '{$this->userId}', '".mysql_escape_string($this->message)."', '{$this->createdAt}')";
      $res = DB::query($sql);
      $this->id = mysql_insert_id();
    }
  }
  
  function loadAll() {
    $sql = "SELECT
              u2bm.mandantId, u2bm.userId, u2bm.message, u2bm.createdAt, u.LOGIN userName
            FROM
              user2beamer_messages u2bm, USER u
            WHERE
              u.USERID = u2bm.userId
            ORDER BY
              u2bm.createdAt DESC";          
    $res = DB::query($sql);
    
    $messages = array();
    
    while ($row = mysql_fetch_assoc($res)) {    
      $message = new User2BeamerMessage();
      $message->id =             (int) $id;
      $message->mandantId =      (int) $row['mandantId'];
      $message->userId =         (int) $row['userId'];
      $message->userName =       (string) $row['userName'];
      $message->message =        (string) $row['message'];
      $message->createdAt =     (int) $row['createdAt'];
      $message->approvedAt =     (int) $row['approvedAt'];
      $messages[] = $message;
    }
    
    return $messages;
  }
  
  function loadAllForUser($userId) {
    $sql = "SELECT
              u2bm.mandantId, u2bm.userId, u2bm.message, u2bm.createdAt, u.LOGIN userName
            FROM
              user2beamer_messages u2bm, USER u
            WHERE
              u2bm.userId = $userId AND
              u.USERID = u2bm.userId 
            ORDER BY
              u2bm.createdAt DESC";          
    $res = DB::query($sql);
    
    $messages = array();
    
    while ($row = mysql_fetch_assoc($res)) {    
      $message = new User2BeamerMessage();
      $message->id =             (int) $id;
      $message->mandantId =      (int) $row['mandantId'];
      $message->userId =         (int) $row['userId'];
      $message->userName =       (string) $row['userName'];
      $message->message =        (string) $row['message'];
      $message->createdAt =     (int) $row['createdAt'];
      $message->approvedAt =     (int) $row['approvedAt'];
      $messages[] = $message;
    }
    
    return $messages;
  }
  
  /*function loadApproved() {
    $sql = "SELECT
              u2bm.mandantId, u2bm.userId, u2bm.message, u2bm.approvedAt, u.LOGIN userName
            FROM
              user2beamer_messages u2bm, USER u
            WHERE
              u2bm.approvedAt IS NOT NULL AND
              u.USERID = u2bm.userId
            ORDER BY
              u2bm.approvedAt DESC";              
    $res = DB::query($sql);
    
    $messages = array();
    
    while ($row = mysql_fetch_assoc($res)) {    
      $message = new User2BeamerMessage();
      $message->id =             (int) $id;
      $message->mandantId =      (int) $row['mandantId'];
      $message->userId =         (int) $row['userId'];
      $message->userName =       (string) $row['userName'];
      $message->message =        (string) $row['message'];
      $message->approvedAt =     (int) $row['approvedAt'];
      $messages[] = $message;
    }
    
    return $messages;
  }*/
  
}
?>
