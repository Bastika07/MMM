{* Smarty *}
{* Template für Bestätigung *}

{php}
  // Contentborder includen
  startContent("News kommentieren");
{/php}

{if isset($error) && $error}
  {$error}
{else}

  {if $action == 'created'}
    {assign var="action" value="angelegt"}
  {elseif $action == 'changed'}
    {assign var="action" value="geändert"}
  {/if}



  <p><a href="news.htm">News</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="news.htm?action=showComments&newsID={$thread->id}">{$thread->title}</a></p>
  <p>
  {if $what == 'post'}
    Kommentar erfolgreich {$action}.
    <br><br>
    <a href="news.htm?action=showComments&newsID={$thread->id}#post_{$post->id}"><img src="/gfx/headline_pfeil.gif" border="0"> Zum Kommentar</a>
  {elseif $what == 'thread'}
    Thread erfolgreich {$action}.
    <br><br>
    <a href="news.htm?action={$thread->id}"><img src="/gfx/headline_pfeil.gif" border="0"> Zur News</a>
  {/if}
  </p>
{/if}

{php}
  // Contentborder includen
  endContent();
{/php}
