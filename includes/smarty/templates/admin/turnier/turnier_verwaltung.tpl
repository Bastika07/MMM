{* Smarty Template *}
<h1>Turnierverwaltung (Leitung)</h1>

<select size="1" OnChange="document.location.href='{$smarty.server.PHP_SELF}?partyid='+this.value">
{foreach key=id item=party from=$partys}
  <option value="{$id}"{if $id == $partyid} selected="selected" {/if}>{$party.partyname}</option>
{/foreach}
</select>
<br><br>

{foreach key=id item=party from=$partys}{if $id == $partyid}
<table cellspacing="0" cellpadding="0" width="850"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
  <tr><td class="navbar" colspan="5"><b>{$party.partyname}</b></td></tr>
  <tr>
    <td class="dblau" width="20"></td>
    <td class="dblau" width="20"></td>
    <td class="dblau" width="250"><b>Turnier</b></td>
    <td class="dblau" width="80"><b>Teams</b></td>
    <td class="dblau" width="150"><b>Status</b></td>
  </tr>
  <form method="POST" action="{$smarty.server.PHP_SELF}?action=multicmd">
  {csrf_field}
  {foreach key=turnierid item=turnier from=$party.turniere}
  {if $turnier.groupid != $groupid}
  
    {php}
	    $data1 =& $smarty->get_template_vars['groupid'];
      $data2 = & $smarty->get_template_vars['turnier']['groupid'];
      $data1 = $data2;
    	// $this->_tpl_vars['groupid'] = $this->_tpl_vars['turnier']['groupid'];
     {/php}
    
    {if $groups.$groupid.flags & $smarty.const.GROUP_SHOW}
      <tr><td class="dblau" colspan="5" align="center"><b>{$groups.$groupid.name}</b></td></tr>
    {/if}
  {/if}
  {cycle values='hblau,dblau' assign=tdclass}
    <tr>
    <td class="{$tdclass}" width="20"><input type="checkbox" name="multi[{$turnierid}]"></td>
    <td class="{$tdclass}" width="20" align="center">{if !empty($turnier.icon)}<img src="{$turnier.icon}">{/if}</td>
    <td class="{$tdclass}" width="200">{$turnier.name}</td>
    <td class="{$tdclass}" width="70">{$turnier.teams} / {$turnier.teamnum}
    {if $turnier.flags & $smarty.const.TURNIER_DOUBLE} (DE){/if}
    </td>
    <td class="{$tdclass}" width="150">{$turnier.statusstr}</td>
  </tr>
  {foreachelse}
  <tr><td class="dblau" colspan="5"></td></tr>
  {/foreach}

  <tr><td class="dblau" colspan="5">
    <table><tr>
    <td width="400" align="left">
      <img src="/gfx/arrow_ltr.gif">
      <select name="cmd"><option value=""></option>
      <option value="coverage">Coverage Upload (force)</option>
      <option value="flush">Turnierbaum flushen</option>
      <option value="copy">Turnier kopieren</option>
      <option value="---">-----</option>
      <option value="empty">Alle Teams entfernen</option>
      <option value="remove">Turnier löschen</option>
      </select>&nbsp;<input type="submit" value="=>">
    </td>
  </form>
    <td width="200" align="center"><input type="button" size="200" value="Neues Turnier anlegen" onClick="window.location.href='/admin/turnier/turnier_verwaltung_detail.php?action=new&partyid={$id}'"></td>
    <td width="200" align="center"><input "disabled" type="button" value="Turniere importieren"></td>
    </tr></table>
  </td></tr>

  <tr><td class="dblau" colspan="5">
    <table><tr>
    <td width="200" align="center"><input type="button" value="Turnier Admins" onClick="window.location.href='/admin/turnier/turnier_admins.php?action=show&partyid={$id}'"></td>
    <td width="200" align="center"><input type="button" value="Turnier Preise" onClick="window.location.href='/admin/turnier/turnier_preise.php?action=show&partyid={$id}'"></td>
    <!--<td width="200" align="center"><input type="button" value="Liga Export" onClick="window.location.href='/admin/turnier/turnier_export.php?action=show&partyid={$id}'"></td>-->
    <td width="200" align="center"></td>
    </tr></table>
  </td></tr>

</table></td></tr></table><br>
{/if}{/foreach}