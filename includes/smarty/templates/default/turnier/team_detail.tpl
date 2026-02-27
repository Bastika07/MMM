{* Smarty Template *}
<a href="?page=20">Turnierliste</a> -
<a href="?page=21&turnierid={$turnier->turnierid}">{$turnier->name}</a>
{if $intranet && (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ {if !($turnier->flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnier->turnierid}">Turnierbaum</a>
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>

{if $errstr}<p class="fehler">{$errstr|escape}</p>{/if}

<h1>Team: <a href="?page=29&turnierid={$turnier->turnierid}&teamid={$team->teamid}">{$team->name|escape}</a></h1>

<table class="rahmen_allg" cellspacing="1" cellpadding="3" width="530">
<tr><td class="TNListe" colspan="2">Aufstellung ({$team->size}/{$turnier->teamsize})</td>
<td class="TNListe" align="center">{if $tempflags.coinsback}<a href="?page=29&action=coinsback&turnierid={$turnier->turnierid}&teamid={$team->teamid}">Coin R&uuml;ckgabe</a>{/if}</td>
{if $tempflags.wwclid}<td width="70" class="TNListe" nowrap>Email2WWCL*</td>
{elseif $tempflags.nglid}<td width="110"  class="TNListe" nowrap>Email an NGL*</td>
{/if}
</tr>

{if $tempflags.wwclid || $tempflags.nglid}{strip}
  <tr><td class="dblau" width="80">{if $tempflags.wwclid}WWCL ID:{elseif $tempflags.nglid}NGL ID:{/if}</td>
  <td class="hblau" width="280">
  {if $action == "setligaid"}
    <form method="post" name="ligaid" action="?page=29&action=setligaid&turnierid={$turnier->turnierid}&teamid={$team->teamid}">
    <input type="text" name="ligaid" value="{$team->ligaid}" maxlength="10" onChange="javascript:document.forms.ligaid.submit()">
    </form></td>
    <td class="hblau" width="110" align="center"><a href="javascript:document.forms.ligaid.submit()">ID &auml;ndern</a></td>
  {else}
    {$team->ligaid|escape}</td>
    <td class="hblau" width="110" align="center">
    {if $tempflags.setligaid}<a href="?page=29&action=setligaid&turnierid={$turnier->turnierid}&teamid={$team->teamid}">ID &auml;ndern</a>{/if}
    </td>
  {/if}
  <td class="hblau" width="110" align="center"></td>
  </tr>
{/strip}{/if}


{assign var=userid value=$team->leader}{strip}
<tr><td class="dblau" width="80">Leader:</td>
<td class="hblau" width="280">
<a href="/?page=4&nUserID={$userid}">{$team->namelist[$userid]|escape}</a>
{if $seats.$userid.ebene}
  &nbsp;<a href="/?page=9&ebene={$seats.$userid.ebene}&locateUser={$userid}">({$seats.$userid.reihe}-{$seats.$userid.platz})</a>
{/if}
</td>
<td class="hblau" width="110" align="center">
{if $tempflags.cancelteam}
  <a href="?page=29&action=del&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}"><font color="#FF0000">Team aufl&ouml;sen</font></a>
{/if}
</td>
{if $tempflags.wwclid || $tempflags.nglid}
  <td class="hblau" width="110" align="center">
  {if $tempflags.ligamail.show.$userid}
    <input type="checkbox" {if !$tempflags.ligamail.enabled.$userid}disabled="disabled"{/if} {if $tempflags.ligamail.checked.$userid}checked="checked"{/if} onChange="document.location.href='?page=29&action=setligamail&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}'">
  {/if}
  </td>
{/if}
</tr>
{/strip}

{foreach key=userid item=name from=$team->namelist}
  {if (($team->userlist[$userid] && ($smarty.const.TEAM2USER_MEMBER || $smarty.const.TEAM2USER_LEADER)) == $smarty.const.TEAM2USER_MEMBER)}{strip}
    <tr><td class="dblau" width="80">Mitspieler:</td>
    <td class="hblau" width="280"><a href="/?page=4&nUserID={$userid}">{$name|escape}</a>
    {if $seats.$userid.ebene}
      &nbsp;<a href="?page=9&ebene={$seats.$userid.ebene}&locateUser={$userid}">({$seats.$userid.reihe}-{$seats.$userid.platz})</a>
    {/if}
    </td>
    <td class="hblau" width="110" align="center">
    {if $tempflags.kick.$userid}
      <a href="?page=29&action=del&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}"><font color="#FF0000">kick</font></a>
    {/if}
    {if $tempflags.setleader.$userid}
      &nbsp;<a href="?page=29&action=setleader&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}">leader</a>
    {/if}
    </td>
    {if $tempflags.wwclid || $tempflags.nglid}
      <td class="hblau" width="110" align="center">
      {if $tempflags.ligamail.show.$userid}
        <input type="checkbox" {if !$tempflags.ligamail.enabled.$userid}disabled="disabled"{/if} {if $tempflags.ligamail.checked.$userid}checked="checked"{/if} onChange="document.location.href='?page=29&action=setligamail&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}'">
      {/if}
      </td>
    {/if}
    </tr>
  {/strip}{/if}
{/foreach}

{foreach key=userid item=name from=$team->namelist}
  {if ($team->userlist[$userid] & $smarty.const.TEAM2USER_QUEUED)}{strip}
    <tr><td class="dblau" width="60">Anw&auml;rter:</td>
    <td class="hblau" width="280"><a href="/?page=4&nUserID={$userid}">{$name|escape}</a>
    {if $seats.$userid.ebene}
      &nbsp;<a href="/?page=9&ebene={$seats.$userid.ebene}&locateUser={$userid}">({$seats.$userid.reihe}-{$seats.$userid.platz})</a>
    {/if}
    </td>
    <td class="hblau" width="110" align="center">
    {if $tempflags.kick.$userid}
      <a href="?page=29&action=del&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}"><font color="#FF0000">kick</font></a>
    {/if}
    {if $tempflags.accept.$userid}
      &nbsp;<a href="?page=29&action=accept&turnierid={$turnier->turnierid}&teamid={$team->teamid}&userid={$userid}"><font color="#00DD00">accept</font></a>
    {/if}
    </td>
    {if $tempflags.wwclid || $tempflags.nglid}
      <td class="hblau" width="110" align="center"></td>
    {/if}
    </tr>
  {/strip}{/if}
{/foreach}

{if $tempflags.adduser}
  <form method="POST" action="?page=29&action=add&turnierid={$turnier->turnierid}&teamid={$team->teamid}">
  <tr><td class="dblau" width="60">Add UserID:</td>
  <td class="hblau" width="280">
  <input type="text" name="userid">
  </td><td class="hblau" width="110" align="center">
  <input type="submit" value="Add to Team">
  </td>
  {if $tempflags.wwclid || $tempflags.nglid}
    <td class="hblau" width="110" align="center"></td>
  {/if}
  </tr>
  </form>
{/if}

</table>

{if $tempflags.wwclid}
  <p>* Wenn dieser Haken gesetzt ist, wird Deine Emailadresse im Rahmen der Ergebnis&uuml;bermittlung an die WWCL gesandt. Die WWCL ist ein Label der PlanetLAN GmbH, Bochum.</p>
{elseif $tempflags.nglid}
  <p>* Wenn dieser Haken gesetzt ist, wird Deine Emailadresse im Rahmen der Ergebnis&uuml;bermittlung an die NGL-Europe gesandt. Die NGL-Europe ist ein Label der Freaks4u GmbH, Berlin.</p>
{/if}

{if $tempflags.addclan}
  <br>
  <form method="POST" action="?page=29&action=add&turnierid={$turnier->turnierid}&teamid={$team->teamid}">
  Clan Mitglied hinzuf&uuml;gen:&nbsp;
  <select name="userid">
  {foreach key=userid item=name from=$clan}
    <option value="{$userid}">{$name|escape}</option>
  {foreachelse}
    <option value="-1">--</option>
  {/foreach}
  </select>
  <input type="submit" value="=>">
  </form>
{/if}

<br>
{if $tempflags.join}
  <input type="button" value="<< Team beitreten >>" onClick="javascript:window.location.href='?page=29&action=join&turnierid={$turnier->turnierid}&teamid={$team->teamid}'">
{else}
  <input type="button" disabled="disabled" value="<< Team beitreten >>">
{/if}

{if $intranet}
  <br><br>
  <table class="rahmen_allg" cellspacing="1" cellpadding="3" width="490">
  <tr><td class="TNListe" colspan="4">Turnierverlauf</td></tr>
  {foreach key=matchid item=match from=$matches}{strip}
    <tr><td class="dblau" width="100">
    <a href="?page=26&turnierid={$turnier->turnierid}&matchid={$matchid}">
    Match # {$match.viewnum}:</a></td>

    <td class="hblau" align="right" width="170">
    {if $match.flags & $smarty.const.MATCH_TEAM1_GELB}<img src="gfx_turnier/gelbekarte.gif">&nbsp;{/if}
    {if $match.flags & $smarty.const.MATCH_TEAM1_ROT}<img src="gfx_turnier/rotekarte.gif">&nbsp;{/if}
    {if ($match.team1 > 0)}
      <a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match.team1}">{$match.team1name|escape}</a>
    {elseif ($match.team1 == -1)}
      <i>freilos</i>
    {else}-{/if}
    </td>

    {if $match.flags & $smarty.const.MATCH_COMPLETE}
      <td align="center" width="50" bgcolor="{if $match.win}#77FF77{else}#FF7777{/if}">
      <b>{$match.result1} : {$match.result2}</b>
      </td>
    {elseif (($match.flags & $smarty.const.MATCH_TEAM1_ACCEPT) || ($match.flags & $smarty.const.MATCH_TEAM2_ACCEPT))}
      <td align="center" width="50" bgcolor="#FFFF00">vs</td>
    {else}
      <td class="dblau" align="center" width="50">vs</td>
    {/if}

    <td class="hblau" align="left" width="170">
    {if ($match.team2 > 0)}
      <a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match.team2}">{$match.team2name|escape}</a>
    {elseif ($match.team2 == -1)}
      <i>freilos</i>
    {else}-{/if}
    {if $match.flags & $smarty.const.MATCH_TEAM2_ROT}&nbsp;<img src="gfx_turnier/rotekarte.gif">{/if}
    {if $match.flags & $smarty.const.MATCH_TEAM2_GELB}&nbsp;<img src="gfx_turnier/gelbekarte.gif">{/if}
    </td>
    </tr>
  {/strip}{foreachelse}
    <tr><td class="dblau" colspan="4">Es wurden noch keine Paarungen gespielt.</td></tr>
  {/foreach}
  </table>
{/if}
