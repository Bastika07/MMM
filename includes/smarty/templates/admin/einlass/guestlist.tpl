{* Smarty *}

<h1>Einlasskontrolle - Begleiterliste</h1>

<table><tr><th>Name</th><th>Postleitzahl</th><th>Adresse</th><th>Zeitpunkt</th></tr>
{foreach from=$guests item="guest"}
  <tr>
  <td>{$guest.name}</td>  
  <td>{$guest.plz}</td>  
  <td>{$guest.address}</td>  
  <td>{$guest.checkedin_at|date_format:'%A, %H:%M:%S'}</td>
  </tr>
{/foreach}
</table>

