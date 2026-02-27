{* Design für Newspost, Bild oben *}

{if $lang == 'en' && $content_en != ""}
  {assign var=title value=$title_en}
  {assign var=content value=$content_en}
{/if}

<img src="{$helperstring}" width="796" height="486"><br>
<img src="/style/content_bg_top.png" width="800" height="18"><br>

<table cellspacing="0" cellpadding="0" border="0" width="800">
<tr>
<td style="background: url('/style/content_bg.png') repeat-y; padding:1px 40px 10px 40px;">

<a style="text-decoration: none" href="{$filename}&action=showComments&newsID={$contentID}"><h1>{$title}</h1></a>

{$content|smileys:$smileyDir|bbcode2html|nl2br}

<p style="text-align:right;">{$time|date_format:"%d.%m.%Y"} | {$time|date_format:"%H:%M"} | {$authorName|escape} |
	{if $posts-1 == 0}
		<a class="navlink" href="{$filename}&action=addComment&newsID={$contentID}">Dein Kommentar</a>
	{else}
		<a class="navlink" href="{$filename}&action=showComments&newsID={$contentID}">{$posts-1} Kommentare</a>
	{/if}
</p>

</td>
</tr>
</table>
<img src="/style/content_bg_bottom.png" width="800" height="1">
<br><br>
