{* Overall-Index des Forums *}
{include file="../common/displayHeader.tpl" title="Inside &gt; <a href=forum.htm>Forum</a>"}
<table class="rahmen_allg" border=0 width="100%" cellpadding="3" cellspacing="1" align="center">
  <tr>
    <td class="forum_titel">Forum</td>
    <td class="forum_titel" NOWRAP>Beiträge</td>
    <td class="forum_titel" NOWRAP>Themen</td>
    <td class="forum_titel" NOWRAP>letzter Beitrag</td>
  </tr>
{foreach key=key item=val from=$data}
  {cycle values="dblau,hblau" assign="class"}
  <tr>
    <td class="{$class}"><a href="{$filename}&board={$val.boardID}">{$val.name}</a><br><small>{$val.description}</small></td>
    <td class="{$class}" align="center">{$val.posts|default:"n/a"}</td>
    <td class="{$class}" align="center">{$val.threads}</td>
    <td class="{$class}" align="center">{$val.lastpost|date_format:"%d.%m.%Y %H:%M"|default:"n/a"}<br> <small>by {$val.lastposterName|default:"n/a"|escape}</small></td>
  </tr>
{/foreach}
</table>

{if $isLoggedIn}
	<p><a href="{$filename}&action=markAllThreadsRead">Alle Threads in allen Boards als gelesen markieren</a></p>
{/if}
{include file="../common/displayFooter.tpl"}