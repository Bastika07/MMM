<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

 <title>News</title>
 <subtitle>Newsfeed</subtitle>
 <link href="{$baseUrl}" rel="alternate" type="text/html" />
 <updated>{$updated|date_format:'%Y-%m-%dT%H:%M:%S+01:00'}</updated>

 <id>tag:www.lan,{$updated|date_format:'%Y-%m-%d'}:/news</id>

{foreach item=news from=$newsArray}
	<entry>
    <title type="html">{$news.title|bbcode2html|nl2br|escape|replace:'Â„':'"'|replace:'Â“':'"'|utf8_encode}</title>
    <link href="{$baseUrl}news.php?action=showComments&amp;newsID={$news.contentID}"/>
    <id>tag:www.lan,{$news.time|date_format:'%Y-%m-%d'}:/news/{$news.contentID}</id>    
    <updated>{$news.time|date_format:'%Y-%m-%dT%H:%M:%S+01:00'}</updated>
		{*<summary type="html">{$news.content|bbcode2html|nl2br|escape:'htmlall'|replace:'&':'&amp;'|utf8_encode}</summary>*}
		<content type="xhtml" xml:base="{$baseUrl}">
      <div xmlns="http://www.w3.org/1999/xhtml">
        {$news.content|escape|bbcode2html|nl2br|replace:'Â„':'"'|replace:'Â“':'"'|utf8_encode}
      </div>
    </content>
	</entry>
{/foreach}

</feed>