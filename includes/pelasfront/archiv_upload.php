<?php
####################################################
# PELAS-Datei: archiv.php
#
# Stellt die Uploadfunktion für das Archiv zur
# Verfügung.
# Es können nur noch Bilderarchive hochgeladen werden.
# Filme können als URL zu einem youtube Film eingefügt
# werden.
#
# Variablen, die Übergeben werden können:
#
# $seitenBreite: Breite der Tabellen in em (Def: 40em)
#
######################################################

include_once "dblib.php";
include_once "format.php";
include_once "session.php";
//include_once "pelasfunctions.php";

include_once 'classes/PelasSmarty.class.php';

DB::connect();

if (LOCATION == "intranet") {
    echo "Der Bildupload steht im Intranet nicht zur Verf&uuml;gung.";
} else {
    // dispatcher
    $action = (isset($_GET['action']) ? $_GET['action'] : '');
    switch ($action) {
	    case 'doUpload':	doUpload();
			    break;
	    case 'doUploadYouTube':	doUploadYouTube();
			    break;
	    case 'upload':	upload();
			    break;
	    default:	upload();
			    break;
    }
}


function upload() {
  $archiv_typ = (isset($_GET['typ']) ? $_GET['typ'] : '');

  $smarty = new PelasSmarty();
  $smarty->appName = 'pelas';
  $smarty->assign('pelasHost', PELASHOST);

  // Letzte historische Parties herausfinden
  $sql = "select partyId, beschreibung AS PartyName 
	  from party 
	  where MANDANTID = ".MANDANTID."
	  and ( terminVon < NOW()
	  or aktiv = 'J')
	  order by terminVon desc";
;
  $last_parties = DB::getRows($sql);

  $form_disabled = "";
  global $nLoginID, $sLogin;

  if($nLoginID < 1) { #user ist nicht eingeloggt
    $form_disabled = "disabled";
  } else {
    $smarty->assign('sLogin', $sLogin);
  }
  $smarty->assign('form_disabled', $form_disabled);
  $smarty->assign('iName', $_POST['iName']);
  $smarty->assign('last_parties', $last_parties);
	
	$smarty->assign('max_upload_size', ini_get('upload_max_filesize'));

  if ($archiv_typ == "youtube")
    $smarty->displayWithFallback('archiv_upload_youtube.tpl');
  elseif($archiv_typ == "img")
    $smarty->displayWithFallback('archiv_upload_img.tpl');
  else
    $smarty->displayWithFallback('archiv_upload.tpl');

}

function doUpload() {
  // Execution-Limit auf 20 Stunden setzen
  set_time_limit(72000);
    
  $iName = addslashes($_POST['iName']);
  $iKommentar = addslashes($_POST['iKommentar']);

  $iParty = (isset($_POST['iParty']) ? intval($_POST['iParty']) : 0);

  global $nLoginID;

  if($nLoginID < 1) { #user ist nicht eingeloggt
    echo "du bist nicht eingeloggt.";
    die();
  }

  #An dieser Stelle sollte die Datei schon komplett hoch geladen sein. Selbst wenn der User nun noch Abbricht, wird die Datei daher zu Ende verarbeitet:
  ignore_user_abort(true);

  #übergeben PartyID prüfen:
  $sql = "select partyId AS PartyName 
	  from party 
	  where MANDANTID = ".MANDANTID."
	  and ( terminVon < NOW()
	  or aktiv = 'J')
	  and partyId = '$iParty'";
		
  if (DB::getRow($sql) == false) echo "Party ist ungültig!";
  
  if (!is_dir(UPLOADDIR) && !mkdir(UPLOADDIR))
    echo "Fileupload error (Directory)";
  else if ($_FILES['userfile']['error'] != UPLOAD_ERR_OK)
    PELAS::logging("Fileupload error: '{$_FILES['userfile']['name']}': ".resolveErrorCode2($_FILES['userfile']['error']), 'archivupload', $nLoginID);
  else if (strtoupper(substr($_FILES['userfile']['name'], strlen($_FILES['userfile']['name']) - 3, 3)) <> "ZIP") {
    PELAS::logging("Fileupload error: '{$_FILES['userfile']['name']}': Falsche Dateiendung", 'archivupload', $nLoginID);
    echo("Fehler: Es können nur ZIP Dateien hoch geladen werden.");
  } else {  
      //ArchivID für weitere Verarbeitung erzeugen
      $sql = "insert into ARCHIV "
            ."(MANDANTID, PARTYID, TYP, USERID, KOMMENTAR, BESCHREIBUNG, LINK, WANNANGELEGT) "
            ."values ('".MANDANTID."', '$iParty', 'img', '$nLoginID', '".safe($iName)."', '".safe($iKommentar)."', '', NOW())";
      DB::query($sql);
      $archivid = DB::$link->insert_id;

      $uploadfile = UPLOADDIR.'archiv'.$archivid.'.upload';
      $archiv_dst = ARCHIV_UPLOADDIR."$archivid/";

    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
      mkdir ($archiv_dst, 0777);

      switch ($_FILES['userfile']['type']) {
        case 'application/x-zip-compressed':
	case 'application/zip':
	case 'application/octet-stream':
            // Zip-File, entpacken
            $cmd = UNZIP." -o -j ".escapeshellarg($uploadfile)." -d $archiv_dst";     
	    exec($cmd, $o);  
            $rc = array();
            foreach ($o as $value) {
              if (preg_match("#(?:inflating|extracting): $archiv_dst(.*)#i", $value, $m)) {
                //$cmd = NICE." ".DJPEG." ".escapeshellarg($archiv_dst.$m[1])." | ".NICE." ".PNMSCALE." -ysize 97 | ".NICE." ".CJPEG." -progressive -outfile ".escapeshellarg($archiv_dst."tn_".$m[1]).""; 
                //var_dump($cmd);
                //exec($cmd);
				
					$thumb = new Imagick($archiv_dst.$m[1]);

					$thumb->scaleImage(0,145,false);
					$thumb->stripImage();
					$thumb->writeImage($archiv_dst.'tn_'.$m[1]);

					$thumb->destroy(); 
				
                chmod($archiv_dst.$m[1], 0664);
                chmod($archiv_dst."tn_".$m[1], 0664);
                array_push($rc, $m[1]);
              }              
            }  
            unlink($uploadfile); 
	    echo("Das Archiv wurde erfolgreich hoch geladen und wird nun von einem Admin überprüft.");
	    break;
        default:
          unlink($uploadfile);
          break;
      }
    } else {
      $sql = "DELETE FROM ARCHIV WHERE archivid = ".$archivid;
      DB::query($sql);
      PELAS::logging("Fileupload error: '{$_FILES['userfile']['name']}'. Could not move {$_FILES['userfile']['tmp_name']} to {$uploadfile}", 'archivupload', $nLoginID);
      echo "Fileupload error (Copy2dir)";
    }
  }
  return $rc;
}

function doUploadYouTube() {
  $iName = addslashes($_POST['iName']);
  $iKommentar = addslashes($_POST['iKommentar']);
  $iUrl = addslashes($_POST['iurl']);

  $iParty = (isset($_POST['iParty']) ? intval($_POST['iParty']) : 0);

  global $nLoginID;

  if($nLoginID < 1) { #user ist nicht eingeloggt
    echo "du bist nicht eingeloggt..";
    die();
  }
  
  #übergeben PartyID prüfen:
  $sql = "select partyId AS PartyName 
	  from party 
	  where MANDANTID = ".MANDANTID."
	  and ( terminVon < NOW()
	  or aktiv = 'J')
	  and partyId = $iParty";
  if (DB::getRow($sql) == false) echo "Party ist ungültig!";

  #URL prüfen
  # Raus genommen, gibt nun andere Links: substr($iUrl, 0, strlen('http://www.youtube.com/watch?v=')) <> "http://www.youtube.com/watch?v="
  if (1 == 2) {
    echo("Der angegebene Link ".htmlspecialchars($iUrl)." ist ungültig!");
  } else {
    $videoID = substr($iUrl, strlen('https://www.youtube.com/watch?v='), strlen($iUrl) - strlen('https://www.youtube.com/watch?v='));
    $sql = "insert into ARCHIV "
          ."(MANDANTID, PARTYID, TYP, USERID, KOMMENTAR, BESCHREIBUNG, LINK, WANNANGELEGT) "
          ."values ('".MANDANTID."', '$iParty', 'youtube', '$nLoginID', '".safe($iName)."', '".safe($iKommentar)."', '$videoID', NOW())";
    DB::query($sql);
    echo("Dein Beitrag wurde erfolgreich gespeichert und wird nun von einem Admin geprüft.");
  }
}

function resolveErrorCode2($code) {
  switch ($code) {  
    case 0: return "There is no error, the file uploaded with success"; break;
    case 1: return "The uploaded file exceeds the upload_max_filesize directive in php.ini"; break;
    case 2: return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; break;
    case 3: return "The uploaded file was only partially uploaded"; break;
    case 4: return "No file was uploaded"; break;
    case 6: return "Missing a temporary folder"; break;
    default: return "unknown error";
  }
}
?>