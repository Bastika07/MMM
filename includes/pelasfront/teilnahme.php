<?php

include_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "pelasfunctions.php";

function PayPal ()
{
	global $nPartyID, $dbname, $nLoginID, $config, $str;

	// Configtabelle fragen, ob PayPal möglich ist
	if (CFG::getMandantConfig("PAYPAL", $nPartyID) == "J") {
	
		//Namen aus DB holen
		$rowTemp = DB::query("select NAME, NACHNAME from USER where USERID='$nLoginID'")->fetch_assoc(); $sRealName = $rowTemp[NAME]." ".$rowTemp[NACHNAME];
	
		echo "<p><b>".$str['paypal_infotext_header']."</b></p>\n";
		echo "<p align=\"justify\">".$str['paypal_infotext']."</p>\n";
		
		?>
		
		<table cellspacing="0" cellpadding="6" border="0">
		<tr><td>
			<?php
			$gesamtEintritt = ($config[EINTRITT_NORMAL] + 0.35) / (1 - 0.034);
			?>
			
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="paypal@innovalan.de">
			<input type="hidden" name="item_name" value="<?php echo $UBmandant." ID ".$nLoginID.", ".$sRealName; ?>">
			<input type="hidden" name="amount" value="<?=round($gesamtEintritt,2);?>">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="currency_code" value="EUR">
			<input type="image" src="https://www.paypal.com/images/x-click-but5.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
			</form>
		</td>
		<?php
		if ($config['EINTRITT_LOGE'] > 0) {
			
			$gesamtEintritt = ($config[EINTRITT_LOGE] + 0.35) / (1 - 0.034);
			
			echo "<td><img src=\"gfx/headline_pfeil.png\"> Parkett </td><td> &nbsp; </td><td>\n";
			?>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="paypal@innovalan.de">
			<input type="hidden" name="item_name" value="<?php echo $UBmandant." ID ".$nLoginID.", ".$sRealName; ?>">
			<input type="hidden" name="amount" value="<?=round($gesamtEintritt,2);?>">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="currency_code" value="EUR">
			<input type="image" src="https://www.paypal.com/images/x-click-but5.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
			</form>
			<?php
			echo "</td><td><img src=\"gfx/headline_pfeil.png\"> Loge </td>\n";
		}
		?>
		</tr></table>
		
		<?php
		
	}

}

// Ausgelagert um Redundanz zu verhindern 
function ÜberweisungsDatenAnzeigen($nPartyID, $nLoginID){
  
      	global $UBmandant, $config, $str;
  
     	echo "<p><b>$str[kontodaten]</b></p>\n";
    	echo "<p>".$config[KONTO_NAME]."<br>\n";
    	echo "$str[kontonummer]: ".$config[KONTO_NUMMER]."<br>\n";
    	echo "$str[blz]: ".$config[KONTO_BLZ]."<br>\n";
    	echo $config[KONTO_BANK]."<br>\n</p>";
    	echo "<p><b>$str[internationaleKontodaten]</b></p>\n";
    	echo "$str[IBAN]: ".$config[KONTO_IBAN]."<br>\n";
    	echo "$str[BIC]: ".$config[KONTO_BIC]."<br>\n";
	   	echo "<p>$str[zweck1] &quot;".$UBmandant.", ID $nLoginID&quot; $str[zweck2].</p>\n";
}




// evtl. Voranmeldung offen?
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'VORANMELDUNG_OFFEN' and MANDANTID = $nPartyID")->fetch_assoc();
// checken, ob get-variable on
if ($voranmeldung == "true" && $row[STRINGWERT] == "J") {
	$bVoranmeld = 1;
} else {
	$bVoranmeld = 0;
}


$row = DB::query("select USERID, LOGIN, EMAIL from USER where USERID = $nLoginID")->fetch_assoc(); 
$config[USERID] = $row[USERID];
$config[LOGIN] = $row[LOGIN];
$config[EMAIL] = $row[EMAIL];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NAME' and MANDANTID = $nPartyID")->fetch_assoc();
$config[KONTO_NAME] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_NUMMER' and MANDANTID = $nPartyID")->fetch_assoc();
$config[KONTO_NUMMER] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BLZ' and MANDANTID = $nPartyID")->fetch_assoc();
$config[KONTO_BLZ] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BANK' and MANDANTID = $nPartyID")->fetch_assoc();
$config[KONTO_BANK] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_NORMAL' and MANDANTID = $nPartyID")->fetch_assoc();
$config[EINTRITT_NORMAL] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_LOGE' and MANDANTID = $nPartyID")->fetch_assoc();
$config[EINTRITT_LOGE] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_XTRA' and MANDANTID = $nPartyID")->fetch_assoc();
$config[EINTRITT_XTRA] = " ".$row[STRINGWERT];
// Added IBAN and BIC to config Array
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_IBAN' and MANDANTID = $nPartyID")->fetch_assoc();
$config[KONTO_IBAN] = $row[STRINGWERT];
$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'KONTO_BIC' and MANDANTID = $nPartyID")->fetch_assoc();
$config[KONTO_BIC] = $row[STRINGWERT];
$row = DB::query("select BESCHREIBUNG from MANDANT where MANDANTID = $nPartyID")->fetch_assoc();
$config[BESCHREIBUNG] = $row[BESCHREIBUNG];
$row = DB::query("select BESCHREIBUNG, EMAIL from MANDANT where MANDANTID=$nPartyID")->fetch_array();
$sMandant = $row[BESCHREIBUNG]; $sMandantEmail = $row[EMAIL];


//zu lange Partynamen kuerzen
if (strlen($config[BESCHREIBUNG]) > 12 ) {
	$UBmandant = db2display(substr( $config[BESCHREIBUNG], 0, 12));
} else {
	$UBmandant = db2display($config[BESCHREIBUNG]);
}

$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'ANMELDUNG_OFFEN' and MANDANTID = $nPartyID")->fetch_assoc();
if (($row[STRINGWERT] == "J" || $bVoranmeld == 1 || User::hatBezahlt($nLoginID, $nPartyID)) && !$iAction) {
	if (!$nLoginID) {
		echo "<p>$str[pelasgebraucht] ";
		echo "$str[weitermachen]</p>";
		echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"login.php\">$str[loginodererstellen]</a><br>";
		echo "</p>";
	} else {
		$row = DB::query("select STATUS from ASTATUS where MANDANTID = $nPartyID and USERID = $nLoginID")->fetch_assoc();
		if (!$row[STATUS] || $row[STATUS] == $STATUS_ABGEMELDET) {
			$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_NORMAL' and MANDANTID = $nPartyID")->fetch_assoc(); $config[EINTRITT_NORMAL] = $row[STRINGWERT];
			$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_LOGE' and MANDANTID = $nPartyID")->fetch_assoc(); $config[EINTRITT_LOGE] = $row[STRINGWERT];
			$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'EINTRITT_XTRA' and MANDANTID = $nPartyID")->fetch_assoc(); $config[EINTRITT_XTRA] = " ".$row[STRINGWERT];
			echo "<p>\n$str[klickundfertig1](".$config[EINTRITT_NORMAL]." EUR";

			if ($config[EINTRITT_LOGE] > 0) {
				echo " (Loge ".$config[EINTRITT_LOGE]." EUR)";
			}
			echo $config[EINTRITT_XTRA].") $str[klickundfertig2]\n</p>\n";
			echo "<form action=\"teilnahme.php\" method=POST>";
			echo csrf_field() . "\n";
			echo "<p>\n<input type=\"checkbox\" name=\"check\" value=\"J\">&nbsp;$str[jaichakzeptiere1] <a href=\"bedingungen.php\">$str[jaichakzeptiere2]</a>";
			$row = DB::query("select BESCHREIBUNG from MANDANT where MANDANTID = $nPartyID")->fetch_assoc();
			echo " $str[jaichakzeptiere3] <i>".$row[BESCHREIBUNG]."</i> $str[jaichakzeptiere4].\n</p>\n";
			echo "<p>\n<input type=\"hidden\" name=\"PELASSESSID\" value=\"$PELASSESSID\"><input type=\"hidden\" name=\"nPartyID\" value=\"$nPartyID\"><input type=\"hidden\" name=\"iAction\" value=\"new\"><input type=\"submit\" value=\"$str[anmeldungabschliessen]\"></form>\n</p>\n";
		} elseif ($row[STATUS] == $STATUS_ANGEMELDET) {
			echo "<p>$str[angemeldetschon]\n";
			
			echo " Die Teilnahmegeb&uuml;hr betr&auml;gt ".$config[EINTRITT_NORMAL]." EUR ";
			if ($config[EINTRITT_LOGE] > 0) {
				echo " (Loge ".$config[EINTRITT_LOGE]." EUR)";
			}
			echo ".</p>";
			
			//Überweisungsdaten in Funktion gezogen
      		ÜberweisungsDatenAnzeigen($nPartyID, $nLoginID);

			// PayPal Zahlungsinfos anzeigen			
			PayPal();
			
		} else {
			echo "<p>$str[gezahlt]\n</p>\n";
			echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"".PELASHOST."zahlungsbeleg.php?PELASSESSID=$PELASSESSID&PELASSESSUSERID=$nLoginID&nPartyID=$nPartyID\">Zahlungsbest&auml;tigung drucken</a></p>";
		}
	}
} elseif ($iAction) {

    if ($check=="J") {
    	echo "<p class=\"hervorgehoben\">$str[duangemeldet]</p>\n";
    	echo "<p>$str[angekommen1] <i>".$config[BESCHREIBUNG]."</i> $str[angekommen2], ".db2display($config[LOGIN]).". $str[angekommen3] (".$config[EINTRITT_NORMAL]." EUR ";
    	
    	if ($config[EINTRITT_LOGE] > 0) {
    		echo " (Loge ".$config[EINTRITT_LOGE]." EUR)";
    	}
    	
    	
    	echo $config[EINTRITT_XTRA].") ".$str[angekommen4]."</p>\n";
    	
    	ÜberweisungsDatenAnzeigen($nPartyID, $nLoginID);
    	
    	// PayPal Zahlungsinfos anzeigen			
    	PayPal();
    	
    	echo "<p><img src=\"gfx/headline_pfeil.png\" border=\"0\"> <a href=\"teilnehmer.php\">$str[teilnehmerliste]</a></p>";
    	
    	$result = DB::query("select USERID from ASTATUS where USERID = ? and MANDANTID = ?", $nLoginID, $nPartyID);
    	$row = $result->fetch_array();
    	if ($row[USERID] > 0 ) {
    		DB::query("update ASTATUS set STATUS = ?, WANNANGEMELDET = NOW() where USERID = ? and MANDANTID = ?", $STATUS_ANGEMELDET, $nLoginID, $nPartyID);
    	} else {
    		DB::query("insert into ASTATUS (MANDANTID, USERID, STATUS, WANNANGEMELDET) values (?, ?, ?, NOW())", $nPartyID, $nLoginID, $STATUS_ANGEMELDET);
    	}
    	
    	// Anmeldebestätigung verschicken
    	SendeAnmeldeMail($nPartyID, $nLoginID);
    } else {
       echo $str[geschäftsbedingungenAkzeptierenTeilnahme]."<br><br>";
       echo "<a href=\"./teilnahme.php\">Zurück";
    }
} else {
	$row = DB::query("select STRINGWERT from CONFIG where PARAMETER = 'ANMELDUNG_OFFEN_AB' and MANDANTID = $nPartyID")->fetch_assoc();
	echo "<p>\n$str[astart1]: ".$row[STRINGWERT]." - $str[astart2].\n</p>\n";
}

?>
