<?php
include_once "getsession.php";

if ($authState->isLoggedIn()) {
	$loginID = $authState->nLoginID; # Übersetzung der Login-Variable
	$login = $authState->sLogin;
} else {

  header('Location: login.php?sRefer=' . $_SERVER['REQUEST_URI']);
  exit;
}
?>
