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

<p align="justify">
Etwas besonderes ist die <b>Eventhalle</b>: Auf 1.500 Quadratmetern
befinden sich die Hauptb&uuml;hne, die Coveragearea, mehrere Aussteller, Non-PC-Entertainment
und der Haupt-Infopoint. Hier wird euch rund um die Uhr Unterhaltung geboten und das dank
abgetrennter Halle ohne beim Zocken zu st&ouml;ren (ausgenommen die Pl&auml;tze an den Durchg&auml;ngen zur Eventhalle).
</p>


<br>

<p>
<a name="zeitplan"><b>Zeitplan</b></a><br>
Damit ihr bei der vielzahl an Events nicht den Überblick verliert, findet ihr hier einen Zeitplan für das Wochenende:
<table width="100%">
{assign var="curday" value=""}
{foreach from=$events item="event"}
	{if $curday != $event.weekday}
		<tr><td colspan="2" height="15"></td></tr>
		<tr><td colspan="2"><b>{$event.weekday}</b></td></tr>
		{assign var="curday" value=$event.weekday}
	{/if}
	<tr><td><a href="events.htm?event={$event.id}">{$event.name}</a></td><td align="center">{$event.daytime}</td></tr>
{/foreach}
</table>

</p>

<br>

<p align="justify">
<b>Umfangreiche Turniercoverage</b><br>
Gamer-FM ist vor Ort und wird auf der Hauptb&uuml;hne
die wichtigsten Turnierspiele des Wochenendes &uuml;bertragen und moderieren.
</p>
