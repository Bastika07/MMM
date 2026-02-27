<?php
include_once "dblib.php";
include_once "pelasfunctions.php";

$nHoehe     = 6;
$nTblHeight = 10;


$aktuellePartyID = PELAS::mandantAktuelleParty($nPartyID);

// Anzahl der Plätze
$sql = "select
          y.anzahlVorhanden,
          y.typId
        from 
          acc_ticket_typ y
        where 
          y.partyId = '$aktuellePartyID' and
	  y.translation > 0
          ";
$res = DB::query($sql);
$partyPlaetze     = 0;
$partyRestplaetze = 0;
while ($row = mysql_fetch_array($res)) {
	$partyPlaetze = $partyPlaetze + $row['anzahlVorhanden'];
	$partyRestplaetze = $partyRestplaetze+ verfuegbareTickets($row['typId'], $aktuellePartyID);
}

// Anzahl fest bezahlt
$sql = "select
          count(t.ticketId) as tickets
        from 
          acc_ticket_typ y,
          acc_tickets t
        where 
          y.partyId  = '$aktuellePartyID' and
	  y.typId    = t.typId and
	  t.statusId = ".ACC_STATUS_BEZAHLT."
";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$partyBezahlt = $row[0];

// Anzahl Session registrierte Benutzer
/*$sql = "select 
          count(*) as cnt 
        from 
          SESSION
        where 
          UNIX_TIMESTAMP(ZEITSTEMPEL) >= UNIX_TIMESTAMP() - 500 and
          MANDANTID = '$nPartyID'";
$res = DB::query($sql);         
$row = mysql_fetch_row($res);
$anzahlSessions = $row[0];*/

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

$freiePlaetze = $partyPlaetze-$partyAngemeldet-$partyBezahlt;

?>

<table cellspacing="0" cellpadding="1" border="0">
  <tr><td style="text-align: center; font-family: Helvetica, Arial; font-size:7pt;"><?=$str['tickets_sold']?>: <?=($partyPlaetze-$partyRestplaetze);?></td></tr>
  <tr><td style="text-align: center; font-family: Helvetica, Arial; font-size:7pt;"><?=$str['tickets_on_hand']?>: <?=($partyRestplaetze);?></td></tr>
  <tr><td style="text-align: center; font-family: Helvetica, Arial; font-size:7pt;"><?=$str['registered_users']?>: <?=$anzahlAccounts;?></td></tr>
</table>