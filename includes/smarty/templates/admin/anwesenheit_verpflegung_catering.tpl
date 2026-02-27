{include file='_vorspann.tpl'}

<h1>Verpflegungsnachweis projektbezogen</h1>

<table>
  <tr>
    <td>Mandant:</td>
    <td>{$event.name|escape}</td>
  </tr>
  <tr>
    <td>Event/ Anlass:</td>
    <td>{$event.description|escape}</td>
  </tr>
  <tr>
    <td>Datum von:</td>
    <td>{$event.start|date_format:'%d.%m.%Y'}</td>
  </tr>
  <tr>
    <td>Datum bis:</td>
    <td>{$event.end|date_format:'%d.%m.%Y'}</td>
  </tr>
  <tr>
    <td>Ort des Verzehrs:</td>
    <td>....................................................</td>
  </tr>
  <tr>
    <td style="padding-right: 2em; vertical-align: top;">Teilnehmer:</td>
    <td>
      <ol>
      {foreach from=$users item=user}
        <li style="margin-bottom: 0.3em;">{$user.NAME} {$user.NACHNAME}</li>
      {/foreach}
      </ol>
    </td>
  </tr>
	<tr>
		<td colspan="2" height="60" valign="bottom">...................................................................................<br>Datum, Unterschrift</td>
	</tr>
</table>

{include file='_nachspann.tpl'}
