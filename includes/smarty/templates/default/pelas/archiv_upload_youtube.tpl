<table cellspacing="0" cellpadding="0" width="99%">
<tr><td colspan="2">
	<div class='kasten'>
		<div class='ecke-or'></div>
		<div class='ecke-ol'></div>
		<h3 class='kasten'>Medienupload: YouTube Video</h3>
		<div class='kastentext'>

			<p><b>Wichtig:</b> 
			<ul>
			<li>Links im folgenden Format angeben: <br>http://www.youtube.com/watch?v=??????????</li>
			{if $form_disabled}
			<li><p class=fehler>Fehler: Um einen Beitrag hochzuladen musst Du eingeloggt sein.</p></li>
			{/if}
			</ul>
			</p>

			<p><table cellspacing="1" cellpadding="3" border="0">


			<form method="post" action="?page=15&action=doUploadYouTube" name="data">
			{csrf_field}
			<TR><TD>Autor</TD><TD>{$sLogin|escape}</TD></TR>

			<TR><TD>Party</TD><TD>
			<select name="iParty" {$form_disabled}>
				
				{foreach from=$last_parties item="party"}
				<option value="{$party.partyId}">{$party.PartyName|escape}</option>
				{/foreach}
				</select>
				</TD></TR>

				<TR><TD>Name des Beitrages</TD><TD><input type='text' name='iName' size='40' maxlength='100' value='{if $sLogin|escape ne ""}{$sLogin|escape}&#39;s Video{/if}' {$form_disabled}> *</TD></TR>

				<TR><TD>YouTube Link</TD><TD><input type='text' name='iurl' size='40' maxlength='50' {$form_disabled} value='http://www.youtube.com/watch?v='></TD></TR>

				<TR><TD valign="top">Kommentar f&uuml;r den Admin</TD><TD><textarea name="iKommentar" wrap="virtual" cols="35" rows="2" maxlength="1000" {$form_disabled}></textarea></TD></TR>

				<tr><td colspan="2" height="40"><input type="submit" value="Daten &uuml;bertragen" name="knopf" {$form_disabled}>

			</TABLE></p>
			</form>
			<p>
			* Erscheint in der &Uuml;bersichtsliste des Archives
			</p>

		</div>
		<div class='kasten-footer'>
			<div class='ecke-ul'></div>
			<div class='ecke-ur'></div>
		</div>
	</div>
</td></tr>
</table>