<?php
// Mailing-Script. Sendet

// ACHTUNG: Netcup akzeptiert max. 100 Mails pro Stunde. D.h. Cron nur alle 2 Minuten mit 3 Mails starten!

set_time_limit(55); // Execution-Limit auf 55 Sekunden setzen
define('MAX_SEND_PER_CALL', 3); // Maximal 3 Mails pro Aufruf versenden
define('MAX_TRIES', 1); // Maximal 1 Versuch (vorerst)
define('DEBUG', false);

// set_include_path('/var/www/vhosts/hosting103794.af995.netcup.net/includes/');

include_once "dblib.php";
include_once "format.php";
include_once "pelasfunctions.php";
include_once "language.inc.php";
include_once 'PHPMailer/PHPMailerAutoload.php';

$sql = "SELECT 
					mailing_id,
					user_id,
					email,
					format,
					betreff,
					body
				FROM 
					mailing_execute
				WHERE
					sent IS NULL
					AND bounced IS NULL
					AND tries < '".intval(MAX_TRIES)."'
				ORDER BY
					tries,
					user_id
				LIMIT ".MAX_SEND_PER_CALL;
$res = DB::getRows($sql);

if (DEBUG === true)
	echo "Mails to send: ".count($res)."<br>\n";

$count = 0;
if (is_array($res)) {
	foreach ($res as $key => $row) {
		# Ab geht es
		if (sende_mail_newsletter($row['email'], $row['betreff'], $row['body'], $row['format'])) {
			# Erfolg
			$sent = "sent = NOW(),";
		} else {
			# Schon Probleme beim Versuch via PHPmailer
			$sent = "";
		}
		
		# Aktion vermerken in der Datenbank
		$sql = "UPDATE
							mailing_execute
						SET
							".$sent."
							tries = tries + 1
						WHERE
							mailing_id = '".intval($row['mailing_id'])."'
							AND user_id = '".intval($row['user_id'])."'";
		DB::query($sql);
		
		$count++;
		# Ende Versandschleife
	}
	
} else {
	# Wenn nichts da, dann nichts machen!
	
}
if (DEBUG === true)
	echo $count." done.\n\r";
?>
