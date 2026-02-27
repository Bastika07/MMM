{* Smarty *}
{* Template für Bestätigung *}

{assign var="escapedThreadTitle" value=$threadTitle|escape}

{include file="../common/displayHeader.tpl" 
  title="Inside &gt; <a href=forum.htm>Forum</a> // <a href=forum.htm?board=$boardId>$boardName</a> // <a href=\"$filename&thread=$threadId\">$escapedThreadTitle</a>"
  noDisclaimer=1}

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

{include file="../common/displayFooter.tpl"}