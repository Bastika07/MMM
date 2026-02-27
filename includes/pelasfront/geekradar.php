<?php

include_once "dblib.php";
include_once "format.php";
require_once 'constants.php';


// Aktuelle Party-ID raussuchen
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

// WICHTIG! Sonst wird statt 2.5 2,5 geschrieben! 
setlocale(LC_ALL, 'en_US');

$time_start = getmicrotime();

define('GFX_FILE', 'geekradar/map.png');
define('TXT_FILE', 'geekradar/map.txt');

// y
define('MIN_BREITENGRAD', 47.2);
define('MAX_BREITENGRAD', 55.1);

// x
define('MIN_LAENGENGRAD', 5.5);
define('MAX_LAENGENGRAD', 15.5);

define('PIXEL_KM_RATE', 1.63441195);

//gerundet auf ein vielfaches von ROUND_TARGET
define('ROUND_TARGET', 4);

define('DISPLAY_USER_PER_COLUMN', 10);

define('GFX_TEMPLATE', 'geekradar/D.png');

define('DAY', 86400);
define('INTERVAL', DAY/4);

define('FORCE_GENERATE', false);

if (!file_exists(GFX_FILE) || (filectime(GFX_FILE) < time() - INTERVAL) 
    || 
    !file_exists(TXT_FILE) || (filectime(TXT_FILE) < time() - INTERVAL) || FORCE_GENERATE) {
	define('DELTA_BREITENGRAD', (MAX_BREITENGRAD - MIN_BREITENGRAD));
	define('DELTA_LAENGENGRAD', (MAX_LAENGENGRAD - MIN_LAENGENGRAD));
	define('ROUND_FACTOR', 10 / ROUND_TARGET);
	
	createMap();  
}

$teilnehmerMap = file_get_contents(TXT_FILE);

echo "\n<map name=\"TeilnehmerMap\">\n";
echo $teilnehmerMap;
echo "</map>\n";

echo "\n<img src=\"".GFX_FILE."\" border=0  usemap=\"#TeilnehmerMap\" style=\"float:left; margin-right:35px; margin-bottom:30px;\">\n";

// Internationale Besucher als Liste zeigen
$sql = "SELECT
		   u.LAND,
		  (select count(distinct USERID) from USER where UCASE(LAND) = UCASE(u.LAND)) as anz,
    	  (select isoCode from acc_flags where UCASE(isoCode) = UCASE(u.LAND)) as isoCode
        FROM    
          USER u,
          acc_tickets t
        WHERE   
          t.userId = u.USERID AND
          t.partyId = '$aktuellePartyID' AND
          t.statusId = ".ACC_STATUS_BEZAHLT." AND
          UCASE(u.LAND) != 'de'
        GROUP BY 
          u.LAND";          
/*$sql = "SELECT distinct
				  count(*) anz, u.LAND, f.*
				FROM 
  			  USER u, acc_tickets t, acc_flags f 
				WHERE 
  			  t.userId = u.USERID AND 
  			  t.partyId = '$aktuellePartyID' AND 
  			  t.statusId = ".ACC_STATUS_BEZAHLT." AND 
  			  UCASE(u.LAND) != 'de' AND
  			  UCASE(f.isoCode) = UCASE(u.LAND) 
				GROUP BY 
  			  u.LAND";*/

$result = DB::query($sql);


if ($result->num_rows > 0) {
	echo "<br><br><table width=\"410\" cellpadding=\"2\" cellspacing=\"1\"><tr><td class=\"header\" colspan=\"3\"><b>International visitors</b></td></tr>";
	$class = "hblau";
	
	$sql_userlist = "SELECT distinct 
				  					u.LOGIN, u.PLZ, u.ORT, u.LAND
									FROM 
										USER u, acc_tickets t, acc_flags f 
									WHERE 
										t.userId = u.USERID AND 
										t.partyId = '$aktuellePartyID' AND 
					  			  t.statusId = ".ACC_STATUS_BEZAHLT." AND 
										UCASE(u.LAND) != 'de' AND 
										UCASE(f.isoCode) = UCASE(u.LAND)";

	$result_userlist = DB::query($sql_userlist);
	while ($row_userlist = $result_userlist->fetch_assoc()) {
		$userlist[$row_userlist['LAND']][] = $row_userlist['LOGIN']." (".$row_userlist['PLZ'].", ".$row_userlist['ORT'].")";
	}
	
		
	while ($row = $result->fetch_assoc()) {
		if ($sLang == "en") {
			$userHtml =  "<b>".db2display($row['descEnglish']).":</b><br>";
		} else {
			$userHtml =  "<b>".db2display($row['descGerman']).":</b><br>";
		}		
		$userHtml .= implode("<br>", $userlist[strtolower($row['isoCode'])]);
		echo "<tr onMouseOver=\"return overlib('<table><tr><td class=\'geekradar\' nowrap>$userHtml</td></tr></table>', FGCOLOR, '#DEDEDE', BGCOLOR, '#CCCCCC', CENTER);\" onMouseOut=\"return nd();\"><td class=\"$class\" width=\"30\">".$row['anz']."</td><td class=\"$class\" width=\"15\">";
		
		echo PELAS::displayFlag($row['isoCode']);
		
		echo "</td><td class=\"$class\" width=\"145\">";
		if ($sLang == "en") {
			echo db2display($row['descEnglish']);
		} else {
			echo db2display($row['descGerman']);
		}
		echo "</td></tr>";
		
		if ($class == "hblau") {
			$class = "dblau";
		} else {
			$class = "hblau";
		}
	}
	echo "</table>";
}


$time_end = getmicrotime();
$time = $time_end - $time_start;

echo "<!--needed $time seconds-->\n";

function createMap() {
	global $aktuellePartyID, $nPartyID, $STATUS_BEZAHLT, $STATUS_BEZAHLT_LOGE;
  	//ImageCreation und Map creation
  	$im = imageCreateFromPNG(GFX_TEMPLATE);
  	$kreisfarbe = ImageColorAllocate($im, 112, 112, 112);
  
  	//Picture Koordinatenbegrenzungen
	$karte['x'] = imagesx($im);
	$karte['y'] = imagesy($im);

	// Koordinaten der Party holen
	$sql = "SELECT 
		 g.laenge, g.breite
		FROM 
		  `party` p, geo g
		WHERE 
		  p.partyId = '$aktuellePartyID' AND 
		  p.locationPLZ = g.plz";
	$res = DB::query($sql);
	if (!$res || $res->num_rows == 0) {
		//trigger_error('No coordinates for party found', E_USER_ERROR);
		echo "<p class=\"fehler\">Fehler: Keine aktive Party gefunden, alter G33kradar wird angezeigt.</p>";
	} else {
		$row = $res->fetch_assoc();
		$party_x = $row['laenge'];
		$party_y = $row['breite'];

		// Runden?
		$party_x = round(ROUND_FACTOR * (($party_x - MIN_LAENGENGRAD) * $karte['x'] / DELTA_LAENGENGRAD), -1) / ROUND_FACTOR; 
		$party_y = round(ROUND_FACTOR * ((MAX_BREITENGRAD - $party_y) * $karte['y'] / DELTA_BREITENGRAD), -1) / ROUND_FACTOR; 
	//	$party_x = ($party_x - MIN_LAENGENGRAD) * $karte['x'] / DELTA_LAENGENGRAD;
	//	$party_y = (MAX_BREITENGRAD - $party_y) * $karte['y'] / DELTA_BREITENGRAD;


		$sql = "SELECT
			  u.userid, u.login, u.plz, u.ort,
			  cast((round(".ROUND_FACTOR." * ((g.laenge - ".MIN_LAENGENGRAD.") * ".$karte['x'] / DELTA_LAENGENGRAD."), -1) / ".ROUND_FACTOR.") as unsigned) as x,
			  cast((round(".ROUND_FACTOR." * ((".MAX_BREITENGRAD." - g.breite) * ".$karte['y'] / DELTA_BREITENGRAD."), -1) / ".ROUND_FACTOR.") as unsigned) as y,
			  round(sqrt(
			    pow((round(".ROUND_FACTOR." * ((g.laenge - ".MIN_LAENGENGRAD.") * ".$karte['x'] / DELTA_LAENGENGRAD."), -1) / ".ROUND_FACTOR.") - $party_x, 2)
			    +
			    pow((round(".ROUND_FACTOR." * ((".MAX_BREITENGRAD." - g.breite) * ".$karte['y'] / DELTA_BREITENGRAD."), -1) / ".ROUND_FACTOR.") - $party_y, 2)
			  ) * ".PIXEL_KM_RATE.", 2) as entfernung
			FROM    
			  USER u,
			  acc_tickets t,
			  geo g
			WHERE   
			  t.userId = u.USERID AND
			  t.partyId = '$aktuellePartyID' AND
			  t.statusId = ".ACC_STATUS_BEZAHLT." AND
			  g.PLZ = u.PLZ AND
			  u.LAND = 'de'
			GROUP BY 
			  u.USERID
			ORDER BY
			  u.plz";
		$result = DB::query($sql);

		while ($row = $result->fetch_assoc()) {
			$row['ort'] = ucfirst($row['ort']);
			if (strlen(trim($row['ort'])) == 0)
				$row['ort'] = "unbekannt";
			$data[$row['x']][$row['y']][] = $row;
		}

		//$hr = "<hr style=\'border:1px dotted orange;\'>";
	//	$hrlen = strlen($hr);
		$bgcolor = '#DEDEDE';
		$teilnehmerMap = "";

		if ($data) {
			foreach ($data as $x => $val) {
				foreach ($val as $y => $userArray) {  			
					foreach ($userArray as $val)
						$users[$val['plz']][$val['ort']][] = str_replace('&', '&amp;', db2display($val['login']));
					$userInColumn = 0;
					$columns = 1;
					$mouseOverContent = "<table><tr><td class=\'geekradar\' nowrap>";
					foreach ($users as $plz => $array) {
						foreach ($array as $ort => $usernames) {
							$userInColumn += count($usernames);
							$mouseOverContent .= "<b>Ort: </b>".db2display($plz).", ".db2display($ort)."<br>";
							$mouseOverContent .= '- ' . implode("<br>- ", $usernames);
		//  					foreach ($array2 as $userName) {
		//  						$mouseOverContent .= "- ".db2display($userName)."<br>";
		//  					}
							$mouseOverContent .= "<br>";
							if ($userInColumn >= DISPLAY_USER_PER_COLUMN) {
								$mouseOverContent .= "</td><td class=\'geekradar\' nowrap>";
								$columns++;
								$userInColumn = 0;
							} else {
		//  						$mouseOverContent .= "<br>$hr";
							}
						}
					}
		//			$mouseOverContent = substr($mouseOverContent, 0, strlen($mouseOverContent) - $hrlen);
					$mouseOverContent .= "</td></tr><tr><td colspan=\'$columns\' class=\'geekradar\' bgcolor=\'#EEEEEE\'><b>Entfernung:</b> ca. $val[entfernung] km</td></tr></table>";
		//			$mouseOverContent .= "<b>Entfernung:</b> ca. $val[entfernung] km";
					$teilnehmerMap .= "<area href=\"#\" shape=\"circle\" coords=\"$x, $y, 3\" style=\"cursor = 'crosshair';\""
						."onMouseOver=\"return overlib('$mouseOverContent', FGCOLOR, '$bgcolor', BGCOLOR, '#CCCCCC', CENTER);\" onMouseOut=\"return nd();\">\n";
					imagefilledarc($im, $x, $y, 3, 3, 0, 360, $kreisfarbe, IMG_ARC_PIE);	
					unset($users);
					unset($mouseOverContent);
				}
			}
		
		}

		// Text und Bild abspeichern
		$fp = fopen(TXT_FILE, 'w');
		fwrite($fp, $teilnehmerMap);
		fclose($fp);
		ImagePng($im, GFX_FILE);
		ImageDestroy($im);
	}
}

function getmicrotime()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

?>
