{include file='_vorspann.tpl'}

<h1>Gastserver-Verwaltung: {$mandant.name}</h1>

<p><strong>{$servers|@count}</strong> Gastserver sind angemeldet.</p>
{if count($servers) < 255}
<p><a href="{$smarty.server.SCRIPT_NAME}?action=add&amp;mandant={$mandant.id}">Gastserver hinzufügen</a></p>
{else}
<p>Das Limit von 255 Servern ist überschritten, es können keine weiteren Server mehr eingetragen werden.</p>
{/if}
<p>Serverliste exportieren als:
  <a href="{$smarty.server.SCRIPT_NAME}?action=export&amp;mandant={$mandant.id}&amp;output=csv">CSV</a> &middot;
  <a href="{$smarty.server.SCRIPT_NAME}?action=export&amp;mandant={$mandant.id}&amp;output=json">JSON</a> &middot;
  <a href="{$smarty.server.SCRIPT_NAME}?action=export&amp;mandant={$mandant.id}&amp;output=sql">SQL-Statements</a>
</p>

<table cellspacing="1" cellpadding="3">
  <tr>
    <th>IP-Adresse</th>
    <th>Name/DNS</th>
    <th>Reverse-Lookup</th>
    <th>Admin</th>
    <th>Beschreibung</th>
    <th>Angelegt</th>
    <th>Aktionen</th>
  </tr>
{foreach from=$servers item=srv}
  <tr class="row-{cycle values='0,1'}">
    <td>{$srv.ipaddr} (<a href="http://{$srv.ipaddr}/">HTTP</a>, <a href="ftp://{$srv.ipaddr}/">FTP</a>)</td>
    <td>{$srv.dnsname}.lan.multimadness.de</td>
    <td>{if $srv.reverse}ja{else}nein{/if}</td>
    <td><a href="{$mandant.url}/?page=4&nUserID={$srv.admin_id}">{$srv.admin_name|escape}</a></td>
    <td>{$srv.description|escape}</td>
    <td>{$srv.added_at|date_format:'%d.%m.%Y, %H:%M'} Uhr</td>
    <td>
      <a href="{$smarty.server.SCRIPT_NAME}?action=edit&amp;mandant={$mandant.id}&amp;server={$srv.id}">ändern</a>
      - <a href="{$smarty.server.SCRIPT_NAME}?action=delete&amp;mandant={$mandant.id}&amp;server={$srv.id}">löschen</a>
    </td>
  </tr>
{foreachelse}
  <tr class="row-0">
    <td colspan="6" style="text-align: center;">Keine Gastserver angemeldet.</td>
  </tr>
{/foreach}
</table>

{include file='_nachspann.tpl'}
