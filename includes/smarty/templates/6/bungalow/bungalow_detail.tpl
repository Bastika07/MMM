{* Smarty *}
<html>
<body>

<h1>Bungalow Detail</h1>

<p align="justify">An dieser Stelle k&ouml;nnt ihr euch eure Hausbesatzung zusammenstellen
und andere Hausbesatzungen einsehen. Weiterhin findet ihr hier die Grundrisse der Bungalows.
Bitte beachtet dass ihr nur die Bungalows der Kategorie reservieren k&ouml;nnt, f&uuml;r
die ihr gezahlt habt.</p>

<p>
<table cellspacing="1" cellpadding="2" border="0" width="400">
<tr><th width="100">Bungalow-Nummer</th><td class="dblau">{$data.ID}</td></tr>
<tr><th width="100">Typ</th><td class="hblau">{$data.bezeichnung}</td></tr>
<tr><th>Gr&ouml;&szlig;e</th><td class="dblau">{$data.size}</td></tr>
<tr><th>Status</th><td class="hblau">{$data.status}
{if $owner}<br><a href="bungalows.htm?action=return&bungalow={$data.ID}">Diesen Bungalow abgeben</a>{/if}</td></tr>
<tr><th>IP-Adresse</th><td class="dblau">172.16.{$data.IP}.y - DHCP ist ebenfalls m�glich</td></tr>
{* Wer wohnt hier? *}
<tr><th valign="top">Bewohner</th><td class="hblau">

	<table cellspacing="0" cellpadding="1" border="0" width="100%">
	{foreach from=$bewohner item="val" name="bewohner"}
	  <tr>
	  <td valign="bottom" width="70%"><a href="?page=4&nUserID={$val.userID}"><img src="gfx/userinfo.gif" border="0"></a> {$val.login|escape:"htmlall"} </td>
	  {if $owner}
	    {if $val.besitzer == 0}
	      <td valign="bottom"><a href="bungalows.htm?action=kickUser&bungalow={$data.ID}&nUserID={$val.userID}">kick</a></td>
	    {else}
	      <td valign="bottom">Mieter</td>
	    {/if}
	  {/if}
	  </tr>
	{/foreach}
	{* keine Bewohner da, h�tte noch nicht gebucht, user darf h�tte buchen *}
	{if $reservAllowed}
  {* Reservierung darf erfolgen *}
    <a href="bungalows.htm?action=reserv&bungalow={$data.ID}">Diesen Bungalow reservieren...</a>
  <tr><td></td></tr>{/if}
	
	{if $owner && ($currentMates < $data.size)}
	  <tr><td colspan="2">
        {* aktueller hat diesen Bungalow gebucht, Men� daf�r anzeigen *}
        {literal}
        <script type="text/javascript">
        <!--
        var user = new Array();
        {/literal}
        {foreach from=$user item="username" key="id"}
          user[{$id}] = "{$username} (ID:{$id})";
        {/foreach}
        //-->
        </script>
        <br>
        <form name="formular" method="post" action="bungalows.htm">
        {csrf_field}
        <table><tr><td>Filter: 
        <input type="text" name="nick" value="" onKeyup="printAuswahl()">
        <input type="hidden" name="action" value="addUser">
        <input type="hidden" name="bungalow" value="{$data.ID}">
        </td></tr>
        <tr><td>
        <select name="userID">
        {foreach from=$user item="username" key="id"}
          <option value="{$id}">{$username|escape:"htmlall"} (ID:{$id})</option>";
        {/foreach}
        {literal}
        <script type="text/javascript">
        <!--   
        function printAuswahl() {
          var i, j, addme;
          inp = document.formular.nick.value; 
          inp = inp.replace(/([\[\]\\\/\*\.\?\(\)\-\<\>\{\}\|\^\$\+\&])/g, "\\$1");
          var search = eval("/" + inp + "/i");

          document.formular.userID.length = 0;
          j = 0;
          for (var id in user) {
            if (inp.length == 0 || search.test(user[id])) {
              addme = new Option(user[id], id);
              document.formular.userID[j] = addme;
              j++;
            }
          }      
          document.formular.userID.length = j;
        }
        //-->
        {/literal}
        </script>
        </select>
        </td><td><input type="submit" value="eintragen" class="submit"></td></tr>
        </table>
        </form>
        </td></tr>
      {/if}
	</table>
</td></tr>

</table>
</p>
<br>

<p>
<img src="{$data.pic}">
</p>
<br>


</body>
</html>