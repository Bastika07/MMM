{include file='_vorspann.tpl'}

<h1>Ladies at Play</h1>

{if isset($error)}
    <p>{$error}</p>
{else}
    <p class="confirm">Eintrag für {$userId} erfolgreich gespeichert.</p>
{/if}

<p><a href="{$filename}">Zurück</a></p>

{include file='_nachspann.tpl'}
