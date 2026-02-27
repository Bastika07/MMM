<?php
require('controller.php');
require_once "dblib.php";
$iRecht = "ARCHIVADMIN";
include "checkrights.php";
include_once("format.php");
include "admin/vorspann.php";

$dbh = DB::connect();



if ($_GET['action'] == "preview" || $_GET['action'] == "delete" || $_GET['action'] == "release" || $_GET['action'] == "lock") {
	// Daten des Beitrages heraussuchen und dabei gleich Rechte checken
	$sWhere = "select distinct a.BESCHREIBUNG, a.LINK, a.TYP, a.ARCHIVID, a.KOMMENTAR ".
		"from ARCHIV a, RECHTZUORDNUNG r ".
		"where r.MANDANTID=a.MANDANTID ".
		"and r.USERID='".intval($loginID)."' ".
		"and r.RECHTID='ARCHIVADMIN' ".
		"and a.ARCHIVID='".intval($_GET['id'])."'";
	$result= DB::query($sWhere);
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";
	$row = $result->fetch_array();
	
	// Verzeichnisse
	// Temporär
	$sDirTemp = ARCHIV_UPLOADDIR.$row['ARCHIVID']."/";
	// Live
	$sLiveDir = PELASDIR."/archiv/_".$row['TYP']."/".$row['ARCHIVID']."/";

}


if ($_GET['action'] == "release") {
	// Freigeben
	if ($row['ARCHIVID'] > 0) {
		// Ok, Freigabe
		// Status in DB setzen
		DB::query("update ARCHIV set LOCKED='no' where ARCHIVID='".$row['ARCHIVID']."'");
		if ($row['TYP'] == "img") {
			exec("mv $sDirTemp ".PELASDIR."/archiv/_".$row['TYP']."/");
			#echo("mv $sDirTemp ".PELASDIR."/archiv/_".$row['TYP']."/");
		}
		//echo "<pre>mv $sDirTemp ".PELASDIR."/archiv/_".$row['TYP']."/</pre>";
		echo "<p>Der Beitrag wurde freigegeben.</p>\n";		
	} else {
		// Keine Berechtigung
		echo "<p class=\"fehler\">Fehler: Keine Berechtigung</p>";
	}

	echo "<p><a href=\"archivadmin2.php\">zur&uuml;ck zum Archivadmin</a></p>\n";

} elseif ($_GET['action'] == "lock") {
	// Sperren
	if ($row['ARCHIVID'] > 0) {
		// Ok, Sperren
		DB::query("update ARCHIV set LOCKED='yes' where ARCHIVID='".$row['ARCHIVID']."'");
		if ($row['TYP'] == "img") {
			@exec("mv $sLiveDir ".ARCHIV_UPLOADDIR);
		}
		//echo "<pre>mv $sLiveDir ".ARCHIV_UPLOADDIR."/_oldarchiv/</pre>";
		echo "<p>Der Beitrag wurde gesperrt.</p>\n";
	} else {
		// Keine Berechtigung
		echo "<p class=\"fehler\">Fehler: Keine Berechtigung</p>";
	}
	
	echo "<p><a href=\"archivadmin2.php\">zur&uuml;ck zum Archivadmin</a></p>\n";

} elseif ($_GET['action'] == "preview") {
	// Voransicht
	
	echo "<p><b>Preview</b> des Beitrages <b>&quot;".db2display($row['KOMMENTAR'])."&quot;</b>:</p>\n";

	echo "<p>Kommentar des Autors: ".db2display($row['BESCHREIBUNG'])."</p>";

	if ($row['TYP'] == "img") {

		echo "<table cellpadding=\"$cCellp\" cellspacing=\"1\" border=\"0\">";

		echo "<tr>";

		//### Bilder und Zeitungsartikel aus Dateisystem lesen
		$sDir = ARCHIV_UPLOADDIR.$row['ARCHIVID']."/";
		exec("ls $sDir",$Slines,$Src);
		$Scount = count($Slines) - 1;
		$counter = 0;
		$j = 0;
		for ($Si = 0; $Si <= $Scount ; $Si++) {
			if (substr($Slines[$Si],0,3) == "tn_") {
				if ( $j == 3 ) { $j = 0; echo "</tr>\n<tr>"; }
				$j++;

				echo "<td width=\"33%\" align=center class=\"TNListeTDA\" valign=\"top\"><a target=\"_blank\" href=\"".$sTheURL."\"><img src=\"archiv_preview_pic2.php?nGallery=".$row['ARCHIVID']."&sPic=".urlencode($Slines[$Si])."\" border=0 alt=\"".$row4[KOMMENTAR]."\"></a><br>".db2display($row4[KOMMENTAR])."</td>";
				$counter ++;
			}
		}
		echo "</tr></table>";

	} elseif ($row['TYP'] == "youtube") {
		echo "<br><br><object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube-nocookie.com/v/".$row['LINK']."&hl=de&fs=1&rel=0\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube-nocookie.com/v/".$row['LINK']."&hl=de&fs=1&rel=0\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"425\" height=\"344\"></embed></object>";
	}


} elseif ($_GET['action'] == "delete") {
	// Beitrag loeschen
	
	if ($row['ARCHIVID'] < 1) {
		// Keine Berechtigung
		echo "<p class=\"fehler\">Fehler: Keine Berechtigung</p>";
	} elseif ($_POST['iDelete'] != "yes") {
		//Sicherheitsabfrage anzeigen
		?>
		<form method="post" action="archivadmin2.php?action=<?= htmlspecialchars($_GET['action'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>&id=<?= intval($_GET['id']) ?>" name="data">
		<?= csrf_field() ?>

		<input type="hidden" name="iDelete" value="yes">

		<p>M&ouml;chtest Du wirklich den Beitrag <b>&quot;<?php echo db2display($row['KOMMENTAR']); ?>&quot;</b> l&ouml;schen?</p>

		<input value="Ja" type="submit"> <input value="nein" type="button" OnClick="javascript:window.history.back();">

		</form>

		<?php
	} else {
		// Loeschen
		DB::query("delete from ARCHIV where ARCHIVID='".$row['ARCHIVID']."'");
		
		// Bei Bildern, Zeitung und Viodeos Verzeichnisse löschen
		if ($row['TYP'] == "img") {
			// Temp-Dir, existiert bei freigeschalteten Beiträgen nicht
			@exec("rm -R $sDirTemp");
			//echo "<pre>rm -R $sDirTemp</pre><br>";
			
			// Main-Dir, existiert bei gesperrten Beiträgen nicht
			@exec("rm -R $sLiveDir");
			//echo "<pre>rm -R $sLiveDir</pre>";
		}
		
		echo "<p>Der Beitrag wurde gel&ouml;scht.</p>\n";
		echo "<p><a href=\"archivadmin2.php\">zur&uuml;ck zum Archivadmin</a></p>\n";
	}
	
} else {

	?>
  
  <p><small><a class="arrow" href="archivadmin.php"> Hier geht es zum alten Archiv vor 2009</a></small></p>
  
	<table cellspacing="0" cellpadding="0" border="0">
	<tr><td class="navbar">
	<table width="100%" cellspacing="1" cellpadding="3" border="0">

	<form method="post" action="archivadmin2.php">
	<?= csrf_field() ?>

	<input type="hidden" name="iGo" value="yes">

	<tr><td class="navbar" colspan="6"><b>Filtereinstellungen</b></td></tr>
	<tr>
		<td class="dblau">ID </td><td class="hblau"><input type="text" name="iId" size=5 maxlength=10 value="<?php echo $i_POST['Id'];?>"></td>
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
		<td class="hblau">Bilder/Videos</td>
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
				if ($_POST['iMandant'] == $row['MANDANTID']) {echo " selected";}
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
	if ($_POST['iStatus'] != "-1") $sAddWhere.=" and a.LOCKED='".intval($_POST['iStatus'])."'";
	if ($_POST['iTyp'] > 0) $sAddWhere.=" and a.TYP='".intval($_POST['iTyp'])."'";
	if ($_POST['iId'] > 0) $sAddWhere.=" and a.ARCHIVID='".intval($_POST['iId'])."'";
	if ($_POST['iAutor'] != "") $sAddWhere.=" and u.LOGIN like '%$".$_POST['iAutor']."%''";

	$bgc="hblau";

	$sAddWhereMandant = "";
	if ($_POST['iMandant'] > 0) {
		$sAddWhereMandant = " p.MANDANTID='".intval($_POST['iMandant'])."' and a.MANDANTID='".intval($_POST['iMandant'])."'";
	} else {
		$sAddWhereMandant = " r.MANDANTID=a.MANDANTID and p.MANDANTID=r.MANDANTID and r.USERID='".intval($loginID)."' and r.RECHTID='ARCHIVADMIN'";
	}
/*
	$sWhere = "select distinct a.LINK, a.PARTYID, m.REFERER, p.beschreibung as NAME, u.LOGIN, u.EMAIL, a.ARCHIVID, a.USERID, a.TYP, a.LOCKED, a.KOMMENTAR, a.WANNANGELEGT ".
		"from MANDANT m, ARCHIV a, USER u, party p, RECHTZUORDNUNG r ".
		"where m.MANDANTID = a.PARTYID and a.PARTYID=p.partyid $sAddWhereMandant and u.USERID=a.USERID $sAddWhere ".
		"order by $sortierung";
*/
	$sWhere = "select
		     	a.LINK, a.PARTYID, m.REFERER, p.beschreibung as NAME, u.LOGIN, u.EMAIL, 
			a.ARCHIVID, a.USERID, a.TYP, a.LOCKED, a.KOMMENTAR, a.WANNANGELEGT 
		   from 
			MANDANT m, ARCHIV a, USER u, party p ". ($_POST['iMandant'] > 0 ? '' : ', RECHTZUORDNUNG r ').
		   "where 
			m.MANDANTID = a.MANDANTID and 
			a.PARTYID=p.partyid and
			$sAddWhereMandant and 
			u.USERID=a.USERID
			$sAddWhere 
		   order by 
			".safe($_POST['sortierung']);
			
//die($sWhere);
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
				echo "<a href='archivadmin2.php?action=preview&id=",$row['ARCHIVID'],"'>Preview</a>";
			} else {
				echo "<a href='".$row['REFERER']."?page=14&selectPartyID=".$row['PARTYID']."&selectTyp=".$row['TYP']."&archivID=",$row['ARCHIVID'],"' target=\"_blank\">View</a>";
			}
		}

		if ($row['LOCKED'] == "yes") {
			echo " | <a href='archivadmin2.php?action=release&id=",$row['ARCHIVID'],"'>Release</a>";
		} else {
			echo " | <a href='archivadmin2.php?action=lock&id=",$row['ARCHIVID'],"'>Lock</a>";
		}
		
		echo " | <a href='archivadmin2.php?action=delete&id=",$row['ARCHIVID'],"'>Del.</a>";

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
