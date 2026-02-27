<?php
require_once('dblib.php');
require_once('session.php');
require_once('format.php');

if ($nLoginID > 0) {
	echo '<a class="loginlink" href="/login_edit.php"><small>User: ' . db2display($sLogin) . '</small></a>' . "\n";
	echo ' | <a class="loginlink" href="/login.php?Action=logout"><small>Logout</small></a>' . "\n";
} else {
	echo '<a href="/login.php" class="loginlink"><small>Login</small></a>' . "\n";
}
?>
