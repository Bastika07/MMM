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
<a href="?page=21&turnierid={$turnier->turnierid}"><b>{$turnier->name|escape}</b></a>
{if (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ {if !($turnier->flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnier->turnierid}">Turnierbaum</a>
| {/if}<a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>
<h1>Turnier: '{$turnier->name}'</h1>

<table class="rahmen_allg" cellspacing="1" cellpadding="3" width="740">
<tr><td class="TNListe" colspan="3">Turnierdetails:</td></tr>

<tr><td class="dblau" width="100">Ligaunterst&uuml;tzung:</td>
<td class="hblau">
{if $ligagame->liga == $smarty.const.TURNIER_LIGA_NORMAL}Keine Ligaunterst&uuml;tzung
{elseif $ligagame->liga == $smarty.const.TURNIER_LIGA_FUN}Fun Turnier
{elseif $ligagame->liga == $smarty.const.TURNIER_LIGA_WWCL}WWCL Turnier
{elseif $ligagame->liga == $smarty.const.TURNIER_LIGA_NGL}NGL Turnier{/if}
</td>
<td class="hblau" rowspan="6" valign="top" align="right">
	<img hspace="5" vspace="5" src="{$turnier->icon_big}">
	{if ($icon_mindestalter != "")}
		&nbsp; 
		<img hspace="5" vspace="5" src="{$icon_mindestalter}">
	{/if}
</td>
</tr>

{* Turnierart nicht anzeigen wenn es sich um ein HTML-Turnier handelt *}
{if !($turnier->flags & $smarty.const.TURNIER_HTML)}
<tr><td class="dblau" width="100">Turnierart:</td>
<td class="hblau">
{if ($turnier->flags & $smarty.const.TURNIER_SINGLE)}Single Elimination
{elseif ($turnier->flags & $smarty.const.TURNIER_DOUBLE)}Double Elimination
{elseif ($turnier->flags & $smarty.const.TURNIER_RUNDEN)}Rundenturnier
{/if}{if ($turnier->flags & $smarty.const.TURNIER_TREE_RUNDEN)} mit Gruppenphase{/if}</td></tr>
{/if}

<tr><td class="dblau" width="100">Anmeldeschluss:</td><td class="hblau">{$turnier->startzeit}</td></tr>
{if $intranet}<tr><td class="dblau" width="100">Turnierstatus:</td><td class="hblau">{$statusStr}</td></tr>{/if}
{* Coins und Rückgabe *}
<tr><td class="dblau" width="100">Kosten:</td><td class="hblau">{$turnier->coins} Coins
{if ($turnier->coinsback == -1)}(Keine Rückgabe)
{elseif ($turnier->coinsback == 32)}(Rückgabe bis Turnierende)
{else}(Rückgabe bis inkl. Runde {$turnier->coinsback+1})
{/if}
</td></tr>
<tr><td class="dblau" width="100">Teamgr&ouml;sse:</td><td class="hblau">{$turnier->teamsize}</td></tr>
<tr><td class="dblau" width="100">Team Anzahl:</td><td class="hblau">{$teamcount} / {$turnier->teamnum}</td></tr>

<tr><td class="dblau" width="100" >Admins:</td><td colspan="2" class="hblau">
{foreach key=userid item=login from=$admins}
<a href="/?page=4&nUserID={$userid}">{$login|escape}</a>&nbsp;
{foreachelse}
Keine Admins zugeteilt
{/foreach}
</td></tr>

{if $intranet & !empty($turnier->ircchannel)}
  <tr><td class="dblau" width="100">IRC Channel:</td>
  <td class="hblau" colspan="2"><a href="irc://irc.lan:6667/{$turnier->ircchannel}">#{$turnier->ircchannel}</a>
  </td></tr>
{/if}

<tr><td class="TNListe" colspan="3">Preise:</td></tr>
{foreach key=platz item=beschreibung from=$preise}
  <tr><td class="dblau" align="center" width="100"><b>Platz {$platz}</b></td>
  <td class="hblau" colspan="2">{$beschreibung}</td></tr>
{/foreach}

</table>
<br><br>

{if ($turnier->flags & $smarty.const.TURNIER_TREE_RUNDEN && $turnier->pturnierid eq 0)}
<table class="rahmen_allg" cellspacing="1" cellpadding="3" width="500">
<tr><td class="TNListe" colspan="2">Vorrundenturniere:</td></tr>
{foreach item=subtourney from=$subtourneys}
  {strip}
  {cycle name=tropen values="<tr>,"}
    <td class="hblau" width="250"><a href="?page=24&turnierid={$subtourney->turnierid}">{$subtourney->name}</a></td>
  {cycle name=trclose values=",</tr>"}
  {/strip}
{foreachelse}
  <tr><td class="dblau" colspan="2">Es wurden noch keinen Vorrunden erstellt.</td></tr>
{/foreach}

{* ungerade anzahl von tds anpassen *}
{cycle name=trclose values=",</tr>" assign=align}{if $align eq "</tr>"}<td class="hblau">-</td></tr>{/if}

</table>
<br><br>
{/if}

<table class="rahmen_allg" cellspacing="1" cellpadding="3" width="500">
<tr><td class="TNListe" colspan="2">Angemeldete Teams:</td></tr>
{foreach key=teamid item=team from=$teams}
  {strip}
  {cycle name=tropen values="<tr>,"}
    <td class="hblau" width="250"><a href="?page=29&turnierid={$turnier->turnierid}&teamid={$teamid}">{$team.name|escape} {if $turnier->teamsize != 1}({$team.size} / {$turnier->teamsize}){/if}</a></td>
  {cycle name=trclose values=",</tr>"}
  {/strip}
{foreachelse}
  <tr><td class="dblau" colspan="2">Keine Teams angemeldet</td></tr>
{/foreach}

{* ungerade anzahl von tds anpassen *}
{cycle name=trclose values=",</tr>" assign=align}{if $align eq "</tr>"}<td class="hblau">-</td></tr>{/if}

<tr><td class="dblau" colspan="2" align="center">
{if $turnier->status == $smarty.const.TURNIER_STAT_RES_OPEN}
  <input type="button" value="<< Team Anmelden >>" onClick="javascript:window.location.href='?page=27&turnierid={$turnier->turnierid}'">
{else}
  <input type="button" value="<< Team Anmelden >>" disabled="disabled">
{/if}

{if $isadmin}
  &nbsp;<input type="button" value="Create Team" onClick="javascript:window.location.href='?page=28&turnierid={$turnier->turnierid}'">
{/if}

</td>

</tr></table>

<br>
<b><u>Regeln:</u></b><br>
{$turnier->regeln|bbcode2html|nl2br}

