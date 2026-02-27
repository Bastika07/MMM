<table cellspacing="0" cellpadding="0" width="99%">
<tr><td colspan="2">
	<div class='kasten'>
		<div class='ecke-or'></div>
		<div class='ecke-ol'></div>
		<h3 class='kasten'>Medienupload: Bilder</h3>
		<div class='kastentext'>



			<p align="justify">So gehts: <b>Alle Bilder</b> (*.jpg) in eine <b>ZIP-Datei</b> (*.zip, <b>kein</b> *.rar) packen
			und dann hochladen. Unser System entpackt die Datei automatisch und erstellt dazugeh&ouml;rige Thumbnails.
			</p>

			<p><b>Wichtig:</b> 
			<ul>
			<li>Der Upload kann sehr lange dauern, bitte nicht abbrechen.</li>
			<li>Maximale Größe für den Upload: {$upload_max_filesize}</li>
			{if $form_disabled}
			<li><p class=fehler>Fehler: Um einen Beitrag hochzuladen musst Du eingeloggt sein.</p></li>
			{/if}
			</ul>
			</p>

			<p><table cellspacing="1" cellpadding="3" border="0">


			<form enctype="multipart/form-data" method="post" action="?page=15&action=doUpload" name="data">
			<input type="hidden" name="MAX_FILE_SIZE" value="50000000">
			<TR><TD>Autor</TD><TD>{$sLogin|escape}</TD></TR>

			<TR><TD>Party</TD><TD>
			<select name="iParty" {$form_disabled}>
				
				{foreach from=$last_parties item="party"}
				<option value="{$party.partyId}">{$party.PartyName|escape}</option>
				{/foreach}
				</select>
				</TD></TR>

				<TR><TD>Name des Beitrages</TD><TD><input type='text' name='iName' size='40' maxlength='100' value='{if $sLogin|escape ne ""}{$sLogin|escape}&#39;s Stuff{/if}' {$form_disabled}> *</TD></TR>

				<TR><TD>Dateiname</TD><TD><input type='file' name='userfile' size='40' maxlength='250' {$form_disabled}></TD></TR>

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