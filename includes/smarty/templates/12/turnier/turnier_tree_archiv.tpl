{* Smarty Template *}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD>
<title>Northern LAN Convention - LAN-Party in der Holstenhalle Neum&uuml;nster bei Hamburg</title>
<style>
{include_php file="/var/www/www.northcon.de/format.css"}
</style>

<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
</HEAD>
<BODY BGCOLOR="#FFFFFF" LEFTMARGIN=0 TOPMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0 >

<h1>{$turnier->name|escape}</h1>
<table class="rahmen_allg" border="0" cellspacing="0" cellpadding="1">
{foreach key=y item=row from=$table}
{strip}
  <tr>
  {foreach key=x item=col from=$row}
    {php}$this->_tpl_vars['col2'] = $this->_tpl_vars['col'] & TREE_VALUE;{/php}
    {if $col & TREE_MATCH}
      {php}$this->_tpl_vars['match'] = $this->_tpl_vars['matches'][$this->_tpl_vars['col2']];{/php}
      <td class="hblau" rowspan="3" height="50">
      &nbsp;# {$match.viewnum}
      <table border="0" cellspacing="0" cellpadding="1" width="150" height="50"><tr><td class="TNListe">
      <table border="0" cellspacing="0" cellpadding="1" width="100%" height="100%">

      <tr><td class="dblau" align="center" colspan="3">
      {if $match.team1 > 0}
        {php}$this->_tpl_vars['team1'] = $this->_tpl_vars['teams'][$this->_tpl_vars['match']['team1']];{/php}
	{$team1.name|escape}
      {elseif $match.team1 == -1}
	<i>freilos</i>
      {else}-{/if}
      </td></tr>

      <tr><td class="dblau" width="50" align="center">
      {if $match.flags & $smarty.const.MATCH_TEAM1_GELB}<img src="gfx_turnier/gelbekarte.gif">&nbsp;{/if}
      {if $match.flags & $smarty.const.MATCH_TEAM1_ROT}<img src="gfx_turnier/rotekarte.gif">&nbsp;{/if}
      </td>

      <td class="dblau" width="50" align="center">

      {if $match.flags & $smarty.const.MATCH_COMPLETE}
	<b>{$match.result1} : {$match.result2}</b>
      {else}vs{/if}
      </td>

      <td class="dblau" width="50" align="center" valign="center">
      {if $match.flags & $smarty.const.MATCH_TEAM2_ROT}&nbsp;<img src="gfx_turnier/rotekarte.gif">{/if}
      {if $match.flags & $smarty.const.MATCH_TEAM2_GELB}&nbsp;<img src="gfx_turnier/gelbekarte.gif">{/if}
      </td></tr>

      <tr><td class="dblau" align="center" colspan="3">
      {if $match.team2 > 0}
	{php}$this->_tpl_vars['team2'] = $this->_tpl_vars['teams'][$this->_tpl_vars['match']['team2']];{/php}
	{$team2.name|escape}
      {elseif $match.team2 == -1}
	<i>freilos</i>
      {else}-{/if}
      </td></tr>

      </table></td></tr></table>
      &nbsp;{if $match.note}{$match.note}{/if}
      </td>

    {elseif $col & TREE_LINE}
      <td rowspan="{$col2}" bgcolor="#7f7f7f">&nbsp;</td>

    {elseif $col & TREE_SPAN}
      <td class="hblau" rowspan="{$col2}">&nbsp;</td>

    {elseif $col & TREE_SPAN2}
      <td class="hblau" rowspan="{$col2}" colspan="2">&nbsp;</td>

    {elseif $col & TREE_FREE}
      <td class="hblau" height="10">&nbsp;</td>

    {elseif $col & TREE_ROUND}
      <td class="dblau" align="center" height="30">
      {if $rounds.$col2}
	{php}$this->_tpl_vars['round'] = $this->_tpl_vars['rounds'][$this->_tpl_vars['col2']];{/php}
	<b>{$round.name|escape}</b><br>{$round.begins|escape} - {$round.ends|escape}
      {/if}
      </td>
    {/if}
  {/foreach}
  </tr>
{/strip}
{/foreach}
</table>

</body></html>
