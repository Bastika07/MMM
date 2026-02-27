{* Smarty Template *}

<p><b>Turnierliste</b>&nbsp; <a href="?page=22" class="arrow">Turnier-FAQ</a></p>

{if isset($coins)}
  <p>Deine Coins: {$coins} / {$maxCoins}</p>
{/if}

<table class="rahmen_allg" width="99%" cellspacing="1" cellpadding="3">
  <tr>
    <td class="TNListe" width="20"></td>
    <td class="TNListe" width="330">Turnier (Teilnehmer)</td>
    <td class="TNListe" width="30">Coins</td>
    {if $intranet}<td class="TNListe" width="40">IRC</td>{/if}
    <td class="TNListe" width="130">Anmeldeschluss</td>
    <td class="TNListe" width="35">&nbsp;</td>
    {if $intranet}
    <td class="TNListe" width="120">Turnierstatus</td>
    <td class="TNListe" width="80">Turnier&uuml;bersicht</td>
    {/if}
  </tr>
  {foreach key=turnierid item=turnier from=$turniere}
  {if $turnier.pturnierid eq 0} {* Nur Hauptturniere anzeigen*}
  {if $turnier.groupid != $groupid}
    
    {$groupid = $turnier.groupid }
    
    {if $groups.$groupid.flags & $smarty.const.GROUP_SHOW}
      <tr><td class="dblau" colspan="{if $intranet}7{else}5{/if}" align="center">
      <b>{$groups.$groupid.name}</b></td></tr>
    {/if}
  {/if}
  {cycle values='hblau,dblau' assign=tdclass}
    <tr>
    <td class="{$tdclass}" width="20" align="center">{if !empty($turnier.icon)}<img src="{$turnier.icon}">{/if}</td>
    <td class="{$tdclass}" width="330">
    <a href="?page=21&turnierid={$turnierid}">{$turnier.name}</a>&nbsp;({$turnier.teams}/{$turnier.teamnum})
    {if $turnier.isSignedUp} <a href="?page=29&action=del&turnierid={$turnierid}&teamid={$turnier.ownTeamId}&userid={$userId}" title="Von diesem Turnier abmelden"><img src="/gfx/turnier_abmelden.gif" border="0" align="top"></a> {/if}
    </td>
    <td class="{$tdclass}" width="30" align="center">{$turnier.coins}</td>
    {if $intranet}
      <td class="{$tdclass}" width="40">{if $turnier.ircchannel}<a href="irc://irc.lan:6667/{$turnier.ircchannel}/">#{$turnier.ircchannel}</a>{/if}</td>
    {/if}
    <td class="{$tdclass}" width="130">{$turnier.startzeit}</td>
    <td class="{$tdclass}" width="35">
			{if ($turnier.mindestalter != "")}
				<img src="{$pelashost}/gfx/ab{$turnier.mindestalter}.png" height="32">
			{/if}
		</td>
    {if $intranet}
      <td class="{$tdclass}" width="120">{$turnier.statusstr}</td>
      <td class="{$tdclass}" width="130" align="center">
      {if ($turnier.status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier.status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier.status == $smarty.const.TURNIER_STAT_FINISHED)}
        {if !($turnier.flags & $smarty.const.TURNIER_RUNDEN)}<a href="?page=25&turnierid={$turnierid}">Turnierbaum</a>{/if}
        <a href="?page=24&turnierid={$turnierid}">&Uuml;bersicht</a>
        <a href="?page=23&turnierid={$turnierid}">Ranking</a>
      {/if}
      </td>
    {/if}
    </tr>
  {/if}
  {/foreach}
</table><br>
