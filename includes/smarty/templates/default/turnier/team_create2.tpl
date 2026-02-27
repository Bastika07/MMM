{* Smarty Template *}
<a href="?page=20">Turnierliste</a> -
<a href="?page=21&turnierid={$turnier->turnierid}">{$turnier->name}</a>
{if $intranet && (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ {if !($turnier->flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnier->turnierid}">Turnierbaum</a>
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>

<h1>Create Team</h1>

{if $errstr}
  <p class="fehler">{$errstr|escape}</p>
{else}
  {if $warnstr}<p class="fehler">{$warnstr|escape}</p>{/if}

  <form method="post" name="create" action="?page=28&turnierid={$turnier->turnierid}">
  {csrf_field}
  <table class="rahmen_allg" cellspacing="1" cellpadding="3"><tr>
  <tr><td class="TNListe" colspan="2">Create new:</td></tr>
  <td class="dblau">Team name</td>
  <td class="hblau"><input type="text" name="teamname" value="{$teamname}" maxlength="24"></td>
  </tr>

  {if $flags.wwclid || $flags.nglid}
    <tr><td class="dblau">{if $flags.wwclid}WWCL ID:{elseif $flags.nglid}NGL ID:{/if}</td>
    <td class="hblau"><input type="text" name="ligaid" value="{$ligaid}" maxlength="10"></td>
    <tr>
  {/if}

  {foreach key=num item=name from=$usernames}
    <tr><td class="dblau">UserID</td>
    <td class="hblau"><input type="text" name="usernames[{$num}]" value="{$name}" maxlength="24"></td>
    </tr>
  {/foreach}
  <td class="dblau" colspan="2" align="center">
    <input type="submit" name="action" value="Resolve IDs">&nbsp;
    <input type="submit" name="action" value="Reset">
  </td>

  <tr><td class="dblau" colspan="2"></td></tr>
  <tr><td class="TNListe" colspan="2">Import from:</td></tr>

  <tr><td class="dblau">Turnier</td>
  <td class="hblau"><select name="turnier_sel" onChange="javascript:window.location.href='?page=28&turnierid={$turnier->turnierid}&turnier_sel='+this.value">
   {html_options options=$turnier_list selected=$turnier_sel}
  </select></td></tr>
  <tr><td class="dblau">Team</td>
  <td class="hblau"><select name="team_sel">
   {html_options options=$team_list selected=$team_sel}
  </select></td></tr>
  <tr><td class="dblau" colspan="2" align="center"><input type="submit" name="action" value="Import"></td></tr>
  </table>

  <br>
  <input type="submit" name="action" value="Create Team">

  </form>
{/if}
