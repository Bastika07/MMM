{include file='_vorspann.tpl'}

<h1>Gastserver-Verwaltung</h1>

<table cellspacing="1" class="outer">
  <tr>
    <th>Mandant</th>
    <th>Gastserver</th>
  </tr>
{foreach from=$mandanten item=mandant}
  <tr class="row-{cycle values='0,1'}">
    <td><a href="{$smarty.server.SCRIPT_NAME}?action=list_servers&amp;mandant={$mandant.id}">{$mandant.title}</a></td>
    <td style="text-align: center;">{$mandant.server_count}</td>
  </tr>
{/foreach}
</table>

{include file='_nachspann.tpl'}
