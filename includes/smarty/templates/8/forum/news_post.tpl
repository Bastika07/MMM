{* Design für Newspost, Bild links, zweireihig *}
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td valign="top" width="198" align="right" height="100%">

	<table cellspacing="0" cellpadding="0" border="0" width="100%" height="100%">
	<tr>
	  <td><img src="/gfx_struct2/tbl_content_lo_2.gif" width="2" height="2"></td>
	  <td bgcolor="#DCDCDC"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td><img src="/gfx_struct2/tbl_content_ro_2.gif" width="2" height="2"></td>
	</tr><tr>
	  <td bgcolor="#DCDCDC"><img src="/gfx/lgif.gif" height="17" width="1"></td>
	  <td bgcolor="#DCDCDC" valign="top">&nbsp;
	  
	  </td>
	  <td bgcolor="#DCDCDC"><img src="/gfx/lgif.gif" height="1" width="1"></td>
	</tr><tr>
	  <td bgcolor="#E7E7E7"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td bgcolor="#E7E7E7" width="100%" height="100%" valign="top"	>
	    <table cellspacing="0" cellpadding="4" border="0" width="100%">
		<tr><td align="center" valign="top">

		<img width="179" height="179" src="{$helperstring}" border="0">
		
		</td></tr>
		<tr><td align="right">
		
		{if $showLink}
		  <font class="latest_link">
		  {if $posts-1 == 0}
		    no comments &nbsp;
		    <a href="{$filename}&action=addComment&newsID={$contentID}">add &gt;&gt;</a>
		  {else}
		    {$posts-1} comments &nbsp;
		    <a href="{$filename}&action=showComments&newsID={$contentID}">show &gt;&gt;</a>  
		  {/if}
		  </font>
		{/if}
		
	    </td></tr>
	    </table>
	  </td>
	  <td bgcolor="#E7E7E7"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	</tr><tr>
	  <td><img src="/gfx_struct2/tbl_content_lu_2.gif" width="2" height="2"></td>
	  <td background="/gfx_struct2/tbl_content_bg_bottom_2.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td><img src="/gfx_struct2/tbl_content_ru_2.gif" width="2" height="2"></td>
	</tr>
	</table>
	
	
</td><td><img src="/gfx/lgif.gif" width="4" height="1"></td>
<td valign="top" height="100%">

	<table cellspacing="0" cellpadding="0" border="0" width="399" height="100%">
	<tr>
	  <td width="2"><img src="/gfx_struct2/tbl_content_lo.gif" width="2" height="2"></td>
	  <td width="198" background="/gfx_struct2/tbl_content_bg_top.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td width="197" background="/gfx_struct2/tbl_content_bg_top.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td width="2"><img src="/gfx_struct2/tbl_content_ro.gif" width="2" height="2"></td>
	</tr><tr>
	  <td bgcolor="#E3E3E3"><img src="/gfx/lgif.gif" height="17" width="1"></td>
	  <td bgcolor="#E3E3E3" valign="top">
	  <font class="pelas_newstitle"">&nbsp; {$title}</font>
	  </td><td bgcolor="#E3E3E3">
	  <font class="newshead_date">{$time|date_format:"%A, %d.%m.%Y %H:%M"} by</font> <font class="colortext"> &gt; {$authorName|escape}</font>
	  </td>
	  <td bgcolor="#E3E3E3"><img src="/gfx/lgif.gif" height="1" width="1"></td>
	</tr><tr>
	  <td width="2" background="/gfx_struct2/tbl_content_bg_left.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td width="395" colspan="2" bgcolor="#EFEFEF" height="100%" valign="top">
	    <table cellspacing="0" cellpadding="4" border="0">
		<tr><td width="50%" valign="top">

	        <p align="justify" class="newsbody">
		   {$content|smileys:$smileyDir|bbcode2html|nl2br|replace:"[spalte]":"</p></td><td width=50% valign=top><p align=justify class=newsbody>"}
		</p>
                
	      </td></tr>
	    </table>
	    
	  </td>
	  <td width="2" background="/gfx_struct2/tbl_content_bg_right.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	</tr><tr>
	  <td><img src="/gfx_struct2/tbl_content_lu.gif" width="2" height="2"></td>
	  <td background="/gfx_struct2/tbl_content_bg_bottom.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td background="/gfx_struct2/tbl_content_bg_bottom.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td><img src="/gfx_struct2/tbl_content_ru.gif" width="2" height="2"></td>
	</tr>
	</table>


</td></tr>

<tr>
  <td colspan="3"><img src="/gfx/lgif.gif" width="0" height="5"></td>
</tr>

</table>