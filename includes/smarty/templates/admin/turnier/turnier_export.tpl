{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">
<h1>[<a href="turnier/{$smarty.server.PHP_SELF}?action=show&liga={$smarty.const.TURNIER_LIGA_WWCL}&partyid={$partyid}"> WWCL </a>|
<a href="turnier/{$smarty.server.PHP_SELF}?action=show&liga={$smarty.const.TURNIER_LIGA_NGL}&partyid={$partyid}"> NGL </a>] Turniere</h1>

<form method="post" action="{$smarty.server.PHP_SELF}?action=generate&liga={$liga}&partyid={$partyid}">
<table cellspacing="0" cellpadding="0"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">

<tr><td class="navbar"></td>
<td class="navbar">Turnier</td>
<td class="navbar">LigaID</td>
<td class="navbar">Teams</td>
<td class="navbar">Status</td></tr>

{foreach key=turnierid item=turnier from=$turniere}
  <tr><td class="hblau" align="center">
  {if $turnier.liga == $liga}
    <input type="checkbox" name="check[{$turnierid}]" {if $turnier.check}checked="checked"{/if}></td>
  {else}
    <input type="checkbox" name="check[{$turnierid}]" disabled="disabled"></td>
  {/if}
    <td class="hblau" width="250">{$turnier.name}</td>
    <td class="hblau" width="80">{$turnier.liganame}</td>
    <td class="hblau" width="50">{$turnier.teams}/{$turnier.teamnum}</td>
    <td class="hblau" width="200">{$turnier.statusstr}</td></tr>
{/foreach}

{if $liga == $smarty.const.TURNIER_LIGA_NGL}
  <tr><td class="navbar" colspan="5">Party Infos</td></tr>
  <tr><td class="dblau" colspan="2">PartyID</td><td class="hblau" colspan="3"><input type="text" name="form[ngl][partyid]">&nbsp;<a href="turnier/http://www.ngl-europe.com/" target="_blank">ngl-europe.com</a></td></tr>
  <tr><td class="dblau" colspan="2">Party Name</td><td class="hblau" colspan="3"><input type="text" name="form[ngl][partyname]">&nbsp;<a href="turnier/http://www.ngl-europe.com/" target="_blank">ngl-europe.com</a></td></tr>
  <tr><td class="dblau" colspan="2">Start Datum der LAN</td><td class="hblau" colspan="3"><input type="text" name="form[ngl][datum]">&nbsp;(YYYY-MM-DD)</td></tr>
  <tr><td class="dblau" colspan="2">Kontakt Email</td><td class="hblau" colspan="3"><input type="text" name="form[ngl][contact]" value="info@innovalan.de"></td></tr>
  <tr><td class="dblau" colspan="5" align="center">
  <input type="submit" value="Generate Export">&nbsp;<input type="button" value="Zur&uuml;ck" OnClick="window.history.back();">

{else if $liga == $smarty.const.TURNIER_LIGA_WWCL}
  <tr><td class="navbar" colspan="5">Party Infos</td></tr>
  <tr><td class="dblau" colspan="2">Party Name</td><td class="hblau" colspan="3"><input type="text" name="form[wwcl][partyname]"></td></tr>
  <tr><td class="dblau" colspan="2">Party ID</td><td class="hblau" colspan="3"><input type="text" name="form[wwcl][partyid]"></td></tr>
  <tr><td class="dblau" colspan="2">Veranstalter ID</td><td class="hblau" colspan="3"><input type="text" name="form[wwcl][veranstalterid]"></td></tr>
  <tr><td class="dblau" colspan="2">Stadt</td><td class="hblau" colspan="3"><input type="text" name="form[wwcl][stadt]"></td></tr>
  <tr><td class="dblau" colspan="5" align="center">
  <input type="submit" value="Generate Export">&nbsp;<input type="button" value="Zur&uuml;ck" OnClick="window.history.back();">
{/if}

</td></tr>
</table></td></tr></table><br>
</form>

</body></html>
