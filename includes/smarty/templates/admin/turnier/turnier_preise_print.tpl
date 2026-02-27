{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">
{foreach key=turnierid item=turnier from=$turniere}
{if $turnier.pturnierid eq 0} {* Nur Hauptturniere anzeigen*}
  {if $turnier.groupid != $groupid}
    {php}
 	    $data1 = & $smarty->get_template_vars['groupid'];
      $data2 = & $smarty->get_template_vars['turnier']['groupid'];
      $data1 = $data2;
    	// $this->_tpl_vars['groupid'] = $this->_tpl_vars['turnier']['groupid'];
    {/php}
    {if $groups.$groupid.flags & $smarty.const.GROUP_SHOW}
      <h1>{$groups.$groupid.name}</h1>
    {/if}
  {/if}
  <table width="800" cellspacing="1" cellpadding="3" border="1" style="page-break-inside:avoid">
  <tr><td colspan="3" align="center"><b>{$turnier.name|escape}</b></td></tr>
  {foreach key=platz item=beschreibung from=$turnier.preise}
    <tr>
    <td width="80" align="center"><b>Platz {$platz}</b></td>
    <td width="200" align="center">{$turnier.ranking.$platz.teamname|escape}&nbsp;</td>
    <td width="420">{$beschreibung|escape}&nbsp;</td>
    </tr>
  {/foreach}
  </table>
  <br><br>
{/if}
{/foreach}
</body></html>
