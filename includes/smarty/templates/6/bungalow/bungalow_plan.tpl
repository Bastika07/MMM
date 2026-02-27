{* Smarty *}
<html>
<body>
{assign var="overlib" value=$pelasHost|cat:"overlib/overlib.js"}
{popup_init src=$overlib}
<h1>Belegungsplan</h1>
<p align="justify"><a href="/gfx/parkplan.pdf" target="_blank"><img src="/gfx/parkplan_small.jpg" width="226" height="149" style="margin-left:20px; margin-bottom:5px;" align="right" border="0" alt="Gesamt&uuml;bersicht des Parks"></a>
Wenn eure Mietgeb&uuml;hr eingegangen ist, dann kann der Hausmieter an dieser Stelle einen Bungalow reservieren 
und seine Bungalowbesatzung hinzufügen. Klickt auf das Bild neben dem Text, um eine Gesamtübersicht des Parks zu bekommen.<br><br>
Der <b>Info-Point ist das Haus Nr. 674</b>. Dort findet ihr einen Ansprechpartner für die Turniere, den Support und die Leihtische sowie Switches. Bitte beachtet dass
der <b>Infopoint Nachts von 03:00 bis 08:00 geschlossen ist</b>. Dies ist besonders wichtig für die Rückgabe eurer Miettische oder Switches - bringt sie bitte vor 03:00 Uhr oder nach 
08:00 Uhr am Montag Morgen zurück!
</p>

<p>
<map name="park">
{foreach from=$data item="bungalow"}
  {if $bungalow.status == 'frei'}
    {assign var="status" value="<span style=\'color: green\'>frei</span>"}
  {elseif $bungalow.status == 'gesperrt'}
    {assign var="status" value="<span style=\'color: red\'>gesperrt</span>"}
  {else}
    {assign var="status" value=$bungalow.status}
  {/if}
  <area shape="rect" coords="{$bungalow.coord_x1},{$bungalow.coord_y1},{$bungalow.coord_x2},{$bungalow.coord_y2}" href="bungalows.htm?action=detail&bungalow={$bungalow.ID}"
    onmouseover="return overlib('<table width=\'210\'><tr><td valign=\'top\' class=\'mouseover\' width=\'130\'>'
    +'<b>Typ</b>:<br>{$bungalow.bezeichnung}<br>'
    +'<b>Plätze</b>: {$bungalow.size}<br>'
    +'<b>Status</b>:<br>{$status|escape}'
    +'{if $bungalow.status != 'frei' && $bungalow.status != 'gesperrt'}<br><b>Mitbewohner:</b>{foreach from=$bungalow.user item="user" name="user"}<br>- {$user|escape:"htmlall"}{/foreach}{if $smarty.foreach.user.total == 0}<br>noch keine{/if}{/if}</span>'
    +'</td><td valign=\'top\'>'
    +'<img src=\'{$bungalow.pic}\' width=\'80\'>'
    +'</td></tr></table>', 
    CAPTION, 'Bungalow {$bungalow.ID}', OFFSETX, 30, OFFSETY, 30);"
  
  
    {*onmouseover="return overlib('<p align=\'center\'><img src=\'{$bungalow.pic}\' width=\'80\' align=\'center\'></p><br>'
    +'<span class=\'mouseover\'><b>Typ:</b><br>{$bungalow.bezeichnung}<br>'
    +'<b>Plätze:</b> {$bungalow.size}<br>'
    +'<b>Status:</b><br>{$status|escape}<br>'
    +'{if $bungalow.status != 'frei' && $bungalow.status != 'gesperrt'}<b>Mitbewohner:</b>{foreach from=$bungalow.user item="user" name="user"}<br>- {$user|escape:"htmlall"}{/foreach}{if $smarty.foreach.user.total == 0}<br>noch keine{/if}{/if}</span>', 
    CAPTION, 'Bungalow {$bungalow.ID}', OFFSETX, 30, OFFSETY, 30, WIDTH, 140);"*}
    onmouseout="return nd();">
{/foreach}
</map>

<p align="center"><img src="/gfx/park_2013.jpg" width="521" height="534" border="0" usemap="#park" align="center" style="margin-top:10px; margin-bottom:10px;"></p>

<h2>Statistik</h2>

<p>
<table cellspacing=1 cellpadding=2><tr><th>Typ</th><th>Vorhanden</th><th>Verkauft *</th><th>Frei</th><th>Preis</th></tr>
{foreach from=$stats item="bungalow"}
  {cycle values="hblau,dblau" assign="class"}
  <tr>
    <td class="{$class}" width="160">{$bungalow.kurzbeschreibung}</td>
    <td class="{$class}" width="80" align="center">{$bungalow.anzahlVorhanden}</td>
    <td class="{$class}" width="80" align="center">{$bungalow.vermietet} ({$bungalow.bezahlt})</td>
    <td class="{$class}" width="80" align="center">{$bungalow.frei}</td>
    <td class="{$class}" width="80" align="center">{$bungalow.preis}</td>
  </tr>
{/foreach}
</table>
<small>* In Klammern die Anzahl der bestellten Bungalows, für die bereits eine Zahlung eingegangen ist. Unbezahlte Bestellungen
werden nach Ablauf der Zahlungsfrist wieder freigegeben.</small>
</p>
<br>

<h2>Alle Bungalows</h2>
<p align="justify">Hier werden nur freigegebene Bungalows angezeigt.</p>
<p>
<table cellspacing=1 cellpadding=2><tr><th>Nr.</th><th>Typ</th><th>Pl&auml;tze</th><th>Status</th><th>&nbsp;</th></tr>
{foreach from=$data item="bungalow"}
  {if $bungalow.status != 'gesperrt'}
    {* nur freigegebene Häuser anzeigen *}
    {if $bungalow.status == 'frei'}
      {assign var="status" value="<span style=\'color: green\'>frei</span>"}
    {elseif $bungalow.status == 'gesperrt'}
      {assign var="status" value="<span style=\'color: red\'>gesperrt</span>"}
    {else}
      {assign var="status" value=$bungalow.status}
    {/if}
    {cycle values="hblau,dblau" assign="class"}
    <tr>
      <td class="{$class}" width="35"><b>{$bungalow.ID}</b></td>
      <td class="{$class}" width="100">{$bungalow.bezeichnung}</td>
      <td class="{$class}" width="55" align="center">{$bungalow.size}</td>
      <td class="{$class}" width="220">{$status}</td>
      <td class="{$class}" width="50"><a href="bungalows.htm?action=detail&bungalow={$bungalow.ID}">{if $bungalow.reservAllowed}mieten{else}details{/if}</a></td>    
    </tr>
  {/if}
{/foreach}
</table>
</p>
<br>

</body>
</html>