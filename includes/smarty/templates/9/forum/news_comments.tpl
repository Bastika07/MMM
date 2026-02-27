{* News-Anzeige mit Comments *}

{* eigentlich News anzeigen *}
{include file="file:news_post.tpl" time=$thread->time title=$thread->title title_en=$thread->title_en content=$thread->content content_en=$thread->content_en contentID=$thread->contentId 
                       authorID=$thread->authorId authorName=$thread->authorName fileaname=$filename posts=$thread->posts 
                       helperstring=$thread->helperstring edit=0 showLink=0 short=0 showBar=1}

{*
News hat noch keine Seiteneinteilung

{foreach key=seite item=offset from=$pages name=pages}
  <a href="{$filename}&thread={$threadID}&ppp={$ppp}&offset={$offset}">{$seite}</a>
  {if !$smarty.foreach.pages.last}  || {/if}
{/foreach}
*}


{* Rand links und rechts *}
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
  <td width="10" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="10" height="1" border="0"></td>
  <td width="1" bgcolor="#0f233c"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>
  <td width="10"><img src="/gfx/lgif.gif" width="10" height="1" border="0"></td>
  <td width="702">
  
  <table cellspacing="0" cellpadding="12" border="0" width="100%">
  <tr><td>
  
  
<table class="rahmen_allg" border="0" width="100%" cellspacing="1" cellpadding="3">

{foreach key=key item=val from=$data name=news}  
  {* News soll natürlich nicht nochmal als Comment angezeigt werden *}
  {if $val.contentID != $thread->id}
    {include file="file:news_comments_post.tpl" time=$val.time lastEdited=$val.lastEdited content=$val.content contentID=$val.contentID authorID=$val.authorID authorName=$val.authorName 
                                authorClass=$val.authorClass fileaname=$filename edit=$val.edit hidden=$val.hidden hiddenBy=$val.hiddenBy admin=$admin avatar=$val.avatar}
  {/if}  
{/foreach}
</table>

<table cellpadding='3' cellspacing='5' border='0'>
  <tr>
    <td class='forum_titel'>
      <a href="{$filename}&action=addComment&newsID={$thread->id}" class="forumlink">Kommentar erstellen</a>
    </td>        
  </tr>
</table>

<br>

  </td></tr>
  </table>

{* End of Padding *}
  </td>
  <td width="50" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>  
</tr>
</table>
