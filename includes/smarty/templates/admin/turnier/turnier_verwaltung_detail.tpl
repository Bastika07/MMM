{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">

<h1>Turnierverwaltung</h1>
{if isset($formerr)}<p class="fehler">{$formerr}</p>{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action={$action}">
{csrf_field}
<input type="hidden" name="form[turnierid]" value="{$turnier->turnierid}">
<input type="hidden" name="form[partyid]" value="{$turnier->partyid}">

<table cellspacing="0" cellpadding="0">
<tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">

<tr><td class="navbar" colspan="2"><b>{if $action eq 'new'}Turnier anlegen{else}Turnier bearbeiten{/if}</b></td></tr>
<tr><td class="dblau">Titel</td><td class="hblau"><input type="text" name="form[name]" size="40" maxlength="64" value="{$turnier->name}">&nbsp;*&nbsp;</td></tr>

<tr><td class="dblau">Mindestalter</td><td class="hblau">
	<select name="form[mindestalter]">
	{foreach from=$mindestalterArr item=usk key=schluessel}
		 <option value="{$schluessel}" {if $schluessel == $turnier->mindestalter} selected="selected" {/if}>{$usk}</option>
	{/foreach}
	</select>
</td></tr>


<tr><td class="dblau">Startzeit (informativ):</td><td class="hblau"><input type="text" name="form[startzeit]" size="30" maxlength="64" value="{$turnier->startzeit}"> *</td></tr>

<tr><td class="dblau">Type / Coins / Gruppe:</td><td class="hblau">
<select name="form[flags][type]">
<option value="{$smarty.const.TURNIER_SINGLE}"{if ($turnier->flags & $smarty.const.TURNIER_SINGLE)} selected="selected" {/if}>Single Elimination</option>
<option value="{$smarty.const.TURNIER_DOUBLE}"{if ($turnier->flags & $smarty.const.TURNIER_DOUBLE)} selected="selected" {/if}>Double Elimination</option>
<option value="{$smarty.const.TURNIER_RUNDEN}"{if ($turnier->flags & $smarty.const.TURNIER_RUNDEN)} selected="selected" {/if}>Einfaches Rundenturnier</option>
<option value="{$smarty.const.TURNIER_HTML}"{if ($turnier->flags & $smarty.const.TURNIER_HTML)} selected="selected" {/if}>HTML only</option>
</select>&nbsp;
<input type="checkbox" name="form[flags][vorrunden]" value="{$smarty.const.TURNIER_TREE_RUNDEN}" {if ($turnier->flags & $smarty.const.TURNIER_TREE_RUNDEN)}checked{/if}> Vorrunden aktivieren.&nbsp;/&nbsp;
<input type="hidden" name="form[pturnierid]" value="{$turnier->pturnierid}">
<select name="form[coins]">
{html_options options=$coinArr selected=$turnier->coins}</select>&nbsp;/&nbsp;
<select name="form[groupid]">
{html_options options=$groupArr selected=$turnier->groupid}</select>&nbsp;
</td></tr>

<tr><td class="dblau">Liga / LigaID:</td><td class="hblau">
<select name="form[gameid]">
{html_options options=$gameidArr selected=$turnier->gameid}</select>&nbsp;
</td></tr>


<!--tr><td class="dblau">Auto. Anmeldung:</td><td class="hblau"><input "disabled" type="checkbox" name="form[flags][autostart]" value="{$smarty.const.TURNIER_AUTOANMELDESTART}" {if ($turnier->flags & $smarty.const.TURNIER_AUTOANMELDESTART)}"checked"{/if}>&nbsp;Die Internetanmeldung für das Turnier wird automatisch gestartet/beendet.</td></tr>
<tr><td class="dblau">Ab 18? (versteckt):</td><td class="hblau"><input "disabled" type="checkbox" name="form[flags][ab18]" value="{$smarty.const.TURNIER_AB18}" {if ($turnier->flags & $smarty.const.TURNIER_AB18)}"checked"{/if}>&nbsp;Turnier wird nicht in der Internet Turnier Liste angezeigt.</td></tr-->
<tr><td class="dblau">Coverage:</td><td class="hblau"><input type="checkbox" name="form[flags][coverage]" value="{$smarty.const.TURNIER_COVERAGE}" {if ($turnier->flags & $smarty.const.TURNIER_COVERAGE)}"checked"{/if}>&nbsp;Turnier wird w&auml;hrend der Party automatisch in der Internet Coverage aktualisiert.</td></tr>
<tr><td class="dblau">Teams / Teamgr&ouml;sse:</td><td class="hblau"><select name="form[teamnum]">
{html_options options=$teamArr selected=$turnier->teamnum}</select> Teams mit <select name="form[teamsize]">
{html_options options=$teamSizeArr selected=$turnier->teamsize}</select> Personen pro Team</td></tr>
<tr><td class="dblau">R&uuml;ckgabe der Coins:</td><td class="hblau">
<select name="form[coinsback]">
<option value="-1" {if $turnier->coinsback == -1} selected="selected" {/if}>Keine R&uuml;ckgabe</option>
<option value="0" {if $turnier->coinsback == 0} selected="selected" {/if}>bis inkl. Runde 1</option>
<option value="1" {if $turnier->coinsback == 1} selected="selected" {/if}>bis inkl. Runde 2</option>
<option value="2" {if $turnier->coinsback == 2} selected="selected" {/if}>bis inkl. Runde 3</option>
<option value="3" {if $turnier->coinsback == 3} selected="selected" {/if}>bis inkl. Runde 4</option>
<option value="4" {if $turnier->coinsback == 4} selected="selected" {/if}>bis inkl. Runde 5</option>
<option value="5" {if $turnier->coinsback == 5} selected="selected" {/if}>bis inkl. Runde 6</option>
<option value="6" {if $turnier->coinsback == 6} selected="selected" {/if}>bis inkl. Runde 7</option>
<option value="7" {if $turnier->coinsback == 7} selected="selected" {/if}>bis inkl. Runde 8</option>
<option value="8" {if $turnier->coinsback == 8} selected="selected" {/if}>bis inkl. Runde 9</option>
<option value="32" {if $turnier->coinsback == 32} selected="selected" {/if}>bis Turnierende</option>
</select>&nbsp;Runde x.5 zählt zu Runde x, nicht zu Runde x+1
</td></tr>
<tr><td class="dblau">IRC Channel:</td><td class="hblau"><b>#</b>&nbsp;<input type="text" name="form[ircchannel]" size="30" maxlength="64" value="{$turnier->ircchannel}"></td></tr>
<tr><td class="dblau">Icon klein:</td><td class="hblau"><input type="text" name="form[icon]" size="30" maxlength="62" value="{$turnier->icon}"></td></tr>
<tr><td class="dblau">Icon gross:</td><td class="hblau"><input type="text" name="form[icon_big]" size="30" maxlength="62" value="{$turnier->icon_big}"></td></tr>
<tr><td class="dblau">Regeln:</td><td class="hblau"><textarea name="form[regeln]" cols=65 rows=18>{$turnier->regeln}</textarea></td></tr>

{if ($turnier->flags == $smarty.const.TURNIER_HTML)}
	<tr><td class="dblau">HTML-Turnierbaum:</td><td class="hblau"><textarea name="form[htmltree]" cols=65 rows=18>{$turnier->htmltree}</textarea></td></tr>
	<tr><td class="dblau">HTML-Ranking:</td><td class="hblau"><textarea name="form[htmlranking]" cols=65 rows=9>{$turnier->htmlranking}</textarea></td></tr>
{/if}

<tr><td class="dblau" colspan="2" align="center"><input type="submit" value="{if $action eq 'new'}Turnier anlegen{else}Turnier &auml;ndern{/if}">
<input type="button" value="Zur&uuml;ck" OnClick="window.history.back();"> <a href="{$smarty.server.PHP_SELF}?action=mixit&turnierid={$turnier->turnierid}">Mixed Turnier</a></td></tr>

</table></td></tr></table>
</form></body></html>
