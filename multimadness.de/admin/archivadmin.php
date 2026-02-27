<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "ARCHIVADMIN";
include "checkrights.php";
include_once("format.php");
include "admin/vorspann.php";

echo "<h1>ALT: Verwaltung Archiv</h1>";
echo "<p><b>Wichtig:</b> Dies ist die Verwaltung für das alte Archiv. Bitte nur benutzen, wenn ausdrücklich
alte Archivdatensätze bearbeitet werden müssen. Im Dateibaum ist den alten Datensätzen ein Underscore vorgestellt.</p>";

$dbh = DB::connect();

if ($_GET['action'] == "preview" || $_GET['action'] == "delete" || $_GET['action'] == "release" || $_GET['action'] == "lock") {
	// Daten des Beitrages heraussuchen und dabei gleich Rechte checken
	$sWhere = "select distinct a.BESCHREIBUNG, a.LINK, a.TYP, a.ARCHIVID, a.KOMMENTAR ".
		"from ARCHIV_INFO a, RECHTZUORDNUNG r ".
		"where r.MANDANTID=a.PARTYID ".
		"and r.USERID='".intval($loginID)."' ".
		"and r.RECHTID='ARCHIVADMIN' ".
		"and a.ARCHIVID='".intval($_GET['id'])."'";
	$result= DB::query($sWhere);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$row = $result->fetch_array();
	
	// Verzeichnisse
	// Temporär
	$sDirTemp = ARCHIV_UPLOADDIR."/_oldarchiv/".$row['ARCHIVID']."/";
	// Live
	$sLiveDir = PELASDIR."/archiv/_".$row['TYP']."/".$row['ARCHIVID']."/";
}


if ($_GET['action'] == "release") {
	// Freigeben
	if ($row['ARCHIVID'] > 0) {
		// Ok, Freigabe
		// Status in DB setzen
		DB::query("update ARCHIV_INFO set LOCKED='no' where ARCHIVID='".$row['ARCHIVID']."'");
		if ($row['TYP'] != $KATEGORIE_ARCH_LINK) {
			@exec("mv $sDirTemp ".PELASDIR."/archiv/_".$row['TYP']."/");
		}
		//echo "<pre>mv $sDirTemp ".PELASDIR."/archiv/_".$row['TYP']."/</pre>";
		echo "<p>Der Beitrag wurde freigegeben.</p>\n";		
	} else {
		// Keine Berechtigung
		echo "<p class=\"fehler\">Fehler: Keine Berechtigung</p>";
	}

	echo "<p><a href=\"archivadmin.php\">zur&uuml;ck zum Archivadmin</a></p>\n";

} elseif ($_GET['action'] == "lock") {
	// Sperren
	if ($row['ARCHIVID'] > 0) {
		// Ok, Sperren
		DB::query("update ARCHIV_INFO set LOCKED='yes' where ARCHIVID='".$row['ARCHIVID']."'");
		if ($row['TYP'] != $KATEGORIE_ARCH_LINK) {
			@exec("mv $sLiveDir ".ARCHIV_UPLOADDIR."/_oldarchiv/");
		}
		//echo "<pre>mv $sLiveDir ".ARCHIV_UPLOADDIR."/_oldarchiv/</pre>";
		echo "<p>Der Beitrag wurde gesperrt.</p>\n";
	} else {
		// Keine Berechtigung
		echo "<p class=\"fehler\">Fehler: Keine Berechtigung</p>";
	}
	
	echo "<p><a href=\"archivadmin.php\">zur&uuml;ck zum Archivadmin</a></p>\n";

} elseif ($_GET['action'] == "preview") {
	// Voransicht
	
	echo "<p><b>Preview</b> des Beitrages <b>&quot;".db2display($row['KOMMENTAR'])."&quot;</b>:</p>\n";

	echo "<p>Kommentar des Autors: ".db2display($row['BESCHREIBUNG'])."</p>";

	echo "<table cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\">";

	echo "<tr>";

	//### Bilder und Zeitungsartikel aus Dateisystem lesen
	$sDir = ARCHIV_UPLOADDIR."/_oldarchiv/".$row['ARCHIVID']."/";
	exec("ls $sDir",$Slines,$Src);
	$Scount = count($Slines) - 1;
	$counter = 0;
	$j = 0;
	for ($Si = 0; $Si <= $Scount ; $Si++) {
		if (substr($Slines[$Si],0,3) == "tn_") {
			if ( $j == 3 ) { $j = 0; echo "</tr>\n<tr>"; }
			$j++;

			//### Für Kategorie Video Download aktivieren
			//### Name des Files steht im Feld Link
			if ($row['TYP'] == $KATEGORIE_ARCH_VIDEOS) {
				$sTheURL = "archiv_preview_pic.php?nGallery=".$row['ARCHIVID']."&sPic=".urlencode($row['LINK']);
			} else {
				$sTheURL = "archiv_preview_pic.php?nGallery=".$row['ARCHIVID']."&sPic=".urlencode(substr($Slines[$Si],3));
			}
			echo "<td width=\"33%\" align=center class=\"TNListeTDA\" valign=\"top\"><a target=\"_blank\" href=\"".$sTheURL."\"><img src=\"archiv_preview_pic.php?nGallery=".$row['ARCHIVID']."&sPic=".urlencode($Slines[$Si])."\" border=0 alt=\"".$row4[KOMMENTAR]."\"></a><br>".db2display($row4[KOMMENTAR])."</td>";
			$counter ++;
		}
	}
	echo "</tr></table>";
	if ($counter < 1 && $row['TYP'] == $KATEGORIE_ARCH_VIDEOS) {
		echo "<p>Keine Preview-Bilder vorhanden, Download <a href=\"archiv_preview_pic.php?nGallery=".$row['ARCHIVID']."&sPic=".urlencode($row['LINK'])."\">hier</a>.</p>";
	} elseif ($counter < 1 ) {
		echo "<p class=\"fehler\">Fehler: Kann gew&auml;hlte Kategorie nicht anzeigen!</p>";
	}

} elseif ($_GET['action'] == "delete") {
	// Beitrag loeschen
	
	if ($row['ARCHIVID'] < 1) {
		// Keine Berechtigung
		echo "<p class=\"fehler\">Fehler: Keine Berechtigung</p>";
	} elseif ($_POST['iDelete'] != "yes") {
		//Sicherheitsabfrage anzeigen
		?>
		<form method="post" action="archivadmin.php?action=<?= htmlspecialchars($_GET['action'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>&id=<?= intval($_GET['id']) ?>" name="data">

		<input type="hidden" name="iDelete" value="yes">

		<p>M&ouml;chtest Du wirklich den Beitrag <b>&quot;<?php echo db2display($row['KOMMENTAR']); ?>&quot;</b> l&ouml;schen?</p>

		<input value="Ja" type="submit"> <input value="nein" type="button" OnClick="javascript:window.history.back();">

		</form>

		<?php
	} else {
		// Loeschen
		DB::query("delete from ARCHIV_INFO where ARCHIVID='".$row['ARCHIVID']."'");
		
		// Bei Bildern, Zeitung und Viodeos Verzeichnisse löschen
		if ($row['TYP'] == $KATEGORIE_ARCH_VIDEOS || $row['TYP'] == $KATEGORIE_ARCH_BILDER || $row['TYP'] == $KATEGORIE_ARCH_ZEITUNG) {
			// Temp-Dir, existiert bei freigeschalteten Beiträgen nicht
			@exec("rm -R $sDirTemp");
			//echo "<pre>rm -R $sDirTemp</pre><br>";
			
			// Main-Dir, existiert bei gesperrten Beiträgen nicht
			@exec("rm -R $sLiveDir");
			//echo "<pre>rm -R $sLiveDir</pre>";
		}
		
		echo "<p>Der Beitrag wurde gel&ouml;scht.</p>\n";
		echo "<p><a href=\"archivadmin.php\">zur&uuml;ck zum Archivadmin</a></p>\n";
	}
	
} else {

	?>
	<table cellspacing="0" cellpadding="0" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">

	<form method="post" action="archivadmin.php">

	<input type="hidden" name="iGo" value="yes">

	<tr><td class="navbar" colspan="6"><b>Filtereinstellungen</b></td></tr>
	<tr>
		<td class="dblau">ID </td><td class="hblau"><input type="text" name="iId" size=5 maxlength=10 value="<?= intval($_POST['iId'] ?? 0) ?: '' ?>"></td>
		<td class="dblau">Autor </td><td class="hblau"><input type="text" name="iAutor" size=25 maxlength=30 value="<?= htmlspecialchars($_POST['iAutor'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
		<td class="dblau">Status </td>
		<td class="hblau"><select name="iStatus">
		<option value="-1">Alle
		<option value="yes"
			<?php
			if ($_POST['iStatus'] == "yes") {
				echo " selected";
			}
			?>
		>Gesperrt
		<option value="no"
			<?php
			if ($_POST['iStatus'] == "no") {
				echo " selected";
			}
			?>
		>Freigegeben
		</select></td>
	</tr><tr>
		<td class="dblau">Typ </td>
		<td class="hblau"><select name="iTyp">
			<option value="-1">Alle
			<option value="<?=$KATEGORIE_ARCH_VIDEOS?>"
			<?php
			if ($_POST['iTyp'] == $KATEGORIE_ARCH_VIDEOS) {
				echo " selected";
			}
			?>
			>Videos
			<option value="<?=$KATEGORIE_ARCH_BILDER?>"
			<?php
			if ($_POST['iTyp'] == $KATEGORIE_ARCH_BILDER) {
				echo " selected";
			}
			?>
			>Bilder
			<option value="<?=$KATEGORIE_ARCH_ZEITUNG?>"
			<?php
			if ($_POST['iTyp'] == $KATEGORIE_ARCH_ZEITUNG) {
				echo " selected";
			}
			?>
			>Zeitung
			<option value="<?=$KATEGORIE_ARCH_TURNIER?>"
			<?php
			if ($_POST['iTyp'] == $KATEGORIE_ARCH_TURNIER) {
				echo " selected";
			}
			?>
			>Turnier
			<option value="<?=$KATEGORIE_ARCH_LINK?>"
			<?php
			if ($_POST['iTyp'] == $KATEGORIE_ARCH_LINK) {
				echo " selected";
			}
			?>
			>Link
		</select></td>
		<td class="dblau">Mandant</td>
		<td class="hblau"><select name="iMandant">
		<option value="-1"
		<?php
			if ($_POST['iMandant'] == -1) {echo " selected";}
		?>
		>Alle
		<?php
			$result= DB::query("select m.MANDANTID, m.BESCHREIBUNG from MANDANT m, RECHTZUORDNUNG r where r.MANDANTID=m.MANDANTID and r.USERID=".intval($loginID)." and r.RECHTID='ARCHIVADMIN'");
			//echo DB::$link->errno.": ".DB::$link->error."<BR>";
			while ($row = $result->fetch_array()) {
				echo "<option value=\"$row[MANDANTID]\"";
				if ($_POST['iMandant'] == $row[MANDANTID]) {echo " selected";}
				echo ">$row[BESCHREIBUNG]";
			}
		?>
		</select></td>
		<td class="dblau">Sortierung </td>
		<td class="hblau"><select name="sortierung">
		<option value="WANNANGELEGT desc" <?php if ($_POST['sortierung'] == "WANNANGELEGT desc") {echo "selected";} ?>>Erstelldatum
		<option value="ARCHIVID" <?php if ($_POST['sortierung'] == "ARCHIVID") {echo "selected";} ?>>Archiv ID
		<option value="USERID" <?php if ($_POST['sortierung'] == "USERID") {echo "selected";} ?>>Autor
		<option value="LOCKED" <?php if ($_POST['sortierung'] == "LOCKED") {echo "selected";} ?>>Status
		<option value="TYP" <?php if ($_POST['sortierung'] == "TYP") {echo "selected";} ?>>Typ
		<option value="MANDANTID" <?php if ($_POST['sortierung'] == "MANDANTID") {echo "selected";} ?>>Mandant
		<option value="a.LFDNR desc" <?php if ($_POST['sortierung'] == "a.LFDNR desc") {echo "selected";} ?>>Party
		</select></td>
	</tr><tr>
		<td colspan="6" class="dblau" align="center"><input type="submit" value="Beitr&auml;ge auflisten"></td>
	</tr>
	</form>
	</table></td></tr></table>

	<?php 

	if ($_POST['iGo'] == "yes" ) {

	?>
	<br>
	<table cellspacing="0" cellpadding="0" width="98%" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">
	<tr>
		<td class="navbar"><b>ID</b></td>
		<td class="navbar"><b>Titel</a></b></td>
		<td class="navbar"><b>Party</b></td>
		<td class="navbar"><b>Typ</b></td>
		<td class="navbar"><b>Gesperrt</b></td>
		<td class="navbar"><b>Autor</b></td>
		<td class="navbar"><b>Angelegt</b></td>
		<td class="navbar"><b>Action</b></td>
	</tr>
	<?php

	if ($_POST['sortierung']=="") $_POST['sortierung']="a.WANNANGELEGT";
	if ($_POST['iStatus'] != "-1") $sAddWhere.=" and a.LOCKED='".safe($_POST['iStatus'])."'";
	if ($_POST['iTyp'] > 0) $sAddWhere.=" and a.TYP='".intval($_POST['iTyp'])."'";
	if ($_POST['iId'] > 0) $sAddWhere.=" and a.ARCHIVID='".intval($_POST['iId'])."'";
	if ($_POST['iAutor'] != "") $sAddWhere.=" and u.LOGIN like '%".safe($_POST['iAutor'])."%'";

	$bgc="hblau";

	$sAddWhereMandant = "";
	if ($_POST['iMandant'] > 0) {
		$sAddWhereMandant = " p.MANDANTID='".intval($_POST['iMandant'])."' and a.PARTYID='".intval($_POST['iMandant'])."'";
	} else {
		$sAddWhereMandant = " r.MANDANTID=a.PARTYID and p.MANDANTID=r.MANDANTID and r.USERID='".intval($loginID)."' and r.RECHTID='ARCHIVADMIN'";
	}

	$sWhere = "select distinct a.LINK, a.PARTYID, m.REFERER, p.NAME, u.LOGIN, u.EMAIL, a.ARCHIVID, a.USERID, a.TYP, a.LOCKED, a.KOMMENTAR, a.WANNANGELEGT ".
		"from MANDANT m, ARCHIV_INFO a, USER u, PARTYHISTORIE p, RECHTZUORDNUNG r ".
		"where m.MANDANTID = a.PARTYID and p.LFDNR=a.LFDNR $sAddWhereMandant and u.USERID=a.USERID $sAddWhere ".
		"order by ".safe($_POST['sortierung']);

	$sWhere = "select
		     	a.LINK, a.PARTYID, m.REFERER, p.NAME, u.LOGIN, u.EMAIL, 
			a.ARCHIVID, a.USERID, a.TYP, a.LOCKED, a.KOMMENTAR, a.WANNANGELEGT 
		   from 
			MANDANT m, ARCHIV_INFO a, USER u, PARTYHISTORIE p ". ($_POST['iMandant'] > 0 ? '' : ', RECHTZUORDNUNG r ').
		   "where 
			m.MANDANTID = a.PARTYID and 
			p.LFDNR=a.LFDNR and
			$sAddWhereMandant and 
			u.USERID=a.USERID
			$sAddWhere 
		   order by 
			".safe($_POST['sortierung']);

	$result = DB::query($sWhere);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";

//	DB::outputQueryStatistic();

	while ($row = $result->fetch_array()) {
		$sDatum = $row['WANNANGELEGT'];
		$date2Display = substr($sDatum,8,2).".".substr($sDatum,5,2).".".substr($sDatum,0,4)." ".substr($sDatum,11,2).":".substr($sDatum,14,2);

		echo "<tr>";
		echo "<td class=\"$bgc\">".$row['ARCHIVID']."&nbsp;</td>";
		echo "<td class=\"$bgc\">".db2display($row['KOMMENTAR'])."&nbsp;</a></td>";
		echo "<td class=\"$bgc\">".db2display($row['NAME'])."&nbsp;</td>";
		echo "<td class=\"$bgc\">",db2display($row['TYP']),"&nbsp;</td>";
		echo "<td class=\"$bgc\">",db2display($row['LOCKED']),"&nbsp;</td>";
		echo "<td class=\"$bgc\"><a href=\"mailto:".$row['EMAIL']."?subject=Party-Archiv ".db2display($row['NAME'])."\">",db2display($row['LOGIN']),"</a>&nbsp;</td>";
		echo "<td class=\"$bgc\"><nobr>",$date2Display,"</nobr></td>";
		echo "<td class=\"$bgc\"><nobr>";
		
		if ($row['TYP'] == $KATEGORIE_ARCH_LINK) {
			echo "<a href=\"".$row['LINK']."\" target=\"_blank\">Visit</a>";
		} elseif ($row['TYP'] == $KATEGORIE_ARCH_TURNIER) {
			echo "<a href='".PELASHOST."/archiv/".$row['TYP']."/".$row['ARCHIVID'].".php' target=\"_blank\">View</a>";
		} else {
			if ($row['LOCKED'] == "yes") {
				echo "<a href='archivadmin.php?action=preview&id=",$row['ARCHIVID'],"'>Preview</a>";
			} else {
				echo "<a href='".$row['REFERER']."/archiv.php?selectPartyID=".$row['PARTYID']."&selectTyp=".$row['TYP']."&archivID=",$row['ARCHIVID'],"' target=\"_blank\">View</a>";
			}
		}

		if ($row['LOCKED'] == "yes") {
			echo " | <a href='archivadmin.php?action=release&id=",$row['ARCHIVID'],"'>Release</a>";
		} else {
			echo " | <a href='archivadmin.php?action=lock&id=",$row['ARCHIVID'],"'>Lock</a>";
		}
		
		echo " | <a href='archivadmin.php?action=delete&id=",$row['ARCHIVID'],"'>Del.</a>";

		echo "</nobr></td>";
		echo "</tr>";
		if ($bgc=="hblau") $bgc="dblau"; else $bgc="hblau";
	}

	}

	?>

	</table></td></tr></table>

	<?php
//Ende Liste
}

include "admin/nachspann.php";
?>
