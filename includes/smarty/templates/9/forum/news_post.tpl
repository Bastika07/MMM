{* Design für Newspost, Bild links, einreihig *}
<table width="100%" cellspacing="0" cellpadding="0" border="0">


{if $lang == 'en' && $content_en != ""}
  {assign var=title value=$title_en}
  {assign var=content value=$content_en}
{/if}

<tr height="25">
  <td width="10" bgcolor="#38464f"><img src="/gfx/lgif.gif" width="10" height="1" border="0"></td>
  <td width="1" bgcolor="#0f233c"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>
  <td width="702" colspan="2" style="background-color: #38464f; color: #e6e6e6; font-weight: bold;">

    <table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>      
      <td width="500" style="background-color: #38464f; color: #e6e6e6; font-weight: bold;">&nbsp;&nbsp; News: {$title}</td>
      <td width="202" align="right" style="background-color: #38464f; color: #e6e6e6;">{$time|date_format:"%d.%m.%Y %H:%M"} {$authorName|escape} &nbsp;&nbsp; </td>
    </tr>
    </table>

  </td>
  <td width="50" style="background-color: #38464f; color: #e6e6e6;">&nbsp;</td>
</tr>
<tr>
  <td width="10" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="10" height="1" border="0"></td>
  <td width="1" bgcolor="#0f233c"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>
  <td width="200" valign="top" align="center" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="1" height="1" border="0">
    <img src="/gfx/lgif.gif" width="0" height="15" border="0"><br>
    
    
    <table cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td><img width="155" height="132" src="{$helperstring}" border="0"></td>
    </tr>
    <tr>
      <td align="center" background="/gfx_struct/newsbild_background.jpg">    
	  <br><br>  
	  {if $posts-1 == 0}
            <center>
	    <a class="newsbildlink" href="{$filename}&action=addComment&newsID={$contentID}">{$str.abgeben}</a>
	    </center>
	  {else}
	    <center>
	    <a class="newsbildlink" href="{$filename}&action=showComments&newsID={$contentID}">{$str.kommentare} ({$posts-1})</a>
	    </center>

	  {/if}

        <br>
      </td>
    </tr>
    </table>

  <img src="/gfx/lgif.gif" width="0" height="16" border="0">
  
  </td>
  <td width="502" valign="top" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="1" height="1" border="0">
    
    <table cellspacing="0" cellpadding="10" border="0">
    <tr>
      <td><p align="justify">{$content|smileys:$smileyDir|bbcode2html|nl2br}</p></td>
    </tr><tr>
      <td height="1"><img src="/gfx/lgif.gif" width="0" height="1" border="0"></td>
    </tr>
    </table>    
  </td>
  <td width="50" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>  
</tr>
</table>
