{* Smarty Template *}
<html><head>
<link rel="stylesheet" type="text/css" href="style/style.css">
<script type="text/javascript">
{literal}
	function checkNachtruhe(time) {
		var nachtruheStart = 3;
		var nachtruheEnd = 10;

		if (time.getHours() >= nachtruheStart && time.getHours() < nachtruheEnd) {
			time.setHours(nachtruheEnd);
			time.setMinutes(0);
		}
		return time;
	}
	function calculateRounds() {
		var timePerRound;
		var nachtruheBeachten;
		var isDE = false;
		var startElement;
		var overallFinalStart;
		var overallFinalEnd;
		var time = new Date();
        	var elem = document.getElementById('date').elements;
        	for (var i = 0; i < elem.length; i++) {
			var value = elem[i].value;
			switch (elem[i].name) {
				case 'Date_Day':
					time.setDate(value);
					break;
				case 'Date_Month':
					value -= 1;
					if (value == 0) {
						value = 12;
					}
					time.setMonth(value);
					break;
				case 'Time_Hour':
					time.setHours(value);
					break;
				case 'Time_Minute':
					time.setMinutes(value);
					break;
				case 'timePerRound':
					timePerRound = parseInt(value);
					break;
				case 'nachtruheBeachten':
					nachtruheBeachten = elem[i].checked;
					break;
			}
		}
		var startOfFirstRound = new Date(time);
        	var elem = document.getElementById('rounds').elements;
        	for (var i = 0; i < elem.length; i++) {
			// DE abfangen
			if (elem[i].name.indexOf("form[257]") !== -1) {
				// mit diesem Element unten bei der DE-Behandlung weitermachen
				// beinhaltet die Runde 2 des Loserbracket
				startElement = i;
				isDE = true;
				break;
			}
			if (nachtruheBeachten) {
				time = checkNachtruhe(time);
			}
			if (endsWith(elem[i].name, '[begins]')) {
				elem[i].value = formatDate(time);
				overallFinalStart = elem[i];
			} else if (endsWith(elem[i].name, '[ends]')) {
				time.setMinutes(time.getMinutes() + timePerRound);
				elem[i].value = formatDate(time);
				overallFinalEnd = elem[i];
			}
		}
		// DE machen
		if (isDE) {
			time = startOfFirstRound;
			// Mit der Zeit der 2. Runde anfangen
			time.setMinutes(time.getMinutes() + timePerRound);
			for (var i = startElement; i < elem.length; i++) {
				if (nachtruheBeachten) {
					time = checkNachtruhe(time);
				}
				if (endsWith(elem[i].name, '[begins]')) {
					elem[i].value = formatDate(time);
				} else if (endsWith(elem[i].name, '[ends]')) {
					time.setMinutes(time.getMinutes() + timePerRound);
					elem[i].value = formatDate(time);
				}
			}
			overallFinalStart.value = formatDate(time);
			time.setMinutes(time.getMinutes() + timePerRound);
			overallFinalEnd.value = formatDate(time);
		}
		return false;
	}

	function endsWith(str, suffix) {
		return str.indexOf(suffix, str.length - suffix.length) !== -1;
	}
	
	function formatDate(date) {
		var weekDay = new Array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
		var h = date.getHours();
		var m = date.getMinutes();
		return weekDay[date.getDay()] + ", " + (h<=9?'0'+h:h) + ":" + (m<=9?'0'+m:m);
	}
{/literal}
</script>
</head><body bgcolor="#FFFFFF">
<h1>Runden &Uuml;bersicht - {$turnier->name|escape}</h1>

<form method="post" name="save" id="rounds" action="{$smarty.server.PHP_SELF}?action=save&turnierid={$turnier->turnierid}">
<table cellspacing="0" cellpadding="0" width="600"><tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3">
{foreach key=roundid item=round from=$rounds}
  {if $roundid == 0}
    <tr><td class="navbar" colspan="4"><b>Winnerbracket</b></td><tr>
    <tr><td class="dblau"><b>Name</b></td>
    <td class="dblau"><b>Begins</b></td>
    <td class="dblau"><b>Ends</b></td>
    <td class="dblau"><b>Info</b>(z.B. default Map)</td><tr>
  {elseif $roundid == 257}
    <tr><td class="navbar" colspan="4"><b>Loserbracket</b></td><tr>
    <tr><td class="dblau"><b>Name</b></td>
    <td class="dblau"><b>Begins</b></td>
    <td class="dblau"><b>Ends</b></td>
    <td class="dblau"><b>Info</b>(z.B. default Map)</td><tr>
  {/if}
  <tr>
    <td class="hblau">{$round.name}</td>
    <td class="hblau"><input type="text" maxlength="16" name="form[{$roundid}][begins]" value="{$round.begins}"></td>
    <td class="hblau"><input type="text" maxlength="16" name="form[{$roundid}][ends]" value="{$round.ends}"></td>
    <td class="hblau"><input type="text" maxlength="16" name="form[{$roundid}][info]" value="{$round.info}"></td>
  </tr>
{foreachelse}
  <tr><td class="dblau" colspan="4">Keine Runden gefunden. Turnier muss erst erstellt werden</td><tr>
{/foreach}
</table></td></tr></table>
<br>
<input type="submit" value="Speichern">
</form>
<form id="date" onSubmit="calculateRounds(); return false;">
Start der ersten Runde: 
{html_select_date display_years=false field_order='DM'}, {html_select_time display_seconds=false} Uhr
<br>
Minuten pro Runde: <input type="text" value="45" id="timePerRound" name="timePerRound" />
<br>
Nachtruhe bei Startzeiten beachten: <input type="checkbox" id="nachtruheBeachten" name="nachtruheBeachten" />
<br>
<input type="button" value="Rundenzeiten ausrechnen" onclick="calculateRounds();">
</form>
</body></html>
