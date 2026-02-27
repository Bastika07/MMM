<?php
include_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "pelasfunctions.php";

$selected = 1;

if ($nLoginID > 0) {
	$selected = 2;
}

if (!isset($dbh))
	$dbh = DB::connect();

// Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

function getAnzahl($theStatus) {
	// liefert Anzahl Bestellungen im gegebenen Status
	global $nPartyID, $dbname, $dbh, $nLoginID;
	$sql = "select count(b.anzahl) as anzahl
  	  from
  	    acc_bestellung b,
  	    party p
  	  where
  	    p.partyId  = b.partyId and
  	    p.aktiv    = 'J' and
  	    b.bestellerUserId   = '$nLoginID' and
  	    p.mandantId = '$nPartyID' and
  	    b.status = '$theStatus'
  	  ";
  	  $result = DB::query($sql);

	if ($result->num_rows) {
		$row = $result->fetch_array();
		return $row['anzahl'];
	} else {
		return -1;
	}
}

function getZugeordnetesTicket() {
	// Liefert 1 zurück wenn User ein Ticket zugeordnet hat
	global $nPartyID, $dbname, $dbh, $nLoginID;
	$sql = "select count(*) as anzahl
  	  from
  	    acc_tickets t,
  	    party p
  	  where
  	    p.partyId  = t.partyId and
  	    p.aktiv    = 'J' and
  	    (t.userId   = '$nLoginID' and
  	     t.ownerId != '$nLoginID') and
  	    p.mandantId = '$nPartyID' and
  	    t.statusId = '".ACC_STATUS_BEZAHLT."'
  	  ";
  	  $result = DB::query($sql);

	if ($result->num_rows) {
		$row = $result->fetch_array();
		return $row['anzahl'];
	} else {
		return -1;
	}
	
}

#
# Gibt Anzahl der Tickets ohne Sitzplatz zurück
#
function getTicketsOhneSitzplatz() {
	global $nPartyID, $dbname, $dbh, $nLoginID;
	$sql = "select count(*) as anzahl
  	  from
  	    acc_tickets t,
  	    party p
  	  where
  	    p.partyId  = t.partyId and
  	    p.aktiv    = 'J' and
  	    (t.userId   = '$nLoginID' or
				 t.ownerId  = '$nLoginID')and
  	    p.mandantId = '$nPartyID' and
				t.statusId = '".ACC_STATUS_BEZAHLT."' and
  	    (t.sitzReihe IS NULL OR t.sitzReihe = '')
  	  ";
  	  $result = DB::query($sql);
	if ($result->num_rows) {
		$row = $result->fetch_array();
		return $row['anzahl'];
	} else {
		return 1;
	}
}

#
# Liest die Anzahl der Turnieranmeldungen
#
function getAnzahlTurnieranmeldungen() {
	global $nPartyID, $dbname, $dbh, $nLoginID, $aktuellePartyID;
	$sql = "select count(*) as anzahl
  	  from
  	    t_turnier t,
  	    t_team2user tu
  	  where
				t.turnierid = tu.turnierid
				AND tu.userid = '".intval($nLoginID)."'
				AND t.partyid = '".intval($aktuellePartyID)."'
  	  ";
  	  $result = DB::query($sql);

	if ($result->num_rows) {
		$row = $result->fetch_array();
		return $row['anzahl'];
	} else {
		return -1;
	}
}



if ($nLoginID < 1) {
	// noch nicht eingeloggt
	$selected = 1;
} else {
	if (getAnzahl(ACC_STATUS_OFFEN) > 0) {
		// offene Bestellung vorhanden, zahlen!
		$selected = 3;
	} elseif (getAnzahl(ACC_STATUS_BEZAHLT) > 0 || getZugeordnetesTicket() > 0) {
		// Es gibt eine bezahlte Bestellung aber keine offene -> Sitzplatz
		if (getTicketsOhneSitzplatz() > 0)
			$selected = 4; # Alles gut aber ein Ticket hat noch keinen Platz
		else
			$selected = 5; # Alle Tickets mit Platz
	} else {
		// User ist eingeloggt und hat keine bezahlte oder offene -> bestellen
		$selected = 2;
	}
}

#
# Turnier separat
#
if (getAnzahlTurnieranmeldungen() > 0) {
	$select_turnier = 2;
} else if ($selected >= 4) {
	$select_turnier = 1;
} else {
	$select_turnier = 1;
};

if (isset($sParty) && $sParty == "northcon2012") {
	// Angepasst für NC 2012
	//select 1 = einloggen oder reservieren
	if ($selected == 1 || $selected == 2) {
		echo '<a href ="accounting.php?action=order"><img src="/style/'.$str['loginfield_tickets'].'" width="220" height="86" border="0"></a><br>';
	} elseif ($selected == 3) {
	  //select 3 = bezahlen
		echo '<a href ="accounting.php?action=bill"><img src="/style/'.$str['loginfield_bezahlen'].'" width="220" height="86" border="0"></a><br>';
	} elseif ($selected == 4) {
		echo '<a href ="sitzplan.php"><img src="/style/'.$str['loginfield_platz'].'" width="220" height="86" border="0"></a><br>';
	}
} elseif (isset($sParty) && $sParty == "northcon") {
	// Spezielles Erscheinungsbild für NorthCon

	echo "<table width=\"205\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
	// Erste Zeile einloggen oder pers. Daten
	if ($nLoginID > 0) {
		echo "<tr><td width=\"18\"><img src=\"/gfx_struct/loginfield_user.png\" width=\"10\" height=\"10\"></td>\n";
		echo "<td width=\"187\"><a href=\"/login_edit.php\" class=\"forumlink\">Persönliche Daten</a></td></tr>";				
	} else {
		echo "<tr><td width=\"18\"><img src=\"/gfx_struct/loginfield_user.png\" width=\"10\" height=\"10\"></td>\n";
		echo "<td width=\"187\"><a href=\"/login.php\" class=\"forumlink\">Login</a></td></tr>\n";		
	}

	// Separator
	echo "<tr><td colspan=\"2\" height=\"1\"><img src=\"/gfx_struct/loginfield_separator.png\" height=\"1\"></td></tr>\n";

	// Zweite Zeile Ticketstatus
	echo "<tr><td><img src=\"/gfx_struct/loginfield_ticket.png\" width=\"10\" height=\"10\"></td>\n";
	echo "<td>";
	if ($selected == 2) {
	//select 2 = anmelden
		echo "<a class=\"forumlink\" href=\"/accounting.php?action=order\">$str[al_bestellen]</a>";
	} elseif ($selected == 3) {
	//select 3 = bezahlen
		echo "<a class=\"forumlink\" href=\"/accounting.php?action=bill\">$str[al_bezahlen]</a>";
	} elseif ($selected == 4) {
	//select 4 = setzen
		echo "<a class=\"forumlink\" href=\"/sitzplan.php\">$str[lh_reservieren]</a>";
	} else {
		echo "<a class=\"forumlink\" href=\"/accounting.php\">$str[ticketsverwalten]</a>";
	}
	echo "</td></tr>\n";

	// Logout oder nix
	if ($nLoginID > 0) {
		// Separator
		echo "<tr><td colspan=\"2\" height=\"1\"><img src=\"/gfx_struct/loginfield_separator.png\" height=\"1\"></td></tr>\n";
		echo "<tr><td><img src=\"/gfx_struct/loginfield_logout.png\" width=\"10\" height=\"10\"></td>\n";
		echo "<td><a href=\"/login.php\" class=\"forumlink\">Ausloggen</a></td></tr>";		
	} else {
		echo "<tr><td colspan=\"2\"><a href=\"#\" class=\"forumlink\">&nbsp;</td></tr>";
	}

	echo "</table>\n";
	
} else {
	//select 1 = einloggen
	if ($selected == 1) {
		echo "<li class='alert'><a class=\"navlink\" href=\"?page=5\"><b>$str[lh_einloggen]</b></a></li>".
		"<li class='nook'>$str[lh_anmelden]</li>".
		"<li class='nook'>$str[lh_bezahlen]</li>".
		"<li class='nook'>$str[lh_reservieren]</li>".
		"<li class='nook'>Turnieranmeldung</li>";
	} elseif ($selected == 2) {
	//select 2 = anmelden
		echo "<li class='ok'>$str[lh_einloggen]</li>".
		"<li class='alert'><a class=\"navlink\" href=\"?page=6&action=order\"><b>$str[lh_anmelden]</b></a></li>".
		"<li class='nook'>$str[lh_bezahlen]</li>".
		"<li class='nook'>$str[lh_reservieren]</li>".
		"<li class='nook'>Turnieranmeldung</li>";
	} elseif ($selected == 3) {
	//select 3 = bezahlen
		echo "<li class='ok'>$str[lh_einloggen]</li>".
		"<li class='ok'>$str[lh_anmelden]</li>".
		"<li class='alert'><a class=\"navlink\" href=\"?page=6&action=bill\"><b>$str[lh_bezahlen]</b></a></li>".
		"<li class='nook'>$str[lh_reservieren]</li>".
		"<li class='nook'>Turnieranmeldung</li>";
	} elseif ($selected == 4) {
	//select 4 = setzen
		echo "<li class='ok'>$str[lh_einloggen]</li>".
		"<li class='ok'>$str[lh_anmelden]</li>".
		"<li class='ok'>$str[lh_bezahlen]</li>".
		"<li class='alert'><a class=\"navlink\" href=\"?page=13\"><b>$str[lh_reservieren]</b></a></li>".
		"<li class='".(($select_turnier == 2) ? "ok" : "alert")."'><a class='navlink' href='?page=20'><b>Turnieranmeldung</b></a></li>";
	} elseif ($selected == 5) {
	//select 5 = alles erledigt!
		echo "<li class='ok'>$str[lh_einloggen]</li>".
		"<li class='ok'>$str[lh_anmelden]</li>".
		"<li class='ok'>$str[lh_bezahlen]</li>".
		"<li class='ok'><a class=\"navlink\" href=\"?page=13\"><b>$str[lh_reservieren]</b></a></li>".
		"<li class='".(($select_turnier == 2) ? "ok" : "alert")."'><a class='navlink' href='?page=20'><b>Turnieranmeldung</b></a></li>";
	}

}
?>
