{* normierte Darstellung eines Posts / Comments *}

{* versteckt nicht anzeigen, ausser es ist ein Admin *}
{if !$hidden || $admin}
<tr height="180">
  <td width="120" valign="top" {if $hidden}class="forum_hidden"{else}class="hblau"{/if}>
    <a name="post_{$contentID}">
    {if $val.isNew}<a name="new">{/if}	
      <a href="?page=4&nUserID={$authorID}">{$authorName|wrap:20:' ':true|escape}</a>
    </a>
    </a>
    <div class="kleinertext">{$authorClass}</div>
    {if $avatar}
      <img src="{$pelasHost}userbild/{$authorID}.jpg{$smileySuffix}" border="1" vspace="3" hspace="3">
    {/if}
    <div class="badges">
    {foreach from=$passes item="pass"}
      {if $pass.picSmall}
        <img vpsace="5" hspace="5" src="{$pass.picSmall}" title="Supporterpässe: {$pass.count} Stück">
      {/if}
    {/foreach}
    </div>
  <td valign="top" {if $hidden}class="forum_hidden"{else}class="hblau"{/if}>
    <table width="100%">
      <tr>
        <td align="left">{$time|date_format:"%d.%m.%Y %H:%M"} {if $val.isNew}<span style="color:red"><i>(</i>neu<i>)</i></span>{/if}</td>
        <td align="right">
        <a href="{$filename}&action=add&thread={$thread->id}&postToQuoteId={$contentID}" class="forumlink"><img src="/gfx/forum_quote.gif" border="0"></a>
        {if $edit || $isAdmin}
          <a href="{$filename}&action=edit&post={$contentID}"><img src="/gfx/forum_edit.gif" border="0"></a>
        {/if}
        {if $admin}
          {if $hidden}
            <a href="{$filename}&action=changemode&mode=hidden&post={$contentID}">anzeigen</a>
          {else}
            <a href="{$filename}&action=changemode&mode=hidden&post={$contentID}">verstecken</a>
          {/if}
        {/if}
        </td>
      </tr>
    </table>
    <hr>
    {$content|replace:"info@innovalan.de":"info (at) innovalan.de"|escape|smileys:$smileyDir|bbcode2html|nl2br|wrap:50:' '}
    {if $lastEdited}
      <br><br>
      <div class="lastEdited">[ zuletzt editiert: {$lastEdited|date_format:"%d.%m.%Y %H:%M"} ]</div>
    {/if}
    {if $hidden}      
      <p class="error">&lt;Admin&gt;Dieser Post ist von '{$hiddenBy|default:unbekannt}' versteckt.&lt;/Admin&gt;</p>
    {/if}
    </td>
</tr>
{/if}
