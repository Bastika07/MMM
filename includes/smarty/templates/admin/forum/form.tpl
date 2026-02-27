{* Smarty *}

<form method="post" action="{$filename}" name="data" enctype="multipart/form-data">
{csrf_field}
<input type="hidden" name="action" value="submit">
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="100000">
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
	  <td class="forum_bg1">Titel:</td>
	  <td class="forum_bg2"><input type="text" name="title" size="40" maxlength="100" value="{$thread->title|default:""}"></td>
	</tr>
	
	<tr>
	  <td class="forum_bg1">Titel (en):</td>
	  <td class="forum_bg2"><input type="text" name="title_en" size="40" maxlength="100" value="{$thread->title_en|default:""}"></td>
	</tr>

  <tr>
    <td valign="top" class="forum_bg1">BildUrl:</td>
    <td class="forum_bg2"><input type="text" name="helperstring" value="{$post->helperstring}"> nur Beben</td>
  </tr>
  <tr>
        <td valign="top" class="forum_bg1">Geplant?</td>  
        <td class="forum_bg2"><input type="checkbox" name="planned" value="1" {if $post->planned}checked{/if}/></td></tr>
		<tr><td valign="top" class="forum_bg1">Geplant wann?</td>  
        <td class="forum_bg2"><input type="text" name="timeplanned" value="{$post->timeplanned|date_format:"%d.%m.%Y %H:%M"}"/></td>
  </tr>
	<tr>
	  <td valign="top" class="forum_bg1">Text:</td>
	  <td class="forum_bg2">
	    {if $mode == 'editPost'}
	      {* edit *}
	      {assign var="contentToDisplay" value=$post->content}
	    {elseif isset($postToQuote)}
	      {* quote *}
	      {assign var="contentToDisplay" value="[quote=\"`$postToQuote->authorName`\"]`$postToQuote->content`[/quote]"}	      	      
	    {/if}	    
	    <textarea name="content" wrap="virtual" cols="55" rows="10">{$contentToDisplay|default:""|escape}</textarea>
	  </td>
	</tr>
	<tr>
	  <td valign="top" class="forum_bg1">Text (en):</td>
	  <td class="forum_bg2">
	    {if $mode == 'editPost'}
	      {* edit *}
	      {assign var="contentToDisplay_en" value=$post->content_en}
	    {elseif isset($postToQuote)}
	      {* quote *}
	      {assign var="contentToDisplay_en" value="[quote=\"`$postToQuote->authorName`\"]`$postToQuote->content`[/quote]"}	      	      
	    {/if}	    
	    <textarea name="content_en" wrap="virtual" cols="55" rows="10">{$contentToDisplay_en|default:""|escape}</textarea>
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
	  <td class="forum_titel" colspan="2">Bilder</td>
	</tr>
	<tr>
	  <td colspan="2" class="forum_bg1" width="500">	   
	    {if count($images) == 0}
	      Keine Bilder für diesen Mandanten.
	    {else}	      
	      {*{popup_init src="`$pelasHost`overlib.js"}*}
	      <select name="image" onMouseMove="if (!document.data.image.value) return nd(); else return overlib('<img src=\'' + document.data.image.value + '\'>', FGCOLOR, '#FFFFFF', BGCOLOR, '#CCCCCC', FIXX, 300, FIXY, 300);" onMouseOut="return nd();">
	        <option value="">Bitte auswählen</option>
	        {html_options options=$images}
	      </select>
	      <input type="button" value="einfuegen" onClick="javascript:addImage(document.data.image.value);">	   
	      <br>
	      <span onMouseMove="if (!document.data.image.value) return nd(); else return overlib('<img src=\'' + document.data.image.value + '\'>', FGCOLOR, '#FFFFFF', BGCOLOR, '#CCCCCC', FIXX, 300, FIXY, 300);" onMouseOut="return nd();">Vorschau</span>
	    {/if}
	    <br><a href="newsbild.php">Zum Upload</a>
	  </td>
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

{literal}
  <script language="JavaScript">
    <!--
    function addSmiley(smiley) {
      document.data.content.value=document.data.content.value + smiley;
      document.data.content.focus();
    }
    
    function addImage(imageUrl) {
      if (imageUrl) {
        document.data.content.value=document.data.content.value + "[img]" + imageUrl + "[/img]";
        document.data.content.focus();
      }
    }
    //-->
  </script>
{/literal}

