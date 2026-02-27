<?php
include_once "getsession.php";

if (isset($nLoginID) && $nLoginID > 0) {
	$loginID = $nLoginID; # Übersetzung der Login-Variable
	$login = $sLogin;
} else {

  header('Location: login.php?sRefer=' . $_SERVER['REQUEST_URI']);
  exit;
}
?>
