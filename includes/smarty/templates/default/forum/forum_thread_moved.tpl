{* Smarty *}
{* Template für Bestätigung *}


<p><a href="{$filename}">Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name}</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a></p>

{if isset($error)}
  {if $error == 'dstboardEqualsCurrentBoard'}
    Der Thread '<a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>' befindet sich schon im Board '<a href="{$filename}&board={$board->id}">{$board->name}</a>'.
  {elseif $error == 'moveBoardFailed'}
    Der Thread '<a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>' konnte nicht ins Board '<a href="{$filename}&board={$board->id}">{$board->name}</a>' verschoben werden.
  {else}
    {$error}    
  {/if} 
{else}
  Thread '<a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>' erfolgreich nach Board '<a href="{$filename}&board={$board->id}">{$board->name}</a>' verschoben.
{/if}

<br><br>
<a href="{$filename}&thread={$thread->id}"><img src="/gfx/headline_pfeil.gif" border="0"> Zum Thread</a>
<!-- moved to {$thread->id} -->
