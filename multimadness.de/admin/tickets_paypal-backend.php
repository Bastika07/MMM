<?php
require('controller.php');
require_once "dblib.php";
include "pelasfunctions.php";
include "format.php";

// Email vorbereiten. Nur Mails schicken wenn Freischaltung NICHT ok!
$nomail = 0;
$email = "";
$email .= "=== WARNUNG: PayPal-Zahlung prüfen! ===\n\n";

$dbh = DB::connect();

//reading raw POST data from input stream. reading pot data from $_POST may cause serialization issues since POST data may contain arrays
$raw_post_data = file_get_contents('php://input');

  $raw_post_array = explode('&', $raw_post_data);
  $myPost = array();
  foreach ($raw_post_array as $keyval)
  {
      $keyval = explode ('=', $keyval);
      if (count($keyval) == 2)
         $myPost[$keyval[0]] = urldecode($keyval[1]);
  }
  // read the post from PayPal system and add 'cmd'
  $req = 'cmd=_notify-validate';
  foreach ($myPost as $key => $value)
  {        
       $value = urlencode($value);
       $req .= "&$key=$value";
  }

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://ipnpb.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: ipnpb.paypal.com'));

// In wamp like environment where the root authority certificate doesn't comes in the bundle, you need
// to download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.phpl" and set the directory path 
// of the certificate as shown below.
// curl_setopt($ch, CURLOPT_CAINFO, '../includes/cacert.pem');

curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

$res = curl_exec($ch);

$curl_error = curl_error ($ch);

curl_close($ch);


// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];


if ($res == FALSE) {
	// Curl-Aufruf schlug fehl

	$email .= "FEHLER: Curl-Aufruf schlug fehl mit: ".$curl_error."\n\n";

} else if (strcmp ($res, "VERIFIED") == 0) {
	// Nachricht von PayPal verifiziert. Details der Zahlung bearbeiten.

			if ($receiver_email != "paypal@innovalan.de") {
				// check that receiver_email is your Primary PayPal email
				$email .= "FEHLER: It is not our receiver Email\n\n";
			} elseif ($payment_status != "Completed") {
				// check the payment_status is Completed
				$email .= "FEHLER: Payment Status not completed!\n\n";
			} elseif ($payment_currency != "EUR") {
				// check that payment_amount/payment_currency are correct
				$email .= "FEHLER: Currency not correct!\n\n";
			} else {

				// TODO! check that txn_id has not been previously processed
				// gegen zuvor bearbeitete zahlungs txn_id vergleichen

				// IDs aus Bestellnummer extrahieren
				$bestellId = PELAS::getBestellIdFromBestNr($item_name);
				$partyId   = PELAS::getPartyIdFromBestNr($item_name);

				// Suche Bestellung
				$sql = "select
					  ticketTypId,
					  anzahl,
					  preis
					from
					  acc_bestellung
					where
					  partyId   = '$partyId' and
					  bestellId = '$bestellId'
				";
				$result= DB::query($sql);
				//echo DB::$link->errno.": ".DB::$link->error."<BR>";
				$summe = 0;
				while ($row = $result->fetch_array()) {
					$summe = $summe + $row['anzahl'] * $row['preis'];
				}

				/* PayPal Gebühren hinzurechnen */
				$paypalGeb = PELAS::PayPalGebuehr($summe);
				$summe = $summe + $paypalGeb;

				// prüfen ob Summe stimmt
				if (round($summe,2) == $payment_amount) {
					// ##### process payment #####
					$email .= "OK: Now change status of Bestellung ".PELAS::formatBestellNr($partyId, $bestellId)."\n\n";
					
					BestellungFreischalten($partyId, $bestellId);

					// Bestellung updaten
					$sql = "update
						  acc_bestellung
						set
						  status        = '".ACC_STATUS_BEZAHLT."',
						  paymentMethod = 'PayPal',
						  zahlungsweiseId = '".ACC_ZAHLUNGSWEISE_PAYPAL."'
						where
						  bestellId = '$bestellId' and
						  partyId   = '$partyId'
					";
					$result= DB::query($sql);

					sendeBestellBestaetigung($partyId, $bestellId, 1, ACC_STATUS_BEZAHLT);
					
					$nomail = 1;
				} else {
					$email .= "FEHLER: Betrag nicht korrekt!";
				}
			}

} else if (strcmp ($res, "INVALID") == 0) {
			// log for manual investigation
			$email .= "FEHLER: Nicht verifiziert!\n\n";
}

// Zusammenfassung als Mail an paypal@innovalan.de schicken
$email .= "----------\n\n";
$email .= "Item Name: ".$item_name."\n";
$email .= "Item Number: ".$item_number."\n";
$email .= "Payment status: ".$payment_status."\n";
$email .= "Payment amount: ".$payment_amount."\n";
$email .= "Payment currency: ".$payment_currency."\n";
$email .= "TXN ID: ".$txn_id."\n";
$email .= "Receiver Mail: ".$receiver_email."\n";
$email .= "Payer Mail: ".$payer_email."\n";


$betreff = "PELAS Ticketing: PayPal Backend-Benachrichtigung";
$header  = "From: noreply@innovalan.de <noreply@innovalan.de>\nError-To: noreply@innovalan.de\nReply-To: noreply@innovalan.de\nX-Priority: 3\nX-Mailer: PHP/". phpversion();

// In allen Fällen ausser erfolgreicher Freischaltung Mail an info@ schicken
if ($nomail != 1) {
	/* mail("info@innovalan.de", $betreff, $email, $header); */
	$erfolg = sende_mail_text(ADMIN_MAIL, $betreff, $email);
}

?>
