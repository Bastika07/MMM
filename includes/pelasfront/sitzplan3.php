<?php
require_once "dblib.php";
require_once "session.php";
require_once "format.php";
require_once "language.inc.php";
require_once('classes/PelasSmarty.class.php');

//ini_set('display_errors', 1);
//error_reporting(E_ALL);


//DEFINE('FONT', '/usr/share/fonts/truetype/ttf-bitstream-vera/Vera.ttf');
DEFINE('FONT', PELASDIR.'/gfx/server_id_card/Avgardm.ttf');
DEFINE('FONT_SIZE', 6);
DEFINE('FONT_ROTATION', 90);

DEFINE('AUSRICHTUNG_SUED', 3);
DEFINE('AUSRICHTUNG_NORD', 4);
DEFINE('AUSRICHTUNG_SCHRAEG', 5);	


// TODO: mehrere Ebenen ermöglichen
$ebene = 1;

$overviewImageFile = PELASDIR."sitzbild/sitzplan_bild_${nPartyID}_${ebene}.png";
$overviewImageLockFile = PELASDIR."sitzbild/sitzplan_html_${nPartyID}_${ebene}.png.lock";

$overviewHtmlFile = PELASDIR."sitzbild/sitzplan_html_${nPartyID}_${ebene}.txt";
$overviewHtmlLockFile = PELASDIR."sitzbild/sitzplan_html_${nPartyID}_${ebene}.txt.lock";

$vorlageImageFile = PELASDIR."sitzbild/vorlage_${nPartyID}_${ebene}.png";

$minimapImageFile = PELASDIR."sitzbild/sitzplan_bild_small_${nPartyID}_${ebene}.png";
$minimapImageLockFile = PELASDIR."sitzbild/sitzplan_bild_small_${nPartyID}_${ebene}.png.lock";

if (!file_exists($overviewImageLockFile)) {
  $fp = fopen($overviewImageLockFile, 'x');
  fclose($fp);
}

if (!file_exists($overviewHtmlLockFile)) {
  $fp = fopen($overviewHtmlLockFile, 'x');
  fclose($fp);
}

if (!file_exists($minimapImageLockFile)) {
  $fp = fopen($minimapImageLockFile, 'x');
  fclose($fp);
}


$pixel = PELASDIR."sitzbild/pixel.gif";

if (isset($_GET['action']) && !empty($_GET['action'])) {
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
	switch ($_GET['action']) {
		// Images
		case 'overview':		  
		  header('Content-type: image/png');      
		  
		  // neu erzeugen?
			if (isset($_GET['generate']) && $_GET['generate'] == 'true' || !file_exists($overviewImageFile)) {
				$overviewImage = generateOverview($overviewImageFile, $overviewHtmlFile, $vorlageImageFile, $ebene, $nPartyID);
			}
			
			// User eingeloggt? Entsprechenden Plan für ihn generieren		
			if (isset($nLoginID) && $nLoginID > 0) {			  
			  if (!isset($overviewImage)) {
			    // Bild wurde nicht neu erzeugt, aus Datei lesen
			    $overviewImage = imagecreatefrompng($overviewImageFile);
			  }
			  $personalOverviewImage = generatePersonalOverview($overviewImage, $ebene, $nPartyID, $nLoginID);
			  imagepng($personalOverviewImage);			  
	      imagedestroy($personalOverviewImage);
			} else {
			  readfile($overviewImageFile);
			}
			break;
		case 'minimap':
			if (isset($_GET['generate']) && $_GET['generate'] == 'true' || !file_exists($minimapImageFile)) {
				generateMinimap($minimapImageFile, $overviewImageFile, $minimapWidth);
			}
			header('Content-type: image/png');
			readfile($minimapImageFile);
			break;
		case 'pixel':
			header('Content-type: image/gif');
			readfile($pixel);
			break;
		// IFrames
		case 'overviewiframe':			
		  if (isset($_GET['generate']) && $_GET['generate'] == 'true' || !file_exists($overviewHtmlFile)) {
				generateOverview($overviewImageFile, $overviewHtmlFile, $vorlageImageFile, $ebene, $nPartyID);
			}		
			$smarty = new PelasSmarty('sitzplan');
			$smarty->assign('htmlMap', file_get_contents($overviewHtmlFile));
			$smarty->displayWithFallback("overviewiframe.tpl");			
			break;
		case 'mainiframe':		
			if (!file_exists($overviewImageFile))
			  generateOverview($overviewImageFile, $overviewHtmlFile, $vorlageImageFile, $ebene, $nPartyID);
			$overviewSize = getimagesize($overviewImageFile);
			if (true || !file_exists($minimapImageFile))
				generateMinimap($minimapImageFile, $overviewImageFile, $minimapWidth);
			$minimapSize = getimagesize($minimapImageFile);
		
		
			$smarty = new PelasSmarty('sitzplan');
						
			$smarty->assign("userId", $nLoginID);
			
			$smarty->assign("minimap", $minimapImageFile);
			$smarty->assign("minimapWidth", $minimapSize[0]);
			$smarty->assign("minimapHeight", $minimapSize[1]);
			$smarty->assign("overview", $overviewImageFile);
			$smarty->assign("overviewWidth", $overviewSize[0]);
			$smarty->assign("overviewHeight", $overviewSize[1]);
			
			$smarty->assign("overviewIframeWidth", $overviewIframeWidth);	
			$smarty->assign("overviewIframeHeight", $overviewIframeHeight);	
			
			$smarty->assign("boxWidth", min(round($overviewIframeWidth  / $overviewSize[0] * $minimapSize[0]) - 4, $minimapSize[0] - 4));
			$smarty->assign("boxHeight", min(round($overviewIframeHeight / $overviewSize[1] * $minimapSize[1]) - 4, $minimapSize[1] - 4));
			$smarty->displayWithFallback("main.tpl");
			break;
	}
} else {
  $smarty = new PelasSmarty('sitzplan');
  
  //reservieren
  if (isset($_GET['reserve'])) {
    $ticketId = intval($_GET['ticketId']);
    $row = intval($_GET['row']);
    $seat = intval($_GET['seat']);
    $smarty->assign('row', $row);
    $smarty->assign('seat', $seat);
    if (reserveSeat($ticketId, $row, $seat)) {
      $smarty->assign("reservation", true);      
    } else {
      $smarty->assign("reservation", false);
    }
  }	 
  
  // freigeben
  if (isset($_GET['free'])) {
    $row = intval($_GET['row']);
    $seat = intval($_GET['seat']);
    $smarty->assign('row', $row);
    $smarty->assign('seat', $seat);
    if (freeSeat($row, $seat)) {
      $smarty->assign("free", true);
    } else {
      $smarty->assign("free", false);
    }
  }	
  
	if ($nLoginID > 0) {
  	$tickets = getUsersTickets($nPartyID, $nLoginID);
  	$tickets = addSeatCoordsForTickets($tickets);
  	$smarty->assign("tickets", $tickets);
  }
  $smarty->assign("userId", $nLoginID);
	$smarty->assign("mainIframeWidth", $mainIframeWidth);
	$smarty->assign("mainIframeHeight", $mainIframeHeight);
	$smarty->displayWithFallback("index.tpl");
}

function freeSeat($row, $seat) {
  global $nLoginID, $nPartyID, $overviewImageLockFile, $overviewHtmlLockFile;
  $partyId = PELAS::mandantAktuelleParty($nPartyID);
  $ticket = getTicketByRowAndSeat($row, $seat, $nPartyID, $partyId);
  // Ticket muss existieren und der aktuelle User muss Besitzer oder Benutzer sein
  if ($ticket != null && ($ticket['ownerId'] == $nLoginID || $ticket['userId'] == $nLoginID)) {
    $sql = "UPDATE
              acc_tickets
            SET
              sitzReihe = NULL,  
              sitzPlatz = NULL
            WHERE 
              sitzReihe = '$row' AND
              sitzPlatz = '$seat' AND
              mandantId = $nPartyID AND
              partyId = '$partyId'";
    if ($res = DB::query($sql)) {
      // alten grün malen, HTML umbauen
      $overviewImage = imagecreatefrompng($overviewImageFile);
      print_r($overviewImage);
      
      /*$imageLockFile = fopen($overviewImageLockFile, 'r');
      flock($imageLockFile, LOCK_EX);
      
      $htmlLockFile = fopen($overviewHtmlLockFile, 'r');
      flock($htmlLockFile, LOCK_EX);*/
            
      $colors = allocateColors($overviewImage);
      $tischTiefe = getTischTiefe($nPartyID);
      $tischBreite = getTischBreite($nPartyID);
      $tischBreiteLoge = getTischBreiteLoge($nPartyID); 
            
      $rowArray = array($ticket['sitzReihe']);
      $oldRow = getRows($rowArray, $nPartyID);
      $oldRow = $oldRow[$ticket['sitzReihe']];
      
      $coords = calculateSeatCoordinates($oldRow, $seat, $tischBreite, $tischBreiteLoge, $tischTiefe);
      drawTable($overviewImage, $coords, $colors['tischfrei'], $colors['tischrand'], $oldRow['ISTLOGE']);
      
      $htmlArray = file($overviewHtmlFile);
      foreach ($htmlArray as $key => $line) {
        if (strpos($line, "<!-- ".$ticket['sitzReihe']."/".$ticket['sitzPlatz']." -->")) {
          $htmlArray[$key] = "found";
          break;
        }
      }      

    	$fp = fopen($overviewHtmlFile, "w");
    	foreach ($htmlArray as $line)
        fputs($fp, $line);
      fclose($fp);
      
      /*
      flock($imageLockFile, LOCK_UN);
      flock($htmlLockFile, LOCK_UN);
      */
      
      
    } else
      return false;
  } else {
    return false;
  } 
}

function getTicketByRowAndSeat($row, $seat, $mandantId, $partyId) {   
  $sql = "SELECT 
            * 
          FROM 
            acc_tickets 
          WHERE 
		        sitzReihe = '$row' AND
		        sitzPlatz = '$seat' AND
		        mandantId = $mandantId AND
		        partyId = $partyId";
	$result = DB::query($sql);
  $ticket = mysql_fetch_assoc($result);
  return $ticket;
}

function reserveSeat($ticketId, $row, $seat) {
  global $nLoginID, $nPartyID, $overviewImageFile;
  $partyId = PELAS::mandantAktuelleParty($nPartyID);
  $ticket = getTicket($ticketId, $nPartyID, $partyId);
  // Ticket muss existieren und der aktuelle User muss Besitzer oder Benutzer sein
  if ($ticket != null && ($ticket['ownerId'] == $nLoginID || $ticket['userId'] == $nLoginID)) {
    $sql = "UPDATE
              acc_tickets
            SET
              sitzReihe = '$row', 
              sitzPlatz = '$seat'
            WHERE 
              ticketId = '$ticketId' AND
              mandantId = $nPartyID AND
              $partyId = $partyId";
    if ($res = DB::query($sql)) {
      // alten grün malen, neuen rot malen
      
      $overviewImage = imagecreatefrompng($overviewImageFile);
      $colors = allocateColors($overviewImage);
      $tischTiefe = getTischTiefe($nPartyID);
      $tischBreite = getTischBreite($nPartyID);
      $tischBreiteLoge = getTischBreiteLoge($nPartyID); 
      
      
      $rowArray = array($ticket['sitzReihe']);
      $oldRow = getRows($rowArray, $nPartyID);
      $oldRow = $oldRow[$ticket['sitzReihe']];
      
      $coords = calculateSeatCoordinates($oldRow, $seat, $tischBreite, $tischBreiteLoge, $tischTiefe);
      drawTable($overviewImage, $coords, $colors['tischfrei'], $colors['tischrand'], $oldRow['ISTLOGE']);
      
      $rowArray = array($row);
      $newRow = getRows($rowArray, $nPartyID);
      $newRow = $newRow[$row];
      
      $coords = calculateSeatCoordinates($newRow, $seat, $tischBreite, $tischBreiteLoge, $tischTiefe);
      drawTable($overviewImage, $coords, $colors['tischbesetzt'], $colors['tischrand'], $newRow['ISTLOGE']);
      return true;
    } else
      return false;
  } else {
    return false;
  } 
}

function getTicket($ticketId, $mandantId, $partyId) {   
  $sql = "SELECT 
            * 
          FROM 
            acc_tickets 
          WHERE 
		        ticketId = '$ticketId' AND
		        mandantId = $mandantId AND
		        partyId = $partyId";
	$result = DB::query($sql);
  $ticket = mysql_fetch_assoc($result);
  return $ticket;
}

function generateMinimap($dstFileName, $overviewFileName, $minimapWidth = 100) {
  global $minimapImageLockFile;
  $lockFile = fopen($minimapImageLockFile, 'r');
  flock($lockFile, LOCK_EX);

	$img = imagecreatefrompng($overviewFileName);
	list($width, $height) = getimagesize($overviewFileName);
	$minimapHeight = round(($minimapWidth / $width) * $height);
	$image_p = imagecreatetruecolor($minimapWidth, $minimapHeight);
	imagecopyresampled ($image_p, $img, 0, 0, 0, 0, $minimapWidth, $minimapHeight, $width, $height);
	imageinterlace($image_p, 1);
	imagepng($image_p, $dstFileName);
	imagedestroy($img);
	
	flock($lockFile, LOCK_UN);
}


function generatePersonalOverview($overviewImage, $ebene, $nPartyID, $nLoginID) {
  // Wo sind die Karten des Users platziert?
  $tickets = getUsersTickets($nPartyID, $nLoginID);
  $tickets = addSeatCoordsForTickets($tickets);
  
  $colors = allocateColors($overviewImage);
  
  foreach ($tickets as $ticket) {
    // Hat das Ticket schon einen Platz? Wenn nein, dann auch nicht zeichnen
    if (isset($ticket['sitzReihe'])) {
      if ($ticket['userId'] == $nLoginID)
        $color = $colors['tischCurrentUser'];
      else
        $color = $colors['tischCurrentUsersTickets'];
  	
      drawTable($overviewImage, $ticket['coords'], $color, $colors['tischrand'], $ticket['ISTLOGE']);
    }
  }
  imageinterlace($overviewImage, 1);	
  return $overviewImage;
}

function generateOverview($dstFileName, $dstHtmlFileName, $vorlageImageFile, $ebene, $nPartyID) {		
  global $overviewImageLockFile, $overviewHtmlLockFile;
  
  $imageLockFile = fopen($overviewImageLockFile, 'r');
  flock($imageLockFile, LOCK_EX);
  
  $htmlLockFile = fopen($overviewHtmlLockFile, 'r');
  flock($htmlLockFile, LOCK_EX);
   
	//####################################
	// Grundlegende Daten aus DB holen
	$sql = "select 
		  * 
		from 
		  SITZDEF 
		where 
		  MANDANTID=$nPartyID and 
		  EBENE='$ebene'";
	$result = DB::query($sql);
	$platzArray = array();
	while ($row = mysql_fetch_array($result)) {
		$platzArray[$row['REIHE']] = $row;
	}

  // groessenkonstanten
  $tischTiefe = getTischTiefe($nPartyID);
  $tischBreite = getTischBreite($nPartyID);
  $tischBreiteLoge = getTischBreiteLoge($nPartyID); 

	//Maximale Reihen
	$result = DB::query("select MAX(REIHE) as MAXROW from SITZDEF where MANDANTID=$nPartyID and EBENE=$ebene");
	$row = mysql_fetch_array($result);
	$maxreihen = $row['MAXROW'];

	//Reihen starten ab
	$result = DB::query("select MIN(REIHE) as MINROW from SITZDEF where MANDANTID=$nPartyID and EBENE=$ebene");
	$row = mysql_fetch_array($result);
	$startreihen = $row['MINROW'];
	//# Daten Ende
	//#############################
	
  $userOnSeats = getAllSeats($nPartyID, $ebene);
	$im = imageCreateFromPNG($vorlageImageFile);
  $colors = allocateColors($im);	
	$html = "<map name=\"overviewMap\">\n";
		
	for ($i = $startreihen; $i <= $maxreihen; $i++) {		
		// Eventuell gibt es diese Reihe gar nicht
		if (!isset($platzArray[$i]))
			continue;
			
		$currentRow = $platzArray[$i];		
					
		for ($j = 1; $j <= $currentRow['LAENGE']; $j++) {		  
		  $coords = calculateSeatCoordinates($currentRow, $j, $tischBreite, $tischBreiteLoge, $tischTiefe);		  
		  $rowNumberCoordinates = calculateRowNumerCoordinates($currentRow);
						
			if ($j == 1) {
				// Reihennummer schreiben
				imagettftext($im, FONT_SIZE, FONT_ROTATION, $rowNumberCoordinates['x'], $rowNumberCoordinates['y'], $colors['tischrand'], FONT, "$i");
			}		
				
			if ($currentRow['AUSRICHTUNG'] == AUSRICHTUNG_SCHRAEG) {
				// TODO
				$html .= "<!-- $i/$j --><area shape='poly' coords='$points[0],$points[1],$points[2],$points[3],$points[4],$points[5],$points[6],$points[7]'";
			} else {
				$html .= "<!-- $i/$j --><area shape='rect' coords='".floor($coords['x1']).",".floor($coords['y1']).",".floor($coords['x2']).",".floor($coords['y2'])."'";
			}
		
			// Platz besetzt?
			if (isset($userOnSeats[$i][$j])) {
			  $tableColor = $colors['tischbesetzt'];
				//$html .= " onMouseOver=\"return show($i, $j, '$Besetzer', '$row[RESTYP]', '$iShowBild', '$sClan');\" onMouseOut=\"return nd();\">\n";
				$html .= " href=\"javascript:parent.klickSeat()\" onMouseOver=\"parent.displayData($i, $j, '".db2display($userOnSeats[$i][$j]['user'])."', ".$userOnSeats[$i][$j]['userId'].", ".$userOnSeats[$i][$j]['ownerId'].", true);\" onMouseOut=\"parent.clearData();\">\n";
			} else {
				$tableColor = $colors['tischfrei'];
				//$html .= " onMouseOver=\"return show($i, $j, '$Besetzer', '$row[RESTYP]', '$iShowBild', '$sClan');\" onMouseOut=\"return nd();\">\n";
				$html .= " href=\"javascript:parent.klickSeat()\" onMouseOver=\"parent.displayData($i, $j, 'frei', 0, 0, false);\" onMouseOut=\"parent.clearData();\">\n";
			}
						
			drawTable($im, $coords, $tableColor, $colors['tischrand'], ($currentRow['ISTLOGE'] == 1));		
		}
	}
	$html .= '</map>';
	
	imageinterlace($im, 1);	
	imagepng($im, $dstFileName);
	
	$fp = fopen($dstHtmlFileName, "w");
  fputs($fp, $html);
  fclose($fp);
  
  flock($imageLockFile, LOCK_UN);
  flock($htmlLockFile, LOCK_UN);
  
  return $im;
}


function getRows($rowNumbers, $mandantId) {
  if (count($rowNumbers) == 0)
    return array();
        
  $sql = "SELECT
              *
            FROM
              SITZDEF s
            WHERE
  				  	s.REIHE IN (".implode(',', $rowNumbers).") AND
  				  	s.mandantId = $mandantId";				  	
  	$result = DB::query($sql);
  	while ($row = mysql_fetch_assoc($result)) {
  	  $rows[$row['REIHE']] = $row;
  	}
  	return $rows;  
}

function addSeatCoordsForTickets($tickets) {
  $reihen = array();
  foreach ($tickets as $ticket) {
    if (isset($ticket['sitzReihe']))
      $reihen[] = $ticket['sitzReihe'];
    if (!isset($mandantId))
      $mandantId = $ticket['mandantId'];
  }
  
  if (count($reihen) != 0) {
    
    $tischTiefe = getTischTiefe($mandantId);
    $tischBreite = getTischBreite($mandantId);
    $tischBreiteLoge = getTischBreiteLoge($mandantId); 
    
    $rows = getRows($reihen, $mandantId);
  	
  	foreach ($tickets as $key => $ticket) {
  	  if (isset($ticket['sitzReihe'])) {
  	    $tickets[$key]['coords'] = calculateSeatCoordinates($rows[$ticket['sitzReihe']], $ticket['sitzPlatz'], $tischBreite, $tischBreiteLoge, $tischTiefe);
  	    $tickets[$key]['ISTLOGE'] = $rows[$ticket['sitzReihe']]['ISTLOGE'];
  	  }
  	}
  }
	
	return $tickets;
}

function getAllSeats($nPartyID, $ebene) {
  $sql = "SELECT
            t.userId, t.ownerId, t.sitzReihe, t.sitzPlatz, s.EBENE, u.LOGIN
          FROM
            acc_tickets t, SITZDEF s, USER u
          WHERE
            t.statusId = ".ACC_STATUS_BEZAHLT." AND
				  	t.mandantId = $nPartyID AND
            s.REIHE = t.sitzReihe AND
            s.MANDANTID = t.mandantId AND
            u.USERID = t.userId AND
            s.EBENE = '$ebene'";
	$result = DB::query($sql);
	$seats = array();
	while ($row = mysql_fetch_assoc($result)) {
	  $seats[$row['sitzReihe']][$row['sitzPlatz']] = array('user' => $row['LOGIN'], 'userId' => $row['userId'], 'ownerId' => $row['ownerId']);	
	}
	return $seats;
}
	
function getUsersTickets($nPartyID, $userId) {
  $sql = "SELECT          
            t.ticketId, t.userId, t.sitzReihe, t.sitzPlatz, t.mandantId, u.login, u.land
          FROM
            acc_tickets t, USER u
          WHERE
            t.statusId = ".ACC_STATUS_BEZAHLT." AND
				  	(t.ownerId = $userId OR t.userId = $userId) AND
				  	t.mandantId = $nPartyID AND
				  	u.USERID = t.userId
				  ORDER BY
				    ticketId";
	$result = DB::query($sql);
	$tickets = array();
	while ($row = mysql_fetch_assoc($result)) {
	  $row['ticketId'] = PELAS::formatTicketNr($row['ticketId']);
	  $tickets[] = $row;
	}
	return $tickets;
}

function drawTable($im, $coords, $tableColor, $borderColor, $isLoge) {
		// Tisch malen
		ImageRectangle($im, $coords['x1'], $coords['y1'], $coords['x2'], $coords['y2'], $borderColor);
		ImageFilledRectangle($im, $coords['x1'] + 1, $coords['y1'] + 1, $coords['x2'] - 1, $coords['y2'] - 1, $tableColor);
		// Loge bekommt ein L auf den Tisch
  	if ($isLoge) {
  	  ImageString($im, 1, $coords['x1'] + 4, $coords['y1'] + 3, "L", $borderColor);
    }
}

function allocateColors($im) {
  $colors['bg'] = ImageColorAllocate($im,255,255,255);
	$colors['tischrand'] = ImageColorAllocate($im,0,0,0);
	$colors['tischfrei'] = ImageColorAllocate($im,13,206,4);
	$colors['tischbesetzt'] = ImageColorAllocate($im,220,0,0);
	$colors['tischCurrentUser'] = ImageColorAllocate($im,255,255,0);
	$colors['tischCurrentUsersTickets'] = ImageColorAllocate($im,0,0,255);
	$colors['tischCurrentUsersClanmates'] = ImageColorAllocate($im,128,128,255);
	return $colors;
}


// Koordinaten für Reihennummer
function calculateRowNumerCoordinates($row) {
  switch ($row['AUSRICHTUNG']) {
    case AUSRICHTUNG_SUED:
      $coordinates['x'] = $row['XCORD'] + 8;
  	  $coordinates['y'] = $row['YCORD'] - 8;					
      break;
    case AUSRICHTUNG_NORD:
      $coordinates['x'] = $row['XCORD'] + 8;
		  $coordinates['y'] = $row['YCORD'] + 13;
      break;
    default:
      trigger_error("Unknown Ausrichtung: ".$row['AUSRICHTUNG'], E_USER_ERROR);
  }
  return $coordinates;
}

// TODO: West, Ost und Schräg
function calculateSeatCoordinates($currentRow, $currentSeat, $tischBreite, $tischBreiteLoge, $tischTiefe) {
  // Logenplaetze koennen breiter sein
	if ($currentRow['ISTLOGE'] == 1) {
		$tischBreite = $tischBreiteLoge;
	}
		
  $coords['row'] = $currentRow['REIHE'];
  $coords['seat'] = $currentSeat;  
  switch ($currentRow['AUSRICHTUNG']) {
    case AUSRICHTUNG_SUED:
      $coords['x1'] = $currentRow['XCORD'];
			$coords['y1'] = $currentRow['YCORD'] + $tischBreite * ($currentSeat - 1);
			$coords['x2'] = $currentRow['XCORD'] + $tischTiefe;
			$coords['y2'] = $currentRow['YCORD'] + $tischBreite * ($currentSeat - 1) + $tischBreite;								
      break;
    case AUSRICHTUNG_NORD:
      $coords['x1'] = $currentRow['XCORD'];
			$coords['y1'] = $currentRow['YCORD'] - $tischBreite * ($currentSeat - 1);
			$coords['x2'] = $currentRow['XCORD'] + $tischTiefe;
			$coords['y2'] = $currentRow['YCORD'] - $tischBreite * ($currentSeat - 1) + $tischBreite;
      break;
    default:      
      trigger_error("Unknown Ausrichtung: ".$currentRow['AUSRICHTUNG'], E_USER_ERROR);
        
  }	
  return $coords;	  
}

function getTischTiefe($nPartyID) {
  	$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='SITZTIEFE' and MANDANTID=$nPartyID");
  	$row = mysql_fetch_array($result);
  	$tTempTiefe = $row['STRINGWERT'];
  	if ($tTempTiefe > 0) {
  		$tischTiefe = $tTempTiefe;
  	} else {
  		$tischTiefe = 13;
  	}
  	return $tischTiefe;
  } 
  
  function getTischBreite($nPartyID) {
  	$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='SITZBREITE' and MANDANTID=$nPartyID");
  	$row = mysql_fetch_array($result);
  	$tTempBreite = $row['STRINGWERT'];
  	if ($tTempBreite > 0) {
  		$tischBreite = $tTempBreite;
  	} else {
  		$tischBreite = 13;
  	}
  	return $tischBreite;
  }
  
  function getTischBreiteLoge($nPartyID) {
  	//Breite der Loge
  	$result = DB::query("select STRINGWERT from CONFIG where PARAMETER='LOGE_SITZBREITE' and MANDANTID=$nPartyID");
  	$row = mysql_fetch_array($result);
  	$tTempBreite = $row['STRINGWERT'];
  	if ($tTempBreite > 0) {
  		$tischBreiteLoge = $tTempBreite;
  	} else {
  		$tischBreiteLoge = 18;
  	}
  	return $tischBreiteLoge;
  }

?>
