<?php
require_once "dblib.php";
include_once "format.php";
include_once "session.php";
include_once "language.inc.php";

// Wieviele Server
define('MAXSERVER', 2);

$forbidden = array('www', 
									'ey',
									'admin',
									'ftp',
									'ftp-server',
									'ftpserver',
									'ns',
									'ns1',
									'ns2',
									'gateway',
									'proxy',
									'irc',
									'stream',
									'bridge',
									'il',
									'innovalan',
									'porn',
									'porno',
									'ficken',
									'xxx',
									'sex',
									'mmm',
									'multimadness');
									
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

function checkName($sName) {
	global $dbname, $nPartyID;

	$q = "SELECT LFDNR "
	    ."FROM GASTSERVER "
	    ."WHERE MANDANTID = '".intval($nPartyID)."' "
	    ."and LCASE(NAMEDNS)='".safe(strtolower($sName))."'";
	
    	$result = DB::query($q);
    	$row = $result->fetch_array();
	
	if ($row['LFDNR'] > 0) {
	  	return true;
	  } else {
	  	return false;
	  }
}

$q = "SELECT * "
    ."FROM MANDANT "
    ."WHERE MANDANTID = ".intval($nPartyID);
if ($res = DB::query($q)) {
  $row = $res->fetch_row();
  $sLanParty = $row[1];
}

$sInfoText = "<p align=\"justify\">Du kannst insgesamt zwei Server anmelden und bekommst f&uuml;r jeden eine IP. Wenn Du m&ouml;chtest, \n".
	"dann kannst Du auch beide IPs f&uuml;r einen Server verwenden.</p>\n";

	
$sInfoTextServerIDCard = "<p align=\"justify\"><b>Du solltest dir f&uuml;r jeden Server die spezielle Server-ID Card ausdrucken und auf Deinen \n".
  "Server kleben, um eine schnellere Abgabe des Servers im Gastserver-Bereich auf der $sLanParty zu gew&auml;hrleisten. </b> <br><br>Diese Server-ID \n".
  "Card muss ansonsten bei der Abgabe des Servers ausgef&uuml;llt werden! Die Server-ID Card ist nur f&uuml;r diese $sLanParty g&uuml;ltig!\n".
  "<!-- <br><b>Wichtig:</b> Die IP etc. muss <b>vor</b> der Abgabe auf Deinem Server eingerichtet werden! --></p>\n";

$sInfoTextServerIDCard2 =  "<br><p>Als Zubehör für den Server wird nur ein Stromkabel und ein Netzwerkkabel (ca. 10m) benötigt.</p>\n";

// Anzahl der bisher eingetragenen Server ermitteln
if ($nLoginID < 1) {
	$nLoginID = -1;
}
$nAnzahlServer = 0;
$q = "SELECT count(*) as ServerAnzahl "
    ."FROM GASTSERVER "
    ."WHERE USERID = ".intval($nLoginID)." "
    ."and MANDANTID = ".intval($nPartyID);
if ($res = DB::query($q)) {
  $row = $res->fetch_row();
  $nAnzahlServer = $row[0];
}

if ($nLoginID < 1) {
  echo "<p>Nur eingeloggte Benutzer k&ouml;nnen Gastserver anmelden.</p>";
} elseif (!User::hatBezahlt()) {
  echo "<p>Nur Benutzer die gezahlt haben, k&ouml;nnen Gastserver anmelden.</p>";
} elseif ($_GET['iAction'] == "ServerIDCard") {
   
	/* ZEIGE ID-Card (ich öffne mich idR. in einem neuen Fenster */
   
     $q = "SELECT * "
      ."FROM GASTSERVER "
      ."WHERE USERID = ".intval($nLoginID)." "
      ."and LFDNR = '".intval($LFDNR)."' "
      ."and MANDANTID = ".intval($nPartyID);
     if ($res = DB::query($q)) {
      $row = $res->fetch_row();
      $ServerIP = $row[0];
     }   
     
	// $ServerIP =  CFG::getMandantConfig("GASTSERVER_IP",$nPartyID)."$ServerIP";
	$url = "./";
	/* $gfxlink = "gastserver_idcard.php?ServerIP=$ServerIP&Name=".urlencode ($Name)."&Sitzreihe=$Sitzreihe&Sitzplatz=$Sitzplatz&nPartyID=$nPartyID";

	if($PersoNr != ""):
		$gfxlink = $gfxlink."&PersoNr=$PersoNr";
	endif; */
	
	$gfxlink = "gastserver_idcard.php?LFDNR=$ServerIP";

	$gfxlink = "<img src=\"$gfxlink\">";

echo("

<html>
<head>
<title>Server-ID Card für Server $_GET[ServerName].lan</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">

<style type=\"text/css\">
A, A:HOVER, A:LINK, A:VISITED {
	font-weight: none;
	font-size: 10pt;
	text-decoration: none;
	cursor: none;
}

A:HOVER, A:ACTIVE {
	text-decoration: underline;
	font-size: 10pt;
	cursor: none;
}

</style>

</head>

<body bgcolor=\"#FFFFFF\" text=\"#000000\">
<center>$gfxlink</center>


<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr> 
          <td colspan=\"2\"><font size=\"4\">&nbsp;</font></td>
        </tr>
        <tr> 
          <td colspan=\"2\"><center><a href=\"javascript:window.print()\"><font color=\"#000000\" size=\"4\">Diese Seite drucken</font></a></center></td>
        </tr>
</table>
</body>
</html>

");

} elseif ($_GET['iAction'] == "add") {
  // Server hinzufügen
  $err = false;
 
  if ($nAnzahlServer > MAXSERVER) {
    PELAS::fehler('Du kannst maximal '.MAXSERVER.' Server anmelden!');
    $err = true;  
  } elseif (isset($_POST['iName']) && (empty($_POST['iName']) || empty($_POST['iBeschreibung']))) {
    PELAS::fehler('Bitte alle Felder ausf&uuml;llen!');
    $err = true;
  } elseif (ereg ("[^0-9a-zA-Z-]", $_POST['iName'])) {
    // DNS im falschen Format
    PELAS::fehler('Name / DNS-Eintrag darf keine Leer- und Sonderzeichen enthalten!');
    $err = true;
  } elseif (isset($_POST['iName']) && checkName($_POST['iName'])) {
    // DNS schon vergeben?
    PELAS::fehler('Dieser Name / DNS-Eintrag ist bereits vergeben!');
    $err = true;
  } elseif (in_array($_POST['iName'], $forbidden)) {
    // DNS schon vergeben?
    PELAS::fehler('Dieser Name / DNS-Eintrag ist leider nicht möglich!');
    $err = true;
  }
  
  if (isset($_POST['iName']) && $nUserID != -1 && !$err) {
    // Höchste LFD herausfinden
    $nNewLFD = 1;
    $q = "SELECT max(LFDNR) as MaxLFD "
        ."FROM GASTSERVER "
        ."WHERE MANDANTID = '".intval($nPartyID)."'";
    if ($res = DB::query($q)) {
      $row = $res->fetch_row();
      $nNewLFD = $row[0];
      $nNewLFD++;
    }
    
    if ($MaxLFD > 254) {
    	// Alle Slots voll
    	PELAS::fehler('Es sind keine freien Pl&auml;tze mehr f&uuml;r Deinen Gastserver vorhanden!');
    } else {
	    // Go, ab in die Datenbank !
	    $q = "insert into GASTSERVER "
		."(LFDNR, USERID, MANDANTID, NAMEDNS, BESCHREIBUNG, REVERSE, WANNANGELEGT) "
		."values "
		."('$nNewLFD', '".intval($nLoginID)."', '".intval($nPartyID)."', '".safe($_POST['iName'])."', '".safe($_POST['iBeschreibung'])."', '".safe($_POST['iReverse'])."', NOW())";
	    $res = DB::query($q);
	    echo "<p>Du hast Deinen Gastserver erfolgreich eingetragen.</p>\n";
	    echo "<p><a href=\"?page=31\">Zur Gast-Server Übersicht</a></p>\n";
    }
    
  } else {
    echo $sInfoText;

    echo "<form method=\"post\" action=\"?page=31&iAction=add\">\n";
    echo csrf_field() . "\n";
    echo "<table class=\"rahmen_allg\" style=\"max-width:800px;\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\">\n";
    echo "  <tr><td class=\"pelas_benutzer_titel\" colspan=\"3\" valign=\"top\">";
    echo "    <b>Gastserver anmelden</b>\n";
    echo "  </td></tr>";
    echo "  <tr><td class=\"pelas_benutzer_prefix\">Name / DNS-Eintrag <br> (Wie heißt er?)</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\"><input type=\"text\" name=\"iName\" value=\"".$_POST['iName']."\" size=\"30\" maxlength=\"40\">.lan.multimadness.de</td></tr>\n";
    
    echo "  <tr><td class=\"pelas_benutzer_prefix\">Reverse-Lookup <br> (Soll er über den Namen gefunden werden?)</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\"><input type=\"checkbox\" name=\"iReverse\" value=\"J\"";
      if ($_POST['iReverse']=="J") { echo " checked"; }
    echo "  > <small>Aufl&ouml;sung von IP-Adresse zu DNS</small></td></tr>";
    
    echo "  <tr><td class=\"pelas_benutzer_prefix\">Beschreibung <br> (Was tut/ist er?)</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\"><input type=\"text\" name=\"iBeschreibung\" value=\"".$_POST['iBeschreibung']."\" size=\"40\" maxlength=\"150\"></td></tr>\n";
    
    echo "  <tr><td class=\"pelas_benutzer_inhalt\" align=\"center\" colspan=\"3\" height=\"40\"><input type=\"submit\" value=\"Server anmelden\"></td></tr>\n";

    echo "</table>\n";
    echo "</form>\n";
  }
  
} else {
  // Server anzeigen
  echo $sInfoText;
  if ($nAnzahlServer > 0) {
    //echo $sInfoTextServerIDCard;
  }
  
  echo ("
  
            <SCRIPT LANGUAGE='JavaScript'>
            <!-- Begin
            function popup(URL) {
              window.open(URL,'ServerIDCard','toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=390,height=563');
            }
            // End -->
            </script>
        ");
  
  $q = "SELECT * "
      ."FROM GASTSERVER "
      ."WHERE USERID = ".intval($nLoginID)." "
      ."and MANDANTID = ".intval($nPartyID);
  $res = DB::query($q);
  while ($row = $res->fetch_array()) {
    
    echo "<p><table class=\"rahmen_allg\" width=\"650\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\">\n";
    echo "  <tr><td class=\"pelas_benutzer_titel\" colspan=\"3\" valign=\"top\">";
    echo "    <b>".db2display($row['NAMEDNS'])."</b>\n";
    echo "  </td></tr>";
    
    echo "  <tr><td width=\"180\" class=\"pelas_benutzer_prefix\">Name / DNS-Eintrag</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\">".db2display($row['NAMEDNS']).".lan.multimadness.de ";
    if ($row['REVERSE'] == "J") { echo "<small>(mit Reverse lookup)</small>"; }
    echo "</td></tr>\n";

    echo "  <tr><td width=\"180\" class=\"pelas_benutzer_prefix\">IP-Adresse</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\">".CFG::getMandantConfig("GASTSERVER_IP",$nPartyID).db2display($row['LFDNR'])."</td></tr>";
    
    echo "  <tr><td class=\"pelas_benutzer_prefix\">Subnet-Mask</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\">".CFG::getMandantConfig("GASTSERVER_SMASK",$nPartyID)."</td></tr>";
    
    echo "  <tr><td class=\"pelas_benutzer_prefix\">Gateway</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\">".CFG::getMandantConfig("GASTSERVER_GATEWAY",$nPartyID)."</td></tr>";
    
    echo "  <tr><td class=\"pelas_benutzer_prefix\">Erster DNS</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\">".CFG::getMandantConfig("GASTSERVER_DNS1",$nPartyID)."</td></tr>";
    
	$dns2_ip = CFG::getMandantConfig("GASTSERVER_DNS2",$nPartyID);
	if ($dns2_ip != "-1" && $dns2_ip != "")
	{
		echo "  <tr><td class=\"pelas_benutzer_prefix\">Zweiter DNS</td>\n";
		echo "  <td class=\"pelas_benutzer_inhalt\">".htmlspecialchars($dns2_ip)."</td></tr>";
	}

    echo "  <tr><td class=\"pelas_benutzer_prefix\">Beschreibung</td>\n";
    echo "  <td class=\"pelas_benutzer_inhalt\">".db2display($row['BESCHREIBUNG'])."</td></tr>";

// TEST BY HBUSS

$ServerName = db2display($row['NAMEDNS']);
        
//    echo "  <tr><td class=\"pelas_benutzer_prefix\">Server-ID Card</td>\n";
//    echo "  <td class=\"pelas_benutzer_inhalt\"><a class=\"arrow\" href=\"Javascript:popup('";

//    echo    "?page=31&iAction=ServerIDCard&LFDNR=".db2display($row['LFDNR']);

//    echo "  ')\">anzeigen</a></td></tr>";
    
    echo "</table></p>\n";
    
  }

echo "$sInfoTextServerIDCard2";
  
  if ($nAnzahlServer < MAXSERVER) {
  	echo "<p><table cellpadding='3' cellspacing='5' border='0'><tr><td class='forum_titel'><a href='?page=31&iAction=add' class=\"forumlink\">Server anmelden</a></td></tr></table></p>";
  }
}


?>