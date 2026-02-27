<?php

include_once "constants.php";
include_once "dblib.php";

function _session_open($save_path, $session_name) {
  // Connection is already established by DB::connect() in dblib.php
  return true;
}

function _session_close() {
  return true;
}

function _session_read($id) {
  if (! preg_match('/^([0-9a-f]{32})$|/i',$id)) return NULL;
  $id = DB::$link->real_escape_string($id);
  $res = DB::$link->query('SELECT `session_data` FROM `php_session` WHERE `session_id` = \'' . $id . '\'');
  if (!(list($session_data) = $res->fetch_row())) {
    return '';
  } else
    return $session_data;

}

function write2file($string, $file) {
  $fp = fopen($file, 'a');
  fwrite($fp, $string);
  fclose($fp);
}

function _session_write($id, $sess_data) {
	global $nPartyID;
  _session_open('','');
  if (! preg_match('/^([0-9a-f]{32})$|/i',$id)) return NULL;   
  $data = substr($sess_data, strpos($sess_data, '|') + 1);
  if (!empty($data)) {
    $sess_array = unserialize($data);
    $nLoginID = $sess_array['nLoginID'];
  }
	
  if (!isset($nLoginID)) 
    $nLoginID = '';
  
  $sess_data = DB::$link->real_escape_string($sess_data);
  $id = DB::$link->real_escape_string($id);
  $query = 'REPLACE INTO `php_session` (`session_id`, `session_data`, `userId`, `mandantId`) VALUES (\'' . 
    $id . '\', \'' . $sess_data . '\', \'' . intval($nLoginID) . '\', \'' . intval($nPartyID) . '\')';
		
  DB::$link->query($query) || die(DB::$link->error);
  return true;
}

function _session_destroy($id) {
  _session_open('','');
  if (! preg_match('/^([0-9a-f]{32})$|/i',$id)) return NULL;
  $id = DB::$link->real_escape_string($id);
  DB::$link->query('DELETE FROM `php_session` WHERE `session_id` = \'' . $id . '\'');
  return DB::$link->affected_rows == 1;
}

function _session_gc($maxlifetime) {
  _session_open('','');
  DB::$link->query('DELETE FROM `php_session` WHERE UNIX_TIMESTAMP(`session_time`) < UNIX_TIMESTAMP()-'.$maxlifetime);
  return true;
}

if (!session_set_save_handler("_session_open", "_session_close", "_session_read", "_session_write", "_session_destroy", "_session_gc")) 
  die("Could not initiate Session Handler");

session_start();

if (isset($_SESSION['MMMSESSION']['nLoginID'])) {
	$sLogin	  = $_SESSION['MMMSESSION']['sLogin'];
	$nLoginID = $_SESSION['MMMSESSION']['nLoginID'];
} else {
	$sLogin	  = "";
	$nLoginID = "";
}

// Ensure a CSRF token exists for this session and verify on POST requests.
csrf_token();
csrf_verify();

// Wenn keine aktuelle Session beim Client gespeichert ist
if ( ( !isset($_SESSION['MMMSESSION']['nLoginID']) || $_SESSION['MMMSESSION']["nLoginID"] < 1 ) && isset($_COOKIE['pelasGlobalLogin']) ) {
	// Nach einem token im vorhandenen global-Cookie schauen
	$theSplit = explode(';', $_COOKIE['pelasGlobalLogin']);
	$theUser  = $theSplit[0];
	$theToken = $theSplit[1];

	// Token und userId in der Datenbank suchen
	$sql = "select 
						g.userId,
						u.LOGIN
					from 
						userGlobalLogin g,
						USER u
					where 
						g.mandantId = '".intval($nPartyID)."' and 
						g.userId = '".intval($theUser)."' and
						g.token = '".safe($theToken)."' and
						g.userId = u.USERID
	";
	$result = DB::getRow($sql);

	if ($result['userId'] > 0) {
		// Ein gültiges Token liegt vor, einloggen!
		$_SESSION['MMMSESSION']["nLoginID"] = $result['userId'];
		$_SESSION['MMMSESSION']["sLogin"] = $result['LOGIN'];
		$nLoginID = $result['userId'];
		$sLogin = $result['LOGIN'];

		// Neues Bla-Hash generieren, neuen Cookie setzen
		$theToken = bin2hex(random_bytes(16));
		setcookie("pelasGlobalLogin", $theUser.';'.$theToken, time()+45240000);

		// Daten in die Tabelle userGlobalLogin schreiben
		$sql = "replace into userGlobalLogin
							(mandantId, userId, token, wannGeaendert)
						values
							('$nPartyID', '".intval($theUser)."', '".safe($theToken)."', NOW())";
    if (!DB::query($sql)) {
	      $str = "could not save permanent login";
       PELAS::logging($str, 'login', $theUser);
		}
	}

}

// Encapsulate the resolved auth state in a value-object.
// New code should use $authState (or accept it as a parameter) rather than
// the bare $nLoginID / $sLogin / $loginID globals.  The globals are kept for
// backward compatibility with existing code.
$authState = new AuthState($nLoginID, $sLogin);
?>