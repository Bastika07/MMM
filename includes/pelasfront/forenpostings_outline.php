<?php
include_once "dblib.php";
include_once "format.php";
include_once "new_forum.php";


/*
* Forenpostings am rechten Rand. Es werden 12 Postings in einer Tabelle gezeigt.
* Die Einträge sind auf 10 Chars begrenzt worden
*/


$forum = new forum($nPartyID, '');
$threads = $forum->forumActivity(12);

if (is_array($threads)) {
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
  foreach ($threads as $thread) {
    $fullTitle = $thread['title'];
    $time = date('H:i', $thread['lastpost']);
  	if (strlen($fullTitle) > 10) {
  		$title = db2display(substr($fullTitle, 0, 10)."...");
  	} else {
  		$title = db2display($fullTitle);
  	}
  	$fullTitle = db2display($fullTitle);
  	$authorName = $thread['lastposterName'];
  	$lastpost = date('d.m.Y H:i', $thread['lastpost']);
  
    $fullTitle = htmlentities($fullTitle, ENT_QUOTES);
    $overlibText = "onMouseOver=\"return overlib('<b>Titel:</b> $fullTitle<br><b>Author:</b> $authorName<br><b>Zeit:</b> $lastpost', FGCOLOR, '#FFFFFF', BGCOLOR, '#CCCCCC');\" onMouseOut=\"return nd();\"";
  
   	echo "<tr><td class=\"box_content\">
    <font class=\"latest_date\">$time</font>
    </td>
    <td class=\"box_content\">&nbsp; &nbsp;</td>
    <td class=\"box_content\">
    <a class=\"latest_link\" href=\"forum.php?thread=".$thread['contentID']."\" $overlibText>$title</a>
    </td></tr>";
  }
  echo "</table>";
}
?>