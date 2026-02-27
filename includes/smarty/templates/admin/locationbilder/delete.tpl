{*Smarty*}

<h1>Lokationbilder</h1>

{if isset($error)}
  {$error}
{else}
  <p>'{$imageName}' wirklich löschen?</p>
  <p><a href="{$filename}?image={$imageName}&amp;action=delete&amp;confirm=true">Ja</a></p>
{/if}
<p><a href="locationbilder.php">Zurück zu Locationbilder</a></p>