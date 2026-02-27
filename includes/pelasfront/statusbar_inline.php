<?php
include_once "dblib.php";

$nHoehe     = 6;
$nTblHeight = 10;


// Anzahl der Plätze
$sql = "select
          STRINGWERT 
        from 
          CONFIG 
        where 
          MANDANTID = $nPartyID and 
          PARAMETER='TEILNEHMER'";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$partyPlaetze = $row[0];

// Anzahl bezahlt
$sql = "select 
          count(*) as cnt 
        from 
          ASTATUS 
        where 
          STATUS IN ($STATUS_BEZAHLT_LOGE, $STATUS_BEZAHLT) and 
          MANDANTID = $nPartyID";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$partyBezahlt = $row[0];

// Anzahl angemeldet
$sql = "select 
          count(*) as cnt 
        from 
          ASTATUS 
        where 
          STATUS = $STATUS_ANGEMELDET and 
          MANDANTID = $nPartyID";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$partyAngemeldet = $row[0];


// Anzahl Session registrierte Benutzer
$sql = "select 
          count(*) as cnt 
        from 
          SESSION
        where 
          UNIX_TIMESTAMP(ZEITSTEMPEL) >= UNIX_TIMESTAMP() - 500 and
          MANDANTID = '$nPartyID'";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$anzahlSessions = $row[0];

// Anzahl Benutzer für Mandant
$sql = "select 
          count(*) as cnt 
        from 
          ASTATUS 
        where 
          MANDANTID='$nPartyID'";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$anzahlAccounts = $row[0];


// Anzahl Forenpostings
$sql = "select 
          count(*) as cnt 
        from 
          forum_boards b, forum_content c 
        where 
          b.mandantID='$nPartyID' and 
          b.boardID = c.boardID and 
          b.type IN (1, 2)";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$anzahlForenpostings = $row[0];


if (!isset($theWidth) || $theWidth < 1) { $theWidth = 100; }

$gesamt = $partyBezahlt + $partyAngemeldet;
if ($gesamt > $partyPlaetze) {
	$einTeil = $theWidth / $gesamt;
} else {
	$einTeil = $theWidth / $partyPlaetze;
}

$freiePlaetze = $partyPlaetze-$partyAngemeldet-$partyBezahlt;

if ($sStyle == "theactivation") {
	// Activation-Style mit zus. Angaben
        echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
        echo "<tr><td width=\"60\" class=\"box_content\"><font class=\"latest_date\">$gesamt</font></td><td class=\"box_content\">&nbsp;</td><td class=\"box_content\"><font class=\"latest_link\">angemeldete G&auml;ste</font></td></tr>";
        echo "<tr><td class=\"box_content\"><font class=\"latest_date\">$partyBezahlt</font></td><td class=\"box_content\">&nbsp;</td><td class=\"box_content\"><font class=\"latest_link\">bezahlte G&auml;ste</font></td></tr>";
        echo "<tr><td class=\"box_content\"><font class=\"latest_date\">$anzahlAccounts</font></td><td class=\"box_content\">&nbsp;</td><td class=\"box_content\"><font class=\"latest_link\">aktive Accounts</font></td></tr>";
        echo "<tr><td class=\"box_content\"><font class=\"latest_date\">$anzahlForenpostings</font></td><td class=\"box_content\">&nbsp;</td><td class=\"box_content\"><font class=\"latest_link\">Forum Postings</font></td></tr>";
	echo "<tr><td class=\"box_content\"><font class=\"latest_date\">$anzahlSessions</font></td><td class=\"box_content\">&nbsp;</td><td class=\"box_content\"><a class=\"latest_link\" href=\"/online.php\">User online</a></td></tr>";
        echo "</table>";

} elseif ($sStyle == "text") {
	// Nur Text Version, insb. für NorthCon.de
	echo "<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\">\n";
	//echo "<tr><td>Verbleibende Tage</td><td><b><font color=\"#0000DD\">33</font></b></td></tr>";
	echo "<tr><td>Pl&auml;tze gesamt</td><td><b>$partyPlaetze</b></td></tr>";
	echo "<tr><td>Spieler gemeldet</td><td><b><font color=\"#FAFA36\">$gesamt</font></b></td></tr>";
	echo "<tr><td>Spieler bezahlt</td><td><b><font color=\"#E52C2C\">$partyBezahlt</font></b></td></tr>";
	echo "<tr><td colspan=\"2\"><img src=\"/gfx/lgif.gif\" height=\"1\" width=\"0\"></td></tr>";
	echo "<tr><td colspan=\"2\" align=\"center\"><small>$anzahlSessions <a class=\"navlink\" href=\"/online.php\">User online</a></small></td></tr>";
	
	echo "</table>\n";
} else {
	// Variante mit grafischen Balken, darunter User online, insb. MultiMadness.de

	echo "<table><tr><td height=\"$nTblHeight\">";
	// Verfügbare Plätze ausgeben
	echo "<img src=\"".PELASHOST."gfx/blackdot.gif\" width=\"".$einTeil*$partyPlaetze."\" height=\"$nHoehe\">";

	echo "</td></tr><tr><td height=\"$nTblHeight\" class=\"navlink\">";

	$breiteRot  = $einTeil*$partyBezahlt;
	$breiteGelb = $einTeil*$partyAngemeldet;
	$breiteGruen= $einTeil*($partyPlaetze-$partyAngemeldet-$partyBezahlt);

	// Bezahlt und angemeldet und evtl. frei ausgeben
	echo "<img src=\"".PELASHOST."gfx/reddot.gif\" width=\"".$breiteRot."\" height=\"$nHoehe\">";

	if ($einTeil*$partyAngemeldet > 0) {
		echo "<img src=\"".PELASHOST."gfx/yellowdot.gif\" width=\"".$breiteGelb."\" height=\"$nHoehe\">";
	}

	if ($breiteGruen > 0) {
		echo "<img src=\"".PELASHOST."gfx/greendot.gif\" width=\"".$breiteGruen."\" height=\"$nHoehe\">";
	}
	
	echo "</td></tr><tr><td><img src=\"/gfx/lgif.gif\" width=\"0\" height=\"3\"></td></tr>";

	echo "</td></tr></table>";
	
	echo "$anzahlSessions <a class=\"navlink\" href=\"/online.php\">User online</a>";

	//##############################
	// Beschriftung

	/*
	Deaktiviert weil sieht doof aus sagt Kokain

	// alles 10% schmaler
	$breiteRot  = $breiteRot * 0.9;
	$breiteGelb = $breiteGelb * 0.9;
	$breiteGruen= $breiteGruen * 0.9;

	echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
	echo "<tr><td><img src=\"/gfx/lgif.gif\" width=\"$breiteRot\" height=\"1\"></td>";
	echo "<td><img src=\"/gfx/lgif.gif\" width=\"$breiteGelb\" height=\"1\"></td>";
	echo "<td><img src=\"/gfx/lgif.gif\" width=\"$breiteGruen\" height=\"1\"></td></tr>";

	echo "<td align=\"center\"><i><b><font color=\"#FF0000\">$partyBezahlt</font></b></i></td>\n";
	echo "<td align=\"center\"><i><b><font color=\"#FFFF00\">$partyAngemeldet</font></b></i></td>\n";
	echo "<td align=\"center\"><i><b><font color=\"#00DD24\">$freiePlaetze</form></b></i></td>\n";
	echo "</tr></table>";

	*/

	// Ende Beschriftung
	//###############################

}
?>
