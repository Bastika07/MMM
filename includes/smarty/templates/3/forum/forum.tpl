{* Overall-Index des Forums *}
<table class="rahmen_allg" border=0 width="99%" cellpadding="3" cellspacing="1" align="center">
  <tr>
    <td class="forum_titel"></td>
    <td class="forum_titel">Forum</td>
    <td class="forum_titel" NOWRAP>Beiträge</td>
    <td class="forum_titel" NOWRAP>Themen</td>
    <td class="forum_titel" NOWRAP>letzter Beitrag</td>
  </tr>  
{foreach key=key item=val from=$data}
  {cycle values="dblau,hblau" assign="class"}
  <tr>
    <td class="{$class}" width=16>
    {if $val.new}{html_image file="/forumicons/new_thread.png" alt="Neue Threads im Board"}{else}{html_image file="/forumicons/no_new_thread.png" alt="Keine neuen Threads im Board"}{/if}
    </td>
    <td class="{$class}" align="left"><a href="{$filename}&board={$val.boardID}">{$val.name}</a><br><small>{$val.description}</small></td>
    <td class="{$class}" align="center">{$val.posts|default:"n/a"}</td>
    <td class="{$class}" align="center">{$val.threads}</td>
    <td class="{$class}" align="center">{$val.lastpost|date_format:"%d.%m.%Y %H:%M"|default:"n/a"}<br> <small>by {$val.lastposterName|default:"n/a"|escape}</small></td>
  </tr>
{/foreach}
</table>

{if $isLoggedIn}
	<p><a href="{$filename}&action=markAllThreadsRead">Alle Threads in allen Boards als gelesen markieren</a></p>
{/if}

<p>
<form method="post">
<input type="hidden" name="action" value="search">
<table class="rahmen_allg" border="0" cellpadding="3" cellspacing="1">
    <tr><td class="forum_titel" colspan="2">Suche</td></tr>
    <tr><td class="dblau"><input type="text" name="value"></td><td class="dblau"><input type="submit" value="suchen"></td></tr>
</table>
</form>
</p>
