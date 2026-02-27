{* Smarty *}
{* Template für Bestätigung *}

<img src="/style/content_bg_top.png" width="800" height="18"><br>
<table cellspacing="0" cellpadding="0" border="0" width="800">
<tr>
<td style="background: url('/style/content_bg.png') repeat-y; padding:1px 40px 10px 40px;">

<h1>News kommentieren</h1>

{if isset($error) && $error}
  {$error}
{else}

  {if $action == 'created'}
    {assign var="action" value="angelegt"}
  {elseif $action == 'changed'}
    {assign var="action" value="geändert"}
  {/if}



  <p><a href="news.htm">News</a> -> <a href="news.htm?action=showComments&newsID={$thread->id}">{$thread->title}</a></p>
  <p>
  {if $what == 'post'}
    Kommentar erfolgreich {$action}.
    <br><br>
    <a href="news.htm?action=showComments&newsID={$thread->id}#post_{$post->id}"><img src="/gfx/headline_pfeil.gif" border="0">Zum Kommentar</a>
  {elseif $what == 'thread'}
    Thread erfolgreich {$action}.
    <br><br>
    <a href="news.htm?action={$thread->id}"><img src="/gfx/headline_pfeil.gif" border="0">Zur News</a>
  {/if}
  </p>
{/if}

<br><br>

</td>
</tr>
<tr><td height="1"><img src="/style/content_bg_bottom.png" width="800" height="1"></td></tr>
</table>