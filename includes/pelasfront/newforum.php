<?php

include_once "dblib.php";
include_once "format.php";
include_once "session.php";

$Parent = intval($Parent);

if ($_GET[Action] == "view" && $_GET[Parent] != "") {

	echo "<p class=\"hervorgehoben\">Thema einsehen</p>";

	if ($nLoginID < 1) {
		echo "<p>Nur eingeloggte Benutzer k&ouml;nnen Themen beginnen und mitdiskutieren.</p>";
	}

	//Thread ansehen

	displayInhalte($KATEGORIE_FORUM, $Parent);

} elseif ($_GET[iAction] == "add") {

	InhaltAnlegen($KATEGORIE_FORUM, $Parent);

} else {

echo "<table width=\"98%\" border=0><tr><td>";

if ($nLoginID < 1) {
	echo "<p>Nur eingeloggte Benutzer k&ouml;nnen Themen beginnen und mitdiskutieren.</p>";
} else {
	echo "<p>Themen&uuml;bersicht</p>";
}

echo "</td>";

if ($ThemenAnzahl < 1) { $ThemenAnzahl = 15; }

echo "<form name=\"anzahlform\"><td align=\"right\">";
echo "<nobr><p>Angezeigte Themen <select name=\"ThemenAnzahl\" OnChange=\"document.location.href='forum.php?ThemenAnzahl='+document.forms.anzahlform.ThemenAnzahl.value;\">";
echo "<option value=\"15\"";
	if ($ThemenAnzahl == 15) { echo " selected"; }
	echo ">15";
echo "<option value=\"25\"";
	if ($ThemenAnzahl == 25) { echo " selected"; }
	echo ">25";
echo "<option value=\"50\"";
	if ($ThemenAnzahl == 50) { echo " selected"; }
	echo ">50";
echo "<option value=\"100\"";
	if ($ThemenAnzahl == 100) { echo " selected"; }
	echo ">100";
echo "</select></p></nobr></td></tr></table></form>\n";



echo "<table width='100%' cellpadding='3' cellspacing='1' border='0'>";
echo "<tr><td class='forum_titel' width='20'>&nbsp;</td><td class='forum_titel' width='*'><table border='0'><tr><td class='forum_titel'> &nbsp;<b>Thema</b></td></tr></table></td><td class='forum_titel' align=\"center\"><b>Aw.</b></td><td class='forum_titel' width=\"185\" align=\"center\"><b>Autor</b></td><td class='forum_titel' width='115' align=\"center\"><b>Letzter Beitrag</b></td></tr>";

if (!isset($dbh))
	$dbh = DB::connect();

	$result= DB::query("select INHALTID, TITEL, AUTOR, AUTORNAME, DATE1 from INHALT where PARENTID = -1 and KATEGORIEID = $KATEGORIE_FORUM and MANDANTID = $nPartyID order by DATE1 desc");
	//echo DB::$link->errno.": ".DB::$link->error."<BR>";

	$maxcounter = 1;
	$bgClass = "forum_bg1";
	while ($row = $result->fetch_array()) {
		if ($maxcounter > $ThemenAnzahl) {
			break;
		}
		$result_count= DB::query("select COUNT(*) from INHALT where PARENTID = $row[INHALTID] and KATEGORIEID = $KATEGORIE_FORUM and MANDANTID = $nPartyID");
		//echo DB::$link->errno.": ".DB::$link->error."<BR>";
		$row_count = $result_count->fetch_array();

		echo "<tr><td class='$bgClass' width='15' height=\"28\">";
		if ($row_count[0] >= 15) {
			echo "<img src='".PELASHOST."gfx/forum_ordner_burn.gif'>";
		} else {
			echo "<img src='".PELASHOST."gfx/forum_ordner.gif'>";
		}
		echo "</td><td class='$bgClass'><a href='forum.php?Action=view&Parent=$row[INHALTID]&Posts=$row_count[0]'>".db2display($row[TITEL])."</a></td><td class='$bgClass' align=\"center\">$row_count[0]</td><td class='$bgClass' align=\"center\">".db2display($row[AUTORNAME])."</td><td class='$bgClass' align=\"center\"><span class='kleinertext'>".dateDisplay2($row[DATE1])."</span></td></tr>\n";
		$maxcounter++;
		if ($bgClass == "forum_bg1") {
			$bgClass = "forum_bg2";
		} else {
			$bgClass = "forum_bg1";
		}
	}

echo "</table>";

echo "<p><table cellpadding='3' cellspacing='5'><tr><td class='forum_titel'><a href='forum.php?iAction=add&Parent=-1' class=\"forumlink\">Thema erstellen</a></td></tr></table></p>";

}

?>
