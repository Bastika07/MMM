<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

 <title>User2Beamer</title>
 <subtitle>User2Beamerfeed</subtitle>
 <link href="{$baseUrl}" rel="alternate" type="text/html" />
 <updated>{$updated|date_format:'%Y-%m-%dT%H:%M:%S+01:00'}</updated>
 <id>tag:www.lan,{$updated|date_format:'%Y-%m-%d'}:/user2beamer</id>

{foreach item=message from=$messages}
	<entry>
    <title type="text">User2BeamerMessage by {$message->userName}</title>
    <link/>
    <id>tag:www.lan,{$message->approvedAt|date_format:'%Y-%m-%d'}:/user2beamer/{$message.messageId}</id>
    <updated>{$message->approvedAt|date_format:'%Y-%m-%dT%H:%M:%S+01:00'}</updated>
		<content type="text">{$message->message|bbcode2html|nl2br|escape|utf8_encode}</content>
		
	</entry>
{/foreach}

</feed>