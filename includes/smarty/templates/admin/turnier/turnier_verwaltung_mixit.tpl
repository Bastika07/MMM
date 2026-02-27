{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
</head><body bgcolor="#FFFFFF">

<h1>Turnierverwaltung</h1>
{if isset($formerr)}<p class="fehler">{$formerr}</p>{/if}
{if isset($confirm)}<p class="confirm">{$confirm}</p>{/if}
<p>Hier kann ein normales Turnier in ein  Mixed-Turnier konvertiert werden. Es wird das Zielsystem, z.B. 4 Teilnehmer pro Team, angegeben und aus den Teilnehmern werden zuf&auml;llige 4on4 Teams erstellt.</p>
<p>Es wird vorrausgesetzt das die Teilnehmerzahl durch die Teamgr&ouml;&szlig;e teilbar ist! - Dieser Schritt kann nicht r&uuml;ckg&auml;ngig gemacht werden! Das Laden ser Seite nicht abbrechen, sonst k&öuml;nnen alle Teams verloren gehen.</p>
<form method="post" action="{$smarty.server.PHP_SELF}?action={$action}">
{csrf_field}
<input type="hidden" name="turnierid" value="{$turnier->turnierid}">

<table cellspacing="0" cellpadding="0">
<tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">

<tr><td class="navbar" colspan="2"><b>Turnier bearbeiten</b></td></tr>
<tr><td class="dblau">Titel</td><td class="hblau"><input type="text" name="form[name]" size="40" maxlength="64" value="{$turnier->name}" disabled></td></tr>

<tr><td class="dblau">Status:</td><td class="hblau"><select name="form[status]" disabled>
{html_options options=$statusArr selected=$turnier->status}</select>
</td></tr>

<tr><td class="dblau">Teamgr&ouml;sse:</td><td class="hblau"><select name="teamsize">
{html_options options=$teamSizeArr selected=$turnier->teamsize}</select></td></tr>

<tr><td class="dblau" colspan="2" align="center"><input type="submit" name="mixit" value="Mixed Turnier erstellen">
<input type="button" value="Zur&uuml;ck" OnClick="window.history.back();"></td></tr>

</table></td></tr></table>
</form></body></html>
