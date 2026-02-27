{* Fehlerausgabe *}

{if $filename == '/news.htm'}
  <img src="/style/content_bg_top.png" width="800" height="18"><br>
  <table cellspacing="0" cellpadding="0" border="0" width="800">
  <tr>
  <td style="background: url('/style/content_bg.png') repeat-y; padding:1px 40px 10px 40px;">
  <h1>News kommentieren</h1>
{/if}
<div style="color: #FF0000;">{$emsg}</div>
<br>
<a href="javascript: history.back();">Zurück</a>

 <br><br>

{if $filename == '/news.htm'}
 </td>
 </tr>
  <tr><td height="1"><img src="/style/content_bg_bottom.png" width="800" height="1"></td></tr>
 </table>
{/if}