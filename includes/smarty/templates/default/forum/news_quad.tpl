{* News-Anzeige "quad" (aus jeder der 4 kategorien eine)*}



<table width="100%" cellpadding=10 cellspacing=0 style="border-collapse:collapse">
<tr><td valign="top" width="25%" style="border-right: 1px black solid; border-bottom: 1px black solid;">
  <img src="/forumicons/news_allgemein.gif"><br>
  {include file="file:news_post.tpl" time=$data.0.time title=$data.0.title content=$data.0.content contentID=$data.0.contentID 
                                     authorID=$data.0.authorID authorName=$data.0.authorName fileaname=$filename posts=$data.0.posts short=$short showLink=1 showBar=0}  
</td><td valign="top" width="25%">
  <img src="/forumicons/news_animation.gif"><br>
  {include file="file:news_post.tpl" time=$data.1.time title=$data.1.title content=$data.1.content contentID=$data.1.contentID 
                                     authorID=$data.1.authorID authorName=$data.1.authorName fileaname=$filename posts=$data.1.posts short=$short showLink=1 showBar=0}  
</td></tr>                                     
<tr><td valign="top" width="25%">
  <img src="/forumicons/news_turniere.gif"><br>
  {include file="file:news_post.tpl" time=$data.2.time title=$data.2.title content=$data.2.content contentID=$data.2.contentID 
                                     authorID=$data.2.authorID authorName=$data.2.authorName fileaname=$filename posts=$data.2.posts short=$short showLink=1 showBar=0}                         
</td><td valign="top" width="25%" style="border-left: 1px black solid; border-top: 1px black solid">
  <img src="/forumicons/news_technik.gif"><br>
  {include file="file:news_post.tpl" time=$data.3.time title=$data.3.title content=$data.3.content contentID=$data.3.contentID 
                                     authorID=$data.3.authorID authorName=$data.3.authorName fileaname=$filename posts=$data.3.posts short=$short showLink=1 showBar=0}
</td></tr>                           
</table>