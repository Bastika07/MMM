{include file='_vorspann.tpl'}

<h1>Gastserver-Verwaltung</h1>

<p><a href="{$smarty.server.SCRIPT_NAME}?action=list_servers&amp;mandant={$mandant_id}">Zur Übersicht</a></p>

{if ! $confirmed}
  <p>Soll der Gastserver wirklich gelöscht werden?</p>
  <p>
    <a href="{$smarty.server.SCRIPT_NAME}?action=delete&amp;mandant={$mandant_id}&amp;server={$smarty.get.server}&amp;confirmed=1">JA</a>
    - <a href="{$smarty.server.SCRIPT_NAME}?action=list_servers&amp;mandant={$mandant_id}">NEIN</a>
  </p>
{else}
  <p class="confirm">Der Server wurde gelöscht.</p>
{/if}

{include file='_nachspann.tpl'}
