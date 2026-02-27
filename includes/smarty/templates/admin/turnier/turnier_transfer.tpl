{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">


</head><body bgcolor="#FFFFFF">

<h1>Teams in den Hauptturnierbaum transferrieren</h1>

{if $notComplete}
  <font color="#FF0000">Es sind noch nicht alle Vorrunden ausgespeilt!</font>
  <br><br>
  <a href="turnier/turnier_verwaltung_list.php?partyid={$turnier->partyid}">Zurück zur Turnierverwaltung</a>
{/if}
{* hier könnten noch mehr Funktionen implementiert werden *}
</body></html>
