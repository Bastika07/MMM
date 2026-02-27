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
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>

<form method="post" action="?page=26&action=editMatch&turnierid={$turnier->turnierid}&matchid={$match->matchid}">
<table class="rahmen_allg" width="600" cellspacing="1" cellpadding="3">
<tr><td class="TNListe" colspan="3">
<table width="99%"><tr>
<td class="TNListe" width="50%" align="left">Match #{$match->viewnum}</td>
<td class="TNListe" width="50%" align="right">
{$round->name|escape} ({$round->begins|escape} - {$round->ends|escape}) {$round->info|escape}
</td></tr></table>
</td></tr>

<tr>
<td class="hblau" width="250" align="right"><h1>
{if $match->flags & $smarty.const.MATCH_TEAM1_GELB}<img src="gfx_turnier/gelbekarte.gif">&nbsp;{/if}
{if $match->flags & $smarty.const.MATCH_TEAM1_ROT}<img src="gfx_turnier/rotekarte.gif">&nbsp;{/if}
{if $match->team1 > 0}<a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match->team1}">{$team1->name|escape}</a>
{elseif $match->team1 == -1}<i>freilos</i>{else}-{/if}
</h1></td>
<td class="dblau" width="100" align="center">vs</td>
<td class="hblau" width="250" align="left"><h1>
{if $match->team2 > 0}<a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match->team2}">{$team2->name|escape}</a>
{elseif $match->team2 == -1}<i>freilos</i>{else}-{/if}
{if $match->flags & $smarty.const.MATCH_TEAM2_ROT}&nbsp;<img src="gfx_turnier/rotekarte.gif">{/if}
{if $match->flags & $smarty.const.MATCH_TEAM2_GELB}&nbsp;<img src="gfx_turnier/gelbekarte.gif">{/if}
</h1></td>
</tr>
<tr>
<td class="hblau" width="250" align="right"><h1>{if $tmp.showres}{$match->result1}{else}-{/if}</h1></td>
<td class="dblau" width="100" align="center">
{if $tmp.accept}<input type="submit" value="Accept" name="subaction">{/if}
{if $tmp.random}<input type="submit" value="Random" name="subaction">{/if}
{if !$tmp.accept && !$tmp.random}:{/if}
</td>
<td class="hblau" width="250" align="left"><h1>{if $tmp.showres}{$match->result2}{else}-{/if}</h1></td>
</tr>

{if $tmp.enter}
  <tr><td class="dblau" width="250" align="right"><input type="text" size="8" maxlength="4" name="result1" value="{$result1}" align="right">
  </td><td class="dblau" width="100" align="center"><input type="submit" value="{if $tmp.admin}Change{else}Enter{/if}" name="subaction">
  </td><td class="dblau" width="250" align="left"><input type="text" size="8" maxlength="4" name="result2" vaule="{$result2}">
  </td></tr>
{/if}

{if $tmp.readytoplay}
  <tr><td class="dblau" width="250" align="right">
  </td><td class="dblau" width="100" align="center"><input type="submit" value="Spielbereit" name="subaction">
  </td><td class="dblau" width="250" align="left">
  </td></tr>
{/if}

{if $tmp.admin}
  <tr><td class="dblau" width="250" align="center">
  <a href="?page=26&action=setflag&flag={$smarty.const.MATCH_TEAM1_GELB}&turnierid={$turnier->turnierid}&matchid={$match->matchid}">Gelbe Karte</a> /
  <a href="?page=26action=setflag&flag={$smarty.const.MATCH_TEAM1_ROT}&turnierid={$turnier->turnierid}&matchid={$match->matchid}">Rote Karte</a>
  </td><td class="dblau" width="100" align="center">
  <br>
  <a href="?page=26action=setflag&flag=-1&turnierid={$turnier->turnierid}&matchid={$match->matchid}">Keine Strafen</a>
  <br><br>
  </td><td class="dblau" width="250" align="center">
  <a href="?page=26action=setflag&flag={$smarty.const.MATCH_TEAM2_GELB}&turnierid={$turnier->turnierid}&matchid={$match->matchid}">Gelbe Karte</a> /
  <a href="?page=26action=setflag&flag={$smarty.const.MATCH_TEAM2_ROT}&turnierid={$turnier->turnierid}&matchid={$match->matchid}">Rote Karte</a><br>
  </td></tr>

  <tr><td class="dblau" width="250" align="center">
  {if $match->team1 > 0}
    <a href="?page=30&turnierid={$turnier->turnierid}&matchid={$match->matchid}&teamid={$match->team1}&side=0">Team tauschen</a>
  {elseif $match->team1 == -1 & $match->round == 0 }
    <a href="?page=30&turnierid={$turnier->turnierid}&matchid={$match->matchid}&teamid={$match->team1}&side=0">Team einfügen</a>
  {/if}
  </td><td class="dblau" width="100" align="center">
  <input type="submit" value="Reset Match" name="subaction">  
  </td><td class="dblau" width="250" align="center">
  {if $match->team2 > 0}
    <a href="?page=30&turnierid={$turnier->turnierid}&matchid={$match->matchid}&teamid={$match->team2}&side=1">Team tauschen</a>
  {elseif $match->team2 == -1 & $match->round == 0}
    <a href="?page=30&turnierid={$turnier->turnierid}&matchid={$match->matchid}&teamid={$match->team2}&side=1">Team einfügen</a>
  {/if}
  </td></tr>
{/if}
</table></form>

<br>
<table class="rahmen_allg" width="700" cellspacing="1" cellpadding="3">
<tr><td class="TNListe" colspan="{if $tmp.admin}4{else}3{/if}">Events</td></tr>
  <tr>
  <td class="dblau" width="200"><b>Zeitpunkt</b></td>
  <td class="dblau" width="100"><b>User</b></td>
  <td class="dblau" width="400"><b>Event</b></td>
  {if $tmp.admin}<td class="dblau" width="10"></td>{/if}
  </tr>
{foreach key=eventid item=event from=$events}
  {strip}
  {if $tmp.admin}
    <tr>
    {if ($event.flags & $smarty.const.EVENT_HIDDEN)}
      <td bgcolor="#808080" width="200">{$event.time|date_format:"%a %d.%m.%Y %T"}</td>
      <td bgcolor="#808080" width="100"><a href="/?page=4&nUserID={$event.userid}">{$event.login}</a></td>
      <td bgcolor="#808080" width="400">{$event.text|escape}</td>
      <td bgcolor="#808080" width="10" align="center"></td>
    {else}
      {cycle values="hblau,dblau" assign=tdclass}
      <td class="{$tdclass}" width="200">{$event.time|date_format:"%a %d.%m.%Y %T"}</td>
      <td class="{$tdclass}" width="100"><a href="/?page=4&nUserID={$event.userid}">{$event.login}</a></td>
      <td class="{$tdclass}" width="400">{$event.text|escape}</td>
      <td class="dblau" width="10" align="center">
      <a href="?page=26&action=hideevent&turnierid={$turnier->turnierid}&matchid={$match->matchid}&eventid={$event.eventid}">
      <font color="#ff0000"><b>hide</b></font></a></td>
    {/if}
    </tr>
  {elseif !($event.flags & $smarty.const.EVENT_HIDDEN)}
    <tr>
    {cycle values="hblau,dblau" assign=tdclass}
    <td class="{$tdclass}" width="200">{$event.time|date_format:"%a %d.%m.%Y %T"}</td>
    <td class="{$tdclass}" width="100"><a href="/?page=4&nUserID={$event.userid}">{$event.login}</a></td>
    <td class="{$tdclass}" width="400">{$event.text|escape}</td>
    </tr>
  {/if}
  {/strip}
{foreachelse}
  <tr><td class="hblau" colspan="3">Keine Events eingetragen.</td></tr>
{/foreach}
</table>
<br />

<h1>Kommentare</h1>
