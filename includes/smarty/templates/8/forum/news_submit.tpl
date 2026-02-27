{* Smarty *}
{* Template für Bestätigung *}

{assign var="escapedThreadTitle" value=$thread->title|escape}

{include file="../common/displayHeader.tpl" 
  title="Home &gt; <a href=\"$filename\">News</a> // <a href=\"$filename&action=showComments&newsID=`$thread->id`\">$escapedThreadTitle</a>" noDisclaimer=1}

{if isset($error) && $error}
  {$error}
{else}

  {if $action == 'created'}
    {assign var="action" value="angelegt"}
  {elseif $action == 'changed'}
    {assign var="action" value="geändert"}
  {/if}



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

{include file="../common/displayFooter.tpl"}
