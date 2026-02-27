<?php

Header("Content-type: image/png");
Header("Pragma: no-cache");
Header("Cache-Control: no-cache, must-revalidate");
require('controller.php');
require_once "dblib.php";
include_once "format.php";

$iRecht = "STATISTIKADMIN";
include "checkrights.php";

if ($_GET['typId'] < 1) {
	echo "<pre>Kein gueltiger Typ geliefert.</pre>";
	exit;
}

function zeichneGrafik($linieFarbe, $sAddWhere, $what)
{
	global $cFirstDay, $typId, $MinDoy, $MaxDoy, $partyId, $im, $yCords, $xStep, $oldX, $oldY, $yFaktor, $cRand;
	$doLegende = 1;
	for ($i=$MinDoy;$i<=$MaxDoy;$i++) {
		
		//alle Tage loopen
		$sql = "select 
			  sum(anzahl), max($what) as DOY
			from 
			  acc_bestellung
			where 
			  partyId = ".intval($_GET['partyId'])." and 
			  TO_DAYS($what) = $i and
			  ticketTypId = '".intval($_GET['typId'])."'
			  $sAddWhere
			group by 
			  TO_DAYS($what)";
		$result = DB::query($sql);

		$row = $result->fetch_array();
		//echo "ID: $row[USERID] Angemeldet: $row[$what] <br>";
		$count = $count + $row[0];

		imageline ($im, $oldX, $oldY, $oldX+$xStep, $yCords-$count*$yFaktor, $linieFarbe);

		if (($yLegendCount >= $legendeBarrier || $i == $MinDoy) && ($row[DOY] != "") && $doLegende == 1) {
			$yLegendCount = 0;
			ImageStringUP ($im, 2, $oldX+$xStep-6, $yCords+63, dateDisplay2Short($row[DOY]), $cRand);
		}

		
		// Senkrechte Linie immer am 1. des Monats zeichnen
		if (substr(dateDisplay2Short($row['DOY']),0,2) == "01") {
			// Senkrechtlinie jeweils am 1. ausgeben
			imageline ($im, $oldX+$xStep, 0, $oldX+$xStep, $yCords+1, $cFirstDay);
		}
		

		$oldX = $oldX + $xStep;
		$oldY = $yCords-$count*$yFaktor;

		$yLegendCount++;
		
		if ($doLegende == 1) {
			$doLegende = 0;
		} else {
			$doLegende = 1;
		}
	}
}


$xCords = 780;
$yCords = 350;

//Bild beginnen
$im = imagecreate($xCords+30,$yCords+1+67) or die ("Kann keinen neuen GD-Bild-Stream erzeugen");

   ImageInterlace($im, 1);
   //$color[bg] = ImageColorTransparent($im, 0);
   $color[bg] = ImageColorAllocate ($im, 230, 230, 230);
   $color[dg] = ImageColorAllocate ($im, 150, 150, 150);

   $cLinieBezahlungen = ImageColorAllocate($im,0,200,0);
   $cLinieBestellungen = ImageColorAllocate($im,0,0,200);
   $cLinieStornierungen = ImageColorAllocate($im,200,0,0);
   $cRand  = ImageColorAllocate($im,0,0,0);
   $cFirstDay  = ImageColorAllocate($im,200,200,200);

$oldX = 0;
$oldY = $yCords;


//Von bis Tag des Jahres?
$sql = "select 
	  min(TO_DAYS(wannAngelegt)) as MinDoy, max(TO_DAYS(wannAngelegt)) as MaxDoy 
	from 
	  acc_bestellung
	where 
	  partyId = '".intval($_GET['partyId'])."' and
	  ticketTypId = '".intval($_GET['typId'])."'";
	  
$result = DB::query($sql);
//echo DB::$link->errno.": ".DB::$link->error."<BR>";

$row = $result->fetch_array();
$MinDoy = $row[MinDoy];
$MaxDoy = $row[MaxDoy];

if ($MinDoy < 1 || $MaxDoy < 1) {
	echo "<pre>Kein eindeutiges Start- oder Enddatum.</pre>";
	exit;
}

$CountDoy = $MaxDoy - $MinDoy;

if ($CountDoy <= 0) {
	$CountDoy = 1;
	$noFill = 1;
}
$xStep = $xCords / $CountDoy;
$legendeBarrier = $CountDoy / 10;

// Ermittle alle je getätigten Bestellungen als Peak
$sql = "select 
	  sum(anzahl) 
	from 
	  acc_bestellung 
	where 
	  partyId = ".intval($_GET['partyId'])." and
	  ticketTypId = '".intval($_GET['typId'])."'
	";
$result = DB::query($sql);
//echo DB::$link->errno.": ".DB::$link->error."<BR>";
$Peak = 0;
while ($row = $result->fetch_array()) {
	if ($row[0] > $Peak) {
		$Peak = $row[0];
	}
}

$yFaktor = ($yCords / $Peak) ;

//nach links ruecken, damit letzter Wert auch in Grafik erscheint
$oldX = $oldX - $xStep;

//Rand einzeichnen
imageline ($im, 0, $yCords+2, $xCords, $yCords+2, $cRand);
imageline ($im, $xCords, $yCords+2, $xCords, 0, $cRand);
   
$yLegendCount = 0;

zeichneGrafik($cLinieBezahlungen, " AND status IN (".ACC_STATUS_BEZAHLT.")", "wannBezahlt");

$oldX = 0;
$oldX = $oldX - $xStep;
$oldY = $yCords;
zeichneGrafik($cLinieBestellungen, "", "wannAngelegt");

$oldX = 0;
$oldX = $oldX - $xStep;
$oldY = $yCords;
zeichneGrafik($cLinieStornierungen, " AND status IN (".ACC_STATUS_STORNIERT.")", "wannGeaendert");


$last = $count;

//Legende und letztes
ImageStringUP ($im, 2, $xCords+6, 335, "Top=$Peak, Blau=Bestellt, Gruen=Bezahlt, Rot=Storniert", $cRand);



imagepng($im)or die ("Kann keinen neues GD-Bild ausgeben");
ImageDestroy($im);

?>
