{* Smarty *}

{if $error}
  <p><a href={$filename}>Forum</a></p>
  <p>{$error}</p>  
{else}
  <p><a href={$filename}>Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name}</a></p>
  <p>
  Alle Threads im Board '{$board->name}' erfolgreich als gelesen markiert.
  <br>
  <br>
  <a href="{$filename}&board={$board->id}"><img src="/gfx/headline_pfeil.gif" border="0">Zurück zum Board</img></a>
  </p>
  
{/if}
