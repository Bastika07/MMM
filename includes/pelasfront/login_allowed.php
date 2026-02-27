<?php
include_once "dblib.php";
include_once "language.inc.php";

if (!isset($dbh))
	$dbh = DB::connect();

	$result = mysql_db_query ($dbname, "select NUTZBAR from SESSION where SESSIONID='$PELASSESSID'", $dbh);
	$row = mysql_fetch_array($result);
	if ($row[NUTZBAR] == 'J') {
		echo "1";
	} else {
		echo "0";
	}
?>
