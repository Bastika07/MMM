{* News-Anzeige "quad" (aus jeder der 4 kategorien eine)*}

{assign var="nonews" value="<p align=\"center\">In dieser Kategorie gibt es noch keine News!</p>"}

<table width="100%" cellpadding=10 cellspacing=0 style="border-collapse:collapse">
<tr><td valign="top" width="25%" style="border-right: 1px black solid; border-bottom: 1px black solid;"> 
  <a href={$filename}&newsBoard={$Allgemein.boardID}>
    <img src="/forumicons/news_allgemein1.gif" border=0 onMouseOver="javascript: change(this, 'forumicons/news_allgemein2.gif'); return true;" onMouseOut="javascript: change(this, 'forumicons/news_allgemein1.gif'); return true;">
  </a><br>
  {if $Allgemein.hidden == 0}
  {include file="file:news_post.tpl" time=$Allgemein.time title=$Allgemein.title content=$Allgemein.content contentID=$Allgemein.contentID 
                                     authorID=$Allgemein.authorID authorName=$Allgemein.authorName fileaname=$filename posts=$Allgemein.posts short=$short showLink=1 showBar=0}  
  {else}
    {$nonews}
  {/if}
</td><td valign="top" width="25%">
  <a href={$filename}&newsBoard={$Animation.boardID}>
    <img src="/forumicons/news_animation1.gif" border=0 onMouseOver="javascript: change(this, 'forumicons/news_animation2.gif'); return true;" onMouseOut="javascript: change(this, 'forumicons/news_animation1.gif'); return true;">
  </a><br>
  {if $Animation.hidden == 0}
  {include file="file:news_post.tpl" time=$Animation.time title=$Animation.title content=$Animation.content contentID=$Animation.contentID 
                                     authorID=$Animation.authorID authorName=$Animation.authorName fileaname=$filename posts=$Animation.posts short=$short showLink=1 showBar=0}  
  {else}
    {$nonews}
  {/if}
</td></tr>                                     
<tr><td valign="top" width="25%">
  <a href={$filename}&newsBoard={$Turniere.boardID}>
    <img src="/forumicons/news_turniere1.gif" border=0 onMouseOver="javascript: change(this, 'forumicons/news_turniere2.gif'); return true;" onMouseOut="javascript: change(this, 'forumicons/news_turniere1.gif'); return true;">
  </a><br>
  {if $Turniere.hidden == 0}
  {include file="file:news_post.tpl" time=$Turniere.time title=$Turniere.title content=$Turniere.content contentID=$Turniere.contentID 
                                     authorID=$Turniere.authorID authorName=$Turniere.authorName fileaname=$filename posts=$Turniere.posts short=$short showLink=1 showBar=0}                         
  {else}
    {$nonews}
  {/if}
</td><td valign="top" width="25%" style="border-left: 1px black solid; border-top: 1px black solid">
  <a href={$filename}&newsBoard={$Technik.boardID}>
    <img src="/forumicons/news_technik1.gif" border=0 onMouseOver="javascript: change(this, 'forumicons/news_technik2.gif'); return true;" onMouseOut="javascript: change(this, 'forumicons/news_technik1.gif'); return true;">
  </a><br>
  {if $Technik.hidden == 0}
  {include file="file:news_post.tpl" time=$Technik.time title=$Technik.title content=$Technik.content contentID=$Technik.contentID 
                                     authorID=$Technik.authorID authorName=$Technik.authorName fileaname=$filename posts=$Technik.posts short=$short showLink=1 showBar=0}
  {else}
    {$nonews}
  {/if}
</td></tr>                           
</table>