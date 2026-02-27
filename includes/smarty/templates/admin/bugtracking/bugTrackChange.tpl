{*smarty*}
{assign var=title value="BTracking::Change"}
{*include file="header.tpl"*}
<h1>&nbsp;[ BugTracking::Change Bug {$bugId}]</h1>
{include file='bugTrack_header.tpl'}
<table border="0" cellpadding="0" cellspacing="0">
<form action='{$smarty.server.PHP_SELF}?do=update&bugId={$bugId}' method="post">
  <tr><td>
    <strong>IP*:</strong>
    </td><td>
    <input type="text" name="ip1" size="15" maxlength="15" value={$data.ip} readonly="readonly">
    </td>
    <td><strong>&nbsp;&nbsp;Priorität:</strong></td>
    <td><select name=prio>
    {section name=sec1 loop=$prio}
	<option value="{$prio[sec1].id}"{if $prio[sec1].id eq $data.prio}selected{/if}>{$prio[sec1].name}</option>
    {/section}
    </select></td>
  </tr><tr>
  <td>
    <strong>Login*:</strong>
    </td><td>
    <input type="text" name="userName" size="15" maxlength="65" value={$login} readonly="readonly">
    </td>
    <td><strong>&nbsp;&nbsp;Bug-Klasse:</strong></td>
    <td><select name='type'>
    {section name=sec2 loop=$bugs}
	<option value="{$bugs[sec2].id}"{if $bugs[sec2].id eq $data.bugType}selected{/if}>{$bugs[sec2].name}</option>
    {/section}
    </select></td>
  </tr><tr>
  <td>
    <strong>userId*:</strong>
    </td><td>
    <input type="text" name="userName" size="15" maxlength="65" value={$data.userId} readonly="readonly">
    </td>
  </tr><tr>
  {if $data.diff eq 1}
  <td colspan=4><font color="#FF0000">ACHTUNG: Der angegebene Sitzplatz weicht von dem im System ab!</font></td>
  </tr><tr>
  {/if}
  <td>
    <strong>Reihe*:</strong>
    </td><td>
    <input type="text" name="reihe" size="3" maxlength="5" value={$data.reihe}  readonly="readonly">
  </td><td>
    <strong>&nbsp;&nbsp;Platz*:</strong>
    </td><td>
    <input type="text" name="platz" size="3" maxlength="5" value={$data.platz}  readonly="readonly">
  </td>
  </tr><tr>
    <td colspan=2><strong>Beschreibung:</strong></td>
  </tr><tr>
    <td colspan=5><font color="grey">
  	<input type="hidden" name="oldText" value ="{$data.beschreibung}"></input> {$data.beschreibung}
    <br><br>
    </font></td>
  </tr><tr>
  {if ($data.endZeit eq 0)}
    <td colspan=4>
       <textarea name="beschreibung" cols = 50 rows=5></textarea>
    </td>
  </tr><tr>
  	<td colspan = 4><INPUT TYPE=CHECKBOX NAME="done" > Berarbeitung beendet<P></td>

  </tr><tr>
    <td colspan=2><strong>in Bearbeitung durch:</strong></td>
    <td colspan=2><select name='bearbeiter'>
    <option value='NULL'>NONE</option>
    {section name=sec2 loop=$inputmglkt}
    <option value="{$inputmglkt[sec2].id}"{if $inputmglkt[sec2].id eq $data.bearbeiter}selected{/if}>{$inputmglkt[sec2].login}</option>
    {/section}
    </select><br></td>
  </tr><tr>
    <td colspan=4>* kann hier nicht geändert werden</td>
  </tr>
  <tr>
    <td colspan = 5><br>
      <input type="submit" value="Change">
    </td>
  </tr>
{/if}
</table>
</form>
