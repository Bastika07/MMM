{* Smarty Template *}
<a href="?page=20">Turnierliste</a> -
<a href="?page=21&turnierid={$turnier->turnierid}">{$turnier->name|escape}</a>
{if $intranet && (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ {if !($turnier->flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnier->turnierid}">Turnierbaum</a>
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
- <a href="?page=26&turnierid={$turnier->turnierid}&matchid={$match->matchid}"><b>Match #{$match->viewnum}</b></a>
<br><br>

{if $err}
  <p class="error">{$err}</p>

{elseif $team->teamid}
  <h1>Team tauschen</h1>
  <form name="tauschen" method="post" action="?page=30&action=swap&turnierid={$turnier->turnierid}&matchid={$match->matchid}&side={$side}">
  {csrf_field}
  <a href="?page=29&{$turnier->turnierid}&teamid={$team->teamid}">{$team->name|escape}</a> gegen
  <select name=tauschid>{html_options options=$teams}</select>
  <input type="submit" value="tauschen">
  </form>

{else}
  <h1>Team einf&uuml;gen</h1>
  <form name="tauschen" method="post" action="?page=30&action=swap&turnierid={$turnier->turnierid}&matchid={$match->matchid}&side={$side}">
  {csrf_field}
  <select name=tauschid>{html_options options=$teams}</select>
  <input type="submit" value="einf&uuml;egen">
  </form>
{/if}
