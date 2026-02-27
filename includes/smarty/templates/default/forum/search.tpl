{* Suchausgabe *}

{if $error}
  {$error}
{else}
  <p>
  <h2>Forum:</h2>
  {if count($data[$smarty.const.BT_FORUM]) == 0}
    keine Ergebnisse
  {else}
    <table class="rahmen_allg" border="0" cellpadding="2" cellspacing="1">
    <tr><td class="TNListe">Titel</td><td class="TNListe">Inhalt</td></tr>
    {foreach from=$data[1] item="result"}
      <tr>
      <td class="dblau"><a href="{$filename}&thread={$result.parent}&page_forum={$result.pageForPost}#post_{$result.contentId}">{$result.title}</a></td>
      <td class="hblau">{$result.content|escape|bbcode2html|nl2br|wrap:50:' '}</td>
      </tr>
    {/foreach}
    </table>
  {/if}
  </p>
  
  <p>
  <h2>News:</h2>
  {if count($data[$smarty.const.BT_NEWS]) == 0}
    keine Ergebnisse
  {else}
    <table class="rahmen_allg" border="0" cellpadding="2" cellspacing="1">
    <tr><td class="TNListe">Titel</td><td class="TNListe">Inhalt</td></tr>
    {foreach from=$data[2] item="result"}
      <tr>
      <td class="dblau"><a href="news.php?action=showComments&newsID={$result.parent}#post_{$result.contentId}">{$result.title}</a></td>
      <td class="hblau">{$result.content|escape|bbcode2html|nl2br|wrap:50:' '}</td>
      </tr>
    {/foreach}
    </table>
  {/if}
  </p>
{/if}
