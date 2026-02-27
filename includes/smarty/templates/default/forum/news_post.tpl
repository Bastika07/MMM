{* Design für Newspost *}

{if $lang == 'en' && $content_en != ""}
  {assign var=title value=$title_en}
  {assign var=content value=$content_en}
{/if}

  <li style="list-style:none">
<table width="100%" style="padding:1.1%">
  <tr>
    <td class="pelas_newstitle">{$title}</td>
  </tr>
  <tr>
    <td class="pelas_newsautor"><img src="/gfx/headline_pfeil.gif" border="0">
      {$time|date_format:"%d.%m.%Y %H:%M"} 
      {$authorName|wrap:20:' ':true|escape}</td>
  </tr>
  <tr>
    <td valign="top"><p align="justify" class="news_content">    
      {if $short}
        {$content|smileys:$smileyDir|bbcode2html|nl2br|truncate:$short}
      {else}
        {$content|smileys:$smileyDir|bbcode2html|nl2br}
      {/if}</p></td>      
  </tr>
  <tr>
    <td align="right">
    {if $showLink}
      {if $posts == 1}
        <a href="?page=2&page_forum=2&action=addComment&newsID={$contentID}"><i class="fa fa-comment-o"></i> Kommentar abgeben</a>
      {else}
        <a href="?page=2&page_forum=2&action=showComments&newsID={$contentID}"><i class="fa fa-comments-o"></i> Kommentare ({$posts-1})</a>
      {/if}
    {/if}
  </td>
  </tr>

</table>  </li>

