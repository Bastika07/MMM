{* Smarty Template *}
<a href="?page=20">Turnierliste</a> -
<a href="?page=21&turnierid={$turnier->turnierid}">{$turnier->name}</a>
{if $intranet && (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ {if !($turnier->flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnier->turnierid}">Turnierbaum</a>
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>

<h1><a href="?page=27&turnierid={$turnier->turnierid}">Create Team</a></h1>

{if $errstr}
  <p class="fehler">{$errstr|escape}</p>
{else}
  {if $warnstr}<p class="fehler">{$warnstr|escape}</p>{/if}
  <form method="post" name="create" action="?page=27&turnierid={$turnier->turnierid}">
  {csrf_field}
  Team name: <input type="text" name="name" maxlength="24">
  <input type="submit" value="anlegen">
  </form>
{/if}
