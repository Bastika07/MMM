{* Smarty *}
{literal}
<script language="JavaScript">
<!--
function change(obj, newpic) {
  obj.src = newpic;
}  
// -->
</script>
{/literal}


<table border=0 align="center" cellspacing="10" cellpadding="0">
<tr>
<td><a href="{$filename}" class="forumlink"><img src="forumicons/news_uebersicht{if $currentBoard == 0}2{else}1{/if}.gif" border=0 onMouseOver="javascript: change(this, 'forumicons/news_uebersicht{if $currentBoard == 0}1{else}2{/if}.gif'); return true;" onMouseOut="javascript: change(this, 'forumicons/news_uebersicht{if $currentBoard == 0}2{else}1{/if}.gif'); return true;"></a>

{foreach key=key item=val from=$boards}  
  <td><a href="{$filename}&newsBoard={$key}" class="forumlink"><img src="forumicons/news_{$val|lower}{if $currentBoard == $key}2{else}1{/if}.gif" border=0 name="img1" onMouseOver="javascript: change(this, 'forumicons/news_{$val|lower}{if $currentBoard == $key}1{else}2{/if}.gif'); return true;" onMouseOut="javascript: change(this, 'forumicons/news_{$val|lower}{if $currentBoard == $key}2{else}1{/if}.gif'); return true;"></a>
{/foreach}
</tr>
</table>