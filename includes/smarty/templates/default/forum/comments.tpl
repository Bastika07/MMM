{* Comments-Anzeige *}

<table border=0 width="100%">

{foreach key=key item=val from=$data name=posts}
  {*{include file="file:post.tpl" time=$val.time content=$val.content authorID=$val.authorID authorName=$val.authorName authorClass=$val.authorClass}*}  
    {include file="file:post.tpl" time=$val.time lastEdited=$val.lastEdited content=$val.content contentID=$val.contentID authorID=$val.authorID authorName=$val.authorName 
                                authorClass=$val.authorClass fileaname=$filename edit=$val.edit hidden=$val.hidden hiddenBy=$val.hiddenBy admin=$admin avatar=$val.avatar}                                       
{/foreach}

</table>