<!DOCTYPE html>

<?php
if(isset($_POST['btnCookieOk1'])){
   setcookie('eu-cookie-mmm', '1', time()+1209600);
   header("Refresh:0");
}

if(empty($_COOKIE['eu-cookie-mmm']) || $_COOKIE['eu-cookie-mmm'] != '1') {
echo '<div id="eu-cookie-message">
   <form action="" method="post">
      Durch Verwendung dieser Webseite stimmst du der Cookie-Nutzung zu. <input type="submit" value="akzeptieren" name="btnCookieOk1" />
   </form>
</div>';
}
?>
<?php
# Check Basic Setup anhand der IP des Servers
#5.6.2022 Gritzi if ($_SERVER['SERVER_ADDR'] == "10.10.250.201") {
#	# Intranet!
#5.6.2022 Gritzi	set_include_path('/var/www/includes/');
#5.6.2022 Gritzi	if ($_SERVER['HTTPS'])
#5.6.2022 Gritzi		$base = "https://www.lan.multimadness.de/";
#5.6.2022 Gritzi	else
#5.6.2022 Gritzi		$base = "http://www.lan.multimadness.de/";	
#5.6.2022 Gritzi} else {
	# Let's include PELAS - yeah, it lives! But it's somewhere else:
	set_include_path('/var/www/vhosts/hosting103794.af995.netcup.net/includes/');
 if ($_SERVER['HTTPS'])
	$base = "https://".$_SERVER['HTTP_HOST']."/";
else
	$base = "http://".$_SERVER['HTTP_HOST']."/";
#}

# Ausgabepufferung ist wegen alter PELAS-Scripte, die mitten im Code ein redirect machen, notwendig.
ob_start();

$nPartyID = 2; # PELAS-Mandant setzen
define('ACCOUNTING', 'NEW'); # Auf neues Accounting setzen (Wichtig!)
date_default_timezone_set ( 'Europe/Berlin' );
define('MANDANTID', '2');

define('BINGMAPS_KEY', getenv('BINGMAPS_KEY') ?: ''); # Key for BING Maps

include_once "getsession.php"; # Für Login-Anzeige schon mal die Session holen
include_once 'PHPMailer/PHPMailerAutoload.php';

# Unsere Seiten
$page_info = array (
	1	=>	"start",
	111	=>	"start2",
	2	=>	"news",
	3	=>	"info",
	4	=>	"benutzerdetails",
	5	=>	"login",
	6	=>	"accounting",
	7	=>	"accounting_rechnung", # ???????????? Wird das direkt aufgerufen?
	8	=>	"teilnehmerliste",
	9	=>	"sitzplan",
	10	=>	"forum",
	11	=>	"login_edit",
	12	=>	"forum",
	13	=>	"sitzplan",
	14	=>	"archiv",
	15	=>	"archiv_upload",
	16	=>	"geekradar",
	17	=>	"kontaktformular",
	18	=>	"clanverwaltung",
	19	=>	"clandetails",
	
	20	=>	"turnier/turnier_list",
	21	=>	"turnier/turnier_detail",
	
	22	=>	"turnier/turnier_faq",
	23	=>	"turnier/turnier_ranking",
	24	=>	"turnier/turnier_table",
	25	=>	"turnier/turnier_tree",
	26	=>	"turnier/match_detail",
	27	=>	"turnier/team_create",
	28	=>	"turnier/team_create2",
	29	=>	"turnier/team_detail",
	30	=>	"turnier/team_swap",

	31	=>	"gastserver",
	32	=>	"umfrage",

	40	=>	"lokation",
	41	=>	"netzwerk",
	42	=>	"bedingungen",
	43	=>	"impressum",
	44	=>	"team",
	45	=>	"verpflegung",
	46	=>	"umgebungskarte",
	47	=>	"datenschutz",
	49  =>  "shirtshop",
	
	48	=>	"sponsoren",
	99	=>	"sitzplanv2",
	500 =>  "covid19",
	999 	=>	"error"
);

# Aufgerufene Seite suchen oder auf die Nr. 1 setzen:
if (isset($_GET['page']) && array_key_exists(intval($_GET['page']), $page_info))
	$page = intval($_GET['page']);
else
	$page = 1;
	
if (is_file("page/".$page_info[$page].".top.php")) include "page/".$page_info[$page].".top.php"; 
?>
<?php
require_once "dblib.php";
include_once "format.php";
include_once "language.inc.php";

if(isset($_POST['btnNewsletterOK'])){
	$sql = "UPDATE USER
					SET NEWSLETTER = 1, NEWSLETTER_ABO_DATE = NOW(), NEWSLETTER_ABO_CODE='', NEWSLETTER_ABO_EMAIL=NULL, POPUP_DISPLAY = 0
					WHERE USERID = '".intval($nLoginID)."'";	
	$erfolg = DB::query($sql);
	//header("Refresh:0");
}


if(isset($_POST['btnNewsletterCancel'])){
   	$sql = "UPDATE USER
					SET NEWSLETTER = 0, NEWSLETTER_ABO_DATE = NULL, NEWSLETTER_ABO_CODE='', NEWSLETTER_ABO_EMAIL=NULL, POPUP_DISPLAY = 0
					WHERE USERID = '".intval($nLoginID)."'";
	$erfolg = DB::query($sql);
	//header("Refresh:0");
}


 if ($nLoginID > 0) {
	$sql = "SELECT POPUP_DISPLAY
					FROM USER
					WHERE NEWSLETTER = 0 AND USERID = '".intval($nLoginID)."'";
	$token = intval(DB::getOne($sql));
	if ($token == 1) {
		echo '<div class="modal">
				<!-- Modal content -->
				  <div class="modal-content"><h1>Moin und willkommen zurück!</h1>
<p>Seit des Inkrafttretens der DSGVO am 25.5.2018 warst du noch nicht bei uns auf der Website oder hast die E-Mail beantwortet.</p>
<h4>Newsletter: </h4><br>
<p>Bist du damit Einverstanden weiterhin Informationen zur MultiMadness per E-Mail Newsletter zu erhalten? Dann klick auf die grüne Schaltfläche!</p><br>
<h4>Aktualisierte Datenschutzrichtlinien: </h4><br>
<p>Zum 25. Mai 2018 setzen wir die neue Datenschutz-Grundverordnung (DSGVO) der Europäischen Union um. <br>
Wir haben unsere Datenschutzrichtlinie aktualisiert, um euch mehr Informationen darüber zur Verfügung zu stellen, wie eure Daten genutzt und geschützt werden.</p>
<p><a href="?page=47" target="_blank" class="ng_url">Datenschutzrichtlinie</a> </p><br>
<h4>Ich möchte den Newsletter weiterhin erhalten!</h4>
								  <form action="/" method="post">' . csrf_field() . '
								  <input type="submit" value="Abonnieren" name="btnNewsletterOK" class="btnNewsletterOK"/>
			   </form><br>
<h4>Ich möchte den Newsletter nicht mehr erhalten. :(</h4>		   
			   <form action="/" method="post">' . csrf_field() . '
			   <input type="submit" value="Abbestellen" name="btnNewsletterCancel" class="btnNewsletterCancel"/>
			   </form>
				  </div>
			</div>';
	}
}
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<base href="<?= $base; ?>">
<title>MultiMadness LAN-Parties - Die Legende lebt</title>
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
<link rel="stylesheet" type="text/css" href="css/main.css">
<link rel="stylesheet" type="text/css" href="css/lightbox.css">
<link rel="stylesheet" href="css/font-awesome.min.css">
<script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="js/lightbox.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#menu1_li").click(function(){
			$("#menu1").slideToggle(100);
			$("#menu1_li").toggleClass("active");
		});
	});
	
	function SendMail(praefix) {
		var toplevel = String.fromCharCode(46,100,101);
		var domain =  String.fromCharCode(109,117,108,116,105,109,97,100,110,101,115,115);
		var at =  String.fromCharCode(64);
		location.href = "mail" + "to:" + praefix + at + domain + toplevel;
	}
</script>

<link rel="shortcut icon" href="favicon.ico">

<?php if (is_file("page/".$page_info[$page].".head.php")) include "page/".$page_info[$page].".head.php"; ?>
</head>

<body>

<div id="wrapper_footerdown">

<div id="header">
	<div class="inside">
  
<?php	/*
    	<div class="wmann" style="float:right; margin-bottom:-10px;">
				<img src="gfx/wmann_guitar.gif" width="104" height="121">
        <?php // Orig size: 104x121 ?>
   		</div>
*/	?>
  
    	<div style="float:left; padding:0px 10px 0px 10px;"><a href="?page=1"><img src="img/logo_2018.png" width="160" height="130" alt="Logo"></a></div>
		<div class="banner" style="float:left;margin-left:180px"><img src="img/MMMBanner42.png" height="170" alt="Banner"></div>
		<!--<div style="float:right; padding:30px 0px 30px 0px;"><a href="?page=500"><img src="img/covid.png" height="70" alt="Unser Umgang mit COVID-19"></a></div>*/-->

        <div class="clear"></div>
	</div>
</div>

<div id="mainmenu">
	<div class="inside">
  
    <div class="login">
<?php 
			if ($nLoginID > 0) {
				echo '<a href="?page=11" title="Meine persönlichen Daten">'.htmlspecialchars($sLogin).'</a> ';
				echo '<span class="orange_light">|</span> <a href="?page=5">Logout</a> <span class="orange_light">|</span> <nobr>IP: '.$_SERVER['REMOTE_ADDR'].'</nobr>';	
			} else {
				echo '<a href="?page=5">Login</a>';
			}
?>
    </div>
		<ul>
			<li id="menu1_li">Navigation</li>
		</ul>
   	<div class="clear"></div>
	</div>
</div>

<div id="submenu">
	<div class="inside">
  <div id="menu1" class="menutoggle">
    <ul>
      <li class="heading">Infos</li>
      <li><a href="?page=2">News</a></li>
      <li><a href="?page=3">Auf einen Blick</a></li>
      <li><a href="?page=48">Sponsoren</a></li>
      <li><a href="?page=46">Umgebungskarte</a></li>
      <li><a href="?page=40">Lokation</a></li>
      <li><a href="?page=41">Netzwerk</a></li>
      <li><a href="?page=45">Verpflegung</a></li>
      <li><a href="?page=14">Party-Archiv</a></li>
    </ul>
  
    <ul>
      <li class="heading">Teilnahme</li>
<?php	if (LOCATION != 'intranet') { ?>
      <li><a href="?page=6">Ticketverwaltung</a></li>
<?php } ?>
      <li><a href="?page=8">Teilnehmerliste</a></li>
      <li><a href="?page=13">Sitzplatzreservierung</a></li>
      <li><a href="?page=20">Turnierliste</a></li>
<?php	if (LOCATION != 'intranet') { ?>
      <li><a href="?page=31">Gastserveranmeldung</a></li>
      <li><a href="?page=18">Clanverwaltung</a></li>
<?php } ?>
      <li><a href="?page=42">Allgemeine Geschäftsbedingungen</a></li>
    </ul>
  
    <ul>
      <li class="heading">Community</li>
      <li><a href="?page=12">Forum</a></li>
      <li><a href="?page=16">Teilnehmerkarte</a></li>
      <li><a href="?page=32&action=results">Umfrage</a></li>
	  <li><a href="?page=49">Shop</a></li>
      <?php # <li><a href="#">Clanliste</a></li> # Deaktiviert, da viele alte Clans ohne Bilder ?>
    </ul>
  
    <ul>
      <li class="heading">Kontakt</li>
      <li><a href="?page=44">Team Madness</a></li>
      <li><a href="https://www.facebook.com/MultimadnessMaschen">MultiMadness auf Facebook</a></li>
      <li><a href="?page=47">Datenschutzerklärung</a></li>
      <li><a href="?page=43">Impressum</a></li>
    </ul>
  
    <div class="clear"></div>
    
	</div>
  </div>
</div>

<div id="content">
  <div class="inside">
 
<?php
	include("pelasfront/party_running_checker.php");
?>
 
<?php if (is_file("page/".$page_info[$page].".php")) include "page/".$page_info[$page].".php"; ?>
       
  </div>
</div>

</div>

<div id="footer">
	<div class="inside">
  
<?php	/*
  	<div style="margin-top:-15px; margin-bottom:15px;">
        <audio src="gfx/ws_mukke.mp3" type="audio/mp3" loop autoplay="true" controls></audio>
    </div>
*/ 	?>
  
			  <a href="?page=43">Impressum</a>
        <span class="orange_light">|</span> <a href="?page=47">Datenschutzerklärung</a>
        <span class="orange_light">|</span> <a href="?page=42">AGB</a>
        <span class="orange_light">|</span> <i class="fa fa-facebook-official"></i> <a href="https://www.facebook.com/MultimadnessMaschen">MultiMadness auf Facebook</a>
	</div>
</div>

</body>
</html>
<script type="text/javascript">
$("js-1124342058").remove();
$("js-3926762131").remove();

var child = document.getElementById("js-1124342058");
child.parentNode.removeChild(child);
</script>

<?php
ob_end_flush();
?>