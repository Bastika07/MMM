{* Smarty Template *}
<div id="turnierbaum">
{* Turniername per JavaScript in den Titel einfügen - Markus Thomas - 26.11.2007 *}
{literal}
<script type="text/javascript">
{/literal}
document.title = "{$turnier->name|escape} - " + document.title;
{literal}
function hideAllButTurnierBaum(){
  document.body.innerHTML=document.getElementById('turnierbaum').innerHTML;
}
window.onbeforeprint=hideAllButTurnierBaum
</script>
{/literal}
<a href="?page=20">Turnierliste</a> -
<a href="?page=21&turnierid={$turnier->turnierid}">{$turnier->name|escape}</a>
{if $intranet && (($turnier->status == $smarty.const.TURNIER_STAT_RUNNING) || ($turnier->status == $smarty.const.TURNIER_STAT_PAUSED) || ($turnier->status == $smarty.const.TURNIER_STAT_FINISHED))}
[ <a href="?page=25&turnierid={$turnier->turnierid}"><b>Turnierbaum</b></a>
| <a href="?page=24&turnierid={$turnier->turnierid}">&Uuml;bersicht</a>
| <a href="?page=23&turnierid={$turnier->turnierid}">Ranking</a> ]
{/if}
<br><br>
<h1>{$turnier->name|escape}</h1>

{* <p>Achtung: Der Turnierbaum steht derzeit nicht zur Verfügung. Bitte nutzt aktuell die Übersicht-Funktion: 
<a class="arrow" href="?page=24&turnierid={$turnier->turnierid}">Zur &Uuml;bersicht</a></p> *}

{if ($turnier->flags == $smarty.const.TURNIER_HTML)}

	{$turnier->htmltree}

{else}


<table class="rahmen_allg" border="0" cellspacing="0" cellpadding="1">

{foreach key=y item=row from=$table}
{strip}
  <tr>
  {foreach key=x item=col from=$row}

		{* {php}$this->_tpl_vars['col2'] = $this->_tpl_vars['col'] & TREE_VALUE;{/php} *}
		{assign var = col2 value = $col & $smarty.const.TREE_VALUE }

    {if $col & $smarty.const.TREE_MATCH}
    
	    {* {php}$this->_tpl_vars['match'] = $this->_tpl_vars['matches'][$this->_tpl_vars['col2']];{/php} *}
    	{assign var = match value = $matches.$col2 } 
      
	    {* Test col2: {var_dump($col2)} Match: {var_dump($match)} *}
      
      <td class="hblau" rowspan="3" height="50">
      &nbsp;# {$match.viewnum}
      
      <table border="0" cellspacing="0" cellpadding="1" width="180" height="50"><tr><td class="TNListe">
      <table border="0" cellspacing="0" cellpadding="1" width="100%" height="100%">

      <tr><td class="dblau" align="center" colspan="3">
      {if $match.team1 > 0}
      
        {* {php}$this->_tpl_vars['team1'] = $this->_tpl_vars['teams'][$this->_tpl_vars['match']['team1']];{/php} *}
        
	<a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match.team1}">{$teams[$match.team1].name|truncate:25:"...":true|escape}</a>
      {elseif $match.team1 == -1}
	<i>freilos</i>
      {else}-{/if}
      </td></tr>

      <tr><td class="dblau" width="50" align="center">
      {if $match.flags & $smarty.const.MATCH_TEAM1_GELB}<img src="gfx_turnier/gelbekarte.gif">&nbsp;{/if}
      {if $match.flags & $smarty.const.MATCH_TEAM1_ROT}<img src="gfx_turnier/rotekarte.gif">&nbsp;{/if}
      </td>

      <td class="dblau" width="50" align="center">
      <a href="?page=26&turnierid={$turnier->turnierid}&matchid={$match.matchid}">
      {if $match.flags & $smarty.const.MATCH_COMPLETE}
	<b>{$match.result1} : {$match.result2}</b>
      {else}vs{/if}
      </a></td>

      <td class="dblau" width="50" align="center" valign="center">
      {if $match.flags & $smarty.const.MATCH_TEAM2_ROT}&nbsp;<img src="gfx_turnier/rotekarte.gif">{/if}
      {if $match.flags & $smarty.const.MATCH_TEAM2_GELB}&nbsp;<img src="gfx_turnier/gelbekarte.gif">{/if}
      </td></tr>

      <tr><td class="dblau" align="center" colspan="3">
      {if $match.team2 > 0}
			 {* {php}$this->_tpl_vars['team2'] = $this->_tpl_vars['teams'][$this->_tpl_vars['match']['team2']];{/php} *}
       
	<a href="?page=29&turnierid={$turnier->turnierid}&teamid={$match.team2}" title="{$teams[$match.team2].name|escape}">{$teams[$match.team2].name|truncate:25:"...":true|escape}</a>
      {elseif $match.team2 == -1}
	<i>freilos</i>
      {else}-{/if}
      </td></tr>

      </table></td></tr></table>
      &nbsp;{if $match.note}{$match.note}{/if}
      </td>

    {elseif $col & $smarty.const.TREE_LINE}
      <td rowspan="{$col2}" bgcolor="#7f7f7f">&nbsp;</td>

    {elseif $col & $smarty.const.TREE_SPAN}
      <td class="hblau" rowspan="{$col2}">&nbsp;</td>

    {elseif $col & $smarty.const.TREE_SPAN2}
      <td class="hblau" rowspan="{$col2}" colspan="2">&nbsp;</td>

    {elseif $col & $smarty.const.TREE_FREE}
      <td class="hblau" height="10">&nbsp;</td>

    {elseif $col & $smarty.const.TREE_ROUND}
      <td class="dblau" align="center" height="30">
      {if $rounds.$col2}
	{* {php}$this->_tpl_vars['round'] = $this->_tpl_vars['rounds'][$this->_tpl_vars['col2']];{/php} *}
  {assign var = round value = $rounds.$col2 }
  
	<b>{$round.name|escape}</b><br>
	{$round.begins|escape} - {$round.ends|escape}<br>
	{$round.info|escape}
      {/if}
      </td>
    {/if}
  {/foreach}
  </tr>
{/strip}
{/foreach}
</table>


{/if}
</div>
