<?php
require('controller.php');
require_once('dblib.php');
$iRecht = 'USERADMIN';
require_once('checkrights.php');
$dbh = DB::connect();
require_once('format.php');

// Read all user-supplied variables explicitly with sanitization
$nMandantID    = intval($_GET['nMandantID'] ?? $_POST['nMandantID'] ?? 0);
$id            = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$nOldStatus    = intval($_GET['nOldStatus'] ?? $_POST['nOldStatus'] ?? 0);
$iGo           = ($_POST['iGo'] ?? '') === 'yes' ? 'yes' : '';
$iAnmeldestatus = intval($_POST['iAnmeldestatus'] ?? 0);
$iClan         = safe($_POST['iClan'] ?? '');
$iRabattStufe  = intval($_POST['iRabattStufe'] ?? 0);
$iBuffet       = in_array($_POST['iBuffet'] ?? '', ['J', 'N']) ? $_POST['iBuffet'] : 'N';
$iInfo         = (($_POST['iInfo'] ?? '') === 'yes') ? 'yes' : '';
$iOldStatus    = intval($_POST['iOldStatus'] ?? $nOldStatus);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title></title>
  <link rel="stylesheet" type="text/css" href="/style/style.css">
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<script type="text/javascript">
<!--
function changeStatus(Mandant) {
	alert("OSa :" + Mandant);
	Formname = "Astatus" + Mandant;
	Oldstatus = document.forms[Formname].OldStatus.value;
	alert("OS: " + Oldstatus);
	theURL = "benutzerstatus.php?nMandantID=" + Mandant + "&nOldStatus=" + Oldstatus;
	detail = window.open(theURL, "Astatus", "width=320,height=320,locationbar=false,resize=false");
	detail.focus();
}
//-->
</script>

<h1>Status ver�ndern</h1>

<?php
if ($iGo == 'yes') {
	# Abfrage, ob User wirklich die Rechte hat! HIER WICHTIG!
	$q = 'SELECT USERID
	      FROM RECHTZUORDNUNG
	      WHERE MANDANTID = ' . $nMandantID . '
	        AND USERID = ' . $loginID;
	$result = mysql_db_query($dbname, $q, $dbh);
	$row = mysql_fetch_array($result);
	if ($row[USERID] > 0) {
		# Und ab.
	
		# Wennn noch kein Eintrag in der Tabelle ASTATUS, dann einf�gen.
		$q = 'SELECT USERID
		      FROM ASTATUS
		      WHERE MANDANTID = ' . $nMandantID . '
		        AND USERID = ' . $id;
		$resultV = mysql_db_query($dbname, $q, $dbh);
		##echo mysql_errno() . ': ' . mysql_error() . "<br/>\n";
		$rowV = mysql_fetch_array($resultV);
		if ($rowV[USERID] <= 0) {
			# ASTATUS vorbelegen.
			$q = "INSERT INTO ASTATUS
			        (MANDANTID, USERID, STATUS, WERGEAENDERT, WERANGELEGT)
				VALUES ($nMandantID, $id, $STATUS_NICHTANGEMELDET, $loginID, $loginID)";
			$resultInsert = mysql_db_query($dbname, $q, $dbh);
			##echo mysql_errno() . ': ' . mysql_error() . "<br/>\n";
		}

		# Wannbezahlt setzen!
		if ((($iAnmeldestatus == $STATUS_BEZAHLT) or ($iAnmeldestatus == $STATUS_BEZAHLT_LOGE)) and (($iOldStatus != $STATUS_BEZAHLT) and ($iOldStatus != $STATUS_BEZAHLT_LOGE))) {
			if ($iOldStatus == $STATUS_NICHTANGEMELDET) {
				$addWhere = ", WANNANGEMELDET = NOW(), WANNBEZAHLT = NOW()";
			} else {
				$addWhere = ", WANNBEZAHLT = NOW()";
			}
		} elseif (($iAnmeldestatus == $STATUS_ANGEMELDET) and (($iOldStatus != $STATUS_BEZAHLT) and ($iOldStatus != $STATUS_BEZAHLT_LOGE))) {
			$addWhere = ", WANNANGEMELDET = NOW()";
		} else {
			$addWhere = "";
		}

		$q = "UPDATE ASTATUS
		      SET STATUS = $iAnmeldestatus,
		          BEZ_IN_CLAN = '$iClan',
		           RABATTSTUFE = $iRabattStufe,
			  BUFFET = '$iBuffet',
			  WERGEAENDERT = $loginID $addWhere
		      WHERE USERID = $id
		        AND MANDANTID = $nMandantID";
		$result = mysql_db_query($dbname, $q, $dbh);
		##echo mysql_errno() . ': ' . mysql_error() . "<br/>\n";

		if ((($iAnmeldestatus == $STATUS_BEZAHLT) or ($iAnmeldestatus == $STATUS_BEZAHLT_LOGE)) and ($iInfo == 'yes')) {
			$result = mysql_db_query($dbname, 'SELECT REFERER, BESCHREIBUNG, EMAIL FROM MANDANT WHERE MANDANTID = ' . $nMandantID, $dbh);
			$row = mysql_fetch_array($result);
			$sURL = $row['REFERER'];
			$sMandant = $row['BESCHREIBUNG'];
			$sMandantEmail = $row['EMAIL'];
			$result = mysql_db_query($dbname, 'SELECT LOGIN, EMAIL FROM USER WHERE USERID = ' . $id, $dbh);
			$row = mysql_fetch_array($result);
			$sLogin = $row['LOGIN'];
			$sEmail = $row['EMAIL'];

			$sEmailText = "Hallo $sLogin\n\nWir haben Deine Teilnahmegeb�hr f�r die $sMandant erhalten.\n\n";
			$sEmailText = $sEmailText . "Somit bist Du nun sicher dabei und kannst Dir, wenn die Sitzplatzreservierung er�ffnet ist, einen Platz reservieren.\n\n";
			$sEmailText = $sEmailText . "Viele Gr�ße,\nDein $sMandant Team\n";
			$sEmailText = $sEmailText . "$sURL\nmailto:$sMandantEmail";

			mail($sEmail, "Anmeldebest�tigung", $sEmailText, "From: $sMandant Mailer <$sMandantEmail>\nReply-To: $sMandantEmail\nX-Mailer:". phpversion());
		}
		?>
		<script type="text/javascript">
		<?php
		# Hat der User einen Platz gehabt?
		if (($iAnmeldestatus == $STATUS_ABGEMELDET) or ($iAnmeldestatus == $STATUS_ANGEMELDET)) {
			$result= mysql_db_query ($dbname, 'SELECT USERID FROM SITZ WHERE MANDANTID = ' . $nMandantID . ' AND USERID = ' . $id, $dbh);
			$row = mysql_fetch_array($result);
			if ($row[USERID] > 0) {
				# Platz aus DB l�schen.
				$result = mysql_db_query($dbname, 'DELETE FROM SITZ WHERE MANDANTID = ' . $nMandantID . ' AND USERID = ' . $id, $dbh);
			}
		}
		
		$result = mysql_db_query($dbname, 'SELECT BESCHREIBUNG FROM STATUS WHERE STATUSID = ' . $iAnmeldestatus, $dbh);
		$row = mysql_fetch_array($result);
		$sAstatusBeschreibung = $row['BESCHREIBUNG'];
		# dann mit diesem Java-Script das Quellformular aktualisieren.
		?>

		Formname = "Astatus<?php echo intval($nMandantID); ?>";
		opener.document.forms[Formname].OldStatus.value = <?php echo intval($iAnmeldestatus); ?>;
		opener.document.forms[Formname].AstatusBeschreibung.value = <?php echo json_encode(db2display($sAstatusBeschreibung), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
		self.close();
		</script>
		<p>Dieses Fenster schliesst sich automatisch. Ausnahme: Die Hauptmaske wurde bereits geschlossen.</p>
		<?php
		
		# Logging
		if ($iAnmeldestatus != $iOldStatus) {
			PELAS::logging("Status for user " . $id . " on PartyID " . $nMandantID . " set to " . db2display($sAstatusBeschreibung) . " by " . $loginID, 'accounting', $id);
		}
	} else {
		echo "<p class=\"fehler\">Schwerwiegender Fehler: Keine Berechtigung um diese Funktion auszuf�hren - Abbruch!</p>\n";
	}
} else {

	echo "<form method=\"post\" action=\"benutzerstatus.php?id=".intval($id)."&nMandantID=".intval($nMandantID)."&nOldStatus=".intval($nOldStatus)."\" name=\"details\">";

	echo "<input type=\"hidden\" name=\"iGo\" value=\"yes\">";

	$result = mysql_db_query($dbname, "select * from USER where USERID=$id", $dbh);
	$row = mysql_fetch_array($result);
	$result = mysql_db_query($dbname, "select STATUS, BEZ_IN_CLAN, RABATTSTUFE, BUFFET from ASTATUS where MANDANTID=$nMandantID and USERID=$id", $dbh);
	$row_status = mysql_fetch_array($result);
	##echo mysql_errno().": ".mysql_error()."<BR>";
	$resultMandant = mysql_db_query($dbname, "select BESCHREIBUNG from MANDANT where MANDANTID=$nMandantID", $dbh);
	##echo mysql_errno().": ".mysql_error()."<BR>";
	$rowMandant = mysql_fetch_array($resultMandant);

	echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
	echo "<tr><td class=\"navbar\">\n";
	echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">\n";
	echo "<tr><td class=\"navbar\" colspan=\"2\"><b>Status von &quot;".db2display($row['LOGIN'])."&quot; f&uuml;r &quot;".db2display($rowMandant['BESCHREIBUNG'])."&quot;</b></td></tr>\n";

	# Alter Status
	$result = mysql_db_query($dbname, "select BESCHREIBUNG from STATUS where STATUSID=$nOldStatus", $dbh);
	$row = mysql_fetch_array($result);
	echo "<tr><td class=\"dblau\">Alter Status</td><td class=\"hblau\">$row[BESCHREIBUNG]</td></tr>";
	echo "\n";
	
	echo "<input type=\"hidden\" name=\"iOldStatus\" value=\"$nOldStatus\">";
	
	# Neuer Status
	echo "<tr><td class=\"dblau\">Neuer Status</td><td class=\"hblau\">";
	echo "<select name=\"iAnmeldestatus\">";
	$result = mysql_db_query($dbname, "select STATUSID, BESCHREIBUNG from STATUS", $dbh);
	##echo mysql_errno().": ".mysql_error()."<BR>";
	while ($row = mysql_fetch_array($result)) {
		echo "<option value=\"$row[STATUSID]\"";
		if ($nOldStatus == $row['STATUSID']) {
			echo ' selected="selected"';
		}
		echo '>' . $row['BESCHREIBUNG'];
	}
	echo "</select></td></tr>";
	echo "\n";
	
	echo "<tr><td class=\"dblau\">Email-Benachrichtigung</td><td class=\"hblau\"><input type=\"checkbox\" name=\"iInfo\" value=\"yes\"> * Wird nur verschickt, wenn neuer Status bezahlt</td></tr>";
	echo "\n";
	
	echo "<tr><td class=\"dblau\">Clan�berweisung</td><td class=\"hblau\"><input type=\"text\" name=\"iClan\" value=\"$row_status[BEZ_IN_CLAN]\"> (ClanID)</td></tr>";
	echo "\n";
	
	echo "<tr><td class=\"dblau\">Rabattstufe</td><td class=\"hblau\"><input type=\"radio\" name=\"iRabattStufe\" value=\"0\"";
	if ($row_status[RABATTSTUFE] <= 0) {
		echo ' checked';
	}	
	echo ">Keine <input type=\"radio\" name=\"iRabattStufe\" value=\"5\" ";
	if ($row_status[RABATTSTUFE] == 5) {
		echo ' checked';
	}
	echo ">5 <input type=\"radio\" name=\"iRabattStufe\" value=\"10\"";
	if ($row_status[RABATTSTUFE] == 10) {
		echo ' checked';
	}
	echo ">10 <input type=\"radio\" name=\"iRabattStufe\" value=\"15\"";
	if ($row_status[RABATTSTUFE] == 15) {
		echo ' checked';
	}	
	echo ">15\n</td></tr>";
	
	echo "<tr><td class=\"dblau\">F�r Buffet gezahlt</td><td class=\"hblau\"><input type=\"checkbox\" name=\"iBuffet\" value=\"J\"";
	if ($row_status['BUFFET'] == 'J') {
	  echo ' checked';
	}
	echo "></td></tr>";
	echo "\n";
	
	echo "<tr><td class=\"dblau\" height=\"40\" align=center valign=bottom colspan=2><input type=submit value=\"Status setzen\"></td></tr>";
	echo "</table></td></tr></table></form>";
}

include('admin/nachspann.php');
?>
