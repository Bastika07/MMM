{*smarty*}
{assign var=title value="BTracking::Index"}
{*include file="header.tpl"*}
<h1>&nbsp;[ BugTracking::Uebersicht ]</h1>
{include file='bugTrack_header.tpl'}
<table border="0" cellpadding="0" cellspacing="0" width=80%>
    <tr>
      <td class="navbar" width="45">BugID</td><td class="navbar" width="80">Priorität</td><td class="navbar" width="120">IP</td>
      <td class="navbar" width="120">Art</td><td class="navbar" width="120">UserName</td><td class="navbar" width="80">Reihe/Platz</td>
      <td class="navbar" width="80">Lebenszeit</td><td class="navbar" colspan=2 width="200"><center>Aktionen</center></td>
    </tr>
    <tr ><td>&nbsp;</td></tr>
    <tr><td colspan = 8><font color="red">TODO:</font></td></tr>
    {section name=sec loop=$data1}
    {cycle values="hblau,dblau" assign=tdclass}
	<tr>
	<td class="{$tdclass}"><font color="red">{$data1[sec].bugId}</font></td>
	<td class="{$tdclass}">{$data1[sec].prio}</td>
	<td class="{$tdclass}">{$data1[sec].ip}</td>
	<td class="{$tdclass}">{$data1[sec].type}</td>
	<td class="{$tdclass}">{$data1[sec].login}</td>
	<td class="{$tdclass}">{$data1[sec].reihe}/{$data1[sec].platz}</td>
	<td class="{$tdclass}">{$data1[sec].zeit}</td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=modify&bugId={$data1[sec].bugId}">
	<font color="#FF0000">UPDATE</font></a></td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=del&bugId={$data1[sec].bugId}">
	<font color="#FF0000">DEL</font></a></td></tr>
   {/section}
   <tr><td>&nbsp;</td></tr>
   <tr><td colspan = 8><font color="orange">IN WORK:</font></td></tr>
   {section name=sec loop=$data2}
    {cycle values="hblau,dblau" assign=tdclass}
	<tr>
	<td class="{$tdclass}"><font color="orange">{$data2[sec].bugId}</font></td>
	<td class="{$tdclass}">{$data2[sec].prio}</td>
	<td class="{$tdclass}">{$data2[sec].ip}</td>
	<td class="{$tdclass}">{$data2[sec].type}</td>
	<td class="{$tdclass}">{$data2[sec].login}</td>
	<td class="{$tdclass}">{$data2[sec].reihe}/{$data2[sec].platz}</td>
	<td class="{$tdclass}">{$data2[sec].zeit}</td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=modify&bugId={$data2[sec].bugId}">
	<font color="#FF0000">UPDATE</font></a></td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=del&bugId={$data2[sec].bugId}">
	<font color="#FF0000">DEL</font></a></td></tr>
	
   {/section}
   <tr><td>&nbsp;</td></tr>
    <tr><td colspan = 8><font color="green">SOLVED:</font></td></tr>
   {section name=sec loop=$data3}
    {cycle values="hblau,dblau" assign=tdclass}
	<tr>
	<td class="{$tdclass}"><font color="green">{$data3[sec].bugId}</font></td>
	<td class="{$tdclass}">{$data3[sec].prio}</td>
	<td class="{$tdclass}">{$data3[sec].ip}</td>
	<td class="{$tdclass}">{$data3[sec].type}</td>
	<td class="{$tdclass}">{$data3[sec].login}</td>
	<td class="{$tdclass}">{$data3[sec].reihe}/{$data3[sec].platz}</td>
	<td class="{$tdclass}">{$data3[sec].zeit}</td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=modify&bugId={$data3[sec].bugId}">
	<font color="#FF0000">UPDATE</font></a></td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=del&bugId={$data3[sec].bugId}">
	<font color="#FF0000">DEL</font></a></td></tr>
	{/section}
</table>
{include file="footer.tpl"}
