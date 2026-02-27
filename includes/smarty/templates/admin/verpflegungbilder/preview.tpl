{*Smarty*}

<h1>Verpflegungsbilder</h1>

{if isset($error)}
  <p>{$error}</p>
{else}
  <table width="400" cellspacing="1" cellpadding="3">
    <tr><td class="navbar" width="100"><b>Name:</b></td><td class="hblau">{$image.name}</td></tr>
    <tr><td class="navbar" width="100"><b>URL: </b></td><td class="hblau">{$image.url}</td></tr>
    <tr><td class="navbar" width="100"><b>Größe: </b></td><td class="dblau">{$image.groesse/1024|round:2} kB</td></tr>
    <tr><td class="navbar" width="100"><b>Maße: </b></td><td class="hblau">{$image.info.0} x {$uploadedImage.info.1}</td></tr>
    <tr><td class="navbar" width="100"><b>MIME-Type: </b></td><td class="dblau">{$image.info.mime}</td></tr>
  </table>
  <p><img src="{$image.url}"></p>
{/if}
<p><a href="verpflegungbilder.php">Zurück zum Slideradmin</a></p>