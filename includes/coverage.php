<?php
require_once "dblib.php";
include_once "format.php";

function zeigeArchivliste($selectTyp, $lfdNr)
{
	global $nPartyID, $cTableSize, $cCellp;

	if ($archivID <= 0) {
		//######################################################
		// Übersichtsliste der gewählten Kategorie anzeigen

		$result2 = DB::query("select * from ARCHIV_INFO where PARTYID = '$nPartyID' and TYP='$selectTyp' and LOCKED='no' and LFDNR='$lfdNr' order by LFDNR desc");

		$nTheLFDNumber= 0;
		echo "<table cellspacing=\"1\" cellpadding=\"$cCellp\" border=\"0\" width=\"$cTableSize\">";
		echo "<tr><td class=\"TNListe\" colspan=\"2\"><b>Aktuelle Bildergalerien von der Party</b></td></tr>";

		$sBGC = "TNListeTDA";
		while ($row2 = mysql_fetch_array($result2)) {
			if ($nTheLFDNumber != $row2[LFDNR]) {
				// Header für Party ausgeben
				$result = DB::query("select NAME, BEGINN from PARTYHISTORIE where LFDNR = '".$row2[LFDNR]."' and MANDANTID = '".$selectPartyID."'");
				$row3 = @mysql_fetch_array($result);
				// Leerzeile ab 2. Party
				if ($nTheLFDNumber > 0 ) {
					echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
				}
				echo "<tr><td class=\"TNListe\" width=70%>Titel</td><td class=\"TNListe\" width=30%>Autor</td></tr>";
				$nTheLFDNumber = $row2[LFDNR];
			}
			$result = DB::query("select LOGIN from USER where USERID = ".$row2[USERID]);
			$row4 = @mysql_fetch_array($result);

			//### Unterschiedliche Verlinkung zB bei Kategorie Links und Turniere
			if ($selectTyp == $KATEGORIE_ARCH_TURNIER) {
				$sTheLink = "<a href=\"".PELASHOST."archiv/$selectTyp/".$row2[ARCHIVID].".phpl\">";
			} elseif ($selectTyp == $KATEGORIE_ARCH_LINK) {
				$sTheLink = "<a href=\"".$row2[LINK]."\" target=\"_blank\">";
			} else {
				$sTheLink = "<a href=\"archiv.php?selectPartyID=$selectPartyID&selectTyp=$selectTyp&archivID=".$row2[ARCHIVID]."\">";
			}
			echo "<TR><TD class=\"$sBGC\"><img src=\"/gfx/pfeil.gif\" border=\"0\"> ".$sTheLink.db2display($row2[KOMMENTAR])."</a></TD><TD class=\"$sBGC\"><a href=\"/benutzerdetails.php?nUserID=".$row2[USERID]."\">".db2display($row4[LOGIN])."</TD></tr>";
			if ($sBGC == "TNListeTDA") {
				$sBGC = "TNListeTDB";
			} else {
				$sBGC = "TNListeTDA";
			}
		}
		if ($nTheLFDNumber == 0) {
			echo "<TR><TD class=\"TNListeTDA\">Es sind noch keine Eintr&auml;ge vorhanden.</TD></tr>";
		}
		echo "</table>";

	}
}


//Layout Cellpadding
$cCellp = 2;
//Layout Tabellenbreite
$cTableSize = "100%";

if ( !isset($selectPartyID) || !is_numeric($selectPartyID) || 
		($selectPartyID < 1 && $selectPartyID != -1) || 
		$selectPartyID > 10000000 ) 
	$selectPartyID = $nPartyID;

// Checken ob Coverage für diese Party Aktiv ist
$row = @mysql_fetch_array(DB::query("select STRINGWERT from CONFIG where MANDANTID = '$nPartyID' and PARAMETER='COVERAGE_ONLINE'"), MYSQL_ASSOC);
//echo mysql_errno().": ".mysql_error()."<BR>";
$bAktiv = $row['STRINGWERT'];

if ($bAktiv == 'J') {
	// Nur weitermachen wenn Coverage aktiv

	// Je nach Party andere Überschrift
	switch ($showParty) { 
		case "NORTHCON" :
			startContent ("NorthCon-TV Livestream");
			break;
		case "ACTIVATION" :
			echo "<h1>Coverage</h1>";
			break;
		case "MMM" :
			echo "<h1>Coverage</h1>";
			break;
		case "SUMMIT" :
			echo "<h1>Coverage</h1>";
			break;
		case "DIMENSION6" :
		?>
		  <table cellspacing="0" cellpadding="0" border="0" width="100%">
		  <tr height="25">
		    <td width="10" bgcolor="#38464f"><img src="/gfx/lgif.gif" width="10" height="1" border="0"></td>
		    <td width="1" bgcolor="#0f233c"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>
		    <td width="752" style="background-color: #38464f; color: #e6e6e6; font-weight: bold;"> &nbsp;&nbsp; <?=$strFe['tit_welcome'];?> - COVERAGE</td>
		  </tr>
		  <tr>
		    <td width="10" bgcolor="#e6e6e6"><img src="/gfx/lgif.gif" width="10" height="1" border="0"></td>
		    <td width="1" bgcolor="#0f233c"><img src="/gfx/lgif.gif" width="1" height="1" border="0"></td>
		    <td valign="top">

		      <!-- Content Table -->
		      <table cellspacing="0" cellpadding="15" border="0" width="752">
		      <tr>
			<td>
			
		<?php
			break;
	}



	// 1. Neueste Party im Archiv ermitteln, es wird angenommen dass diese die richtige ist
	$row = @mysql_fetch_array(DB::query("select max(LFDNR) as LFDmax from PARTYHISTORIE where MANDANTID = '$nPartyID'"), MYSQL_ASSOC);
	//echo mysql_errno().": ".mysql_error()."<BR>";
	$lastNr = $row['LFDmax'];
	if ($lastNr == "") {
		$lastNr = -1;
	}

		?>

		<?php
		
		if ( CFG::getMandantConfig("COVERAGE_WEBCAM", $nPartyID) == "J") {
			if ($showStreamLink) {
				 echo "<p align=\"center\">\n";
				 
				 ?>
				 
				<iframe src="http://www.yur.tv/remote" width="444" height="365" border=0 frameborder=0 scrolling="no"></iframe> 
				 
				 <?php
				 echo "</p>";
			} else {
				// Normales Webcambild zeigen
				echo "<p align=\"center\"><img name=\"theImage\" border=\"0\"></p>\n";
			}
		}
		echo "<p align=\"center\">\n";
		zeigeArchivliste($KATEGORIE_ARCH_BILDER,$lastNr);
		echo "</p>";
		?>

	<?php
	if ( CFG::getMandantConfig("COVERAGE_WEBCAM", $nPartyID) == "J" && !$showStreamLink) {
	?>
		<script language="JavaScript">
		<!--
		 var BaseURL = "";
		 //var File = "/webcam.jpg";
		 var File = "http://www.lattemann.net/cam/bilder/northcon.jpg";

		 // Force an immediate image load
		 var theTimer = setTimeout("reloadImage()",1);

		 function reloadImage() {
		  theDate = new Date();
		  var url = BaseURL;
		  url += File;
		  url += "?dummy=";
		  url += theDate.getTime().toString(10);
		  // The above dummy cgi-parameter enforce a bypass of the browser image cache.
		  // Here we actually load the image
		  window.document.theImage.src = url;

		  // Reload the image every 30 seconds (30000 ms)
		  theTimer = setTimeout("reloadImage()",30000);

		}
		//-->
		</script>
		
	<?php
	}
	?>

<?php

	// Je nach Party anderer Footer
	switch ($showParty) { 
		case "DIMENSION6" :
		?>
			<!-- End of content -->
			</td>
		      </tr>
		      </table>
		    </td>
		  </tr>
		  </table>
			
		<?php
		break;
		
		case "NORTHCON" :
		endContent();
		break;
	}

}
?>
