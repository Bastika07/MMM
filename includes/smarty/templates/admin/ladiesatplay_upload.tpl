{include file='_vorspann.tpl'}

<h1>Ladies at Play</h1>

{if isset($error)}
    <p class="fehler">{$error}</p>
{else}
    <p class="confirm">Erfolgreich hochgeladen!</p>
{/if}
<p><a href="{$filename}">Zurück</a></p>

{include file='_nachspann.tpl'}
