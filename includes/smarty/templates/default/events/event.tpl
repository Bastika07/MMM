{* Smarty *}

{literal}
<script language="JavaScript">
<!--
function openLocationpic(thePic)
{
	detail = window.open("location_pic.htm?sPicSrc="+thePic,"Details","width=801,height=601,locationbar=false,resize=false");
	detail.focus();
}
//-->
</script>
{/literal}

{if $error}
	{if $error == 'NO_EVENT_FOR_ID'}
		<p class="fehler">Kein Event mit der ID {$eventId}</p>
	{else}
		Unbekannter Fehler
	{/if}
{else}
	<h1 align="center">{$event.name}</h1>
	<p align="center"><small>{$event.start|date_format:'%A, %H:%M'}</small></p>
	<p align="justify">{$event.text|default:'no text'|nl2br}</p>
{/if}
<a href="events.htm#zeitplan"><img src="gfx/headline_pfeil.gif" border="0">Zum Zeitplan</a>