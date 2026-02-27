<?php
/* eSportArena.TV schedule */
require('controller.php');
require_once('dblib.php');
$iRecht = 'ESPORTARENATV';
require('checkrights.php');
require_once('admin/helpers.php');

$iMandantID = $_REQUEST['iMandantID'];

######## Prüfen ob actions gewählt sind
if (isset($action) && $action == "delete") {
	$sql = "DELETE FROM s
			USING eatv_schedule s,
				 RECHTZUORDNUNG r,
				eatv_channels c
			WHERE s.broadcastId = '".intval($_GET['broadcastId'])."'
			AND r.USERID = '".intval($loginID)."'
			AND r.RECHTID = 'ESPORTARENATV'
			AND r.MANDANTID = c.mandantId
			AND c.channel = s.channel
	";
	if (DB::query($sql) > 0) {
		echo "<p>Successfully deleted broadcast ID ".intval($_GET['broadcastId'])."</p>";
	} else {
		echo "<p class='fehler'>Error deleting boradcast ID ".intval($_GET['broadcastId'])." - maybe because of insufficient permissions.</p>";
		}
} else if (isset($action) && $action == "eintragen") {
	// TODO: Kausalitätsprüfungen
	if (empty($_POST['startDate']) ||
		empty($_POST['endDate']) ||
		empty($_POST['title'])
	) {
		echo "<p class='fehler'>Please complete all form fields.</p>\n";
	} else {
		$startDate = date("Y-m-d",strtotime($_POST['startDate']));
		$endDate = date("Y-m-d",strtotime($_POST['endDate']));
		$sql = "INSERT INTO eatv_schedule (
					channel,
					title,
					game,
					start,
					end,
					owner
				) VALUES (
					'".safe($_GET['channel'])."',
					'".safe($_POST['title'])."',
					'".intval($_POST['gameId'])."',
					'".safe($startDate." ".$_POST['startHour'].":".$_POST['startMinute'])."',
					'".safe($endDate." ".$_POST['endHour'].":".$_POST['endMinute'])."',
					'".intval($loginID)."'
				)
		";
		if (DB::query($sql)) {
			echo "<p>Successfully added your broadcast to the schedule.</p>";
			unset($_POST);
			header ("location: eatv_schedule.php?channel=".$_GET['channel']);
		} else {
			echo "<p class='fehler'>Error adding your broadcast to the database. Please check your input or call the administrator.</p>";
		}
	}
}
######## Ende Aktionseinheit

include('vorspann.php');

if (strlen($channel) < 1) {
    # Channel nicht gewählt.
    echo "<h1>NorthCon TV schedule - Choose channel</h1>\n";


	$sql = "SELECT c.*
			FROM eatv_channels c,
				RECHTZUORDNUNG r
			WHERE r.MANDANTID = c.mandantId
			AND r.RECHTID = 'ESPORTARENATV'
			AND r.USERID = '".intval($loginID)."'
	";

	$result = DB::query($sql);
	if ($result != false) {
		echo "<form name='channel' action='eatv_schedule.php' method='get'>\n";
		echo "<select name='channel'>\n";
		while ($row = $result->fetch_array() ) {
			echo "<option value='".$row['channel']."'> ".db2display($row['longDescription'])."</option>\n";
		}
		echo "</select>\n";
		echo "<input type='submit' value='choose channel'>\n";
		echo "</form>\n";
	} else {
		echo "<p class='fehler'>You are not authorised to administrate any channel.</p>";
	}

} else {
    # Channel ausgewählt.
	$sql = "SELECT m.BESCHREIBUNG,
				c.longDescription
			FROM MANDANT m,
				eatv_channels c
			WHERE m.MANDANTID = c.mandantId
			AND c.channel = '".safe($_GET['channel'])."'
	";
    $info = DB::getRow($sql);
    printf("<h1>NorthCon TV schedule for <em>%s</em> (%s)</h1>\n",
		db2display($info['longDescription']), db2display($info['BESCHREIBUNG']) );

?>
	<table cellspacing="0" cellpadding="0" border="0" width="900">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">
	<tr>
		<td class="navbar"><b>Start</b></td>
		<td class="navbar"><b>End</b></td>
		<td class="navbar"><b>Broadcast title</b></td>
		<td class="navbar"><b>Event</b></td>
		<td class="navbar"><b>Owner</b></td>
		<td class="navbar"><b>Action</b></td>
	</tr>
<?php
	// An den Inhalt kommt man nur, wenn man über den Mandanten auch die Rechte für den Channel hat. Kann ja sonst jeder kommen!
	$sql = "SELECT s.*,
			DATE_FORMAT(start, '%d %M %Y %H:%i') AS startF,
			DATE_FORMAT(end, '%d %M %Y %H:%i') AS endF
			FROM eatv_schedule s,
				eatv_channels c,
				RECHTZUORDNUNG r
			WHERE c.channel = '".safe($_GET['channel'])."'
			AND c.channel = s.channel
			AND r.MANDANTID = c.mandantId
			AND r.RECHTID = 'ESPORTARENATV'
			AND r.USERID = '".intval($loginID)."'
			ORDER BY s.start desc
	";

	$result = DB::query($sql);
	$count = 0;
	$bgc = 'hblau';
	if ($result != false) {
		while ($rowS = $result->fetch_array() ) {
			echo '<tr>';
			echo '<td class="'.$bgc.'">'.$rowS['startF'].'</td>';
			echo '<td class="'.$bgc.'">'.$rowS['endF'].'</td>';
			echo '<td class="'.$bgc.'">'.db2display($rowS['title']).'</td>';
			echo '<td class="'.$bgc.'"></td>';
			echo '<td class="'.$bgc.'">'.db2display(User::name($rowS['owner'])).'</td>';
			echo '<td class="'.$bgc.'">';
			echo "<form style='margin:0;' name='del' method='post' action='eatv_schedule.php?channel=".htmlspecialchars($_GET['channel'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."&action=delete&broadcastId=".intval($rowS['broadcastId'])."'>\n";
			echo "<input type='submit' value='delete'></form></td>";
			echo '</tr>';
			($bgc == 'hblau') ? $bgc = 'dblau' : $bgc = 'hblau';
			$count++;
		}
	} else {
		echo "<tr><td class='dblau' colspan='6'>There are no broadcasts for this channel.</td></tr>\n";
	}

	echo "<form name='eintragen' action='eatv_schedule.php?channel=".htmlspecialchars($_GET['channel'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."&action=eintragen' method='post'>\n";
	echo "<tr><td class='$bgc'><nobr><input type='text' style='width:80px' maxlength='10' name='startDate' id='startDate' class='datepicker' value='".( (isset($_POST['startDate'])) ? htmlspecialchars($_POST['startDate'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" )."'> ";
		echo "<select name='startHour'>\n";
		for ($i=0;$i<=23;$i++) {
			echo "<option value='".$i."'";
			echo (isset($_POST['startHour']) && $i == $_POST['startHour']) ? " selected" : "";
			echo "> ".$i."</option>\n";
		}
		echo "</select>:";
		echo "<select name='startMinute'>\n";
		for ($i=0;$i<=59;$i++) {
			echo "<option value='".$i."'";
			echo (isset($_POST['startMinute']) && $i == $_POST['startMinute']) ? " selected" : "";
			echo "> ".$i."</option>\n";
		}
		echo "</select>\n";
	echo "</nobr></td>\n";

	echo "<td class='$bgc'><nobr><input type='text' style='width:80px' maxlength='10' id='endDate' name='endDate' value='".( (isset($_POST['endDate'])) ? htmlspecialchars($_POST['endDate'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" )."' class='datepicker'> ";
		echo "<select name='endHour'>\n";
		for ($i=0;$i<=23;$i++) {
			echo "<option value='".$i."'";
			echo (isset($_POST['endHour']) && $i == $_POST['endHour']) ? " selected" : "";
			echo "> ".$i."</option>\n";
		}
		echo "</select>:";
		echo "<select name='endMinute'>\n";
		for ($i=0;$i<=59;$i++) {
			echo "<option value='".$i."'";
			echo (isset($_POST['endMinute']) && $i == $_POST['endMinute']) ? " selected" : "";
			echo "> ".$i."</option>\n";
		}
		echo "</select>\n";
	echo "</nobr></td>\n";

	echo "<td class='$bgc'><input type='text' style='width:250px' maxlength='100' name='title' value='".( (isset($_POST['title'])) ? htmlspecialchars($_POST['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" )."'></td>\n";

	echo "<td class='$bgc' colspan='2'><select name='gameId'>";
	$sql = "SELECT * 
			FROM eatv_game 
			ORDER BY gameId desc
	";
	$resultG = DB::query($sql);
	while ($rowG = $resultG->fetch_array() ) {
		echo "<option value='".$rowG['gameId']."' ";
		if (isset($_POST['gameId']) && $rowG['gameId'] == $_POST['gameId'] ) echo " selected";
		echo "> ".db2display($rowG['description'])."</option>";
	}
	echo "</select></td>\n";

	echo "<td class='$bgc'><input type='submit' value='Add' name='eintragen'></td>\n";
	echo "</td></tr></form>\n";
	
	echo "</table></td></tr></table>";

}

include('nachspann.php');
?>