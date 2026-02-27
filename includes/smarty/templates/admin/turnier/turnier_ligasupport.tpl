{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">
<h1>Turnier Liga Support</h1>

<form method="post" name="save" action="{$smarty.server.PHP_SELF}?action=add">
{csrf_field}
<table cellspacing="0" cellpadding="0" width="600"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
<tr>
  <td class="navbar"></td>
  <td class="navbar" align="center"><b>Liga</b></td>
  <td class="navbar" align="center"><b>Export-Tag</b></td>
  <td class="navbar" align="center"><b>Teamsize</b></td>
  <td class="navbar" align="center"><b>Name</b></td>
  <td class="navbar"></td>
</tr>
{foreach key=num item=game from=$list}
  {cycle values='hblau,dblau' assign=tdclass}
  <tr>
  <td class="{$tdclass}"></td>
  <td class="{$tdclass}" align="center">{$game.liganame|escape}</td>
  <td class="{$tdclass}" align="right">{$game.shortname|escape}</td>
  <td class="{$tdclass}" align="center">{$game.teamsize}</td>
  <td class="{$tdclass}">{$game.name|escape}</td>
  <td class="{$tdclass}"></td>
  </tr>
{/foreach}
<tr>
  <td class="dblau"></td>
  <td class="dblau" align="center">
    <select name="liga">{html_options options=$ligaArr}</select>
  </td>
  <td class="dblau" align="center">
    <input type="text" maxlength="12" size="12" name="shortname"">
  </td>
  <td class="dblau" align="center">
    <select name="teamsize">{html_options options=$teamSizeArr}</select>
  </td>
  <td class="dblau" align="center">
    <input type="text" maxlength="16" size="30" name="name">
  </td>
  <td class="dblau" align="center"><input type="submit" value="Add"></td>
</tr>
</table></td></tr></table></form><br>
</body></html>
