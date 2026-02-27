{* Smarty *}
{* Template für Bestätigung *}

{assign var="escapedThreadTitle" value=$thread->title|escape}

{include file="../common/displayHeader.tpl" 
  title="Inside &gt; <a href=forum.htm>Forum</a> // <a href=forum.htm?board=`$board->id`>`$board->name`</a> // <a href=\"$filename&thread=`$thread->id`\">$escapedThreadTitle</a>"}
{if $error}
  {$error}
{else}
  {if $action == 'created'}
    {assign var="action" value="angelegt"}
  {elseif $action == 'changed'}
    {assign var="action" value="geändert"}
  {/if}

  {if $what == 'post'}
    Post erfolgreich {$action}.
    <br><br>
    <a href="{$filename}&thread={$thread->id}#post_{$jumpToPost}">Zum Post</a>
  {elseif $what == 'thread'}
    Thread erfolgreich {$action}.
    <br><br>
    <a href="{$filename}&thread={$thread->id}">Zum Thread</a>
  {/if}
{/if}

{include file="../common/displayFooter.tpl"}
