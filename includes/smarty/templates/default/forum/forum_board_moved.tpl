{* Smarty *}
{* Template für Bestätigung *}

<p><a href="{$filename}">Forum</a> -> <a href="{$filename}&board={$boardID}">{$boardName}</a> -> <a href="{$filename}&thread={$threadId}">{$threadTitle|escape}</a></p>

{if isset($error)}
  {if $error == 'dstboardEqualsCurrentBoard'}
    Der Thread '<a href="{$filename}&thread={$threadId}">{$threadTitle|escape}</a>' befindet sich schon im Board '<a href="{$filename}&board={$boardId}">{$boardName}</a>'.
  {elseif $error == 'moveBoardFailed'}
    Der Thread '<a href="{$filename}&thread={$threadId}">{$threadTitle|escape}</a>' konnte nicht ins Board '<a href="{$filename}&board={$boardId}">{$boardName}</a>' verschoben werden.
  {else}
    {$error}    
  {/if} 
{else}
  Thread '<a href="{$filename}&thread={$threadId}">{$threadTitle|escape}</a>' erfolgreich nach Board '<a href="{$filename}&board={$boardId}">{$boardName}</a>' verschoben.
{/if}

<br><br>
<a href="{$filename}&thread={$threadId}">Zum Thread</a>