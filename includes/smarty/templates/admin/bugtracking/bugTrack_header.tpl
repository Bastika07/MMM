{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="/format.css">
<title>{$title}</title>
</head><body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" padding="10">
<table>
   <tr>
   	<td>&nbsp;
	<a href="{$smarty.server.PHP_SELF}?show=index">Uebersicht</a>
	</td><td>&nbsp
	<a href="{$smarty.server.PHP_SELF}?show=tresen"> Eingabe einzelne IP</a>
	</td><td>&nbsp
	<a href="{$smarty.server.PHP_SELF}?show=technik"> Eingabe IP-Block</a>
	</td><td>&nbsp
	<a href="{$smarty.server.PHP_SELF}?show=meineBugs"> Meine Bugs</a>
	</td>
   </tr>
</table><hr>
<table width=90% ><td>&nbsp;</td><td>
