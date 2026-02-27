<link rel="stylesheet" type="text/css" href="style/anwesenheit.css"/>

<h1>Anwesenheiten - {$event.description}</h1>

<p>Bitte tragt euch für die Zeit eurer Nachtruhe als nicht anwesend ein!</p>

{* Tabelle mit den Anwesenheitszeiten plus Formular zum Hinzufügen weiter Zeiten. *}
<table cellspacing="1" class="outer">
  <tr>
    <th>Von</th>
    <th>Bis</th>
    <th>Aktionen</th>
  </tr>
{foreach from=$user_presences item=row}
  <tr class="row-{cycle values='0,1' name='user_presences'}">
    <td>{$row.start|date_format:'%H:%M Uhr am %A, %d. %B %Y'|from_charset}</td>
    <td>{$row.end|date_format:'%H:59 Uhr am %A, %d. %B %Y'|from_charset}</td>
    <td><a href="{$smarty.server.SCRIPT_NAME}?action=delete&amp;interval={$row.ID}&amp;eventID={$eventID}">löschen</a></td>
  </tr>
{/foreach}

{* Formular für neue Anwesenheiten *}
<form action="{$smarty.server.SCRIPT_NAME}?eventID={$eventID}" method="post">
  {csrf_field}
  <input type="hidden" name="type" value="p"/>
  <tr class="row-{math equation='count % 2' count=$user_presences|@count}">
    <td>
      <select name="start_stunde">
      {foreach from=$hours_range item=hour}
        <option value="{$hour}"{if $start_stunde == $hour} selected="selected"{/if}>{$hour|string_format:'%02d'}:00</option>
      {/foreach}
      </select> Uhr
      <select name="start_tmj">
      {foreach from=$days2show item=date}
        <option value="{$date->toString()}">{$date->toTimestamp()|date_format:'%A, %d. %B %Y'|from_charset}</option>
      {/foreach}
      </select>
    </td>
    <td>
      <select name="end_stunde">
      {foreach from=$hours_range item=hour}
        <option value="{$hour}"{if $end_stunde == $hour} selected="selected"{/if}>{$hour|string_format:'%02d'}:59</option>
      {/foreach}
      </select> Uhr
      <select name="end_tmj">
      {foreach from=$days2show item=date}
        <option value="{$date->toString()}">{$date->toTimestamp()|date_format:'%A, %d. %B %Y'|from_charset}</option>
      {/foreach}
      </select>
    </td>
    <td><input type="submit" value="eintragen"/></td>
  </tr>
</form>
</table>

<br/>

<form action="{$smarty.server.SCRIPT_NAME}?action=update_absence&eventID={$eventID}" method="post">
  {csrf_field}
  <label><input type="checkbox" name="is_absent" value="1"{if $user_is_absent} checked="checked"{/if}/> Ich werde abwesend sein.</label>
  <input type="submit" value="aktualisieren"/>
</form>

<br/>


{* Entsprechende Tage anzeigen. *}
<table cellspacing="0" class="presences">
{foreach from=$days item=day}
  <tr>
    <th colspan="25">{$day.date->toTimestamp()|date_format:'%A, %d. %B %Y'|from_charset}</th>
  </tr>
  <tr class="hours row-1">
    <td class="first"></td>
    {foreach from=$day.hours key=hour item=exists}
    <td{if ! $exists} class="gray"{/if}>{$hour}</td>
    {/foreach}
  </tr>
  {foreach from=$day.rows item=row}
  <tr class="linesep row-{cycle values='0,1' name='days'}">
    <td class="label">{$row.label}</td>
    {foreach from=$row.cells item=cell}
    <td{$cell.colspan}>{$cell.content}</td>
    {/foreach}
  </tr>
  {/foreach}
{/foreach}
</table>

<br/>


{* Abwesende Teammitglieder anzeigen. *}
<h2>Abwesende Teammitglieder <small>({$absents|@count})</small></h2>
<table cellspacing="1" class="outer">
  <tr>
    <th>Nickname</th>
    <th>Name</th>
  </tr>
{foreach from=$absents item=absent}
  <tr class="row-{cycle values='0,1' name='absents'}">
    <td>{$absent.LOGIN}</td>
    <td>{$absent.NAME} {$absent.NACHNAME}</td>
  </tr>
{/foreach}
</table>

<br/>


{* Aufgabenzeiten anzeigen, wenn entsprechendes Recht vorhanden. *}
{if $user_is_mandantadmin}
<table cellspacing="1" class="outer">
  <tr>
    <th>Beschreibung</th>
    <th>Von</th>
    <th>Bis</th>
    <th>Aktionen</th>
  </tr>
{* Zeiten anzeigen. *}
{foreach from=$task_items item=row}
  <tr class="row-{cycle values='0,1' name='tasks'}">
    <td>{$row.description}</td>
    <td>{$row.start|date_format:'%H:%M, %A, %d. %B %Y'|from_charset}</td>
    <td>{$row.end|date_format:'%H:59, %A, %d. %B %Y'|from_charset}</td>
    <td><a href="{$smarty.server.SCRIPT_NAME}?action=delete&amp;interval={$row.id}&amp;eventID={$eventID}">löschen</a></td>
  </tr>
{/foreach}

{* Formular für neue Zeiten anzeigen. *}
<form action="{$smarty.server.SCRIPT_NAME}?eventID={$eventID}" method="post">
  {csrf_field}
  <input type="hidden" name="type" value="t"/>
  <tr class="row-{math equation='(count + 1) % 2' count=$task_items|@count}">
    <td><input type="text" name="description" size="8" maxlength="20"></td>
    <td>
      <select name="start_stunde">
      {foreach from=$hours_range item=hour}
        <option value="{$hour}"{if $start_stunde == $hour} selected="selected"{/if}>{$hour|string_format:'%02d'}:00</option>
      {/foreach}
      </select> Uhr
      <select name="start_tmj">
      {foreach from=$days2show item=date}
        <option value="{$date->toString()}">{$date->toTimestamp()|date_format:'%A, %d. %B %Y'|from_charset}</option>
      {/foreach}
      </select>
    </td>
    <td>
      <select name="end_stunde">
      {foreach from=$hours_range item=hour}
        <option value="{$hour}"{if $end_stunde == $hour} selected="selected"{/if}>{$hour|string_format:'%02d'}:59</option>
      {/foreach}
      </select> Uhr
      <select name="end_tmj">
      {foreach from=$days2show item=date}
        <option value="{$date->toString()}">{$date->toTimestamp()|date_format:'%A, %d. %B %Y'|from_charset}</option>
      {/foreach}
      </select>
    </td>
    <td><input type="submit" value="eintragen"/></td>
  </tr>
</form>
</table>

{/if}
