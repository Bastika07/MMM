{* Smarty *}
{* Template für Bestätigung *}

{* Padding speziell für NorthCon-Page *}
<table cellspacing="0" cellpadding="0" border="0" width="571">
<tr>
  <td width="571" height="25" valign="bottom" class="pelas_newstitle" style="background-image:url(/gfx_struct/td_bg_header.png); background-repeat:no-repeat;">&nbsp;&nbsp;&nbsp;Newskommentare</td>
</tr><tr>
  <td valign="top" style="background-image:url(/gfx_struct/td_bg_content.png); background-repeat:repeat-y;">
    <table cellspacing="0" cellpadding="12" border="0" width="571">
    <tr>
      <td>

<p>
{if isset($error)}
  {$error}
{else}
	{if $board->type == $smarty.const.BT_FORUM}
		<a href="{$filename}">Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name}</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>
		<br><br>
		Mode erfolgreich geändert.
		<br><br>
		<a href="{$filename}&thread={$thread->id}">Zum Thread</a>
		<br>
		<a href="{$filename}&thread={$thread->id}#post_{$post->id}">Zum Post</a>
	{else}
		<a href="{$filename}">News</a> -> <a href="{$filename}&action=showComments&newsID={$thread->id}#post_{$post->id}">{$thread->title|escape}</a>
		<br><br>
		Mode erfolgreich geändert.
		<br><br>
		<a href="{$filename}&action=showComments&newsID={$thread->id}">Zur News</a>
		<br>
		<a href="{$filename}&action=showComments&newsID={$thread->id}#post_{$post->id}">Zum Post</a>
	{/if}
  {* '<a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>' erfolgreich nach Board '<a href="{$filename}&board={$board->id}">{$board->name}</a>' verschoben.*}
{/if}
</p>

{* End of Padding *}
    </td>
  </tr>
  </table>
  </td>
</tr><tr>
  <td height="25" valign="top" style="background-image:url(/gfx_struct/td_bg_footer.png); background-repeat:no-repeat;">
  </td>
</tr>
</table>
<img src="/gfx_struct/lgif.gif" width="1" height="1" border="0">