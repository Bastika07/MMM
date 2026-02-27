{* Smarty *}
{* Thread-Anzeige im Forum *}

<p><a href="{$filename}">Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name|escape}</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a></p>

{if $thread->hidden && $thread->closed}
  <p class="error">&lt;Admin&gt;Dieser Thread ist von '{$thread->hiddenByName|escape|default:unbekannt}' versteckt.&lt;/Admin&gt;</p>
{/if}

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
  <form method="post" action="{$filename}" name="moveThread">
    {csrf_field}
    <input type="hidden" name="action" value="moveThread">
    <input type="hidden" name="thread" value="{$thread->id}">
    <select name="dstBoardID" onChange="javascript: submit()">
      <option>Thread verschieben nach.....</option>
      {foreach item=val from=$boards key=key}
        <option value="{$key}">--> {$val}</option>
      {/foreach}
    </select>
  </form>
{/if}

{* Thread nicht geschlossen oder User ist Admin (darf immer) *}
{if !$thread->closed || $admin}
  <input type="button" class="button" value="Post erstellen" onclick="document.location.href='{$filename}&action=add&thread={$thread->id}';"/>
{/if}

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

{if $thread->hidden && !$admin}
  {* Thread ist versteckt, wird nicht angezeigt *}
  Dieser Thread ist gesperrt
{else}

<table class="rahmen_allg" border=0 width="99%" cellspacing="1" cellpadding="3">
<tr bgcolor="#e3e3e3"><td class="forum_titel" width="120">&nbsp;</td><td class="forum_titel" align="left">{$thread->title|escape}</td></tr>

{foreach key=key item=val from=$data name=posts}
  {include file="file:post.tpl" time=$val.time lastEdited=$val.lastEdited content=$val.content contentID=$val.contentID authorID=$val.authorID authorName=$val.authorName
                                authorClass=$val.authorClass edit=$val.edit hidden=$val.hidden hiddenBy=$val.hiddenBy avatar=$val.avatar passes=$val.passes}
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

<table>
<tr>
<td>
  {* Thread nicht geschlossen oder User ist Admin (darf immer) *}
  {if !$thread->closed || $admin}
     <input type="button" class="button" value="Post erstellen" onclick="document.location.href='{$filename}&action=add&thread={$thread->id}';"/>
  {/if}
</td>
<td>
  <p><a href="{$filename}">Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name|escape}</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a></p>
</td>
</tr></table>
{/if}
