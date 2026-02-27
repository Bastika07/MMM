<?php
include_once "dblib.php";
include_once "format.php";
include_once "new_forum.php";


/*
* Forenpostings am oberen Rand. Es werden 5 Postings in einer Tabelle gezeigt.
* Die Einträge sind auf 18 Chars begrenzt worden
*/

if ($nLaenge < 1) {
	define('MAXLENGTH', 17);
} else {
	define('MAXLENGTH', $nLaenge);
}


$forum = new forum($nPartyID, '');
$threads = $forum->forumActivity(5);

if ($sStyle == "newsticker") {
	// Nur den ersten Eintrag in Textform mit Newsticker-Link zurück geben
	if (!is_array($threads)) {
		echo "<a class=\"newstickerlink\" href=\"/forum.php\">(keine)</a>";
	} else {
		echo "<a class=\"newstickerlink\" href=\"/forum.php?thread=".$threads[0]['contentID']."\">".db2display($threads[0]['title'])."</a>";
	}
} else {
	// 5 Einträge ausgeben
	if (is_array($threads)) {
	  echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">";
	  foreach ($threads as $thread) {
	    $fullTitle = $thread['title'];
	    $time = date('H:i', $thread['lastpost']);
		if (strlen($fullTitle) > MAXLENGTH) {
			$title = db2display(substr($fullTitle, 0, MAXLENGTH)."...");
		} else {
			$title = db2display($fullTitle);
		}
		$fullTitle = db2display($fullTitle);
		$authorName = $thread['lastposterName'];
		$lastpost = date('d.m.Y H:i', $thread['lastpost']);

	    $fullTitle = htmlentities($fullTitle, ENT_QUOTES);
	    $overlibText = "onMouseOver=\"return overlib('<b>Titel:</b> $fullTitle<br><b>Autor:</b> $authorName<br><b>Zeit:</b> $lastpost', FGCOLOR, '#FFFFFF', BGCOLOR, '#CCCCCC');\" onMouseOut=\"return nd();\"";

		echo "<tr><td class=\"box_content\">
	    <font class=\"latest_forum\">$time</font>
	    </td>
	    <td class=\"box_content\"><img src=\"/gfx_struct/lgif.gif\" width=\"8\" height=\"0\" border=\"0\"></td>
	    <td class=\"box_content\">
	    <a class=\"latest_forum\" href=\"/forum.php?thread=".$thread['contentID']."#new\" $overlibText>$title</a>
	    </td></tr>";
	  }
	  echo "</table>";
	}
}
?>
