{* Smarty *}
{* Template für Bestätigung *}

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