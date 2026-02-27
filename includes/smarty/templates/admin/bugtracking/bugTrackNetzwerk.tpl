{*smarty*}
{assign var=title value="BTracking::Netzwerk"}
{*include file="header.tpl"*}
<h1>&nbsp;[ BugTracking::Eingabemaske ]</h1>
{include file='bugTrack_header.tpl'}
<table border="0" cellpadding="0" cellspacing="0">
   <tr>
   	<td><table border="0" cellpadding="3" cellspacing="2">
	<form action="{$smarty.server.PHP_SELF}?do=addBlock" method="post">
	{csrf_field}
	<table border="0" cellpadding="0" cellspacing="0">
	   <tr>
	   	<td class="navbar" align="center"><font size="4">IP-Liste<font></td>
		<td class="navbar"></td></tr>
	   <tr>
	   	<td rowspan=5><textarea name="import" cols="15" rows="20"></textarea>
		</td>
		
	   </tr><tr>
		<td valign='top'><strong>Priorit�t:</strong><br>
		<select name=prio>
		{section name=sec1 loop=$prio}
		<option value="{$prio[sec1].id}">{$prio[sec1].name}</option>
		{/section}
		</select><br>
		<strong>Bug-Klasse:</strong><br>
		<select name='bugKlasse'>
		{section name=sec2 loop=$bugs}
		<option value="{$bugs[sec2].id}">{$bugs[sec2].name}</option>
		{/section}
		</select>
	  	</td>
	   </tr><tr>
	   	<td width="40" align='center' colspan=2><input type="submit" value="Add"></td>
           </tr>
	</form>
	</table></td>
   </tr>
</table>
{include file="footer.tpl"}
