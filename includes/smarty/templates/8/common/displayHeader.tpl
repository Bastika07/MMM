{* Smarty *}
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
	  <td><img src="/gfx_struct2/tbl_content_lo.gif" width="2" height="2"></td>
	  <td background="/gfx_struct2/tbl_content_bg_top.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td><img src="/gfx_struct2/tbl_content_ro.gif" width="2" height="2"></td>
	</tr><tr>
	  <td bgcolor="#E3E3E3"><img src="/gfx/lgif.gif" height="17" width="1"></td>
	  <td bgcolor="#E3E3E3" valign="top" class="box_title">
	  &nbsp; {$title}
	  </td>
	  <td bgcolor="#E3E3E3"><img src="/gfx/lgif.gif" height="1" width="1"></td>
	</tr><tr>
	  <td background="/gfx_struct2/tbl_content_bg_left.gif"><img src="/gfx/lgif.gif" height="2" width="2"></td>
	  <td bgcolor="#EFEFEF" width="100%" height="100%">
	    <table cellspacing="0" cellpadding="6" border="0" width="100%">
		<tr><td>
		{if !isset($noDisclaimer) || isset($noDisclaimer) && !$noDisclaimer}<p>Willkommen im Forum. Bitte beachtet beim Posten unsere <a href="/bedingungen.htm">AGB</a>.</p>{/if}