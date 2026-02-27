{* Design für Newspost *}

{if $lang == 'en' && $content_en != ""}
  {assign var=title value=$title_en}
  {assign var=content value=$content_en}
{/if}


<table width="99%">
  <tr>
    <td class="pelas_newstitle">{$title}</td>
  </tr>
  <tr>
    <td class="pelas_newsautor"><img src="/gfx/headline_pfeil.gif" border="0">
      {$time|date_format:"%d.%m.%Y %H:%M"} 
      {$authorName|wrap:20:' ':true|escape}</td>
  </tr>
  <tr>
    <td valign="top"><p align="justify">    
      {if $short}
        {$content|smileys:$smileyDir|bbcode2html|nl2br|truncate:$short}
      {else}
        {$content|smileys:$smileyDir|bbcode2html|nl2br}
      {/if}</p></td>      
  </tr>
  <tr>
    <td><img src="/gfx/lgif.gif" width="0" height="4"></td>
  </tr>

  <tr>
    <td align="right">
    {if $showLink}
      {if $posts == 1}
        <a href="{$filename}&action=addComment&newsID={$contentID}">Kommentar abgeben</a>
      {else}
        <a href="{$filename}&action=showComments&newsID={$contentID}">Kommentare ({$posts-1})</a>  
      {/if}
    {/if}
  </td>
  </tr>

  <tr>
    <td>{if $showBar}<hr>{/if}</td>
  </tr>
</table>

