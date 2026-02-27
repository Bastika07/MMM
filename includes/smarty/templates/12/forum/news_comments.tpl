{* News-Anzeige mit Comments *}

{* eigentlich News anzeigen *}
{include file="file:news_post.tpl" time=$thread->time title=$thread->title title_en=$thread->title_en 
                       content=$thread->content content_en=$thread->content_en contentID=$thread->contentId 
                       authorID=$thread->authorId authorName=$thread->authorName fileaname=$filename posts=$thread->posts 
                       helperstring=$thread->helperstring edit=0 showLink=0 short=0 showBar=1}


{*
News hat noch keine Seiteneinteilung

{foreach key=seite item=offset from=$pages name=pages}
  <a href="{$filename}&thread={$threadID}&ppp={$ppp}&offset={$offset}">{$seite}</a>
  {if !$smarty.foreach.pages.last}  || {/if}
{/foreach}
*}


<img src="/style/content_bg_top.png" width="800" height="18"><br>

<table cellspacing="0" cellpadding="0" border="0" width="800">
<tr>
<td style="background: url('/style/content_bg.png') repeat-y; padding:1px 40px 10px 40px;">


{*** Kommentare ab 2007 ausblenden ***}
{if $time|date_format:'%Y' < 2007}

<table class="rahmen_allg" border="0" width="100%" cellspacing="1" cellpadding="3" style="margin-top:30px;">

{foreach key=key item=val from=$data name=news}  
  {* News soll natürlich nicht nochmal als Comment angezeigt werden *}
  {if $val.contentID != $thread->id}
    {include file="file:news_comments_post.tpl" time=$val.time lastEdited=$val.lastEdited content=$val.content contentID=$val.contentID authorID=$val.authorID authorName=$val.authorName 
                                authorClass=$val.authorClass fileaname=$filename edit=$val.edit hidden=$val.hidden hiddenBy=$val.hiddenBy admin=$admin avatar=$val.avatar pass=$val.pass}
  {/if}  
{/foreach}
</table>

<table cellpadding='3' cellspacing='5' border='0'>
  <tr>
    <td class='button'>
      <a href="{$filename}&action=addComment&newsID={$thread->id}" class="forumlink">Kommentar abgeben</a>
    </td>        
  </tr>
</table>

{/if}{* Ende Kommentare ausblenden *}


</td>
</tr>
</table>
<img src="/style/content_bg_bottom.png" width="800" height="1">
<br><br>
