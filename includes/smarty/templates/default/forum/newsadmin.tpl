{* News-Admin*}
<h1>Inhaltstyp News verwalten</h1>

<p>neue News verfassen f&uuml;r</p>
<ul>
{foreach key=key item=val from=$boardList}
  <li><a href="{$filename}&action=add&board={$key}">{$val}</a></li>
{/foreach}
</ul>

<p>
{if !$showAll}
	<a href="news.php?showAll=true">Alle News anzeigen</a>
{else}
	<a href="news.php?showAll=false">Nur begrenzt News anzeigen</a>
{/if}
</p>

<p>
Hinweis: F&uuml;r eine zweispaltige News an der Trennstelle [spalte] eingeben. (Nur Beben auf Startseite)
</p>

{foreach key=key item=val from=$data}
{$boardList.$key}<br>
  <table cellspacing="0" cellpadding="0" width="800">
  <tr><td class="navbar">
  <table width="100%" cellspacing="1" cellpadding="3">
  <tr>
    <td class="navbar" width=50><b>ID</b></th>
    <td class="navbar" width=100><b>Title</b></th>
    <td class="navbar" width=500><b>Content</b></th>
    <td class="navbar" width=50><b>Time</b></th>
    <td class="navbar" width=100><b>action</b></th>
  </tr>
  {foreach key=key2 item=val2 from=$val}
    {cycle values="hblau,dblau" assign="class"}
    <tr><td class={$class}>{$val2.contentID}</td>
      <td class={$class}>{$val2.title}</td>
      <td class={$class}>{$val2.content|truncate:150}</td>
      <td class={$class}>{$val2.time|date_format:"%d.%m.%Y %H:%M"}<br>{if $val2.planned}<p style="color:red"><b>geplant</b><br>{$val2.timeplanned|date_format:"%d.%m.%Y %H:%M"}</p>{/if}</td>
      <td class={$class}>
        <a href="{$filename}&action=changemode&post={$val2.contentID}&mode=hidden">{if $val2.hidden}aktivieren{else}verstecken{/if}</a>
        <a href="{$filename}&action=edit&post={$val2.contentID}">edit</a>
        <a href="{$val2.REFERER}?page=2&action=showComments&newsID={$val2.contentID}" target="_new">preview</a>
      </td></tr>
  {/foreach}
  </table>
  </td></tr></table>
  <a href="{$filename}&action=add&board={$key}">neue News verfassen</a>
<br><br>
{/foreach}
</body>
</html>