<?php

if ($Action == "ok") {
	//Login ok, setze cookie. Alternativ kann die Variable PHPSESSID auch in der URL
	//weiter getragen werden
	//wenn saveLogin gesetzt ist, dann will PELAS, dass der Cookie 150 Tage gehalten wird
	if ($saveLogin == 1) {
		$saveTime = time()+12960000;
	} else {
		$saveTime = 0;
	}
	
	// Backend fragen, ob nicht schon Jemand unter dieser SessionID eingeloggt ist
	$backend = $sPelasHost."login_allowed.php?PELASSESSID=$_GET[PELASSESSID]";
	
	$fpread = fopen($backend, "r");
	if(!$fpread) {
		echo "$errstr ($errno)<br>\n";
	} else {
		feof($fpread);
		$buffer = ltrim(chop(fgets($fpread, 1)));
	}
	if ($buffer != "1") {
		echo "<p>Fehler: Ein erneuter Login mit dieser SessionID ist nicht m&ouml;glich.</p>";
		$PELASSESSID = -1;
	} else {
		setcookie (PELASSESSID, $_GET[PELASSESSID], $saveTime);
	}
	//******** Backend und cookie-Arie Ende
	
} elseif ($Action == "logout") {
	//ausloggen, cookie zersetzen
	setcookie(PELASSESSID, FALSE, mktime(0,0,0,1,1,70));
}

//Formular abgeschickt, Post-Daten weiterleiten
//echo PostToHost("login.php?Action=$Action&PELASSESSID=$PELASSESSID&nPartyID=$nPartyID&sLang=$sLang&returnTo=http://$_SERVER[HTTP_HOST]&".getenv("QUERY_STRING"), getenv("REQUEST_URI"), $_POST);

include PELASDIR."login.php";

?>
