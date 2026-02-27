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
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}"><b>&Uuml;bersicht</b></a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>
<h1>{$turnier->name|escape}</h1>

{if ($turnier->flags == $smarty.const.TURNIER_HTML)}

	{$turnier->htmltree}

{else}

 
{foreach key=roundid item=round from=$rounds}
  {if $turnier->flags & $smarty.const.TURNIER_DOUBLE}
    {if $roundid == 0}<h2>Winnerbracket:</h2>{/if}
    {if $roundid == 257}<h2>Loserbracket:</h2>{/if}
  {/if}
  <table class="rahmen_allg" width="585" cellspacing="1" cellpadding="3">
  <tr>
    <td class="TNListe" colspan="5">
      <b>{$round.name|escape}</b> {if $round.begins eq ""}(Deadline {$round.ends|escape}) {else}({$round.begins|escape} - {$round.ends|escape}){/if} {$round.info|escape}
    </td>
  </tr>
  {foreach key=matchid item=match from=$round.matches}
    <tr>
    <td class="dblau" width="80">
    <a href="?page=26&turnierid={$turnier->turnierid}&matchid={$matchid}">
    Match<br /># {$match.viewnum}</a></td>

    <td class="hblau" align="right" width="150">
    {if $match.flags & $smarty.const.MATCH_TEAM1_GELB}<img src="gfx_turnier/gelbekarte.gif">&nbsp;{/if}
    {if $match.flags & $smarty.const.MATCH_TEAM1_ROT}<img src="gfx_turnier/rotekarte.gif">&nbsp;{/if}
    {if ($match.team1 > 0)}
      {$teams[$match.team1].name|escape}
      <a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match.team1}">{$team1.name|escape}</a>
    {elseif ($match.team1 == -1)}
      <i>freilos</i>
    {else}-{/if}
    </td>

    {if $match.flags & $smarty.const.MATCH_COMPLETE}
      <td align="center" width="50" bgcolor="#77FF77">
      <b>{$match.result1} : {$match.result2}</b>
      </td>
    {elseif (($match.flags & $smarty.const.MATCH_TEAM1_ACCEPT) || ($match.flags & $smarty.const.MATCH_TEAM2_ACCEPT))}
      <td align="center" width="50" bgcolor="#FFFF00">vs</td>
    {elseif (($match.team1 != 0) && ($match.team2 != 0))}
      <td align="center" width="50" bgcolor="#FF7777">vs</td>
    {else}
      <td class="dblau" align="center" width="50">vs</td>
    {/if}
    </td>

    <td class="hblau" align="left" width="150">
    {if ($match.team2 > 0)}
      {$teams[$match.team2].name|escape}
      <a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match.team2}">{$team2.name|escape}</a>
    {elseif ($match.team2 == -1)}
      <i>freilos</i>
    {else}-{/if}
    {if $match.flags & $smarty.const.MATCH_TEAM2_ROT}&nbsp;<img src="gfx_turnier/rotekarte.gif">{/if}
    {if $match.flags & $smarty.const.MATCH_TEAM2_GELB}&nbsp;<img src="gfx_turnier/gelbekarte.gif">{/if}
    </td>

    <td class="dblau"></td>

    </tr>
  {/foreach}
  </table>
  <br>
{/foreach}

{/if}