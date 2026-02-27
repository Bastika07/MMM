{*Smarty*}

<h1>Bilderadmin</h1>

{if isset($error)}
  {$error}
{else}
  <p>L鰏chen von '{$imageName}' erfolgreich!</p>
{/if}
<p><a href="newsbild.php">Zurük zum Bilderadmin</a></p>