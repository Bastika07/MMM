{* Smarty *}

<h1>Einlasskontrolle</h1>


<script type="text/javascript">
<!--

/*
Abkürzungen zur Optimierung:
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
  u[{$user.userid}]['p']='{$user.platzart}';
  u[{$user.userid}]['c']='{$user.checkedin_at|date_format:'%A, %H:%M:%S'}';
  u[{$user.userid}]['blr']='{$user.reason|escape:"quotes"}';
{/foreach}
{/strip}

{*
var popup;
popup = window.open('http://10.10.0.251/checkin.htm?userId={$checkedin_user}','checkinWindow','scrollbars=no, menubar=no, location=noi, status=no, toolbar=no, resizable=yes');
if (popup == null)
	popup=window.open('http://10.10.0.251/checkin.htm?login={$checkedin_user}','checkinWindow','scrollbars=no, menubar=no, location=noi, status=no, toolbar=no, resizable=yes')
*}


{literal}
function displayUserData(user) {

  if (u[user.value].blr.length > 0) {
    document.formular.blacklist.value = u[user.value].blr;
    document.formular.blacklist.className = "blacklistwarning";
  } else {
    document.formular.blacklist.value = "Keine Angaben";
    document.formular.blacklist.className = "";
  }


  document.formular.login.value = u[user.value]['l'];
  document.formular.name.value = u[user.value]['n'];
  document.formular.seat.value = u[user.value]['s'];
  // set checking-button to disabled if already checked in
  if (u[user.value].c != 0) {
    document.formular.checkedinat.value = u[user.value]['c'];
    document.formular.checkinButton.disabled = true;
    document.formular.checkedinat.style.backgroundColor = '#FF0000';
    document.formular.checkedinat.style.color = '#FFFFFF';    
  } else {
    document.formular.checkedinat.value = 'noch nicht';
    document.formular.checkinButton.disabled = false;
    document.formular.checkedinat.style.backgroundColor = '#00FF00';
    document.formular.checkedinat.style.color = '#000000';
  }
}

function clearUserData() {
  document.formular.login.value = "";
  document.formular.name.value = "";
  document.formular.seat.value = "";
  document.formular.checkedinat.value = "";
  document.formular.checkedinat.style.backgroundColor = '#FFFFFF';
}
{/literal}
//-->
</script>

<form method="post" action="" name="formular">
{csrf_field}

<div class="searchbox">
<fieldset>
  <legend>Usersuche</legend>
  
<table cellpadding="0" cellspacing="0">
<tr>
  <td width="100">Suchbegriff</td>
  <!--onBlur="clearUserData();"-->
  <td><input type="text" name="nick" value="" onKeyup="printAuswahl()"></td>
</tr>
<tr>
  <td>Ergebnis</td>
  <!--onBlur="clearUserData();"-->
  <td><select name="UserID" size="20" onChange="displayUserData(this);" >
    <script type="text/javascript">
    <!--
    {literal}
    function printAuswahl() {
      var i, j, addme;
      inp = document.formular.nick.value; 
      //inp = inp.replace(/([\[\]\\\/\*\.\?\(\)\-\<\>\{\}\|\^\$\+\&])/g, "\\$1");
      var search = eval("/" + inp + "/i");
    
      document.formular.UserID.length = 0;
    
      // mindestens 3 Buchstaben, sonst ist das alles zu unperformant. Bottleneck: Füllen des Select-Tags
      if (inp.length > 2) {
        j = 0;
        for (var id in u) {
          //if(users[id].indexOf(inp) != -1) {
          if (search.test(u[id]['n']) || search.test(u[id]['l']) || id == inp) {
            document.formular.UserID[j++] = new Option(u[id]['l'] + ", " + u[id]['n'] + ", ID: " + id + ", " + u[id]['p'], id);
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
</tr>
</table>
</fieldset>
</div>

<div class="userinfobox">
  <fieldset>
    <legend>Userinfo</legend>
    <table cellpadding="0" cellspacing="0" width="100%">
      <tr><td><label for="login">Login:</label></td><td><input type="text" id="login" size="30"></td></tr>
      <tr><td><label for="name">Name: </label></td><td><input type="text" id="name" size="30"></td></tr>
      <tr><td><label for="seat">Platz: </label></td><td><input type="text" id="seat" size="30"></td></tr>
      <tr><td><label for="seat">Eingechecked: </label></td><td><input type="text" id="checkedinat" size="30"></td></tr>
    </table>    
  </fieldset>
</div>

<div class="blacklistbox">
  <fieldset>
    <legend>Blacklist</legend>
    <!--<input type="text" id="blacklist" size="40" align="center">-->
    <textarea id="blacklist" rows="2" cols="30"></textarea>
  </fieldset>
</div>

<div class="checkinbox">
  <fieldset>
    <legend>Aktion</legend>
    <button name="checkinButton" type="button" value="" disabled="false" onClick="self.location.href='{$SCRIPT_NAME}?action=checkinUser&userId=' + UserID.value">User einchecken</button>
    <button name="reloadButton" type="button" value="" onClick="self.location.href='{$SCRIPT_NAME}'">Reload</button>
  </fieldset> 
</div>

<div class="statusbox">
  <fieldset>
    <legend>Status</legend>
    Anwesende: {$present}<br>
    Bezahlte: {$paid}<br>
    Begleiter: {$companions}<br>
    Checkins/h: {$averagePerHour}<br>
    Zeit verbleibend: {$hoursLeft}h<br>
  </fieldset>
</div>

<div class="messagebox">
  <fieldset>
    <legend>Meldungen</legend>
    {if isset($errormsg)}
      <span style="color: red;">{$errormsg|escape}</span>
    {/if}
    
    {if isset($confirmmsg)}
      <span style="color: green;">{$confirmmsg|escape}</span>
    {/if}
  </fieldset> 
</div>

<div class="lastcheckinsbox">
  <fieldset>
    <legend>letzten 10 Checkins</legend>
    {foreach from=$lastCheckins item="checkin"}
      {$checkin.LOGIN}: {$checkin.checkedin_at|date_format:'%A, %H:%M:%S'}<br>
    {/foreach}
  </fieldset> 
</div>
</form>


<div class="guestcheckin">
  <form method="post" action="{$filename}">
  {csrf_field}
  <input type="hidden" name="action" value="checkinGuest">  
  <fieldset>
    <legend>Begleiter Checkin</legend>
    <table cellpadding="0" cellspacing="0" width="100%">
      <tr><td><label for="guest[name]">Name: </label></td><td><input type="text" name="guest[name]" size="30"></td></tr>
      <tr><td><label for="guest[plz]">PLZ: </label></td><td><input type="text" name="guest[plz]" size="5"></td></tr>
      <tr><td><label for="guest[address]">Adresse: </label></td><td><input type="text" name="guest[address]" size="30"></td></tr>
      <tr><td colspan="2" align="right"><input type="submit" value="eintragen"></td></tr>
    </table>
    <a href="einlasskontrolle2.htm?action=guestList">Begleiterliste</a>
  </fieldset> 
  </form>
</div>

<script type="text/javascript">
  document.formular.nick.focus();
</script>

{literal}
<style type="text/css">

div.blacklistbox {
  position: absolute;
  left: 550px;
  top: 190px;
  width: 300px;
}

div.lastcheckinsbox {
  position: absolute;
  top: 480px;
  width: 400px;  
}

div.guestcheckin {
  position: absolute;
  top: 490px;
  width: 400px;  
  left: 500px;
}

div.statusbox {
  position: absolute;
  left: 550px;
  top: 330px;
  width: 300px;  
}

div.messagebox {
  position: absolute;
  left: 550px;
  top: 440px;
  width: 300px;  
}

div.checkinbox {
  position: absolute;
  left: 550;
  top: 275px;
  width: 300px;
}

div.userinfobox {
  position: absolute;
  left: 550px;
  top: 70px;
  width: 350px;
}

div.searchbox {
  position: absolute;
  width: 520px;
  top: 70px;
}

div.labels {
  position: relative;
  width: 80px;
  float: left;
  border: solid black 1px;
}

div.values {
  position: relative;
  float: right;
  border: solid black 1px;
}

legend, fieldset, textarea, select { 
  font: normal 12px Arial;
}

input, select, button, textarea {
  padding: 2px;
  border: solid black 1px;
}

textarea.blacklistwarning {
  background: red;
  color: white;
}

textarea#blacklist {
  font-weight: bold;
}

</style>
{/literal}
