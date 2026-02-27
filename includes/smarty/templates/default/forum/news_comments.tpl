{* News-Anzeige mit Comments *}

{* eigentlich News anzeigen *}
{include file="file:news_post.tpl" time=$thread->time title=$thread->title content=$thread->content contentID=$thread->contentId 
                       authorID=$thread->authorId authorName=$thread->authorName fileaname=$filename posts=$thread->posts edit=0 showLink=0 short=0 showBar=1}

{*
News hat noch keine Seiteneinteilung

{foreach key=seite item=offset from=$pages name=pages}
  <a href="{$filename}&thread={$threadID}&ppp={$ppp}&offset={$offset}">{$seite}</a>
  {if !$smarty.foreach.pages.last}  || {/if}
{/foreach}
*}

{*** Kommentare ab 2007 ausblenden ***}
{* if $thread->time|date_format:'%Y' < 2007 *}

<table class="rahmen_allg" border=0 width="99%" cellspacing="1" cellpadding="3">

{foreach key=key item=val from=$data name=news}  
  {* News soll natürlich nicht nochmal als Comment angezeigt werden *}
  {if $val.contentID != $thread->id}
    {include file="file:news_comments_post.tpl" time=$val.time lastEdited=$val.lastEdited 
      content=$val.content contentID=$val.contentID authorID=$val.authorID authorName=$val.authorName 
      authorClass=$val.authorClass fileaname=$filename edit=$val.edit hidden=$val.hidden 
      hiddenBy=$val.hiddenBy admin=$admin avatar=$val.avatar passes=$val.passes}
  {/if}  
{/foreach}
</table>

<input type="button" class="button" value="Kommentar erstellen" onclick="document.location.href='{$filename}&action=addComment&newsID={$thread->id}';"/>

{* /if *}
