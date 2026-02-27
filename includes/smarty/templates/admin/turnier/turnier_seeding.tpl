{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
{literal}
<script language="JavaScript">
<!--
function team_kicken(teamid)  {
  if (confirm("Soll das Team wirklich gekickt werden?"))
    {window.location.href="{/literal}{$smarty.server.PHP_SELF}?action=kick&turnierid={$turnier->turnierid}{literal}&teamid="+ teamid;}
  else 
    {}
}
// -->
</script>{/literal}

</head><body bgcolor="#FFFFFF">

<h1>Seeding</h1>

{if $alreadyPlayed}
  <font color="#FF0000">Das Turnier wird bereits gespielt:</font>
  <a href="{$smarty.server.PHP_SELF}?action=reset&turnierid={$turnier->turnierid}"><b>Zurücksetzen?</b></a>
  <br><br>
  <a href="turnier/turnier_verwaltung_list.php?partyid={$turnier->partyid}">Zurück zur Turnierverwaltung</a>

{elseif $wrongSize}
  <b>Turnierbaum nicht vorhanden oder falsche Gr&ouml;sse</b>
  <a href="{$smarty.server.PHP_SELF}?action=create&turnierid={$turnier->turnierid}">Erstellen?</a>
  (Achtung, auch Rundenzeiten werden geloescht)
  <br><br>
{else}

{if (!($turnier->flags & TURNIER_TREE_RUNDEN))}
{if $toobig}
  <font color="#FF0000">Das Turnier ist zu gross für diese Teilnehmerzahl</font>
  <a href="{$smarty.server.PHP_SELF}?action=resize&turnierid={$turnier->turnierid}"><b>Korrigieren?</b></a>
  (Sonst zuviele Freilose)
  <br><br>
{/if}

<b>Vorschau [
<a href="/?page=25&turnierid={$turnier->turnierid}" target="_blank"> Turnierbaum </a>-
<a href="/?page=24&turnierid={$turnier->turnierid}" target="_blank"> Turnierübersicht</a> ]</b>
<br><br>
{/if}

<table cellspacing="0" cellpadding="0" width="700"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
<tr><td class="navbar" colspan="2"><b>{$turnier->name|escape}</b> ({$teamcount}/{$turnier->teamnum}) Teams</td></tr>

{foreach name=teams key=teamid item=team from=$teams}{strip}
{cycle name=tropen values="<tr>,"}
<td width="325" {if $turnier->teamsize != $team.size}bgcolor="#FFDDDD"{else}class="dblau"{/if}>

<table><tr>
  <td width="200">
    <a href="/?page=29&turnierid={$turnier->turnierid}&teamid={$teamid}" target="_blank">{$team.name|escape}</a>
    {if $turnier->teamsize != 1} ({$team.size}/{$turnier->teamsize}){/if}&nbsp;
  </td><td width="50" align="center">
    <a onClick="team_kicken({$teamid})" style="cursor:pointer;">
    <font color="#ff0000">kick</font></a>
  
  </td><td width="75" align="center">
    {if isset($seed.$teamid)}
      <b>#{$seed.$teamid}<b>
    {else}
      {if isset($fill.$teamid)}#{$fill.$teamid} {/if}
      <a href="{$smarty.server.PHP_SELF}?action=seed&turnierid={$turnier->turnierid}&teamid={$teamid}">
      <font color="#ff7700">seed</font></a>
    {/if}
  </td>
</tr></table>

</td>
{cycle name=trclose values=",</tr>\n"}
{/strip}{/foreach}

{section name=freilose loop=$freilose}{strip}
{cycle name=tropen values="<tr>,"}
<td class="hblau" width="250"><i>Freilos</i></td>
{cycle name=trclose values=",</tr>\n"}
{/strip}{/section}

</table></td></tr></table>

<br>
<table cellpadding="5">
{if (!($turnier->flags & TURNIER_TREE_RUNDEN))}
<tr>
<td><a href="{$smarty.server.PHP_SELF}?action=random&turnierid={$turnier->turnierid}"><b>Teams zulosen und Freilose auff&uuml;llen</b></a>
</td><td>Mehrfach möglich, Seedings bleiben bestehen</td>
</tr>
{/if}
<tr>

<td><a href="{$smarty.server.PHP_SELF}?action=reset&turnierid={$turnier->turnierid}">Reset Seeding</a>
</td><td>Komplett von vorne anfangen</td>
</tr>

<tr><td><a href="turnier/turnier_roundlist.php?turnierid={$turnier->turnierid}" target="_new">Rundenzeiten bearbeiten</a></td><td>Rundenzeiten und Defaultmaps eintragen (neues Fenster)</td></tr>

</table>

<br><br>
<a href="turnier/turnier_verwaltung_status.php?action=setStatus&turnierid={$turnier->turnierid}&cmd=8"><b>Start des Turniers</b></a>
&nbsp; oder &nbsp;
<a href="turnier/turnier_verwaltung_list.php?partyid={$turnier->partyid}">Zurück zur Turnierverwaltung</a>

{/if}{* if $alreadyPlayed *}
</body></html>
