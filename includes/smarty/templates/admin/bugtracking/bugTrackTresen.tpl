{*smarty*}
{*debug*}
{assign var=title value="BTracking::index"}
{*include file = "header.tpl"*}
<h1>&nbsp;[ BugTracking::Eingabemaske ]</h1>
{include file='bugTrack_header.tpl'}

<script type="text/javascript">
<!--
/*
Abk�rzungen zur Optimierung:
l: login
n: name
s: seat
p: platzart
c: checkedinat
blr: blacklistReason
*/

var u = new Array();
{strip}
{foreach from=$data item="user"}
  u[{$user.userid}] = Array();
  u[{$user.userid}]['l']='{$user.login|escape:"quotes"}';
  u[{$user.userid}]['n']='{$user.name|escape:"quotes"} {$user.nachname|escape:"quotes"}';
  {if isset($user.reihe) && isset($user.platz)}
    u[{$user.userid}]['s']='{$user.reihe} - {$user.platz}';
  {else}
    u[{$user.userid}]['s']='unbekannt';
  {/if}
{/foreach}
{/strip}
//-->
</script>

<table border="0" cellpadding="0" cellspacing="0">
<form action='{$smarty.server.PHP_SELF}?do=addLine' method="post" name="formular">
  {csrf_field}
  <tr><td>
    <strong>IP*:</strong>
    </td><td>
    <input type="text" name="ip" size="15" maxlength="15">
    </td>
    <td><strong>Priorit�t:</strong></td>
    <td><select name=prio>
    {section name=sec1 loop=$prio}
	<option value="{$prio[sec1].id}">{$prio[sec1].name}</option>
    {/section}
    </select></td>
  </tr><tr>
  <td valign="top">
    <strong>User:</strong>
    </td><td valign="top">
    <input type="text" name="user" size="15" maxlength="65" onKeyup="printAuswahl()">
    </td>
    <td valign="top"> <strong>Vorschau</strong></td>
    <!--onBlur="clearUserData();"-->
    <td><select name="userId" size="5">
    	<script type="text/javascript">
	//<!--
	{literal}
	function printAuswahl() {
	var i, j;
	inp = document.formular.user.value; 
	//inp = inp.replace(/([\[\]\\\/\*\.\?\(\)\-\<\>\{\}\|\^\$\+\&])/g, "\\$1");
	var search = eval("/" + inp + "/i");
	
	document.formular.userId.length = 0;
	
	// mindestens 3 Buchstaben, sonst ist das alles zu unperformant. Bottleneck: F�llen des Select-Tags
	if (inp.length > 2) {
      	  j = 0;
	  for (var id in u) {
          	//if(users[id].indexOf(inp) != -1) {
		if (search.test(u[id]['l']) || id == inp) {
		document.formular.userId[j++] = new Option(u[id]['l'] + ", ID: " + id, id);
		}
	}
        document.formular.UserID.length = j;
      }
    }
    {/literal}
    //-->
    </script>
    </select>
  </td>
  </tr><tr>
  <td>
    <strong>Reihe*:</strong>
    </td><td>
    <input type="text" name="reihe" size="3" maxlength="5">
  </td><td>
    <strong>Platz*:</strong>
    </td><td>
    <input type="text" name="platz" size="3" maxlength="5">
  </td>
  </tr><tr>
    <td colspan=2><strong>Beschreibung:</strong></td>
    <td><strong>Bug-Klasse:</strong></td>
    <td><select name='bugKlasse'>
    {section name=sec2 loop=$bugs}
	<option value="{$bugs[sec2].id}">{$bugs[sec2].name}</option>
    {/section}
    </select></td>
  </tr><tr>
    <td colspan=4>
       <textarea name="descript" cols = 50 rows=5></textarea>
    </td>
  </tr><tr>
    <td colspan=3><strong>Submitted by:</strong><select name='inputmglkt'>
    	{section name=sec2 loop=$inputmglkt}
	<option value="{$inputmglkt[sec2].id}">{$inputmglkt[sec2].login}</option>
	{/section}
    </td>
    <td align='right'>
    <input type="submit" value="Submit Bug">
    </td>
  </tr><tr>
  	<td colspan = 4>* optionale Eingabe</td>
  </tr>
</select><br></td>
</table>
</form>
{include file="footer.tpl"}
