{* Fehlerausgabe *}
{if $filename == '/news.htm'}
  {php}
    // Contentborder includen
    startContent("News kommentieren");
  {/php}
{/if}
<div style="color: #FF0000;">{$emsg}</div>
<br>
<a href="javascript: history.back();">Zurück</a>
{if $filename == '/news.htm'}
  {php}
    // Contentborder includen
    endContent();
  {/php}
{/if}