{include file='_vorspann.tpl'}

<h1>Ladies at Play</h1>

{if isset($error)}
    <p>{$error}</p>
{else}
    <form method="post" action="{$filename}" name="formular">
      {csrf_field}
      <input type="hidden" name="action" value="submit"/>
    {if isset($lady.login)}
      <input type="hidden" name="userId" value="{$lady.userId}"/>
    {/if}
      <table cellspacing="1" class="outer" style="width: 680px;">
	<tr>
	  <th colspan="2">Eintrag bearbeiten</th>
	</tr>
	<tr class="row-0">
	  <td>Nickname</td>		
	  <td>
	  {if isset($lady.login)}
	    {$lady.login|escape}
	  {else}
	    <script type="text/javascript">
	    <!--
	      var user = new Array();
	      var userObjects = new Array();
	      {foreach from=$ladies item=lady}
	      user[{$lady.userId}] = "{$lady.login} ({$lady.name} {$lady.nachname})";
	      {/foreach}
	    //-->
	    </script>
	    <input type="text" name="nick" value="" onkeyup="printAuswahl()"/>
	    <select name="userId" size="1">
	      <script type="text/javascript">
	      <!--
	        {literal}
	        function printAuswahl() {
	          var i, j, addme;
	          inp = document.formular.nick.value; 
	          //inp = inp.replace(/([\[\]\\\/\*\.\?\(\)\-\<\>\{\}\|\^\$\+\&])/g, "\\$1");
	          var search = eval("/" + inp + "/i");
	          document.formular.userId.length = 0;
	    
	          // Mindestens drei Buchstaben, sonst ist das alles zu unperformant.
		  // Bottleneck: Füllen des Select-Tags.
	          if (inp.length > 2) {
	  	    j = 0;
		    for (var id in user) {                  
		      //if(user[id].indexOf(inp) != -1) {
		      if (search.test(user[id])) {
		        document.formular.userId[j++] = new Option(user[id], id);
		      }
		    }
		    document.formular.userId.length = j;
	          }
		}
		{/literal}
	      //-->
	      </script>
	    </select>
	  {/if}
	  </td>
	</tr>
	<tr class="row-1">
	  <td>Wie bist du auf deinen Nicknamen gekommen?</td>
	  <td><textarea name="wieNickname" cols="50" rows="10">{$lady.wieNickname}</textarea></td>
	</tr>
	<tr class="row-0">
	  <td>Wann hast du mit dem "Zocken" angefangen und wie bist du dazu gekommen?</td>
	  <td><textarea name="warumZocken" cols="50" rows="10">{$lady.warumZocken}</textarea></td>
	</tr>
	<tr class="row-1">
	  <td>Was ist dein Lieblingsspiel und warum?</td>
	  <td><textarea name="lieblingsspiel" cols="50" rows="10">{$lady.lieblingsspiel}</textarea></td>
	</tr>
	<tr class="row-0">
	  <td>Wie findest du unsere Aktion Ladies@Play?</td>
	  <td><textarea name="meinungAktion" cols="50" rows="10">{$lady.meinungAktion}</textarea></td>
	</tr>
	<tr class="row-1">
	  <td>Was erwartest du von der NorthCon Winter 2004?</td>
	  <td><textarea name="erwartungParty" cols="50" rows="10">{$lady.erwartungParty}</textarea></td>
	</tr>
	<tr class="row-0">
	  <td>&nbsp;</td>
	  <td><input type="submit" value="speichern"/></td>
	</tr>
      </table>
    </form>
{/if}

{include file='_nachspann.tpl'}
