{* Smarty *}

{* dirty hack damit der Forums-pfad nicht beim Posten von News-Comments angezeigt wird*}
{if $filename != '/index.php?page=2'}
  <p>
  <a href="{$filename}">Forum</a> <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&board={$board->id}">{$board->name}</a>
  {if $showThreadName}
    <img src="/gfx/headline_pfeil.gif" border="0"> <a href="{$filename}&thread={$thread->id}">{$thread->title|escape}</a>
  {/if}
  </p>
{/if}

<form method="post" action="{$filename}" name="data" onSubmit="closeAll()">
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

			
<table class="rahmen_allg" cellspacing="1" cellpadding="3" border="0" width="100%">
  <tr>
    <td class="forum_titel">&nbsp;</td>
    <td class="forum_titel">
    {if $mode == 'newPost' || $mode == 'editPost'}
      {* Post wurde angegeben, es wird editiert, oder es wird ein neuer Post für einen bestehen $thread angelegt *}
      {$thread->title|escape}
    {else}
      Neues Thema
    {/if}
    </td>
  </tr>

{if $title_field}
  <tr>
	  <td class="forum_bg1">Titel:</td>
	  <td class="forum_bg2"><input type="text" name="title" size="40" maxlength="100" value="{$thread->title|default:""}"></td>
	</tr>
{else}
  <input type="hidden" name="title" value="inline">
{/if}	
	<tr><td class="forum_bg2"></td>
          <td class="forum_bg2">
          <input type="button" class="button" name="quotebutton" value="quote" style="width: 85px" onClick="switchButton('quote')" />
          <input type="button" class="button" name="codebutton" value="code" style="width: 80px" onClick="switchButton('code')" />
          <input type="button" class="button" name="urlbutton" value="url" style="width: 65px" onClick="switchButton('url')" />
          <input type="button" class="button" name="imgbutton" value="img" style="width: 70px" onClick="switchButton('img')" />
          <input type="button" class="button" name="bbutton" value="b" style="width: 50px" onClick="switchButton('b')" />
          <input type="button" class="button" name="ibutton" value="i" style="width: 50px" onClick="switchButton('i')" />
          <input type="button" class="button" name="ubutton" value="u" style="width: 50px" onClick="switchButton('u')" />
          </td>
        </tr>
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
	    <textarea name="content" wrap="virtual" style="width:90%; height:200px;">{$contentToDisplay|default:""}</textarea>
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
    var btn_status = new Array();
    <!--
    function addSmiley(smiley) {
      document.data.content.value=document.data.content.value+smiley;
      document.data.content.focus();
    }

    function quote() {
      if (quoteOpen) {
        document.data.content.value=document.data.content.value + '[/quote]';
        document.data.quotebutton.value='quote';
        quoteOpen = false;
      } else {
        document.data.content.value=document.data.content.value + '[quote]';
        document.data.quotebutton.value='quote *';
        quoteOpen = true;
      }
      document.data.content.focus();
    }

    function switchButton(name) {
      // nicht gesetzt, wert wird als false angenommen
      if (btn_status[name] == null) {
        btn_status[name] = false; 
      }
      if (btn_status[name]) {
        document.data.content.value = document.data.content.value + '[/' + name + ']';
        eval('document.data.' + name + 'button.value = name');
        btn_status[name] = false;
      } else {
        document.data.content.value = document.data.content.value + '[' + name + ']';
        eval('document.data.' + name + 'button.value = name + \' *\'');
        btn_status[name] = true;
      }
			document.forms.data.content.focus();
    }

	function closeAll() {
		//for (var button in status) {			
		//}
	}

    //-->
  </script>
{/literal}

