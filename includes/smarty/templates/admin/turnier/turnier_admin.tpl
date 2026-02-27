{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">
<h1>Turnier Admins</h1>

<form method="post" action="{$smarty.server.PHP_SELF}?action={$action}">
<input type="hidden" name="form[partyid]" value="{$partyid}">
<table cellspacing="0" cellpadding="0"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
<tr><td class="navbar" colspan="100">Turnieradmin &lt;=&gt; Turnier Zuordnung</td></tr>
<tr><td class="dblau"></td>
{foreach key=userid item=login from=$allAdmins}
  <td class="dblau">{$login}</td>
{/foreach}
</tr>
{foreach key=turnierid item=turnier from=$turniere}
  {cycle values='hblau,dblau' assign=tdclass}
  <tr><td class="{$tdclass}" width="150">{$turnier.name}</td>
  {foreach key=userid item=login from=$allAdmins}
     <td class="{$tdclass}" align="center"><input type="checkbox" name="form[{$turnierid}][{$userid}]" title="{$login} - {$turnier.name}" value="X" {if isset($arr.$turnierid.$userid)}checked="checked"{/if}></td>
  {/foreach}
  </tr>
{/foreach}
</table></td></tr></table><br>
<input type="submit" value="Speichern">&nbsp;<input type="button" value="Zur&uuml;ck" OnClick="window.history.back();">
</form></body></html>
