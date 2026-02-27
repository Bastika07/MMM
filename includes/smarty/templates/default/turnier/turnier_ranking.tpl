{* Smarty Template *}
{* Turniername per JavaScript in den Titel einfügen - Markus Thomas - 26.11.2007 *}
{literal}
<script type="text/javascript">
{/literal}
document.title = "{$turnier->name|escape} - " + document.title;
{literal}
</script>
{/literal}
<a href="?page=20">Turnierliste</a> -
<a href="?page=21&turnierid={$turnier->turnierid}">{$turnier->name|escape}</a>
{if $intranet && (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ {if !($turnier->flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnier->turnierid}">Turnierbaum</a>
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}"><b>Ranking</b></a> ]
{/if}
<br><br>
<h1>Ranking: {$turnier->name|escape}</h1>

{if ($turnier->flags == $smarty.const.TURNIER_HTML)}

	{$turnier->htmlranking}

{else}

<table class="rahmen_allg" cellspacing="1" cellpadding="3">
<tr>
<td class="TNListe" align="center"><b>Platz</b></td>
<td class="TNListe"><b>Team</b></td>
{if $turnier->flags & $smarty.const.TURNIER_RUNDEN}
<td class="TNListe"><b>Punkte</b></b>
{/if}
</tr>

{foreach key=num item=rank from=$ranking}
  {cycle values='hblau,dblau' assign=tdclass}
  <tr>
  <td class="{$tdclass}" width="20" align="center">{$rank.pos}</td>
  <td class="{$tdclass}" width="330">
  {if $team.id != -1}
    <a href="?page=29&turnierid={$turnier->turnierid}&teamid={$rank.teamid}">{$rank.teamname|escape}</a>
  {else}
    <i>freilos</i>
  {/if}
  </td>
{if $turnier->flags & $smarty.const.TURNIER_RUNDEN}
<td class="{$tdclass}">{$rank.points}</b>
{/if}
  </tr>
{/foreach}
</table>

{/if}