{* Smarty *}

{* dirty hack damit der Forums-pfad nicht beim Posten von News-Comments angezeigt wird*}
{if $filename != '/news.htm'}
  <p>
  <a href="{$filename}">Forum</a> -> <a href="{$filename}&board={$board->id}">{$board->name}</a>
  {if $showThreadName}
    -> <a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>
  {/if}
  </p>
{/if}

{if $filename == '/news.htm'}
  <h1>News kommentieren</h1>
<img src="/style/content_bg_top.png" width="800" height="18"><br>

<table cellspacing="0" cellpadding="0" border="0" width="800">
<tr>
<td style="background: url('/style/content_bg.png') repeat-y; padding:1px 40px 10px 40px;">
{/if}

<form method="post" action="{$filename}" name="data">
{csrf_field}
<input type="hidden" name="action" value="submit">
{if isset($data)}
  {foreach key=key item=val from=$data}
    <input type="hidden" name="{$key}" value="{$val}">
  {/foreach}
{/if}
{if $mode == 'newThread'}
  <input type="hidden" name="board" value="{$board->id}">  
{elseif $mode == 'newPost'}
  <input type="hidden" name="thread" value="{$thread->id}">
{elseif $mode == 'editPost'}
  <input type="hidden" name="post" value="{$post->id}">
{/if}

			
<table class="rahmen_allg" cellspacing="1" cellpadding="3" border="0">
  <tr>
    <td class="forum_titel">&nbsp;</td>
    {if $mode == 'newPost' || $mode == 'editPost'}
      {* Post wurde angegeben, es wird editiert, oder es wird ein neuer Post f�r einen bestehen $thread angelegt *}
      <td class="forum_titel"> {$thread->title|escape}</td>
    {else}
      <td class="forum_titel"> Neues Thema</td>
    {/if}
  </tr>

{if $title_field}
  <tr>
	  <td class="forum_bg1">Titel:</td>
	  <td class="forum_bg2"><input type="text" name="title" size="40" maxlength="100" value="{$thread->title|default:""}"></td>
	</tr>
{else}
  <input type="hidden" name="title" value="inline">
{/if}	
	<tr>
	  <td valign="top" class="forum_bg1">Beitrag:</td>
	  <td class="forum_bg2">
	    {if $mode == 'editPost'}
	      {* edit *}
	      {assign var="contentToDisplay" value=$post->content}
	    {elseif isset($postToQuote)}
	      {* quote *}
	      {assign var="contentToDisplay" value="[quote=\"`$postToQuote->authorName`\"]`$postToQuote->content`[/quote]"}	      	      
	    {/if}	    
	    <textarea name="content" wrap="virtual" cols="55" rows="10">{$contentToDisplay|default:""}</textarea>
	  </td>
	</tr>

	<tr>
	  {if $mode == 'editPost'}
	    {* Post wurde angegeben, es wird editiert *}
	    <td colspan="2" height="40" class="forum_bg1"><input type="submit" value="&auml;ndern"></td>
	  {else}
	    <td colspan="2" height="40" class="forum_bg1"><input type="submit" value="erstellen"></td>
	  {/if}
	</tr>
	<tr>
	  <td class="forum_titel" colspan="2">Smilies</td>
	</tr>
	<tr>
	  <td colspan="2" class="forum_bg1" width="500">
	  {foreach key=key item=val from=$smileys}
      {* Smileys *}
      <img src="{$smileyDir}{$val}" border="0" onclick="javascript:addSmiley('{$key}');">&nbsp;
    {/foreach}
	  </td>
	</tr>
	<tr>
	  <td class="forum_titel" colspan="2">bbcode</td>
	</tr>
	<tr>
	  <td colspan="2" class="forum_bg1" width="500">
	    <table>
	      <tr>
	        <td>Zitat: </td>
	        <td>[quote]Text[/quote]</td>
	      </tr>
	      <tr>
	        <td></td>
	        <td>[quote="Nickname"]Text[/quote]</td>
	      </tr>
	      <tr>
	        <td>Url: </td>
	        <td>[url]http://www.example.com[/url]</td>
	      </tr>
	      <tr>
	        <td></td>
	        <td>[url=http://www.example.com]URL Text[/url]</td>
	      </tr>
	      <tr>
	        <td>Bild: </td>
	        <td>[img]http://www.example.com/image.jpg[/img]</td>
	      </tr>
	      <tr>
	        <td>Text: </td>
	        <td>[b]Text[/b]</td>
	      </tr>
	      <tr>
	        <td></td>
	        <td>[i]Text[/i]</td>
	      </tr>
	      <tr>
	        <td></td>
	        <td>[u]Text[/u]</td>
	      </tr>
	      <tr>
	        <td>Code: </td>
	        <td>[code]Text[/code]</td>
	      </tr>
	      <tr>
	        <td>Liste: </td>
	        <td>[list]Text[/list]</td>
	      </tr>
	    </table>
	  </td>
	</tr>
</table>
</form>

{literal}
  <script language="JavaScript">
    <!--
    function addSmiley(smiley) {
      document.data.content.value=document.data.content.value+smiley;
      document.data.content.focus();
    }
    //-->
  </script>
{/literal}

{if $filename == '/news.htm'}
 </td>
 </tr>
  <tr><td height="1"><img src="/style/content_bg_bottom.png" width="800" height="1"></td></tr>
 </table>
{/if}