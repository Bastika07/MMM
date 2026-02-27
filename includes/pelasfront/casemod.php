<?php
include_once "/var/www/include/dblib.php";
include_once "/var/www/include/format.php";
include_once "/var/www/include/getsession.php";

if (!isset($action)) $action = '';
if (!isset($sort)) $sort = '';
if (!isset($sortnumber)) $sortnumber = '';
if (!isset($beschreibung)) $beschreibung = '';
if (!isset($kurzbeschreibung)) $kurzbeschreibung = '';
if (!isset($artist)) $artist = '';
if (!isset($pelasid)) $pelasid = '';
if (!isset($photo)) $photo = '';

if (isset($dbh))
	$db = $dbh;
elseif (!isset($db))
	$db = DB::connect();
?>
<script language="JavaScript">
<!--
function ShowInfo(Spieler)
{
	detail = window.open("benutzerdetails.php?nPartyID=<?php echo $nPartyID; ?>&nUserID="+Spieler,"Details","width=650,height=550,locationbar=false,resize=true");
	detail.focus();
}
//-->
</script>

<?php

  echo "<!--";
  print_r(get_defined_vars());
  echo "-->";


if($nLoginID <1 && $action != "" && $action != "show" && $action != "detail")
		{echo "Bitte logge dich erst ein, um dein Case f&uuml;r den Contest einzutragen.";}
else {
switch($action)
	{
		case 'add' : add($sLogin,$nLoginID,$beschreibung,$kurzbeschreibung,$type,$artist,$pelasid);break;
		case 'rules' : rules(); break;
		case 'show': show($sort, $sortnumber);break;
		case 'detail': detail($id);break;
		case 'edit' : edit($id,$nLoginID,$kurzbeschreibung,$beschreibung);break;
		case 'bild' : bild($photo, $nLoginID, $id);break;
		case 'upload' : upload($photo, $id);break;
		case 'vote' : vote($votevalue,$casemodid,$nLoginID);break;
		case 'del' : del($file,$id);break;
		default : show($sort, $sortnumber); break;
	}
}

function rules() {

  
  if (BUNGALOWLAN) {
	// Spezielle Regeln für Deutschland sucht den Superbonker LANresort
	?>
	<p>Hier sind sie nun, unsere kompakten Regeln für unseren Wettbewerb:
	<br><br>
	<ol>
	<li> Die Bestimmungen des CenterParcs sind unbedingt zu beachten.
	<li> Die Bonker dürfen nur so weit verändert werden, dass eine vollständige Entfernung der Dekoration gewährleistet ist.
	<li> Schlaf- und Badezimmer werden nicht bewertet.
	</ol>
	<br><br>
	Bewertet werden die folgenden Kriterien auf einer Skala von 1 bis 10:
	<br><br>
	<ol>
	<li> Kreativität
	<li> Aufwand
	<li> Erscheinungsbild
	<li> Themenbezogenheit (LAN-Party, Turnierspiele)
	</ol>
	<br><br>
	Aber nicht nur die Jurybewertung ist entscheident für den Gesamtsieg! Jeder Teilnehmer kann seine Stimme für die Bonkerbewertung abgeben. Die Gesamtwertung setzt sich zu 50% aus der Jurywertung und 50% aus der Teilnehmerwertung zusammen.
	<br><br>
	Die Jury beginnt ihre Bewertung am frühen Samstag Nachmittag. Den genauen Zeitplan legen wir fest, nachdem alle Bonker angemeldet wurden. Anmeldeschluss ist Samstag, 12:00 Uhr. Bis 13:00 Uhr wird dann der Zeitplan für die Jurybewertung veröffentlicht.
	<br><br>
	Einige von euch kennen das Verfahren auch von unseren Casemod-Contests. Auf der Party findet ihr die Anmeldung im Intranet unter www.lan, bei der ihr auch eure Bonker-Fotos hochladen könnt.
	<br><br>
	Die Siegerehrung findet am Samstag Abend bei einer kleinen Glühweinparty auf der Terrasse von Bungalow 808 statt.
	<br><br><b>Wichtig:</b> Bitte unbedingt die <b>Kategorie Casemod</b> wählen, ansonsten keine Bewertung!
	</p>
	<br><br>
  <?php
  } else {
  
  ?> 
  <h1>Regelwerk</h1>
  
  <h2>§1 Casemodcontest</h2>
  (1) Der Casemodcontest ist in zwei unabhängige Bewertungsgruppen aufgeteilt: Gruppe 1 bilden die "Casemods" (case modifications), nämlich durch den Teilnehmer modifizierte bzw. umgebaute Computergehäuse aus industrieller Serienproduktion. In Gruppe 2 treten "Casecons" (case constructions) an. Dies sind vom Teilnehmer komplett selbst entworfene und gebaute Computergehäuse, die der individuellen Originalität oder Funktionalität des beherbergten Rechners besser dienen als Seriengehäuse.
  <br>
  
  <h2>§2 Teilnahmebedingungen</h2>
  (1) Die Teilnahme am Contest ist für jeden Teilnehmer der LAN erlaubt, der ein Seriengehäuse gemäß Gruppe 1 modifiziert hat oder gemäß Gruppe 2 ein Computergehäuse selbst entworfen und gebaut hat.
  <br>
  (2) Die Modifikationen müssen zum größten Teil vom Teilnehmer selbst durchgeführt worden sein, anderenfalls erfolgt der Ausschluss vom Contest durch die Jury.
  <br>
  (3) Da der Sinn und Zweck eines jeden Casemods oder Casecons darin besteht, einen Computer zu beherbergen, muss ein funktionsfähiger Rechner im Gehäuse vorgezeigt werden, der durch den Casemod bzw. Casecon nicht bei der Erfüllung der ihm zugedachten Aufgaben behindert wird. Im Zweifelsfall behält sich die Jury vor, Rechner auf Funktionsfähigkeit zu überprüfen und bei Nichtvorhandensein dieser den Casemod bzw. Casecon von der Teilnahme auszuschließen.
  <br>
  (4) Jeder Teilnehmer muss sich bis zum Start des Contest im Intranet angemeldet haben
  <br>
  (5) Die Bilder vom Gehäuse, die bei der Anmeldung hinzugefügt werden können, dürfen nur leicht vom eigentlichen Gehäuse abweichen, mit dem der Teilnehmer am Contest teilnimmt. Dies wird bei der Bewertung überprüft.
  <br>
  (6) Jeder Teilnehmer sollte zumindest während der Bewertung seines Gehäuses der Jury für Fragen und Erläuterungen zur Verfügung zu stehen.
  <br>
  (7) Jeder darf nur mit einem Casemod ODER einem Casecon teilnehmen.
  <br>
  (8) Von der Teilnahme ausgeschlossen Mitglieder der Jury und ihre Angehörigen.
  <br>
  
  <h2>§3 Bewertung und Siegerehrung</h2>
  (1) Die Bewertung setzt sich aus der Gästebewertung und der Jurybewertung zu je 50% zusammen.
  <br>
  (2) Die Gästebewertung wird im Intranet ermittelt. Dazu können alle Lanteilnehmer im Intranet die Teilnehmenden Casemods bzw. Casecos bewerten. Die Bewertung erfolg in Punkten. Dabei ist 10 die beste Punktzahl, 1 die schlechteste. Aus allen Bewertungen wird der Durchschnitt ermittelt. Die Gästebewertung ist ab Anmeldeschluss, bis zur Bekanntgabe der Jurybewertungen geöffnet.
  <br>
  (3) Für die Jurybewertung steht ein Jury-Team zur Verfügung, das sich aus fachkundigen Vertretern der Szene zusammensetzt.
  <br>
  (4) Jeder Teilnehmer darf der Jury Einzelheiten seines Casemods bzw. Casecons erläutern und vorführen, die rein äußerlich schlecht bzw. nicht zu erkennen sind, aber dennoch zum Konzept des Gehäuses und zur eigenen Leistung des Teilnehmers gehören.
  <br>
  (5) Die Jury berät sich intern über jeden Casemod bzw. Casecon, jedoch ohne konkrete Aussagen zur Bewertung.
  <br>
  (6) Die Kriterien für die Bewertung der Casemods und Casecons sind:
  <ul>
    <li>Optik: Äußeres Erscheinungsbild bzw. der Gesamteindruck des Gehäuses</li>
    <li>Elektrik: Originalität, Funktionalität und Kreativität aller selbst gebauten elektrischen und elektronischen Schaltungen im und am Gehäuse </li>
    <li>Handwerkliches: Der generelle Schwierigkeitsgrad bzw. Anspruch aller modifizierten Teile eines Casemods oder Casecons und die Qualität ihrer Umsetzung </li>
    <li>Kreativität: Die Idee bzw. das konzept hinter einem Casemod bzw. Casecon, ihre Originalität, Funktionalität und Innovation und ihr Begeisterungspotential ("Wow"-Effekt) </li>
  </ul>
  <br>
  (7) Jedes Jurymitglied beurteilt jedes Gehäuse nach seinem Ermessen und vergibt für obige vier Bewertungskriterien Punkte von 0 bis 10. Die Endwertung des Casemods bzw. Casecons orientiert sich am Durchschnitt der vier Bewertungskriterien und wird vom Jurymitglied als Gesamtpunktzahl angegeben. Der Juror kann jedoch nach seinem Ermessen anhand der Funktionalität oder des Themas eines Casemods bzw. Casecons die vier Kriterien für seine Endwertung unterschiedlich gewichten, so dass diese vom Durchschnitt der Kriterien etwas abweichen kann.
  <br>
  (8) Das Gesamturteil über einen Casemod bzw. Casecon ist der Durchschnitt der Endwertungen aller Jurymitglieder in Punkten. Dabei ist 10 die beste Wertung und 1 die schlechteste.
  <br>
  (9) Bewertet wird ausschließlich das Gehäuse bzw. auch gewisse Hardwarelösungen, die selbst gebaut und Teil des Casemods bzw. Casecons sind. Die Hardwareausstattung des Rechners spielt bei der Bewertung keine Rolle, ebenso alle Arten von Hardware-Tuning (z.B. übertakten).
  <br>
  (10) Die Entscheidung der Jury ist nicht anfechtbar. Zudem behält sich die Jury vor Teilnehmer vom Contest auszuschließen.
  <br>
  (11) Die Siegerehrung erfolgt gruppenweise. Anhand der Bewertungen werden die besten Casemods sowie die besten Casecons der LAN ermittelt und ihre Erbauer geehrt. Es besteht kein Anspruch auf Preise, wenn der Teilnehmer vorzeitig die Veranstaltung verlassen hat.
  <br>
  <h2>§4 Rechtliches</h2>
  <br>
  (1) Jeder Teilnehmer erklärt sich damit einverstanden, dass Bilder von ihm und seinem Casemod bzw. Casecon zu Pressezwecken und zur Veröffentlichung auf den Webseiten gemacht werden dürfen.
  <br>
  (2) Der Rechtsweg bei der Bestimmung der Platzierungen ist ausgeschlossen. 
  <br>
  Dieses Regelwerk entstand in Zusammenarbeit mit der Deutschen Casemod Meisterschaft (DCMM) www.dcmm.de

<br><br>
  
  <?php
  }
  
  $sqlab2 = "SELECT STRINGWERT FROM CONFIG WHERE PARAMETER='CASEMOD_OFFEN'";
	$res = DB::query($sqlab2);
	$rows = $res->fetch_array();
	$offen = $rows["STRINGWERT"];
		
	if ($offen == 'J') {	
	  echo "<br><a href=casemod.php?action=add>Am Contest Teilnehmen</a><br><br>";
	}	else { 
	  echo "<br>Die Anmeldung f&uuml;r den Casemod-/Casecon-Contest ist geschlossen"; 
	}
}

function add($sLogin,$nLoginID,$beschreibung,$kurzbeschreibung,$type,$artist,$pelasid) {
  global $db;

  $sqlab = "SELECT STRINGWERT FROM CONFIG WHERE PARAMETER='CASEMOD_OFFEN'";
  $res = DB::query($sqlab);
  $rows = $res->fetch_array();
  $offen = $rows["STRINGWERT"];
  
  if($offen == 'J') {
    $sqlab2 = "select * from CASEMOD where userid=$nLoginID";
    $casemodcheck = DB::query($sqlab2);
    $row2 = $casemodcheck->fetch_assoc();
    if (is_array($row2)) {
      echo "Du hast dich schon f&uuml;r den Casemod-Contest beworben!<br><br><a href='javascript:history.back()'><--zur&uuml;ck</a>";
	  }	else {
	    if(isset($sLogin) && strlen($sLogin) && isset($nLoginID) && strlen($nLoginID)) {
			  if(isset($kurzbeschreibung) && strlen($kurzbeschreibung) && isset($beschreibung) && strlen($beschreibung)) {
          $sqlab = "INSERT INTO CASEMOD (artist,beschreibung,kurzbeschreibung,type,userid,gastbewertung,juribewertung) VALUES ('$artist','$beschreibung','$kurzbeschreibung','$type','$pelasid',0,0)";
			    DB::query($sqlab);
			    
			    $query = "SELECT id FROM CASEMOD WHERE userid='$pelasid'";
			    $result = DB::query($query);
			    $row = $result->fetch_array();
			    $id = $row["id"];
          
          echo("<script language=\"javascript\">location.href='casemod.php?action=bild&id=$id'</script>");
			  } else {
			  ?>
	
				<table><tr><td>
				Hier kannst du Dein Case adden. F&uuml;lle einfach das Formular aus und weiter gehts...<br><br>
				
				</td</tr></table>
				<form name="caseadd" action="casemod.php" method="post">
				<input type="hidden" name="action" value="add"><?
				echo "<input type=hidden name=pelasid value=$nLoginID>";
				echo "<input type=hidden name=artist value=$sLogin>";?>
				
				<table witdh=500 border=0>
				<tr><td>Kurzbeschreibung</td><td><input type="text" name="kurzbeschreibung" size=40></td></tr>
				<tr><td valign=top>Beschreibung:</td><td><textarea rows="10" cols="30" name="beschreibung"></textarea></td></tr>
				<tr><td>Typ:</td><td>
				    <select name="type">
				      <option value="mod">CaseMod</option>
				      <option value="con">CaseCon</option>
				    </select>
				    </td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="Upload>>"></td></tr>
				</table>
				</form>
				<p>
				<b>Frage</b>: Was ist der Unterschied zwischen "CaseMod" und "CaseCon"?
				<br>
				<b>Antwort</b>: CaseMod (lang: Case Modding) bezeichnet das Verändern eines gekauften Gehäuses.<br>
				         CaseCon (lang: Case Construction) steht für die Konstruktion eines selbst entworfenen 
				         Gehäuses.
				</p>
			<?
			  }
		  }
	  }
  } else { 
    echo "<br>Die Anmeldung f&uuml;r den Casemod-Contest ist geschlossen";
  }
}

function show($sort, $sortnumber) {
  global $sLogin, $nLoginID, $db, $nPartyID, $SITZ_RESERVIERT;
	
	// Ausgabe CASEMOD
	
	echo "<h2>CaseMod</h2>";
	
  $timePerGuest = CFG::getMandantConfig("CASEMOD_TIME_PER_GUEST", $nPartyID);
  $timePerGuestTime = CFG::getMandantConfig("CASEMOD_TIME_PER_GUEST_TIME", $nPartyID);
  
  // DIrty Hack weil obigen Funktionen von hier nicht gehen
          $q = 'SELECT STRINGWERT
                FROM CONFIG
                WHERE PARAMETER = "CASEMOD_TIME_PER_GUEST_TIME"
                  AND MANDANTID = "$nPartyID"';
	$timePerGuestTime = DB::getOne($q);
  
   // DIrty Hack weil obigen Funktionen von hier nicht gehen
           $q = 'SELECT STRINGWERT
                 FROM CONFIG
                 WHERE PARAMETER = "CASEMOD_TIME_PER_GUEST"
                   AND MANDANTID = "$nPartyID"';
	$timePerGuest = DB::getOne($q);
  
  if ($timePerGuestTime) {  	
  	$sql = "SELECT 
  						c.userid
						FROM  
							CASEMOD c
						LEFT JOIN 
							SITZ s ON 
								c.userid = s.USERID AND 
								s.restyp = $SITZ_RESERVIERT AND 
								s.mandantid = $nPartyID
						ORDER BY 
							s.REIHE, s.PLATZ ASC";
		$res = DB::query($sql);
		$i = 0;
		while ($row = $res->fetch_row()) {				
			$timeArray[$row[0]] = $timePerGuestTime + $timePerGuest * 60 * $i;
			$i++;
		}

  }
	

	
	$sqlab2 = "SELECT STRINGWERT FROM CONFIG WHERE PARAMETER='CASEMOD_HIDDEN'";
  $res2 = DB::query($sqlab2);
  $rows2 = $res2->fetch_array();
  $hidden = $rows2["STRINGWERT"];
   
  if (isset($hidden) && !empty($hidden)) {
    if ($hidden == 'J') {
	    //Jury- und Gesamt-Bewertung versteckt
	    $hidden = true;
	  }	else	{ 
      //Jury- und Gesamt-Bewertung öffentlich
      $hidden = false;	  
	  }
  }
	
	
	if ($hidden) 
	  $sqlab = "SELECT 
	              id, userid, kurzbeschreibung, artist, type, gastbewertung, 'versteckt' as juribewertung, 'versteckt' as gesamtbewertung 
	            FROM 
	              CASEMOD 
	            ORDER BY ";
	else 
	  $sqlab = "select * from CASEMOD ORDER BY ";
						
  if ($sortnumber == 'ASC') {
    $sortby = "ASC";
		$newsort = "DESC"; 
  } else { 
		$sortby = "DESC"; 
		$newsort = "ASC"; 
  }
  switch($sort)	{
    case 'typ' : $sqlab .= "type $sortby"; break;
    case 'teilnehmer' : $sqlab .= "gastbewertung $sortby"; break;
    case 'jury': $sqlab .= "juribewertung $sortby"; break;
    case 'gesamt': $sqlab .= "gesamtbewertung $sortby"; break;
		default : $sqlab .= "gesamtbewertung $sortby"; break;
	}
	$result = DB::query($sqlab);
	echo DB::$link->error;
	?>
	<table cellpadding="1" cellspacing="1" border="0">
	<tr><td class="TNListe" width="210"><b>Kurzbeschreibung</b></td>
	    <td class="TNListe" width="170"><b>Artist</b></td>
	    <td class="TNListe" width="40"><b><a href=casemod.php?action=show&sort=typ&sortnumber=<?php echo "$newsort"; ?> class=TNLink>Typ</a></b></td>
	    <td class="TNListe" width="90"><b><a href=casemod.php?action=show&sort=teilnehmer&sortnumber=<?php echo "$newsort"; ?> class=TNLink>Teilnehmer</a></b></td>
	    <td class="TNListe" width="90"><b><a href=casemod.php?action=show&sort=jury&sortnumber=<?php echo "$newsort"; ?> class=TNLink>Jury</a></b></td>
	    <td class="TNListe" width="90"><b><a href=casemod.php?action=show&sort=gesamt&sortnumber=<?php echo "$newsort"; ?> class=TNLink>Gesamt</a></b></td>
	    <td class="TNListe" width="90"><b>ca. Jury-Zeit</b></td>
	    <td class="TNListe">&nbsp;</td></tr>
	<?
	while($row = $result->fetch_array()) {
	  $id = $row["id"];
	  $kurzbeschreibung = db2display($row["kurzbeschreibung"]);
	  $artist = $row["artist"];
	  $gastbewertung = $row["gastbewertung"];
	  $juribewertung = $row["juribewertung"];
	  $bewertung = $row["gesamtbewertung"];
	  $pelasid = $row["userid"];
		
	  echo ("<tr><td class=\"TNListeTDA\"><a href=casemod.php?action=detail&id=$id>$kurzbeschreibung</a></td>	         
	         <td class=\"TNListeTDB\"><a href=javascript:ShowInfo($pelasid);>".db2display($artist)."</a></td>
	         <td class=\"TNListeTDB\" align=\"center\">".ucfirst($row['type'])."</td>
	         <td class=\"TNListeTDA\">$gastbewertung</td><td class=\"TNListeTDB\">$juribewertung</td><td class=\"TNListeTDA\">$bewertung</td>");
	  
	  echo "<td class=\"TNListeTDB\" align=\"center\">";
	  if (!isset($timeArray[$pelasid]))
	  	echo "n/a";
	  else
	  	echo "<nobr>".date('H:i, d.m.', $timeArray[$pelasid])."</nobr>";
	  
	  echo "</td>";
	  echo ("<td class=\"TNListeTDB\">");
	  if ($pelasid == $nLoginID) { 
	    echo "<a href='casemod.php?action=edit&id=$id'><img src='gfx/turnier_join.gif' alt='Edit' border=0></a>"; 
	  }
	  echo("</td></tr>");
	}
	echo "</table>";
		
	$sqlab2 = "SELECT STRINGWERT FROM CONFIG WHERE PARAMETER='CASEMOD_OFFEN'";
	$res = DB::query($sqlab2);
	$rows = $res->fetch_array();
	$offen = $rows["STRINGWERT"];
		
	if ($offen == 'J') {	
	  echo "<br><a href=casemod.php?action=rules>Am Contest Teilnehmen</a><br><br>";
	}	else { 
	  echo "<br>Die Anmeldung f&uuml;r den Casemod-/Casecon-Contest ist geschlossen"; 
	}
	
	echo "<p>'ca. Jury-Zeit' bezeichnet die ungefähre Zeit, wann die Jury den Rechner bewertet.</p>";
	
}

function detail($id) {
	global $nLoginID, $db;
	

        $sqlab2 = "SELECT STRINGWERT FROM CONFIG WHERE PARAMETER='CASEMOD_HIDDEN'";
  $res2 = DB::query($sqlab2);
  $rows2 = $res2->fetch_array();
  $hidden = $rows2["STRINGWERT"];

  if (isset($hidden) && !empty($hidden)) {
    if ($hidden == 'J') {
            //Jury- und Gesamt-Bewertung versteckt
            $hidden = true;
          }     else    {
      //Jury- und Gesamt-Bewertung öffentlich
      $hidden = false;
          }
  }

	$sqlab = "select 
	            c.id, c.artist, c.beschreibung, c.kurzbeschreibung, 
	            c.juribewertung, c.gastbewertung, c.userid, c.type
	          FROM 
	            CASEMOD AS c, USER AS u 
	          WHERE 
	            c.id = $id AND 
	            c.userid = u.USERID";
	$result = DB::query($sqlab);
	$row = $result->fetch_array();

	$beschreibung = db2display($row["beschreibung"]);
	$kurzbeschreibung = db2display($row["kurzbeschreibung"]);
	$juribewertung = $row["juribewertung"];

	$gastbewertung = $row["gastbewertung"];

	$pelasid = $row["userid"];
	$artist = $row["artist"];
	$bewertung = number_format(($juribewertung+$gastbewertung)/2,2,".",".");

	if ($hidden) {
        	$juribewertung = 'versteckt';	
        	$bewertung = 'versteckt';
	}

	echo "<table cellspacing=\"1\" cellpadding=\"1\" border=\"0\" width=\"600\">";
	echo "<tr><td class=\"TNListe\" colspan=\"2\"><b>Details</b></td></tr>";
	echo "<tr><td class=\"TNListeTDA\"><b>Bewertungen:</b></td><td class=\"TNListeTDB\">Teilnehmer: 
$gastbewertung - Jury: $juribewertung - Gesamt: $bewertung</td></tr>";
	echo "<tr><td class=\"TNListeTDA\"><b>Artist:</b></td><td class=\"TNListeTDB\"><a href=javascript:ShowInfo($pelasid);>$artist</a></td></tr>";
	echo "<tr><td class=\"TNListeTDA\"><b>Kurzbeschreibung:</b></td><td class=\"TNListeTDB\">$kurzbeschreibung</td></tr>";
	echo "<tr><td class=\"TNListeTDA\"><b>Typ:</b></td><td class=\"TNListeTDB\">".ucfirst($row['type'])."</td></tr>";
	echo "<tr><td valign=top class=\"TNListeTDA\"><b>Beschreibung:</b></td><td class=\"TNListeTDB\">$beschreibung</td></tr>";
	echo "</table><br>";

    $sqlab2 = "SELECT STRINGWERT FROM CONFIG WHERE PARAMETER='CASEMODVOTE_OFFEN'";
    $res2 = DB::query($sqlab2);
    $rows2 = $res2->fetch_array();
    $voteoffen = $rows2["STRINGWERT"];
		
		if ($voteoffen == 'J')
		{
        if(isset($nLoginID) && strlen($nLoginID))
        {
	    $sqlab2 = "select * from USER2CASEMOD where casemodid='$id' AND userid=$nLoginID";
	    $user2casemodcheck = DB::query($sqlab2);
	    $row2 = $user2casemodcheck->fetch_assoc();
	    if (is_array($row2))
	    {
	    echo "Du hast den Teilnehmer schon mit <b>".$row2['rating']."</b> bewertet.<br><br>";
	    }
	    else
	    {	
	    echo "<form action='casemod.php' method='POST'>";
	    echo "<input type=hidden name='casemodid' value='$id'>";
	    echo "<input type=hidden name='action' value='vote'>";
	    echo "Bewertung:<br>";
	    echo "<table cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";
	    echo "<td><input type='radio' name='votevalue' value='1'></td>";
	    echo "<td><input type='radio' name='votevalue' value='2'></td>";
	    echo "<td><input type='radio' name='votevalue' value='3'></td>";
	    echo "<td><input type='radio' name='votevalue' value='4'></td>";
	    echo "<td><input type='radio' name='votevalue' value='5'></td>";
	    echo "<td><input type='radio' name='votevalue' value='6'></td>";
	    echo "<td><input type='radio' name='votevalue' value='7'></td>";
	    echo "<td><input type='radio' name='votevalue' value='8'></td>";
	    echo "<td><input type='radio' name='votevalue' value='9'></td>";
	    echo "<td><input type='radio' name='votevalue' value='10'></td>";
	    echo "<td rowspan='2'>&nbsp;</td><td rowspan='2'><input type='submit' value='VOTE'></td><td></td></tr>"; /*<input type='image' src='vote.gif'>*/
	    echo "<tr><td align='center'>1</td><td align='center'>2</td><td align='center'>3</td><td align='center'>4</td><td align='center'>5</td><td align='center'>6</td><td align='center'>7</td><td align='center'>8</td><td align='center'>9</td><td align='center'>10</td>";
	    echo "</table>";
	    echo "</form>";
	    }
		}
		else echo "Um Voten zu k&ouml;nnen, musst du eingeloggt sein!<br><br>";
		}
		else echo "Der Vote ist geschlossen!<br><br>";
    
	echo "<table cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";

	
	$Sdir = "/var/www/casemod/";
	exec("ls ".$Sdir.$pelasid."_* 2>&1",$Slines,$Src);
	$Scount = count($Slines) - 1;
	for ($Si = 0; $Si <= $Scount ; $Si++) {
		$newArray = explode ("/", $Slines[$Si]);
		$sDateiName = $newArray[sizeof($newArray)-1];
		if (!strstr($sDateiName,"No such file or directory"))
		  echo "<tr><td class=\"TNListeTDA\"><img src='/casemod/".$sDateiName."'><br></td>\n";
		
	}
	echo "</table>";
}

function vote($votevalue,$casemodid,$nLoginID)
{
	global $db;
	$sqlab = "select * from USER2CASEMOD where casemodid=$casemodid AND userid=$nLoginID";
	$user2casemodcheck = DB::query($sqlab);
	$row = $user2casemodcheck->fetch_assoc();
	if (is_array($row))
	{
		echo "Du hast den Teilnehmer schon mit <b>".$row['rating']."</b> bewertet.";
	}
	else
	{
	    if(isset($votevalue) && strlen($votevalue))
	    {
                PELAS::logging("vote fuer case: '$casemodid', rating: '$votevalue'", 'casemod', $nLoginID);

		$sqlab = "INSERT INTO USER2CASEMOD (casemodid,userid,rating) VALUES ('$casemodid','$nLoginID','$votevalue')";
		DB::query($sqlab);
		$num = DB::$link->affected_rows;
		
		
		$res = DB::query("SELECT AVG(rating) FROM USER2CASEMOD WHERE casemodid=$casemodid");
		$row = $res->fetch_array();
		$gastbewertung = $row["AVG(rating)"];
		
		$res2 = DB::query("SELECT juribewertung FROM CASEMOD WHERE id=$casemodid");
		$row2 = $res2->fetch_array();
		$juribewertung = $row2["juribewertung"];
		
		$gesamtbewertung=number_format(($gastbewertung+$juribewertung)/2,2,".",".");
		
		$sqlab2 = "UPDATE CASEMOD SET gesamtbewertung = $gesamtbewertung WHERE id = $casemodid";
		DB::query($sqlab2);
		
		$sqlab2 = "UPDATE CASEMOD SET gastbewertung = $gastbewertung WHERE id = $casemodid";
		DB::query($sqlab2);
		echo DB::$link->error;						
		if ($num>0) echo "Dein Eintrag wurde gespeichert";
		else echo "Konnte nicht gespeichert werden";														 
	
	    }
	    else echo "Du musst einen Wert f&ouml;r den Vote eingeben!!<br><br><a href='javascript:history.back()'><--zur&uuml;ck</a>";
	}
}

function bild($photo, $nLoginID, $id) {
  global $db;
	$sqlab = "select userid from CASEMOD where id=$id";
	$result = DB::query($sqlab);
	$row = $result->fetch_array();
	$userid = $row["userid"];
	echo DB::$link->error;

  if ($nLoginID == $userid) {
    $Sdir = "/var/www/casemod/";
    exec("ls ".$Sdir.$nLoginID."_* 2>&1",$Slines,$Src);
    $Scount = count($Slines) - 1;
    for ($Si = 0; $Si <= $Scount ; $Si++) {
    if (substr($Slines[$Si],(Strlen($Slines[$Si])-5),Strlen($Slines[$Si]) ) == ".phpl") {
    echo "<a href='$Sdir".$Slines[$Si]."' target='_blank'>".substr($Slines[$Si],0,(Strlen($Slines[$Si])-5))."</a><br>\n";
    $counter ++;
    	}
    }
    if ($Scount <= 20) {
      $count = count($Slines);
      echo "<table witdh=500 border=0><tr><td><a href=\"casemod.php?action=edit&id=$id\"> Eintrag Editieren</a> - <a href=\"casemod.php?action=detail&id=$id\">Mein Case</a> - <a href=\"casemod.php\">Alle Cases</a><br><br></td></tr></table>";
      echo "<table><tr><td>";
      echo "Die Bilder d&uuml;fen nicht gr&ouml;sser als 100Kb und 800x600 Pixel sein!<br><br><b>Bild Hochladen:<b><br><br>";
      echo "<form action='casemod.php' method='post' enctype='multipart/form-data'>";
      echo "<input name='action' type='hidden' value='upload'>";
      echo "<input name='photo' type='file'>";
      echo "<input type='submit' name='Submit' value='Upload>>'>";
      echo "<input type=hidden name=id value=$id>";
      echo "</form>";
      echo "</td></tr></table>";
      
      echo "<table cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";
      
      $Sdir = "/var/www/casemod/";
      if (!isset($pelasid)) $pelasid = '';
      exec("ls ".$Sdir.$pelasid."_* 2>&1",$Slines,$Src);
      $Scount = count($Slines) - 1;
      for ($Si = 0; $Si <= $Scount ; $Si++) {
        $newArray = explode ("/", $Slines[$Si]);
        $sDateiName = $newArray[sizeof($newArray)-1];
        if (strstr($sDateiName,"No such file or directory")) { 
        } else 
          echo "<tr><td class=\"TNListeTDA\"><img src='/casemod/".$sDateiName."'><br><a href=casemod.php?action=del&file=$sDateiName&id=$id>Löschen</a>";
      }
    	echo "</table>";
    } else {
      echo("<script language=\"javascript\">location.href='casemod.php'</script>");
    }
  } else echo "Falscher User";
}

function upload($photo, $id)
{
    $dir = "/var/www/casemod/";
    global $nLoginID;
	if ($photo != "" && $photo != "none") {
	if ($photo_size > 100000) {
	echo "Die Datei ist gr&oouml;sser als 100Kb <br><br><a href='javascript:history.back()'><--zur&uuml;ck</a>";
	} else {
	// hhe checken, Max. 600 px
	$aImageSize = GetImageSize($photo);
	if ($aImageSize[1] > 600) {
	echo "Die Datei ist h&ouml;her als 600 Pixel<br><br><a href='javascript:history.back()'><--zur&uuml;ck</a>";
	} else {
	$timestamp = date("Ymd\THis");
	$newfile = $dir.$nLoginID."_".$timestamp.".jpg";
        echo "photo: '$photo' newfile: '$newfile'";
	if (!copy($photo, $newfile)) {
	$sMeldung = "interror";
	} else {echo("<script language=\"javascript\">location.href='casemod.php?action=bild&id=$id'</script>");}
}
}}
}

function edit($id,$nLoginID,$kurzbeschreibung,$beschreibung)
{
	global $db;
		$sqlab = "select userid from CASEMOD where id=$id";
		$result = DB::query($sqlab);
		$row = $result->fetch_array();
		$userid = $row["userid"];
if ($nLoginID == $userid) 
{

if(isset($kurzbeschreibung) && strlen($kurzbeschreibung) && isset($beschreibung) && strlen($beschreibung) && isset($id) && strlen($id))
	{
	
	$query = "update CASEMOD set kurzbeschreibung = '$kurzbeschreibung', beschreibung = '$beschreibung' WHERE id = '$id'";	
	DB::query($query);
	echo DB::$link->error;
	echo("<script language=\"javascript\">location.href='casemod.php'</script>");
	}
	else 
	{
		$query = "select kurzbeschreibung, beschreibung from CASEMOD where id=$id";
		$result = DB::query($query);
		$row = $result->fetch_array();
		$kurzbeschreibung = $row["kurzbeschreibung"];
		$beschreibung = $row["beschreibung"];

		echo "<table witdh=500 border=0><tr><td><a href=\"casemod.php?action=bild&id=$id\"> Bilder Uploaden/Löschen</a> - <a href=\"casemod.php?action=detail&id=$id\">Mein Case</a> - <a href=\"casemod.php\">Alle Cases</a></td></tr></table>";
		echo "<form name=\"newsedit\" action=\"casemod.php\" method=\"post\"><input type=\"hidden\" name=\"action\" value=\"edit\"><table witdh=500 border=0><tr><td>Kurzbeschreibung:</td><td><input type=text name=kurzbeschreibung value='$kurzbeschreibung' size=64></td></tr>";
		echo "<tr><td valign=top>Beschreibung:</td><td><textarea rows=20 cols=48 name='beschreibung'>$beschreibung</textarea></td></tr>";
		echo "<input type=hidden name=id value=$id>";
		echo "<input type=hidden name=\"action\" value=\"edit\">";
		echo "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Senden\">&nbsp;<input type=\"reset\" value=\"Löschen\"></td></tr></table></form>";		
	}
} 
else echo "Falscher User";
}

function del($file,$id)
{
global $db;
global $nLoginID;
		$sqlab = "select userid from CASEMOD where id='$id'";
		$result = DB::query($sqlab);
		$row = $result->fetch_array();
		$userid = $row["userid"];
		echo DB::$link->error;
if (!preg_match("/$userid/i", $file)) {
	echo "NICHT ZULÄSSIG"; exit;}
else {

$rm = `rm /var/www/casemod/$file`;

echo "Datei gelöscht";
echo("<script language=\"javascript\">location.href='casemod.php?action=bild&id=$id'</script>");
}


}
?>
