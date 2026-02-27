{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
{literal}
<script language="JavaScript">
<!--
function reseed(turnierid)  {
  if (confirm("Fuer ein neues Seeding muessen alle Maches geloescht werden! Weiter machen?"))
    {window.location.href="{/literal}{$smarty.server.PHP_SELF}?action=setStatus&turnierid="+turnierid+"&cmd={$smarty.const.TURNIER_CMD_RESEED}{literal}";}
  else 
    {}
}
function reset(turnierid)  {
  if (confirm("Turnier wird zurueck gesetzt und alle Maches geloescht werden! Weiter machen?"))
    {window.location.href="{/literal}{$smarty.server.PHP_SELF}?action=setStatus&turnierid="+turnierid+"&cmd={$smarty.const.TURNIER_CMD_RESET}{literal}";}
  else 
    {}
}
// -->
</script>{/literal}
</head><body bgcolor="#FFFFFF">
<h1>Turnierstatus setzten f&uuml;r: {$turnier->name|escape}</h1>

<table cellspacing="0" cellpadding="0" width="850"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
  <tr>
    <td class="navbar" width="200">Status</td>
    <td class="navbar">Beschreibung</td>
  </tr>
  <tr>
    <td class="dblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_RES_NOT_OPEN}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_NOT_OPEN}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_NOT_OPEN}">Anmeldung noch nicht er&ouml;ffnen</a>{else}Anmeldung noch nicht er&ouml;ffnet{/if}</td>
    <td class="dblau">Die Anmeldung wurde noch nicht er&ouml;ffnet.</td>
  </tr>
  <tr>
    <td class="hblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_RES_OPEN}style="font-weight:bold;"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_OPEN}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_OPEN}">Anmeldung &Ouml;ffnen</a>{else}Anmeldung ist offen{/if}</td>
    <td class="hblau">Turnieranmeldungen sind m&ouml;glich.</td>
  </tr>
  <tr>
    <td class="dblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_RES_CLOSED}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_CLOSE}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_CLOSE}">Anmeldung schlie&szlig;en</a>{else}Anmeldung ist geschlossen{/if}</td>
    <td class="dblau">Turnieranmeldung schlie&szlig;en</td>
  </tr>
  <tr>
    <td class="hblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_SEEDING}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_SEED}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_SEED}">Paarungen auslosen</a>{else}Paarungen sind ausgelost{/if}</td>
    <td class="hblau">Paarungen werden ausgelost.</td>
  </tr>
  <tr>
    <td class="dblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_RUNNING}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_PLAY}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_PLAY}">Turnier starten</a>{else}Turnier l&auml;uft{/if}</td>
    <td class="dblau">Matches werden k&ouml;nnen gespielt werden.</td>
  </tr>
  <tr>
    <td class="hblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_PAUSED}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_PAUSE}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_PAUSE}">Turnier pausieren</a>{else}Turnier ist pausiert.{/if}</td>
    <td class="hblau">Matches k&ouml;nnen nicht gespielt werden.</td>
  </tr>
  <tr>
    <td class="dblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_FINISHED}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_FINISHED}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_FINISHED}">Turnier beenden</a>{else}Turnier ist beendet{/if}</td>
    <td class="dblau">Turnier manuelle auf beendet setzten, z.B. nachdem es schon beendet war, f&uuml;r eine &Auml;nderung jedoch nochmal ge&ouml;ffnet wurde..</td>
  </tr>
  <tr>
    <td class="dblau" {if $turnier->status eq $smarty.const.TURNIER_STAT_CANCELED}style="font-weight:bold"{/if}>{if $turnier_cmds & $smarty.const.TURNIER_CMD_CANCEL}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_CANCEL}">Turnier absagen</a>{else}Turnier ist abgesagt{/if}</td>
    <td class="dblau">Das Turnier findet nicht statt. Coins werden auf 0 gesetzt.</td>
  </tr>
  <tr>
    <td class="hblau">{if $turnier_cmds & $smarty.const.TURNIER_CMD_TRANSFER}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_TRANSFER}">Vorrunden auswerten</a>{else}Vorrunden auswerten{/if}</td>
    <td class="hblau">Alle Ergebnisse der Vorrunden zum Hauptturnier &uuml;bertragen. Vorrunden m&uuml;ssen beendet sein. <b>Funktioniert nur einmalig!</b></td>
  </tr>
<!--  <tr>
    <td class="dblau">{if $turnier_cmds & $smarty.const.TURNIER_CMD_PRELIM_DEL}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_PRELIM_DEL}">Vorrunden l&ouml;schen</a>{else}Vorrunden l&ouml;schen{/if}</td>
    <td class="dblau">Alle Vorrunden l&ouml;schen, um das Hauptturnier neu zu auszulosen.</td>
  </tr>-->
  <tr>
    <td class="hblau">{if $turnier_cmds & $smarty.const.TURNIER_CMD_PRELIM_UP}<a href="{$smarty.server.PHP_SELF}?action=setStatus&turnierid={$turnier->turnierid}&cmd={$smarty.const.TURNIER_CMD_PRELIM_UP}">Vorrunden aktualisieren</a>{else}Vorrunden aktualisieren{/if}</td>
    <td class="hblau">Vorrunden aktualisieren nachdem ein Team dem Haupturnier hinzugef&uuml;gt wurde, ohne neu zu seeden.</td>
  </tr>
  <tr>
    <td class="hblau">{if $turnier_cmds & $smarty.const.TURNIER_CMD_RESEED}<a style="cursor:pointer; color:#00F;" onClick="reseed({$turnier->turnierid})">Teams neu seeden</a>{else}Teams neu seeden{/if}</td>
    <td class="hblau">Teams werden neu geseedet. Alle Matches und Vorrunden werden zur&uuml;ck gesetzt!!!</td>
  </tr>
  <tr>
    <td class="hblau">{if $turnier_cmds & $smarty.const.TURNIER_CMD_RESET}<a style="cursor:pointer; color:#00F;" onClick="reset({$turnier->turnierid})">Turnier zur&uuml;chsetzten</a>{else}Turnier zur&uuml;chsetzten{/if}</td>
    <td class="hblau">Anmeldung wird noch nicht ge&ouml;ffnet. Alle Matches und Vorrunden werden zur&uuml;ck gesetzt!!!</td>
  </tr> 
</table>
</td></tr>
</table>

<p><a href="turnier/turnier_verwaltung_list.php?partyid={$turnier->partyid}">Zur Turnierverwaltung</a></p>

</body></html>
