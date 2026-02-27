{* Smarty *}
{* Template für Bestätigung *}

{if isset($error) && $error}
  {$error}
{else}
  {if $action == 'created'} 
    {assign var="action" value="angelegt"}
  {elseif $action == 'changed'}
    {assign var="action" value="geändert"}
  {/if}

  
  
  <p><a href="?page=2">News</a> -> <a href="?page=2&action=showComments&newsID={$thread->id}">{$thread->title}</a></p>
  <p>
  {if $what == 'post'}
    Kommentar erfolgreich {$action}. 
    <br><br>
    <a href="?page=2&action=showComments&newsID={$thread->id}#post_{$jumpToPost}"><img src="/gfx/headline_pfeil.gif" border="0">Zum Kommentar</a>
  {elseif $what == 'thread'}
    Thread erfolgreich {$action}. 
    <br><br>
    <a href="?page=2&action={$thread->id}"><img src="/gfx/headline_pfeil.gif" border="0">Zur News</a>
  {/if}
  </p>
{/if}