<?php
if (LOCATION == "intranet")
	goto ende;

include_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "pelasfunctions.php";
include_once "language.inc.php";

// Aktuelle Party des Mandanten in Variable zwischenspeichern
$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

#
# Warnmeldung für Arbeiten im Internet, wenn Party aktiv
#
$sql = "select
				count(*) as anzahl,
			  cast(p.beschreibung AS CHAR) as beschreibung,
			  p.terminVon,
			  p.terminBis,
			  p.teilnehmer,
		  	p.partyId
		from 
			  party p
		where 
			p.partyId = '".intval($aktuellePartyID)."'
			AND p.terminVon <= NOW()
			AND NOW() <= p.terminBis";

//$res = DB::query($sql);
if ($res)
	if ($row = $res->fetch_assoc())
		if ($row['anzahl'] > 0) {
			# Warnmeldung ausgeben
			?>
				<div style="background-color:#CF6; text-align:center; padding:5px;">
					<b>ACHTUNG:</b>	Es findet gerade die <?= htmlspecialchars($row['beschreibung']); ?> statt. Als Teilnehmer benutze bitte das Intranet
					unter folgender Adresse:<a href="https://www.lan.multimadness.de/" style="color: #000000;">
          <br />https://www.lan.multimadness.de/</a>
        </div>
        <br />
			<?php
		
		}
		
ende:
?>