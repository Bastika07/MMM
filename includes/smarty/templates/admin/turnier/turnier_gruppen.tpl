{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">
<h1>Turniergruppen</h1>

<form method="post" name="save" action="{$smarty.server.PHP_SELF}?action=add">
<table cellspacing="0" cellpadding="0" width="400"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
<tr>
  <td class="navbar"></td>
  <td class="navbar">Gruppenname</td>
  <td class="navbar"></td>
  <td class="navbar"></td>
</tr>
{foreach key=groupid item=group from=$list}
  {cycle values='hblau,dblau' assign=tdclass}
  <tr>
  <td class="{$tdclass}"></td>
  <td class="{$tdclass}">{$group.name|escape}</td>
  <td class="{$tdclass}" align="center">
    {if $group.next}
      <a href="turnier/{$smarty.server.PHP_SELF}?action=move&groupid={$groupid}&to={$group.next}">up</a>
    {/if}
    {if $group.prev}
      <a href="turnier/{$smarty.server.PHP_SELF}?action=move&groupid={$groupid}&to={$group.prev}">down</a>
    {/if}
  </td>
  <td class="{$tdclass}" align="center">
  {if $group.flags & $smarty.const.GROUP_SHOW}
    <a href="turnier/{$smarty.server.PHP_SELF}?action=hide&groupid={$groupid}">hide</a>
  {else}
    <a href="turnier/{$smarty.server.PHP_SELF}?action=hide&groupid={$groupid}">show</a>
  {/if}
  </td>
  </tr>
{foreachelse}
  <tr><td class="hblau" colspan="3">Keine Gruppen vorhanden</td></tr>
{/foreach}
<tr>
  <td class="dblau"></td>
  <td class="dblau"><input type="text" maxlength="64" size="30" name="name"></td>
  <td class="dblau"></td>
  <td class="dblau" align="center"><input type="submit" value="Add"></td>
</tr>
</table></td></tr></table></form><br>
</body></html>
