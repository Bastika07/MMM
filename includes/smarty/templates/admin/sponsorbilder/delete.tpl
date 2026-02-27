{*Smarty*}

<h1>Sponsorbilder</h1>

{if isset($error)}
  {$error}
{else}
  <p>'{$imageName}' wirklich löschen?</p>
  <p><a href="{$filename}?image={$imageName}&amp;action=delete&amp;confirm=true">Ja</a></p>
{/if}
<p><a href="sponsorbilder.php">Zurück zu Sponsorbilder</a></p>