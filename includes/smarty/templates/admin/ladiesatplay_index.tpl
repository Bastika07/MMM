{include file='_vorspann.tpl'}

<h1>Ladies at Play</h1>

<p><a href="{$filename}&action=create">neu anlegen</a></p>

<table cellspacing="1" class="outer">
<tr>
  <th>Name</th>
  <th colspan="4">Aktionen</th>
</tr>
{foreach from=$ladies item='lady'}
  <tr class="row-{cycle values='0,1'}">
    <td>{$lady.login|escape}</td>
    <td><a href="?action=edit&userId={$lady.userId}">edit</a></td>
    <td><a href="?action=delete&userId={$lady.userId}">delete</a></td>
    <td>
    	{if $lady.imageExists}
    		schon ein Bild hochgeladen
    	{/if}
    	<form action="{$filename}" method="post" ENCTYPE="multipart/form-data">
	<input type="hidden" name="action" value="upload">
	<input type="hidden" name="userId" value="{$lady.userId}">
	<input type="file" name="uploadImage"><input type="submit" value="hochladen">
	</form>
    </td>
    {if $lady.hidden}
    	<td><a href="?action=show&userId={$lady.userId}">anzeigen</a></td>
    {else}
    	<td><a href="?action=hide&userId={$lady.userId}">verstecken</a></td>
    {/if}
  </tr>
{/foreach}
</table>

{include file='_nachspann.tpl'}
