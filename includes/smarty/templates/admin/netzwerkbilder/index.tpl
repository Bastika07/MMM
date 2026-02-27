{*Smarty*}

<h1>Netzwerkbilder</h1>

{foreach key="mandantId" item="mandant" from=$mandanten}
  <table cellspacing="0" cellpadding="0" width="800">
  <tr><td class="navbar">
  <table width="100%" cellspacing="1" cellpadding="3">
  <tr>
    <td class="navbar" colspan="4"><b>{$mandant.beschreibung}</b></td>
  </tr>
  {foreach item="image" from=$mandant.images name="images"}
    {cycle values="hblau,dblau" assign="class"}
    <tr>
      <td class="{$class}">{$image.name}</td>
      <td class="{$class}">{$image.groesse/1024|round:2} kB</td>
      <td class="{$class}">{$image.info.0} x {$image.info.1}</td>
      <td class="{$class}">{$image.info.mime}</td>
      <td class="{$class}">
        <a href="{$filename}?action=preview&amp;image={$image.name}">vorschau</a>
        <a href="{$filename}?action=delete&amp;image={$image.name}">löschen</a>
      </td></tr>
  {/foreach}
  {if $smarty.foreach.images.total == 0}
    <tr><td class="hblau">keine Bilder</td></tr>
  {/if}
  </table>
  </td></tr></table>
{/foreach}

<p>Bild hochladen</p>

<form action="{$filename}" method="post" ENCTYPE="multipart/form-data">
{csrf_field}
<input type="hidden" name="action" value="upload">
<table cellspacing="0" cellpadding="0" width="800">
  <tr><td class="navbar">
  <table width="100%" cellspacing="1" cellpadding="3">
  <tr>
    <td class="navbar"><b>Mandant</b></td>
    <td class="navbar"><b>Bild</b></td>
    <td class="navbar"></td>
  </tr>
  <tr>
    <td class="hblau">
      <select name="mandantId">
        {foreach key="mandantId" item="mandant" from=$mandanten}
          <option value="{$mandantId}">{$mandant.beschreibung}</option>
        {/foreach}
      </select>
    </td>
    <td class="hblau"><input type="file" name="uploadImage"></td>
    <td class="hblau"><input type="submit" value="hochladen"></td>
  </tr>
  </table>
</td></tr></table>
</form>

  
