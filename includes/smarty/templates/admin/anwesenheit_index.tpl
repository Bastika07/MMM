<h1>Anwesenheiten</h1>

<p><u>Hinweis:</u> Für Partys, bei denen ihr idR. nicht mitorganisiert, habt ihr keine Rechte und braucht euch auch nicht als abwesend einzutragen. Dies hat technische Gründe, da ihr mit zugeordnetem Recht z.B. auch in der Teamliste auf der Party-Seite auftaucht.</p>

<table cellspacing="1" class="outer">
  <tr>
    <th>Event</th>
    <th>von</th>
    <th>bis</th>
    <th>gehört zu</th>
    <th>Aktion</th>
  </tr>

{foreach from=$events item=event}
  <tr class="row-{cycle values='0,1'}">
    <td><a href="{$smarty.server.SCRIPT_NAME}?eventID={$event.id}"><nobr>{$event.name|escape}</nobr></a></td>
    <td>{$event.start|date_format:'%A, %d. %B %Y'|from_charset}</td>
    <td>{$event.end|date_format:'%A, %d. %B %Y'|from_charset}</td>
    <td>{$event.mandant_name}</td>
    <td><nobr>
      <!--<a href="anwesenheit_verpflegung.php?event={$event.id}">Catering</a>
      | -->(<a href="{$smarty.server.SCRIPT_NAME}?action=eventDelete&amp;event={$event.id}">X</a>)
    </nobr></td>
  </tr>
{/foreach}

{if $user_is_admin}
<form action="{$smarty.server.SCRIPT_NAME}?action=eventAdd" method="post">
  {csrf_field}
  <tr class="row-{math equation="count % 2" count=$events|@count}">
    <td>
    	<input type="text" name="description" size="18" maxlength="40"/><br />
      <input type="checkbox" name="show_calendar" checked value="1" />In Kalender anzeigen
    </td>
    {foreach from=$dropdowns item=dropdown}
    <td>{$dropdown}</td>
    {/foreach}
    <td style="text-align: center;">
      <select name="mandant">
        {foreach from=$mandanten_admin key=id item=name}
        <option value="{$id}">{$name}</option>
        {/foreach}
      </select>
      <br/>
      <input type="submit" value="eintragen"/>
    </td>
    <td>&nbsp;</td>
  </tr>
</form>
{/if}
    
</table>