<?php

date_default_timezone_set('Europe/Berlin');

// ---------------------------------------------------------------------------
// Minimal .env loader
// Reads key=value pairs from includes/.env (if it exists) and registers them
// via putenv() so that getenv() picks them up throughout the application.
// Lines starting with # and lines without '=' are silently ignored.
// ---------------------------------------------------------------------------
(function () {
    $envFile = __DIR__ . '/.env';
    if (!is_readable($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        // Strip optional surrounding quotes (single or double).
        if (strlen($value) >= 2
            && (($value[0] === '"'  && $value[-1] === '"')
             || ($value[0] === "'"  && $value[-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
        }
    }
})();

// Upload-Stuff
define('UNZIP',		'/usr/bin/unzip');
define('NICE',		'/usr/bin/nice');
define('DJPEG',		'/usr/bin/djpeg');
define('PNMSCALE',	'/usr/bin/pnmscale');
define('CJPEG',		'/usr/bin/cjpeg');

define('UPLOADDIR',	'/tmp/');

define('SMARTY_HOME_DIR', 'smarty/');
// Intranet: define('SMARTY_HOME_DIR', '/var/www/Smarty/lib/');
define('SMARTY_CLASS', SMARTY_HOME_DIR.'SmartyBC.class.php');

if (isset($_SERVER['SERVER_NAME']))
{
switch ($_SERVER['SERVER_NAME'])
{
case "admin.northcon.de":
case "admin.innovalan.de":
case "pelas.innovalan.de":
case "www.multimadness.de":
case "multimadness.de":
case "www.lanresort.de":
case "lanresort.de":
case "inet.lanresort.de":
case "www.northcon.de":
case "northcon.de":
case "inet.northcon.de":
case "www.the-summit.de":
case "the-summit.de":
case "www.thesummit.de":
case "thesummit.de":
case "inet.the-summit.de":
case "inet.thesummit.de":
case "www.the-activation.de":
case "the-activation.de":
case "www.theactivation.de":
case "theactivation.de":
case "www.dimension6.de":
case "dimension6.de":
case "www.d6-lan.de":
case "d6-lan.de":
case "ildm6.de":
case "www.ildm6.de":
case "www.lanfortress.de":
case "lanfortress.de":
case "friends.innovalan.de":
case "www.esportarena.tv":
case "esportarena.tv":
case "www.esportarena.de":
case "esportarena.de":
case "hosting103794.af995.netcup.net":
case "madness4ever.de":
case "www.madness4ever.de":
	$srv_conf = "urtyp_live_internet";
	break;

case "admin-dev.innovalan.de":
case "pelas-dev.innovalan.de":
case "multimadness-dev.innovalan.de":
case "lanresort-dev.innovalan.de":
case "northcon-dev.innovalan.de":
case "the-summit-dev.innovalan.de":
case "activation-dev.innovalan.de":
case "dimension6-dev.innovalan.de":
case "lanfortress-dev.innovalan.de":
case "friends-dev.innovalan.de":
case "esportarenatv-dev.innovalan.de":
	$srv_conf = "urtyp_dev_internet";
	break;

case "admin-lan.innovalan.de":
case "multimadness-lan.innovalan.de":
case "lanresort-lan.innovalan.de":
case "northcon-lan.innovalan.de":
case "the-summit-lan.innovalan.de":
case "activation-lan.innovalan.de":
case "dimension6-lan.innovalan.de":
case "lanfortress-lan.innovalan.de":
case "friends-lan.innovalan.de":
	$srv_conf = "urtyp_dev_intranet";
	break;

case "www.lan.multimadness.de":
	// TODO: this will break with the "northcon.de" forward during the party
	$srv_conf = "madnix_live";
	break;

default:
	//die("invalid SERVER_NAME: ".$_SERVER['SERVER_NAME']);
	// localhost / XAMPP local development falls through to live config;
	// DB credentials are read from includes/.env
	$srv_conf = "urtyp_live_internet";
	break;
}
}
else
	$srv_conf = "";

# Kommandozeile oder Host
if ($srv_conf == "urtyp_live_internet" || php_sapi_name() == 'cli') {

	if (isset($_SERVER['HTTPS'])) {
		define('PELASHOST', 'https://'.$_SERVER['HTTP_HOST'].'/pelas/'); # Das Unterverzeichnis "pelas" sollte ein symbolischer Link auf das PELAS-Verzeichnis sein
		define('BASE_URL', 'https://'.$_SERVER['HTTP_HOST'].'/'); # Frontend Base
	} else if (isset($_SERVER['HTTP_HOST'])) {
		define('PELASHOST', 'http://'.$_SERVER['HTTP_HOST'].'/pelas/'); # Das Unterverzeichnis "pelas" sollte ein symbolischer Link auf das PELAS-Verzeichnis sein
		define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'); # Frontend Base
	} else
		define('PELASHOST', 'https://www.multimadness.de/pelas/'); # Das Unterverzeichnis "pelas" sollte ein symbolischer Link auf das PELAS-Verzeichnis sein
		define('BASE_URL', 'https://www.multimadness.de/'); # Frontend Base
	
	define('PELASDIR', getenv('LIVE_PELASDIR') ?: '/var/www/vhosts/hosting103794.af995.netcup.net/pelas/');

	define('ARCHIV_UPLOADDIR',	PELASDIR.'archiv_gesperrt/');
	define('SMARTY_BASE_DIR',	getenv('LIVE_SMARTY_BASE_DIR') ?: '/var/www/vhosts/hosting103794.af995.netcup.net/includes/smarty/');

	define('LOCATION', 'internet');

	$dbname	= getenv('LIVE_DB_NAME') ?: '';
	$dbhost	= getenv('LIVE_DB_HOST') ?: '';
	$dbuser	= getenv('LIVE_DB_USER') ?: '';
	$dbpass	= getenv('LIVE_DB_PASS') ?: '';

} else if ($srv_conf == "urtyp_dev_internet") {
	define('PELASHOST',		'http://pelas-dev.innovalan.de/');
	define('PELASDIR',		'/var/www.il-dev/pelas.innovalan.de/');

	define('ARCHIV_UPLOADDIR',	'/var/www.il-dev/archiv_gesperrt/');
	define('SMARTY_BASE_DIR',	'/var/www.il-dev/Smarty/');

	define('LOCATION', 'internet');

	$dbname	= getenv('DEV_DB_NAME') ?: '';
	$dbhost	= getenv('DEV_DB_HOST') ?: '';
	$dbuser	= getenv('DEV_DB_USER') ?: '';
	$dbpass	= getenv('DEV_DB_PASS') ?: '';

// aktuell via apache-config gesetzt
//	if (!ini_get('display_errors')) {
//		ini_set('display_errors', '1');
//	}
	error_reporting(E_ALL & ~E_STRICT);

} else if ($srv_conf == "urtyp_dev_intranet") {
	define('PELASHOST',		'/pelashost/');
	define('PELASDIR',		'/var/www.il-dev/pelas.innovalan.de/');

	define('ARCHIV_UPLOADDIR',	'/var/www.il-dev/archiv_gesperrt/');
	define('SMARTY_BASE_DIR',	'/var/www.il-dev/Smarty/');

	define('LOCATION', 'intranet');

	$dbname	= getenv('DEV_DB_NAME') ?: '';
	$dbhost	= getenv('DEV_DB_HOST') ?: '';
	$dbuser	= getenv('DEV_DB_USER') ?: '';
	$dbpass	= getenv('DEV_DB_PASS') ?: '';

} else if ($srv_conf == "madnix_live") {
	
	if (isset($_SERVER['HTTPS'])) {
		define('PELASHOST', 'https://'.$_SERVER['HTTP_HOST'].'/pelas/'); # Das Unterverzeichnis "pelas" sollte ein symbolischer Link auf das PELAS-Verzeichnis sein
		define('BASE_URL', 'https://'.$_SERVER['HTTP_HOST'].'/'); # Frontend Base
	} else {
		define('PELASHOST', 'http://'.$_SERVER['HTTP_HOST'].'/pelas/'); # Das Unterverzeichnis "pelas" sollte ein symbolischer Link auf das PELAS-Verzeichnis sein
		define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'); # Frontend Base
	}
	
	define('PELASDIR', 		'/var/www/pelas/');
	define('ARCHIV_UPLOADDIR',	PELASDIR.'archiv_gesperrt/');
	define('SMARTY_BASE_DIR',	'/var/www/includes/smarty/');

	define('LOCATION', 'intranet');

	$dbname	= getenv('LAN_DB_NAME') ?: '';
	$dbhost	= getenv('LAN_DB_HOST') ?: '';
	$dbuser	= getenv('LAN_DB_USER') ?: '';
	$dbpass	= getenv('LAN_DB_PASS') ?: '';

} else {
	die("invalid server config");
}

unset($srv_conf);

$sPelasHost = PELASHOST;

define('DB_STATISTICS', true);

// TODO: check usage
//define('BUNGALOWLAN', FALSE);

// Konstanten für die NewsBilder
define('NEWSBILD_DIR', PELASDIR.'bilder_upload/newsbild/');
define('NEWSBILD_PATH', PELASHOST.'bilder_upload/newsbild/');
define('NEWSBILD_VALID_FILE_PATTERN', "/^([0-9]+)_\w+\.\w{1,4}$/");
$NEWSBILD_VALID_FILE_EXTS = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'); // gif, jpg, png

// Konstanten für die SliderBilder
define('SLIDER_DIR', PELASDIR.'bilder_upload/slider/');
define('SLIDER_PATH', PELASHOST.'bilder_upload/slider/');
define('SLIDER_VALID_FILE_PATTERN', "/^([0-9]+)_\w+\.\w{1,4}$/");
$SLIDER_VALID_FILE_EXTS = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'); // gif, jpg, png

// Konstanten für die Verpflegungsbilder
define('VERPFLEGUNG_DIR', PELASDIR.'bilder_upload/verpflegung/');
define('VERPFLEGUNG_PATH', PELASHOST.'bilder_upload/verpflegung/');
define('VERPFLEGUNG_VALID_FILE_PATTERN', "/^([0-9]+)_\w+\.\w{1,4}$/");
$VERPFLEGUNG_VALID_FILE_EXTS = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'); // gif, jpg, png

// Konstanten für die Lokationbilder
define('LOCATION_DIR', PELASDIR.'bilder_upload/location/');
define('LOCATION_PATH', PELASHOST.'bilder_upload/location/');
define('LOCATION_VALID_FILE_PATTERN', "/^([0-9]+)_\w+\.\w{1,4}$/");
$LOCATION_VALID_FILE_EXTS = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'); // gif, jpg, png

// Konstanten für die Netzwerkbilder
define('NETZWERK_DIR', PELASDIR.'bilder_upload/netzwerk/');
define('NETZWERK_PATH', PELASHOST.'bilder_upload/netzwerk/');
define('NETZWERK_VALID_FILE_PATTERN', "/^([0-9]+)_\w+\.\w{1,4}$/");
$NETZWERK_VALID_FILE_EXTS = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'); // gif, jpg, png

// Konstanten für die Sponsorbilder
define('SPONSOR_DIR', PELASDIR.'bilder_upload/sponsoring/');
define('SPONSOR_PATH', PELASHOST.'bilder_upload/sponsoring/');
define('SPONSOR_VALID_FILE_PATTERN', "/^([0-9]+)_\w+\.\w{1,4}$/");
$SPONSOR_VALID_FILE_EXTS = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'); // gif, jpg, png

// Krams fürs neue Forum
///////////////////////////////////////

define('SMILEYDIR', PELASHOST.'gfx/');

define('DESIGN_FORUM', 1);
// Zeigt die News, also den ersten Post des Threads
define('DESIGN_NEWS', 2);
// Zeigt die News, also den ersten Post des Threads + die Kommentare (restliche Posts)
define('DESIGN_NEWSCOMMENTS', 4);
// Zeigt alle Posts im Thread direkt an, ohne Titel des Threads
define('DESIGN_COMMENTS', 8);
// Zeigt die News für den Admin
define('DESIGN_NEWSADMIN', 16);

// Board ist inline (Comments für News, etc) und kann nicht vom Forum aus betrachtet, etc werden
define('BOARD_INLINE', 1);
// Board ist geschlossen (sichtbar, aber keine Post-Möglichkeit)
define('BOARD_CLOSED', 2);
// Board ist versteckt (nicht sichtbar, aber Post-Möglichkeit)
define('BOARD_HIDDEN', 4);

// Boardtypen
define('BT_FORUM', 1);
define('BT_NEWS', 2);
define('BT_TURNIERCOMMENTS', 4);

// Neues Accounting-System
define('ACC_STATUS_OFFEN', 1);
define('ACC_STATUS_BEZAHLT', 2);
define('ACC_STATUS_STORNIERT', 3);
define('ACC_ZAHLUNGSWEISE_UEBERWEISUNG', 1);
define('ACC_ZAHLUNGSWEISE_PAYPAL', 2);
define('ACC_ZAHLUNGSWEISE_BAR', 3);

///////////////////////////////////////

	//Konstanten

$KATEGORIE_NEWS        = 1;
$KATEGORIE_NEWSCOMMENT = 2;
$KATEGORIE_FORUM       = 3;
$KATEGORIE_TURNIER     = 4;
$KATEGORIE_MAILING     = 5;

$KATEGORIE_ARCH_VIDEOS  = 6;
$KATEGORIE_ARCH_BILDER  = 7;
$KATEGORIE_ARCH_ZEITUNG = 8;
$KATEGORIE_ARCH_TURNIER = 9;
$KATEGORIE_ARCH_LINK    = 10;

$KATEGORIEINFO = Array (
	1  => Array ("News", "NEWSADMIN", true),
	4  => Array ("Turnier", "TURNIERADMIN", true),
	5  => Array ("Mailing", "MAILINGADMIN", true),
	6  => Array ("Videos", "ARCHIVADMIN", true),
	7  => Array ("Fotos", "ARCHIVADMIN", true),
	8  => Array ("Zeitungsartikel", "ARCHIVADMIN", false),
	9  => Array ("Turnierergebnisse", "ARCHIVADMIN", true),
	10  => Array ("Links", "ARCHIVADMIN", false)
);

// Bezahlt-Status

$STATUS_NICHTANGEMELDET = 0;
$STATUS_ANGEMELDET	= 1;
$STATUS_ZUORDBAR = 1; // Doppeltbelegung, da angemeldet im alten System und Zuordbar im neuen!
$STATUS_BEZAHLT		= 2;
$STATUS_BEZAHLT_LOGE	= 3;
$STATUS_ABGEMELDET	= 4;
$STATUS_BEZAHLT_VIPLOGE = 5;
$STATUS_BEZAHLT_SUPPORTERPASS = 6; // Für die Anzeige der Supporterpässe
$STATUS_BEZAHLT_TURNIERSPIELER = 7; // Identifiziert ein Turnierspieler-Ticket für zB eSport Arena
$STATUS_BEZAHLT_CLOGE = 8; // Neu für Demo
 
// Neue Status für LANresort
$STATUS_COMFORT_4PERS = 10;
$STATUS_COMFORT_6PERS = 11;
$STATUS_COMFORT_8PERS = 12;
$STATUS_PREMIUM_4PERS = 13;
$STATUS_PREMIUM_6PERS = 14;
$STATUS_ZUGEORDNET    = 15;
$STATUS_VIP_2PERS     = 16;
$STATUS_VIP_4PERS     = 18;
$STATUS_PREMIUM_8PERS = 19;

//Sitzplanstatus
$SITZ_RESERVIERT = 1;
$SITZ_VORGEMERKT = 2;

//Clanstatus
$AUFNAHMESTATUS_WARTEND = 1;
$AUFNAHMESTATUS_OK      = 2;

$TURNIERTYPE = array(
	"1" => "2-Team Deathmatch",
	"2" => "Fragmaster",
	"3" => "Mixed 2-Team Deathmatch",
	"4" => "Best out of n",
	"5" => "1v1 Deathmatch",
	"6" => "2-Team DM: Doppelt KO"
);

// Beamerfolien
$FOLIENTYP = array(
	"1" => "Standard Text",
	"2" => "HTML mit Rahmen",
	"3" => "HTML pur",
	"4" => "Video",
	"5" => "Bild",
	"6" => "PHP"
);
$FOLIE_STD       = 1;
$FOLIE_HTML_MIT  = 2;
$FOLIE_HTML_OHNE = 3;
$FOLIE_VIDEO     = 4;
$FOLIE_BILD      = 5;
$FOLIE_PHP       = 6;


//Bugtracking.php
$BUGTYPE = Array (
	0  => "Netz",
	1  => "User",
	2  => "HLSW",
	3  => "Arps",
	4  => "Spiele",
	5  => "sonstiges"
);

$BUGPRIORITY = Array (
	0  => "low",
	1  => "middle",
	2  => "high"
);

$BUGSTATUS  = Array (
	0  => "zu bearbeiten",
	1  => "in Bearbeitung",
	2  => "nicht gelöst",
	3  => "gelöst"
);

define('IRC_SERVER_HOST', 'irc.lan');
define('IRC_SERVER_PORT', 6667);

// Anzeige für User Online, wie lange darf der User inaktiv sein, damit er noch angezeigt wird? Unix Timestamp in Sekunden

define('USER_ONLINE_TIMEOUT', 300);

$MINDESTALTER = Array (
	0		=>	"USK 0",
	6		=>	"USK 6",
	12	=>	"USK 12",
	16	=>	"USK 16",
	18	=>	"USK 18"
);

// Mail-Absender und Account
define('MAIL_ABSENDER', 'mailer@multimadness.de');
define('MAIL_ABSENDER_NAME', 'MultiMadness');
define('MAIL_HOST',     getenv('MAIL_HOST')     ?: '');
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');

// Admins Mail-Address
define('ADMIN_MAIL', 'team@multimadness.de');

// Mail-Absender und Account NEWSLETTER!
define('MAIL_ABSENDER_NEWSLETTER', 'news@multimadness.de');
define('MAIL_ABSENDER_NAME_NEWSLETTER', 'MultiMadness');
define('MAIL_HOST_NEWSLETTER',     getenv('MAIL_HOST_NEWSLETTER')     ?: '');
define('MAIL_USERNAME_NEWSLETTER', getenv('MAIL_USERNAME_NEWSLETTER') ?: '');
define('MAIL_PASSWORD_NEWSLETTER', getenv('MAIL_PASSWORD_NEWSLETTER') ?: '');

?>