<?php
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

$font = imageloadfont(PELASDIR."gfx/server_id_card/addlg10.gdf");
// $font = "5";

/* Daten holen */

$sql = "SELECT g.*, u.*, a.sitzReihe, a.sitzPlatz
		FROM GASTSERVER g,
			USER u
		LEFT JOIN acc_tickets a
		ON a.userId = u.USERID
		WHERE u.USERID = '".intval($nLoginID)."'
			AND g.USERID = u.USERID
			AND g.LFDNR = '".intval($_GET['LFDNR'])."'
			AND g.MANDANTID = '".intval($nPartyID)."'
			AND a.partyId = '".intval($aktuellePartyID)."'
			AND a.statusId = '".ACC_STATUS_BEZAHLT."'
";

$res = DB::query($sql);

$row = $res->fetch_assoc();

if ($row['LFDNR'] > 0) {
	/* OK, Anzeigen! */

	// Welche Hintergrundgrafik nehen?
	if($nPartyID == "3"):
		$gfx = PELASDIR."gfx/server_id_card/server_id_card_northcon.png";
	elseif($nPartyID == "1"):
		$gfx = PELASDIR."gfx/server_id_card/server_id_card_standard.png";
	else:
		$gfx = PELASDIR."gfx/server_id_card/server_id_card_standard.png";
	endif;
	// Ende HG

	header("Content-Type: Image/png");
	$im = ImageCreateFrompng("$gfx");

	$black = ImageColorAllocate($im,0,0,0);

	// Name ausgeben
	imagestring ($im, $font, 28, 142,  ($row['NAME']." ".$row['NACHNAME']), $black);

	// Reihe
	imagestring ($im, $font, 120, 168,  $row['sitzReihe'], $black);
	// Platz 
	imagestring ($im, $font, 120, 190,  $row['sitzPlatz'], $black);

	// IP
	imagestring ($im, $font, 131, 233,  (CFG::getMandantConfig("GASTSERVER_IP",$nPartyID).$row['LFDNR']), $black);
	// PersoNr
	imagestring ($im, $font, 28, 328,  ($row['PERSONR']), $black);

	Imagepng($im);
	ImageDestroy($im);
	
} else {
	/* Falsche lfdnr angegeben! */
	echo "<p>FEHLER: Keine Berechtigung!</p>";
}

?>