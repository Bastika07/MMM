{* Board-Anzeige *}
{include file="../common/displayHeader.tpl" 
  title="Inside &gt; <a href=forum.htm>Forum</a> // <a href=forum.htm?board=`$board->id`>`$board->name`</a>"}

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
      <a href="{$filename}&board={$board->id}&page_forum={$page}">{$page}</a>  
    {/if}     
    {if !$smarty.foreach.pages.last} | {/if}
  {/foreach}
  </div>
{/if}

<table class="rahmen_allg" border=0 align="center" width="100%" cellpadding='3' cellspacing='1'>
  <tr bgcolor="#e3e3e3">
    <td class="forum_titel" width="20">&nbsp;</td>
    <td class="forum_titel">Thema</td>
    <td class="forum_titel" width="50">Posts</td>
    <td class="forum_titel" width="185">Autor</td>
    <td class="forum_titel" width="115">Letzter Post</td>
  </tr>
{foreach key=key item=val from=$data}
  <tr bgcolor="#e3e3e3">
    <td class="forum_bg1" width="20">    
    {* hidden und sticky abfangen, der rest geht auf die icon-matrix *}
    {if $val.hidden}
      <img src="forumicons/vs_alt.gif" alt="icon">
    {elseif $val.sticky}
      <img src="forumicons/forum_sticky.gif" alt="icon">
    {else}
      <img src="forumicons/{$icons[$val.new][$val.hot][$val.closed]}" alt="icon">
    {/if}
    </td>
    <td class="forum_bg1">
      <a href="{$filename}&thread={$val.contentID}&page_forum={$val.lastPostOnPage}#new">{$val.title|escape}</a> 
      <br>
      {if count($val.pages) != 1}
      {strip}
      <small>[Seiten:&nbsp; 
      {foreach key=page item="pageval" from=$val.pages name="pages"}
        {if !$pageval}
          ...
        {else}
          <a href="{$filename}&thread={$val.contentID}&page_forum={$page}">{$page}</a> 
        {/if}     
        {if !$smarty.foreach.pages.last && $pageval},{/if}
        &nbsp;
      {/foreach}
      ]</small>
      {/strip}
      {/if}
      {if $val.hidden}<span class="error">&lt;Admin&gt;Dieser Thread ist versteckt.&lt;/Admin&gt;</span>{/if}      
    </td>
    <td class="forum_bg1" align="center">{$val.posts}</td>
    <td class="forum_bg1" align="center">{$val.authorName|escape}</td>        
    <td class="forum_bg1" align="center">{$val.lastPost|date_format:"%d.%m.%Y %H:%M"} <br> <small>by {$val.lastposterName|default:"n/a"|escape}</small></td>
  </tr>
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
      <a href="{$filename}&board={$board->id}&page_forum={$page}">{$page}</a>  
    {/if}     
    {if !$smarty.foreach.pages.last} | {/if}
  {/foreach}
  </div>
{/if}

<a href="{$filename}&action=add&board={$board->id}" class="forumlink"><img src="gfx_struct2/btn_newthread.gif" border=0></a>
<a href="{$filename}" class="forumlink"><img src="gfx_struct2/btn_boardindex.gif" border=0></a>

{if $isLoggedIn}
<br>
<div align="left"><a href="{$filename}&board={$board->id}&action=markAllThreadsRead">Alle Threads in diesem Board als gelesen markieren</a></div>
{/if}

{include file="../common/displayFooter.tpl"}
