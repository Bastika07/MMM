{* Smarty *}
{* Template für Bestätigung *}

{if $error}
  {$error}
{else}
  {if $action == 'created'} 
    {assign var="action" value="angelegt"}
  {elseif $action == 'changed'}
    {assign var="action" value="geändert"}
  {/if}

  
  
  <p><a href="{$filename}">Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name}</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&thread={$thread->id}">{$thread->title}</a></p>
  <p>
  {if $what == 'post'}
    Post erfolgreich {$action}. 
    <br><br>
    <a href="{$filename}&thread={$thread->id}#post_{$post->id}"><img src="/gfx/headline_pfeil.gif" border="0"> Zum Post</a>
  {elseif $what == 'thread'}
    Thread erfolgreich {$action}. 
    <br><br>
    <a href="{$filename}&thread={$thread->id}"><img src="/gfx/headline_pfeil.gif" border="0"> Zum Thread</a>
  {/if}
  </p>
  
{/if}
