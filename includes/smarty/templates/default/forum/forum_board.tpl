{* Board-Anzeige *}
<p><a href={$filename}>Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> {$board->name}</p>


<input type="button" class="button" value="Thema erstellen" onclick="document.location.href='{$filename}&action=add&board={$board->id}';"/>


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

<table class="rahmen_allg" border=0 align="center" width="99%" cellpadding='3' cellspacing='1'>
  <tr bgcolor="#e3e3e3">
    <td class="forum_titel" width="20">&nbsp;</td>
    <td class="forum_titel">Thema</td>
    <td class="forum_titel" width="50">Antworten</td>
    <td class="forum_titel" width="185">Autor</td>
    <td class="forum_titel" width="115">Letzter Post</td>
  </tr>
{foreach key=key item=val from=$data}
  {cycle values="forum_bg1,forum_bg2" assign="class"}
  
  {if !$val.hidden || $admin}
  <tr>
    <td class="{$class}" width="20">
    {assign var="new" value=$val.new}
    {assign var="hot" value=$val.hot}
    {assign var="closed" value=$val.closed}
    
    {* hidden und sticky abfangen, der rest geht auf die icon-matrix *}
    {if $val.hidden}
      <img src="forumicons/vs_alt.gif" alt="icon">
    {elseif $val.sticky}
    	{if $new}
    	  <img src="forumicons/forum_sticky_neu.gif" alt="icon">
    	{else}
      	<img src="forumicons/forum_sticky.gif" alt="icon">
      {/if}
    {else}
      <img src="forumicons/{$icons[$new][$hot][$closed]}" alt="icon">
    {/if}    
    </td>
    <td class="{$class}">
      <a href="{$filename}&thread={$val.contentID}#new">{$val.title|escape}</a> 
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
    <td class="{$class}" align="center">{$val.posts-1}</td>
    <td class="{$class}" align="center">{$val.authorName|wrap:20:' ':true|escape}</td>        
    <td class="{$class}" align="center">{$val.lastPost|date_format:"%d.%m.%Y %H:%M"} <br> <small>by {$val.lastposterName|wrap:20:' ':true|default:"n/a"|escape}</small></td>
  </tr>
  {/if}
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

<input type="button" class="button" value="Thema erstellen" onclick="document.location.href='{$filename}&action=add&board={$board->id}';"/>

{if $isLoggedIn}
<br>
<div align="left"><a class="arrow" href="{$filename}&board={$board->id}&action=markAllThreadsRead">Alle Threads in diesem Board als gelesen markieren</a></div>
{/if}
