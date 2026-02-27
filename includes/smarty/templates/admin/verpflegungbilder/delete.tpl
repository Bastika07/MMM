{*Smarty*}

<h1>Verpflegungsbilder</h1>

{if isset($error)}
  {$error}
{else}
  <p>'{$imageName}' wirklich löschen?</p>
  <p><a href="{$filename}?image={$imageName}&amp;action=delete&amp;confirm=true">Ja</a></p>
{/if}
<p><a href="verpflegungbilder.php">Zurück zum Slideradmin</a></p>