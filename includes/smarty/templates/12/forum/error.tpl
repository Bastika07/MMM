{* Fehlerausgabe *}
{if $filename == '/news.htm'}
  <h1>News kommentieren</h1>
{/if}
<div style="color: #FF0000;">{$emsg}</div>
<br>
<a href="javascript: history.back();">Zurück</a>