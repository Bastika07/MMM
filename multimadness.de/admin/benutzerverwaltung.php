<?php
require('controller.php');
require_once "dblib.php";
$iRecht = array("USERADMIN","USERADMIN_READONLY","EINLASSADMIN");
include "checkrights.php";
include('format.php');
require('admin/vorspann.php');

echo "<h1>Benutzerverwaltung</h1>";

if ($iDest == "") {
	$iDest = "dummy";
}

?>

	<script language="JavaScript">
	<!--
	function uebernehmeUser(uId) {
	    opener.document.forms.ticketForm.<?=$iDest?>.value = uId;
	    self.close();
	}
	//-->
	</script>
	

<p>Die Suche nach Login und Name ist eine Substring-Suche. D.h. alle Datens&auml;tze, die den
gesuchten Wert enthalten, werden angezeigt. Es wird nicht nach Gross- und Kleinschreibung unterschieden.</p>

<table cellspacing="0" cellpadding="0" border="0">
<tr><td class="navbar">
<table width="100%" cellspacing="1" cellpadding="3" border="0">

<form method="post" action="benutzerverwaltung.php">

<input type="hidden" name="iGo" value="yes">
<input type="hidden" name="iDest" value="<?=$iDest?>">

<tr><td class="navbar" colspan="6"><b>Filtereinstellungen</b></td></tr>
<tr>
	<td class="dblau">ID </td><td class="hblau"><input type="text" name="iId" size=5 maxlength=10 value="<?= intval($_POST['iId'] ?? 0) ?: '' ?>"></td>
	<td class="dblau">Login </td><td class="hblau"><input type="text" name="iLogin" size=25 maxlength=30 value="<?= htmlspecialchars($_POST['iLogin'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
	<td class="dblau">Vorname </td><td class="hblau"><input type="text" name="iName" size=25 maxlength=35 value="<?= htmlspecialchars($_POST['iName'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
</tr><tr>
	<td class="dblau">Nachname </td><td class="hblau"><input type="text" name="iNachname" size=25 maxlength=35 value="<?= htmlspecialchars($_POST['iNachname'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
	<td class="dblau">Email </td><td class="hblau"><input type="text" name="iEmail" size=25 maxlength=40 value="<?= htmlspecialchars($_POST['iEmail'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"></td>
	<td class="dblau">&nbsp; </td><td class="hblau">&nbsp;</td>
</tr><tr>
	<td class="dblau">Anmeldestatus </td>
	<td class="hblau"><select name="iAnmeldestatus">
	<option value="-1">Alle
	<?php
		$result = DB::query("select STATUSID, BESCHREIBUNG from STATUS");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		while ($row = $result->fetch_array()) {
			echo "<option value=\"$row[STATUSID]\"";
			if (isset($iAnmeldestatus) && $iAnmeldestatus == $row['STATUSID']) {echo " selected";}
			echo ">$row[BESCHREIBUNG]";
		}
	?>
	</select></td>
	<td class="dblau">Mandant</td>
	<td class="hblau"><select name="iMandant">
	<option value="-1"
	<?php
		if (isset($iMandant) && $iMandant == -1) {echo " selected";}
	?>
	>Alle
	<?php
		$result = DB::query("select distinct m.MANDANTID, m.BESCHREIBUNG from MANDANT m, RECHTZUORDNUNG r where r.MANDANTID=m.MANDANTID and r.USERID='".intval($loginID)."' and (r.RECHTID='USERADMIN' || r.RECHTID='USERADMIN_READONLY' || r.RECHTID='EINLASSADMIN')");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		while ($row = $result->fetch_array()) {
			echo "<option value=\"$row[MANDANTID]\"";
			if (isset($_POST['iMandant']) && $_POST['iMandant'] == $row['MANDANTID']) {echo " selected";}
			echo ">$row[BESCHREIBUNG]";
		}
	?>
	</select></td>
	<td class="dblau">Sortierung </td>
	<td class="hblau"><select name="sortierung">
	<option value="USERID" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "USERID") {echo "selected";} ?>>Benutzer ID
	<option value="LOGIN" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "LOGIN") {echo "selected";} ?>>Login
	<option value="NAME" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "NAME") {echo "selected";} ?>>Name
	<option value="EMAIL" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "EMAIL") {echo "selected";} ?>>Email
	<option value="WANNGEAENDERT desc" <?php if (isset($_POST['sortierung']) && $_POST['sortierung'] == "WANNGEAENDERT desc") {echo "selected";} ?>>Wann ge&auml;ndert
	</select></td>
</tr><tr>
	<td colspan="6" class="dblau" align="center"><input type="submit" value="Benutzer auflisten"></td>
</tr>
</form>
</table></td></tr></table>

<?php 

if (isset($_POST['iGo']) && $_POST['iGo'] == 'yes') {

	$sAddWhere = '';

	if (empty($_POST['iLogin']) && empty($_POST['iName']) && empty($_POST['iNachname']) && empty($_POST['iId']) && empty($_POST['iEmail']) && $_POST['iAnmeldestatus'] < 1 && $_POST['iMandant'] < 1) {
		echo "<p class=\"fehler\">Bitte zumindest auf ein Kriterium eingrenzen.</p>";
	} else {
		// Go, Liste anzeigen

		?>
		<br>
		<table cellspacing="0" cellpadding="0" width="98%" border="0">
		<tr><td class="navbar">
		<table width="100%" cellspacing="1" cellpadding="3" border="0">
		<tr>
			<td class="navbar"><b>ID</b></td>
			<td class="navbar"><b>Login/Benutzer</a></b></td>
			<td class="navbar"><b>Vor-/Nachname</b></td>
			<td class="navbar"><b>Email</b></td>
			<td class="navbar"><b>Modifiziert</b></td>
			<td class="navbar"><b>Action</b></td>
		</tr>
		<?php

		if (empty($_POST['sortierung'])) 
		  $sortierung="u.LOGIN";
		else 
		  $sortierung=$_POST['sortierung'];
		if (!empty($_POST['iLogin'])) {
		  $sAddWhere .= " and u.LOGIN like '%".safe($_POST['iLogin'])."%'";
		} 
		if (!empty($_POST['iName'])) {
		  $sAddWhere .= " and u.NAME like '%".safe($_POST['iName'])."%'";
		} 
		if (!empty($_POST['iNachname'])) {
		  $sAddWhere .= " and u.NACHNAME like '%".safe($_POST['iNachname'])."%'";
		} 
		if (!empty($_POST['iId'])) {
		  $sAddWhere .= " and u.USERID = '".intval($_POST['iId'])."'";
		} 
		if (!empty($_POST['iEmail'])) {
		  $sAddWhere .= " and u.EMAIL like '%".safe($_POST['iEmail'])."%'";
		}
		if ($_POST['iAnmeldestatus'] >= 0) {
		  $sAddWhere .= " and a.STATUS = '".intval($_POST['iAnmeldestatus'])."'";
		}


		if ($_POST['iMandant'] > 0) {
		  $sWhere = "select distinct
					   u.USERID, u.LOGIN, u.NAME, u.NACHNAME, u.EMAIL, UNIX_TIMESTAMP(u.WANNGEAENDERT) WANNGEAENDERT
					   from 
						 USER u, ASTATUS a, RECHTZUORDNUNG r 
					   where 
						 a.USERID = u.USERID and 
						 a.MANDANTID = r.MANDANTID and 
						 r.USERID = '".intval($loginID)."' and 
						 r.MANDANTID = '".intval($_POST['iMandant'])."' and 
						 a.STATUS >= 0 ".$sAddWhere."
					   order by 
						 ".safe($sortierung)."
						 LIMIT 500";
		} else {
			$sWhere = "select distinct 
						 u.USERID, u.LOGIN, u.NAME, u.NACHNAME, u.EMAIL, UNIX_TIMESTAMP(u.WANNGEAENDERT) WANNGEAENDERT
					   from 
						 USER u,
						 RECHTZUORDNUNG r,
						 ASTATUS a
					   where 
						 a.USERID = u.USERID and
						 a.MANDANTID = r.MANDANTID and
						 (r.RECHTID = 'USERADMIN' or r.RECHTID='USERADMIN_READONLY' or r.RECHTID = 'EINLASSADMIN')
						 and r.USERID = '".intval($loginID)."'
						 ".$sAddWhere."
					   order by 
						 ".safe($sortierung)."
						 LIMIT 500";
		}

		$result = DB::query($sWhere);
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";

		$bgc = 'hblau';

		while ($row = $result->fetch_array()) {
			$dateDisplay = date('d.m.Y H:i', $row['WANNGEAENDERT']);

			echo "<tr>";
			echo "<td class=\"$bgc\">$row[USERID]&nbsp;</td>";
			echo "<td class=\"$bgc\"><a href=\"benutzerdetails.php?id=$row[USERID]\">".db2display($row["LOGIN"])."&nbsp;</a></td>";
			echo "<td class=\"$bgc\">",db2display($row["NAME"])." ".db2display($row["NACHNAME"])."&nbsp;</td>";
			echo "<td class=\"$bgc\">",db2display($row["EMAIL"]),"&nbsp;</td>";
			echo "<td class=\"$bgc\"><nobr>$dateDisplay</nobr></td>";
			echo "<td class=\"$bgc\"><a href='javascript:uebernehmeUser(".$row[USERID].");'>auswahl</a></td>";
			echo "</tr>\n";
			if ($bgc == 'hblau')
			  $bgc = 'dblau';
			else 
			  $bgc = 'hblau';
		}

		}

	}
?>

</table></td></tr></table>

<?php

require('admin/nachspann.php');
?>
