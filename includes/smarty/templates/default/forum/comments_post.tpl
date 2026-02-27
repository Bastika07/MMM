{* normierte Darstellung eines Posts / Comments *}

{* versteckt nicht anzeigen, ausser es ist ein Admin *}
{if !$hidden || $admin}
<tr height="180">
  <td width="120" valign="top" class="dblau" {if $hidden}style="background-color: #a0a0a0"{/if}>
    <a href="?page=4&nUserID={$authorID}">{$authorName|escape}</a>
    <div class="kleinertext">{$authorClass} TEST</div>
    {if $avatar}
      <img src="{$pelasHost}userbild/{$authorID}.jpg{$smileySuffix}" border="1" vspace="3" hspace="3">
    {/if}
  <td valign="top" class="hblau" {if $hidden}style="background-color: #a0a0a0"{/if}>
    <table width="100%">
      <tr>
        <td align="left">{$time|date_format:"%d.%m.%Y %H:%M"}</td>
        <td align="right">
        <a href="{$filename}&action=addComment&threadid={$thread->id}&postToQuoteId={$contentID}" class="forumlink"><img src="/gfx/forum_quote.gif" border="0"></a>
        {if $edit}
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
    {$content|escape|smileys:$smileyDir|bbcode2html|nl2br|wrap:50:' '}
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