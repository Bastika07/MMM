{*smarty*}
{*debug*}
{assign var=title value="BTracking::Netzwerk"}
{*include file="header.tpl"*}
<h1>[ BugTracking::Status ]</h1>
{include file='bugTrack_header.tpl'}
<table>
    {section name=sec loop=$perror}
    {cycle values="hblau,dblau" assign=tdclass}
	<tr>
	<td class="{$tdclass}">{$perror[sec]}</td>
	</tr>
   {/section}
</table>
{include file="footer.tpl"}
