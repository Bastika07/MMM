<?php
include_once "dblib.php";
include_once "format.php";
include_once "new_forum.php";


if ($nLaenge < 1) {
	define('MAXLENGTH', 19);
} else {
	define('MAXLENGTH', $nLaenge);
}

$forum = new forum($nPartyID, '', '', BT_NEWS);
$news = $forum->forumActivity(5);

if ($sStyle == "newsticker") {
  // Nur 1. Eintrag für Newsticker anzeigen
  if (!is_array($news)) {
    echo "<a class=\"newstickerlink\" href=\"/news.php\">(keine)</a>";
  } else {
    if ($sLang == 'en')
      $newstitle = $news[0]['title_en'];
    else
      $newstitle = $news[0]['title'];
    echo "<a href=\"/news.php?action=showComments&newsID=".$news[0]['contentID']."\" class=\"newstickerlink\">".db2display($newstitle)."</a>";
  }

} else if (is_array($news)) {
  echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
  foreach ($news as $val) {
    $fullTitle = $val['title'];
    $date = date('d.m.', $val['time']);
    $time = date('H:i', $val['time']);
  
  	if (strlen($fullTitle) > MAXLENGTH) {
  		$title = db2display(substr($fullTitle, 0, MAXLENGTH)."...");
  	} else {
  		$title = db2display($fullTitle);
  	} 	
  	$fullTitle = db2display($fullTitle);
  	$authorName = $val['authorName'];
  	$lastpost = date('d.m.Y H:i', $val['time']);
  	
  	$overlibText = "onMouseOver=\"return overlib('<b>Titel:</b> $fullTitle<br><b>Autor:</b> $authorName<br><b>Zeit:</b> $lastpost', FGCOLOR, '#FFFFFF', BGCOLOR, '#CCCCCC');\" onMouseOut=\"return nd();\"";
  	 	
    // Zeit weggenommen damit mehr Titel reinpasst <td width=\"32\" class=\"box_content\"><font class=\"latest_date\">$time</font>&nbsp;</td>
   	echo "<tr>
   	<td class=\"box_content\"><font class=\"latest_forum\">$date</font></td>
   	<td><img src=\"/gfx_struct/lgif.gif\" width=\"8\" height=\"0\" border=\"0\"></td>
   	<td><a class=\"latest_forum\" href=\"/news.php?action=showComments&newsID=".$val['contentID']."\" $overlibText>$title</a></td></tr>";
  }
  echo "</table>";
}
?>
