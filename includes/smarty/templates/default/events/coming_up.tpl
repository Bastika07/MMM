{* Smarty *}

<table cellspacing="0" cellpadding="0" border="0" width="100%">
  <tr><td class="forum_titel">
    <table width="100%" cellspacing="1" cellpadding="3" border="0">
      <tr>
        <td class="forum_titel" align="center" width="65%"><b>Events: Coming up</b></td>        
      </tr>
      <tr>
        <td class="hblau" valign="top">
          <table width="50%" align="center">
            {foreach from=$events item="event"}
              <tr><td>{$event.start|date_format:'%A, %H:%M'}</td><td><a href="events.htm?event={$event.id}">{$event.name}</a></td></tr>
            {/foreach}
          </table>
        </td>
      </tr>
    </table>
  </tr>
</table>