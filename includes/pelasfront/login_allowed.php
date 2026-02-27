<?php
include_once "dblib.php";
include_once "language.inc.php";

if (!isset($dbh))
	$dbh = DB::connect();

	$result = DB::query("select NUTZBAR from SESSION where SESSIONID='$PELASSESSID'");
	$row = $result->fetch_array();
	if ($row[NUTZBAR] == 'J') {
		echo "1";
	} else {
		echo "0";
	}
?>
