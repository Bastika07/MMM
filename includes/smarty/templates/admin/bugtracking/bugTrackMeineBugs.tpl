{*smarty*}
{debug}
{assign var=title value="BTracking:: Bugs von " $adminLoginId}
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
    {section name=sec loop=$data}
    {cycle values="hblau,dblau" assign=tdclass}
	<tr>
	<td class="{$tdclass}"><font color="red">{$data[sec].bugId}</font></td>
	<td class="{$tdclass}">{$data[sec].prio}</td>
	<td class="{$tdclass}">{$data[sec].ip}</td>
	<td class="{$tdclass}">{$data[sec].type}</td>
	<td class="{$tdclass}">{$data[sec].login}</td>
	<td class="{$tdclass}">{$data[sec].reihe}/{$data1[sec].platz}</td>
	<td class="{$tdclass}">{$data[sec].zeit}</td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=modify&bugId={$data[sec].bugId}">
	<font color="#FF0000">UPDATE</font></a></td>
	<td class="{$tdclass}" align="center">
	<a href="{$smarty.server.PHP_SELF}?do=del&bugId={$data[sec].bugId}">
	<font color="#FF0000">DEL</font></a></td></tr>
   {/section}
</table>
{include file="footer.tpl"}
