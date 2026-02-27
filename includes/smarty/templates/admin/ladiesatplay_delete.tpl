{include file='_vorspann.tpl'}

<h1>Ladies at Play</h1>

{if isset($error)}
    <p>{$error}</p>
{else}
    {if !$confirmed}
	<p>Wirklich löschen?<br/><a href="{$filename}&action=delete&userId={$userId}&confirmed=1">Ja</a></p>
    {else}
	<p>Erfolgreich gelöscht!</p>
    {/if}
    <p><a href="{$filename}">Zurück</a></p>
{/if}

{include file='_nachspann.tpl'}
