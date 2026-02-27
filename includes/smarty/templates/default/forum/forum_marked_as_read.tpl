{* Smarty *}

{if $error}
  <p><a href={$filename}>Forum</a></p>
  <p>{$error}</p>  
{else}
  <p><a href={$filename}>Forum</a></p>
  <p>
  Alle Threads im Forum erfolgreich als gelesen markiert.
  <br>
  <br>
  <a href="{$filename}"><img src="/gfx/headline_pfeil.gif" border="0"> Zurück zum Forum</a>
  </p>
  
{/if}
