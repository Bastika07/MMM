{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">

<br><br>
<h1>Turniere</h1>

  <table class="rahmen_allg" width="550" cellspacing="1" cellpadding="3">
<td colspan="5">
<ul>
{foreach item=tourney from=$tourney_list}
  <li><a href="turnier/#t{$tourney.turnierid}"> {$tourney.turniername|escape}</a></li>
{foreachelse}
  <li>Keine laufenden Turniere gefunden...</li>
{/foreach}
</ul>
<br>
    </td>
  </tr>
</table>
{foreach item=tourney from=$tourney_list}
  <table class="rahmen_allg" width="700" cellspacing="1" cellpadding="3">
  <th colspan="4"><a href="turnier/turnier/turnier_table.php?turnierid={$tourney.turnierid}" alt="{$tourney.turniername|escape} aufrufen" name="t{$tourney.turnierid}" style="color: white">{$tourney.turniername|escape}</a></th>
  <th>Runde WB/LB</th>
  <th>Deadline</th>
  {foreach item=match from=$matches[$tourney.turnierid]}
    {if $match.flags & $smarty.const.MATCH_READY and !($match.flags & $smarty.const.MATCH_COMPLETE) }
      <tr>
	<td class="dblau" width="80">
	<a href="turnier/match_detail.php?turnierid={$tourney.turnierid}&matchid={$match.matchid}">
	Match #{$match.matchid+1}</a></td>

	<td class="hblau" align="right" width="150">
	{if $match.flags & $smarty.const.MATCH_TEAM1_GELB}<img src="gfx_turnier/gelbekarte.gif">&nbsp;{/if}
	{if $match.flags & $smarty.const.MATCH_TEAM1_ROT}<img src="gfx_turnier/rotekarte.gif">&nbsp;{/if}
	{if ($match.team1 > 0)}
	  {php}$this->_tpl_vars['team1'] = $this->_tpl_vars['teams'][$this->_tpl_vars['tourney']['turnierid']][$this->_tpl_vars['match']['team1']];{/php}
	  <a href="turnier/team_detail.php?turnierid={$tourney.turnierid}&teamid={$match.team1}">{$team1.name|escape}</a>
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
	  {php}$this->_tpl_vars['team2'] = $this->_tpl_vars['teams'][$this->_tpl_vars['tourney']['turnierid']][$this->_tpl_vars['match']['team2']];{/php}
	  <a href="turnier/turnier/team_detail.php?turnierid={$tourney.turnierid}&teamid={$match.team2}">{$team2.name|escape}</a>
	{elseif ($match.team2 == -1)}
	  <i>freilos</i>
	{else}-{/if}
	{if $match.flags & $smarty.const.MATCH_TEAM2_ROT}&nbsp;<img src="gfx_turnier/rotekarte.gif">{/if}
	{if $match.flags & $smarty.const.MATCH_TEAM2_GELB}&nbsp;<img src="gfx_turnier/gelbekarte.gif">{/if}
	</td>

	<td class="dblau">{$rounds[$tourney.turnierid][$match.round].name}</td>
	<td class="dblau">{$rounds[$tourney.turnierid][$match.round].ends}</td>
      </tr>
    {/if}
  {/foreach}
  </table>
{/foreach}
</body>