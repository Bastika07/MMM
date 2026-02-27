{* Smarty *}

<table cellspacing="0" cellpadding="0" border="0" width="100%">
  <tr><td class="forum_titel">
    <table width="100%" cellspacing="1" cellpadding="3" border="0">
      <tr>
        <td class="forum_titel" align="center" width="65%"><b>meine Turniere</b></td>
        <td class="forum_titel" align="center" width="35%"><b>meine Mails</b></td>
      </tr>
      <tr>
{if !$isLoggedIn} 
  <td class="dblau" colspan="2" align="center">bitte <a href="login.htm">einloggen</a></td>
{else}
  <td class="hblau" valign="top">
    <table>      
      <tr><td colspan="3">Deine Coins: {$coins} / {$maxCoins}</td></tr>
      {if count($turniere) == 0}
        <tr><td colspan="3">Du hast Dich bei noch keinem Turnier angemeldet.</td></tr>
      {else}      
        
        {foreach from=$turniere item="turnier" key="turnierid"}
          <tr>
            <td>{if !empty($turnier.icon)}<img src="{$turnier.icon}">{/if}</td>
            <td><a href="turnier/?page=21&turnierid={$turnierid}">{$turnier.name}</a></td>
            <td>{$turnier.status}</td>
          </tr>
        {/foreach}
      {/if}
    </table>
  </td>
  <td class="dblau" valign="top">
    <table width="100%"><tr><td>
      {php}
        $nLoginID = $smarty->get_template_vars['userId'];
        $callFromNews = TRUE;
        include "/var/www/pelas/pelas_mainframe.php";
      {/php}
      <a href="javascript:newMail();"><img border="0" src="pelas/newmail.gif"> neue Mail schreiben</a>
    </td></tr></table>
  </td>
{/if}
      </tr>
    </table>
  </tr>
</table>
<br><br>
