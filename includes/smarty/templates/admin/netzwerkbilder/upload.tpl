{*Smarty*}

<h1>Netzwerkbilder</h1>

{if isset($error)}
  <p>{$error}</p>
{else}
  <p>Upload des Bildes erfolgreich!</p>
  <table width="400" cellspacing="1" cellpadding="3">
    <tr><td class="navbar" width="100"><b>Name:</b></td><td class="hblau">{$uploadedImage.name}</td></tr>
    <tr><td class="navbar" width="100"><b>URL: </b></td><td class="hblau">{$uploadedImageUrl}</td></tr>
    <tr><td class="navbar" width="100"><b>Größe: </b></td><td class="dblau">{$uploadedImage.groesse/1024|round:2} kB</td></tr>
    <tr><td class="navbar" width="100"><b>Maße: </b></td><td class="hblau">{$uploadedImage.info.0} x {$uploadedImage.info.1}</td></tr>
    <tr><td class="navbar" width="100"><b>MIME-Type: </b></td><td class="dblau">{$uploadedImage.info.mime}</td></tr>
  </table>
  <p><img src="{$uploadedImageUrl}"></p>
{/if}
<p><a href="netzwerkbilder.php">Zurück zu Netzwerkbilder</a></p>
