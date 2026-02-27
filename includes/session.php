<?php
if (!function_exists('generateSessionID')) {
	function generateSessionID()
	{
		DB::connect();
	
		//session-ID zusammenbauen und checken ob SessionID schon drin, wenn ja neuer Durchlauf
		do {
			$sessid = bin2hex(random_bytes(16));

			$result = DB::query("select SESSIONID from SESSION where SESSIONID='$sessid'");
			$row = $result->fetch_array();

		} while ($row);
		return $sessid;
	}
}

include_once "getsession.php";

if (!function_exists('starteSession')) {
	function starteSession ($nPartyID, $SessionLogin, $SessionLoginID)
	{
		global $dbname, $dbhost, $dbuser, $dbpass;

		DB::connect();

	// garbageCollect starten?
	// garbage OFF!
	/*
		srand(time());
		$Zufall = rand (1, 25);
		if ($Zufall == 9) { 
			$aDa = getdate ();
			$aktDatum= date ("$aDa[year]-$aDa[mon]-$aDa[mday] 07:$aDa[minutes]:$aDa[seconds]");
			if (!isset($dbh))
				$dbh = DB::connect();
				
			SQL is Bullshit, besser is:
			SELECT * 
			FROM `SESSION` 
			WHERE DATE_SUB( CURDATE( ) , INTERVAL 180 
			DAY ) >= ZEITSTEMPEL

			Aber generell nicht Sessions löschen, sondern nur doppelte
			
			$result = DB::query("DELETE FROM SESSION where ZEITSTEMPEL < '$aktDatum'");
			//echo mysql_errno().": ".mysql_error()."<BR>";
		}
	*/


		$ip = getenv("REMOTE_ADDR");
		$ip_sstring = str_replace('.','',$ip);


		$aDa = getdate ();
		$aktDatum= date ("$aDa[year]-$aDa[mon]-$aDa[mday] $aDa[hours]:$aDa[minutes]:$aDa[seconds]");
		
		$sessid = generateSessionID();

		// session-ID in die Datenbank

		//alten Eintrag loeschen
		$result = DB::query("delete from SESSION where MANDANTID=$nPartyID and LOGINID=$SessionLoginID");
		//neuen Eintrag vornehmen
		$result = DB::query("INSERT INTO SESSION (SESSIONID, MANDANTID, LOGINID, LOGIN, REMOTEIP, ZEITSTEMPEL, NUTZBAR) values ('$sessid', $nPartyID, $SessionLoginID, '$SessionLogin', '$ip', '$aktDatum' ,'J')");
		//echo mysql_errno().": ".mysql_error()."<BR>";

	// Session-ID zurueckgeben

		return $sessid; 

	}
}

?>
