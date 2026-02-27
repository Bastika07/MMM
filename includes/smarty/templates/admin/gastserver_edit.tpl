{include file='_vorspann.tpl'}

<h1>Gastserver-Verwaltung</h1>

<p><a href="{$smarty.server.SCRIPT_NAME}?action=list_servers&amp;mandant={$mandant_id}">Zur Übersicht</a></p>

{if $success}
  <p class="confirm">Gastserver erfolgreich gespeichert.</p>
{else}
  {if $error}
  <p class="fehler">{$error}</p>
  {/if}
  <form action="{$smarty.server.SCRIPT_NAME}?action=edit&mandant={$mandant_id}&server={$server_id}" method="post">
    <input type="hidden" name="action" value="edit"/>
    <input type="hidden" name="mandant" value="{$mandant_id}"/>
    <input type="hidden" name="server" value="{$server_id}"/>
    <table cellspacing="1" class="outer">
      <tr>
        <th colspan="2">Gastserver bearbeiten</th>
      </tr>
      <tr class="row-0">
        <td>Besitzer:</td>
        <td><input type="text" name="user_id" value="{$user_id}" size="6" maxlength="6"/> (User-ID eingeben!)</td>
      </tr>
      <tr class="row-1">
        <td>Name/DNS-Eintrag:</td>
        <td><input type="text" name="name" value="{$name}" size="40" maxlength="40"/>.lan.multimadness.de</td>
      </tr>
      <tr class="row-0">
        <td>Reverse-Lookup:</td>
        <td><label><input type="checkbox" name="reverse" value="1"{if $reverse} checked="checked"{/if}/> Auflösung von IP-Adresse zu DNS</label></td>
      </tr>
      <tr class="row-1">
        <td>Beschreibung:</td>
        <td><input type="text" name="description" value="{$description}" size="44" maxlength="150"/></td>
      </tr>
      <tr class="row-0">
        <td>&nbsp;</td>
        <td><input type="submit" value="speichern"/></td>
      </tr>
    </table>
  </form>
  <script type="text/javascript">document.forms[0].elements[1].focus();</script>
{/if}

{include file='_nachspann.tpl'}
