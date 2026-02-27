<?php
include_once "/var/www/include/dblib.php";
include_once "/var/www/include/format.php";


require_once('classes/PelasSmarty.class.php');
 

$smarty = new PelasSmarty('events');

$sql = "SELECT
	  id, unix_timestamp(start) start, name
	FROM
	  timetable
	WHERE
	  start > NOW() AND
	  start < NOW() + INTERVAL 2 HOUR
	ORDER BY
	  start";
$res = DB::query($sql);
if ($res->num_rows == 0) {
	// Kein Event in den nächsten 2h, nächstes Event
	$sql = "SELECT
		  id, unix_timestamp(start) start, name 
		FROM
		  timetable
		WHERE
		  start > NOW()
		ORDER BY
		  start
		LIMIT 1";
	$res = DB::query($sql);
	$events[] = $res->fetch_assoc();
} else {
	while ($row = $res->fetch_assoc())
		$events[] = $row;
}

$smarty->assign('events', $events);
$smarty->displayWithFallback('coming_up.tpl');

?>
