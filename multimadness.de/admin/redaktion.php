<?php
require('controller.php');
require_once "dblib.php";
$iRecht = $KATEGORIEINFO[$_GET['nKategorieID']][1];
include "checkrights.php";
include('format.php');
include "admin/vorspann.php";

echo "<h1>Redaktion Kategorie ".htmlspecialchars($KATEGORIEINFO[$_GET['nKategorieID']][0] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."</h1>";

function show_form ()
{
	global $KATEGORIE_MAILING, $KATEGORIEINFO, $loginID, $dbname;

	echo "<form method=\"post\" action=\"redaktion.php?nKategorieID=".intval($_GET['nKategorieID'])."&iAction=".htmlspecialchars($_GET['iAction'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."&nInhaltID=".intval($_GET['nInhaltID'])."\" name=\"data\">";
	echo csrf_field() . "\n";
	echo "<input type=\"hidden\" name=\"iParent\" value=\"".htmlspecialchars($_POST['Parent'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')."\">";

	if ($_GET['nKategorieID'] == $KATEGORIE_MAILING) {
		echo "<p>Folgende Platzhalter sind möglich: <ul><li> %nick% für Nickname im Betreff und Text</li><li>%cancel% für die URL zum Newsletter abbestellen im Text</li></ul></p>";
	}
	
	echo "<table cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "<tr><td class=\"navbar\">\n";
	echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\">\n";
	
	if ($_GET['nInhaltID'] > 0) {
		$sTitel = "Inhalt &auml;ndern";
	} else {
		$sTitel = "Inhalt anlegen";
	}
	
	echo "<tr><td class=\"navbar\" colspan=\"2\"><b>$sTitel</b></td></tr>\n";
	
	echo "<tr><td class=\"dblau\" colspan=\"2\">";
	
	if ($_GET['nInhaltID'] > 0) {
		echo "Die Zuordnung zu Mandanten kann noch nicht ge&auml;ndert werden.";
	} else {
		$rowCount = 1;
			$sql = "select m.MANDANTID, 
						m.BESCHREIBUNG 
					from MANDANT m, 
						RECHTZUORDNUNG r 
					where r.USERID=".intval($loginID)."
						and r.MANDANTID=m.MANDANTID 
						and r.RECHTID='".$KATEGORIEINFO[$_GET['nKategorieID']][1]."'
			";
			$result = DB::query($sql);
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\"><tr>";
			while ($row = $result->fetch_array()) {
				echo "<td><input type=\"checkbox\" name=\"sendToMandant[]\" value=\"$row[MANDANTID]\"> $row[BESCHREIBUNG]</td>";
				$rowCount++;
				if ($rowCount > 2) {
					$rowCount = 1;
					echo "</tr><tr>";
				}
			}
			echo "</tr></table>";
	}
	echo "</td></tr>";
	echo "<tr><td class=\"navbar\" colspan=\"2\">&nbsp;</td></tr>\n";
	
?>
	<TR><TD class="dblau">Titel</TD><TD class="hblau"><input type="text" name="iBeitragstitel" size="60" maxlength="150" value="<?php echo htmlspecialchars($_POST['iBeitragstitel'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>"> *</TD></TR>
	
	<TR><TD valign="top" class='dblau'>Beitrag</TD><TD class='hblau'><textarea name="iBeitrag" wrap="virtual" cols="65" rows="16"><?php echo htmlspecialchars($_POST['iBeitrag'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></textarea> *</TD></TR>
	
	<tr><td colspan="2" class="dblau" align="center"><input type="submit" value="Speichern" name="knopf"></td></tr>
</TABLE>
</td></tr>
</table>
</p>
</form>

<?php
}

if (!isset($_POST['iBeitragstitel']) && $_GET['iAction'] == "edit") {
	$sql = "select INHALTID, TITEL, DERINHALT from INHALT where KATEGORIEID=".intval($_GET['nKategorieID'])." and INHALTID=".intval($_GET['nInhaltID']);
	$result = DB::query($sql);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$row = $result->fetch_array();
	$_POST['iBeitragstitel'] = $row['TITEL'];
	$_POST['iBeitrag']       = $row['DERINHALT'];
	show_form();
} elseif (!isset($_POST['iBeitragstitel']) ) {
	show_form();
} else {
	if ( empty($_POST['iBeitragstitel']) || empty($_POST['iBeitrag']) ) {
		echo "<p class='fehler'>Du musst alle Felder ausf&uuml;llen.</p>";
		show_form();
	} else {
		if ($loginID != "") {
			$Nick2db = $login;
			$Userid2db = $loginID;
		}
		$itext = $_POST['iBeitrag'];
		
		if ($_GET['nKategorieID'] == $KATEGORIE_NEWS || $_GET['nKategorieID'] == $KATEGORIE_MAILING) {
			$sAktiv = "N";
		} else {
			$sAktiv = "J";
		}
		
		if ($_GET['iAction'] == "new") {
			//Neu anlegen
			if ( sizeof($_POST['sendToMandant']) <= 0) {
				echo "<p class='fehler'>Bitte w&auml;hle mindestens einen Mandanten f&uuml;r diesen Inhalt aus.</p>";
				show_form();
			} else {
				for ($i=0; $i<sizeof($_POST['sendToMandant']); $i++) {
					$sql = "INSERT INTO INHALT (MANDANTID, TITEL, DERINHALT, AUTOR, AUTORNAME, KATEGORIEID, AKTIV, WERANGELEGT, WANNANGELEGT, WERGEAENDERT ) values (".intval($_POST['sendToMandant'][$i]).", '".safe($_POST['iBeitragstitel'])."', '".safe($itext)."', ".intval($Userid2db).", '".safe($Nick2db)."', ".intval($_GET['nKategorieID']).", '$sAktiv', ".intval($loginID)." , NOW(), ".intval($loginID)." )";
					$result = DB::query($sql);
					//echo DB::$link->errno.": ".DB::$link->error."<BR>";
				}
			}
		} else {
			//aendern
			$sql = "update INHALT set TITEL='".safe($_POST['iBeitragstitel'])."', DERINHALT='".safe($itext)."', WERGEAENDERT=".intval($loginID)." where INHALTID=".intval($_GET['nInhaltID'])." and KATEGORIEID=".intval($_GET['nKategorieID']);
			$result = DB::query($sql);
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		}
		//zurueckleiten
		if ($ComeFrom == -1) {$NewAction = 1;} else {$NewAction = 2;}
		echo "<p>Beitrag erfasst.</p>";
		//echo "<meta http-equiv='Refresh' content='4; URL=forum.php3?Action=$NewAction&Parent=$ComeFrom'>";	
	}
}

include "admin/nachspann.php";
?>
