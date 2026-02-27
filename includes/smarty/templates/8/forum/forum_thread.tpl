{* Smarty *}
{* Thread-Anzeige im Forum *}

{assign var="escapedThreadTitle" value=$thread->title|escape}
{assign var="escapedBoardName" value=$board->name|escape}

{include file="../common/displayHeader.tpl" 
  title="Inside &gt; <a href=forum.htm>Forum</a> // <a href=forum.htm?board=`$board->id`>$escapedBoardName</a> // <a href=\"$filename&thread=`$thread->id`\">$escapedThreadTitle</a>"}

{if $thread->hidden && $thread->closed}
  <p class="error">&lt;Admin&gt;Dieser Thread ist von {$thread->hiddenBy} versteckt.&lt;/Admin&gt;</p>
{/if}

{if $thread->hidden && !$admin}
  {* Thread ist versteckt, wird nicht angezeigt *}
  Dieser Thread ist gesperrt
{else}

{* Seitenauswahl *}
{if isset($pages)}
  <div align="right">
  Seiten:
  {foreach key=page item=val from=$pages name=pages}
    {* aktuelle Seite wird ohne link angezeigt *}
    {if $currentPage == $page}
      {$page}
    {elseif $val == false}
      ...
    {else}
      <a href="{$filename}&thread={$thread->id}&page_forum={$page}">{$page}</a>  
    {/if}     
    {if !$smarty.foreach.pages.last} | {/if}
  {/foreach}
  </div>
{/if}

<table class="rahmen_allg" border=0 width="100%" cellspacing="1" cellpadding="3">
<tr bgcolor="#e3e3e3"><td class="forum_titel">&nbsp;</td><td class="forum_titel" align="left">{$thread->title|escape}</td></tr>

{foreach key=key item=val from=$data name=posts}
  {include file="file:post.tpl" time=$val.time lastEdited=$val.lastEdited content=$val.content contentID=$val.contentID authorID=$val.authorID authorName=$val.authorName 
                                authorClass=$val.authorClass fileaname=$filename edit=$val.edit hidden=$val.hidden hiddenBy=$val.hiddenBy admin=$admin avatar=$val.avatar}  
{/foreach}

</table>

{* Seitenauswahl *}
{if isset($pages)}
  <div align="right">
  Seiten:
  {foreach key=page item=val from=$pages name=pages}
    {* aktuelle Seite wird ohne link angezeigt *}
    {if $currentPage == $page}
      {$page}
    {elseif $val == false}
      ...
    {else}
      <a href="{$filename}&thread={$thread->id}&page_forum={$page}">{$page}</a>  
    {/if}     
    {if !$smarty.foreach.pages.last} | {/if}
  {/foreach}
  </div>
{/if}

{if $thread->closed}
  <p class="error">Thread geschlossen, keine weiteren Posts möglich</p>
{/if}

<table><tr><td>
<table cellpadding='3' cellspacing='5' border='0'>
  <tr>
    {* Thread nicht geschlossen oder User ist Admin (darf immer) *}
    {if !$thread->closed || $admin}
      <td class='forum_titel'>
        <a href="{$filename}&action=add&thread={$thread->id}" class="forumlink">Post erstellen</a>
      </td>        
    {/if}
  </tr>
</table>
</td>
<td>
</td>
</tr></table>

{if $admin}
  {if $thread->closed}
    <a href="{$filename}&action=changemode&mode=closed&post={$thread->id}">öffnen</a> (closed by '{$thread->closedByName|escape|default:unbekannt}')
  {else}
    <a href="{$filename}&action=changemode&mode=closed&post={$thread->id}">schliessen</a>
  {/if}
  ||
  {if $thread->hidden}
    <a href="{$filename}&action=changemode&mode=hidden&post={$thread->id}">un-verstecken</a> (hidden by '{$thread->hiddenByName|escape|default:unbekannt}')
  {else}
    <a href="{$filename}&action=changemode&mode=hidden&post={$thread->id}">verstecken</a>
  {/if}
  ||
  {if $thread->sticky}
    <a href="{$filename}&action=changemode&mode=sticky&post={$thread->id}">un-sticky</a> (sticky by '{$thread->stickyByName|escape|default:unbekannt}')
  {else}
    <a href="{$filename}&action=changemode&mode=sticky&post={$thread->id}">sticky</a>
  {/if}
  <form method="get" action="{$filename}" name="moveThread">
    <input type="hidden" name="action" value="moveThread">
    <input type="hidden" name="thread" value="{$thread->id}">
    <select name="dstBoardID")">
      <option>Thread verschieben nach...</option>
      {foreach item=val from=$boards key=key}
        <option value="{$key}">--> {$val}</option>
      {/foreach}
    </select>
    <input type="submit" value="go">
  </form>
{/if}

{/if}
{include file="../common/displayFooter.tpl"}