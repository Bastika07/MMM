{* News-Anzeige ohne Comments *}
{* falls keine news Angezeigt wird, bleibt diese Variable auf 1 *}
{assign var="empty" value="1"}
{foreach key=key item=val from=$data name=news}
  {if $val.hidden == 0}
    {include file="file:news_post.tpl" time=$val.time title=$val.title content=$val.content contentID=$val.contentID 
                                       authorID=$val.authorID authorName=$val.authorName fileaname=$filename posts=$val.posts helperstring=$val.helperstring short=0 showLink=1 showBar=1}  
    {assign var="empty" value="0"}                                      
  {/if}
{/foreach}
{if $empty == 1}
  <p align="center">In dieser Kategorie gibt es noch keine News!</p>
{/if}