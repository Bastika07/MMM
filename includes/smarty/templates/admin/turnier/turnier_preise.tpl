{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
{* @TODO: Stylesheets?!!??! *}
{literal} 
<script type="text/javascript">
function addTurnierPreis(turnierid)
{
	var preisTable = document.getElementById("turnier_preise_" + turnierid);
	var preisNo = preisTable.childElementCount + 1;
	var newField = document.createElement("tr");
	newField.innerHTML = "<tr><td class=\"dblau\" width=\"50\" align=\"center\"><b>Platz " + preisNo + "</b></td>" +
		"<td class=\"hblau\" width=\"750\"><input type=\"text\" size=\"70\" name=\"form[" +preisNo+ "]\"></td></tr>";
	preisTable.appendChild(newField);
} 
</script>
{/literal}
 
</head><body bgcolor="#FFFFFF">
<h1>Turnier Preise</h1>
<a href="{$smarty.server.PHP_SELF}?action=print&partyid={$partyid}">Druckansicht</a>
<br><br>
{foreach key=turnierid item=turnier from=$turniere}
{if $turnier.pturnierid eq 0} {* Nur Hauptturniere anzeigen*}
  {if $turnier.groupid != $groupid}
    {php}
	    $data1 =& $smarty->get_template_vars['groupid'];
      $data2 = & $smarty->get_template_vars['turnier']['groupid'];
      $data1 = $data2;
    	// $this->_tpl_vars['groupid'] = $this->_tpl_vars['turnier']['groupid'];
    {/php}
    {if $groups.$groupid.flags & $smarty.const.GROUP_SHOW}
      <h1>{$groups.$groupid.name}</h1>
    {/if}
  {/if}
  <a name="turnier_{$turnierid}"></a>
  {if $editid eq $turnierid}
  <form method="post" name="turnier" action="{$smarty.server.PHP_SELF}?action=save&partyid={$partyid}&turnierid={$turnierid}#turnier_{$turnierid}">
  {csrf_field}
  {/if} 
  <table cellspacing="0" cellpadding="0" width="800">
  	<tr><td class="navbar">  
  	
  	<table width="100%" cellspacing="1" cellpadding="3">
  		<thead><tr><td class="navbar" colspan="2"><b>{$turnier.name|escape}</b></td></tr></thead>
  		<tbody id="turnier_preise_{$turnierid}">
  		{if $editid eq $turnierid}
    	{foreach key=platz item=beschreibung from=$turnier.preise}
      	<tr>
      		<td class="dblau" width="50" align="center"><b>Platz {$platz}</b></td>
      		<td class="hblau" width="750"><input type="text" size="70" name="form[{$platz}]" value="{$beschreibung}"></td>
      	</tr>
    	{/foreach}
    	</tbody>
    	<tfoot>
    	<tr><td class="dblau" colspan="2">
    		<a href="javascript:document.forms.turnier.submit()">Speichern ...</a>
    		<a href="javascript:addTurnierPreis({$turnierid})">Preis hinzuf&uuml;gen</a>
    	</td></tr>
    	</tfoot>
  		{else}
    	{foreach key=platz item=beschreibung from=$turnier.preise}
      	<tr><td class="dblau" width="50" align="center"><b>Platz {$platz}</b></td><td class="hblau" width="750">{$beschreibung}</td></tr>
    	{/foreach}
    	</tbody>
    	<tfoot>
    	<tr><td class="dblau" colspan="2">
    		<a href="{$smarty.server.PHP_SELF}?action=edit&partyid={$partyid}&turnierid={$turnierid}#turnier_{$turnierid}">Preise editieren ...</a>
    	</td></tr>
    	</tfoot>
  		{/if}
  	</table></td></tr>
  </table>
  <br />
  {if $editid eq $turnierid}
  </form>
  {/if}
{/if}
{/foreach}
</body></html>
