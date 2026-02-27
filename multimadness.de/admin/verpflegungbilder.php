<?php
require('controller.php');
$iRecht = 'BILDERADMIN';
require_once 'dblib.php';
include_once "checkrights.php";
include_once "admin/vorspann.php";
//require_once 'HTTP/Upload.php'; OLD Version required PEAR's HTTP_Upload

require_once 'classes/SmartyAdmin.class.php';

define('MANDANTID', 'admin');

$smarty = new SmartyAdmin();
$smarty->appName = 'verpflegungbilder';

if (!is_dir(VERPFLEGUNG_DIR))
  die(VERPFLEGUNG_DIR.' fehlt!');

$nLoginID = $loginID;

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : NULL);


$sql = "select 
          m.MANDANTID, m.BESCHREIBUNG
        from 
          MANDANT m, RECHTZUORDNUNG r 
        where 
          r.USERID=$loginID and 
          r.MANDANTID=m.MANDANTID and 
          r.RECHTID='NEWSADMIN'";          
$res = DB::query($sql);         

while ($row = mysql_fetch_assoc($res)) {
  // erlaubte Mandanten durchgehen
  $mandanten[$row['MANDANTID']]['beschreibung'] = $row['BESCHREIBUNG'];
  $pattern = VERPFLEGUNG_DIR.$row['MANDANTID'].'_*';
  // Alle Files des Mandanten holen
  $files = glob($pattern);
  $mandanten[$row['MANDANTID']]['images'] = array();
  if (is_array($files)) {
    foreach ($files as $val) {
      // Bilder ins Array. Nur die Dateinamen, keine Verzeichnisse
      $image['name'] = substr($val, strrpos($val, '/') + 1);
      $image['info'] = getimagesize($val);
      $image['groesse'] = filesize($val);
      $mandanten[$row['MANDANTID']]['images'][] = $image;
    }
  }
}


switch($action) {
  
  case 'preview':
    // remove the shit that register_globals does
    unset($image);
    
    $imageName = $_GET['image'];
	$smarty->assign('image', $image);
    if (($mandantId = validateImageName($imageName)) === false) {
      // entspricht nicht der konvention
      $error = 'Fehlerhafter Dateiname';
    } else if (!isset($mandanten[$mandantId])) {
      $error = 'Kein Zugriff auf diesen Mandanten';
    } else if (!file_exists(VERPFLEGUNG_DIR.$imageName)) {
      $error = 'Datei existiert nicht';    
    } else {
      $image['name'] = $imageName;
      $image['info'] = getimagesize(VERPFLEGUNG_DIR.$imageName);
      $image['groesse'] = filesize(VERPFLEGUNG_DIR.$imageName);
      $image['url'] = VERPFLEGUNG_PATH.$imageName;
      $smarty->assign('image', $image);
    }
    if (isset($error)) 
      $smarty->assign('error', $error);
    $smarty->displayWithFallback('preview.tpl');
    break;
    
  case 'upload':
    
    $dstFilename = VERPFLEGUNG_DIR.$_POST['mandantId'].'_'.$_FILES['uploadImage']['name'];
    $dstFilenameWithoutDir = $_POST['mandantId'].'_'.$_FILES['uploadImage']['name'];
    
    if (!isset($_POST['mandantId']) || !is_numeric($_POST['mandantId']) || !isset($mandanten[$_POST['mandantId']])) {
      $error = 'Ungültige MandantId.';
    } else if(file_exists($dstFilename)) {
      $error = "Ein Datei mit dem Namen '$dstFilenameWithoutDir' existiert bereits.";
    } else {
      $val = handleUpload(VERPFLEGUNG_DIR, $_POST['mandantId'].'_');
      if ($val !== true) {
        $error = $val;
      } else {
        $image['name'] = $dstFilenameWithoutDir;
        $image['info'] = getimagesize($dstFilename);
        $image['groesse'] = filesize($dstFilename);
        $smarty->assign('uploadedImage', $image);
        $smarty->assign('uploadedImageUrl', VERPFLEGUNG_PATH.$dstFilenameWithoutDir);
      }      
    }
    if (isset($error))
      $smarty->assign('error', $error);
      
    $smarty->displayWithFallback('upload.tpl');
    break;
    
  case 'delete':
    $imageName = $_GET['image'];
    $smarty->assign('imageName', $imageName);
    if (!isset($_GET['confirm']) || !$_GET['confirm']) {
      // Bestätigungsseite anzeigen
      $template = 'delete.tpl';
    } else { 
      if (($mandantId = validateImageName($imageName)) === false) {
        // entspricht nicht der konvention
        $error = 'Fehlerhafter Dateiname.';
      } else if (!isset($mandanten[$mandantId])) {
        $error = 'Kein Zugriff auf diesen Mandanten.';
      } else if (!file_exists(VERPFLEGUNG_DIR.$imageName)) {
        $error = 'Datei existiert nicht.';    
      } else if (!unlink(VERPFLEGUNG_DIR.$imageName)) {
        $error = 'Löschen fehlgeschlagen.';
      }
      $template = 'delete_confirm.tpl';
    }
    if (isset($error)) 
      $smarty->assign('error', $error);
    $smarty->displayWithFallback($template);
  
    break;
    
  default:
    // verfügbare Mandanten zur Auswahl anzeigen
    $smarty->assign('mandanten', $mandanten);
    $smarty->displayWithFallback('index.tpl');
    break;
}

function validateImageName($imageName) {
  /*
   * return id (first number) in imageName or false on error
   */
  if (preg_match(VERPFLEGUNG_VALID_FILE_PATTERN, $imageName, $m) == 1)
    $rc = (int) $m[1];
  else
    $rc = (bool) false;
  return $rc;
}

function validateImageType($imageType) {
  /*
   * return true if imageType is valid
   */
  global $VERPFLEGUNG_VALID_FILE_EXTS;
  return in_array($imageType, $VERPFLEGUNG_VALID_FILE_EXTS);
}


// NEW version with only php included features
function handleUpload($dstDir, $prefix) {
  $rc = true;
  // Upload der Datei
	
  if ($_FILES['uploadImage']['error']) {
    $rc = 'Fehler beim Upload';  
  } else if (!validateImageType($_FILES['uploadImage']['type'])) {
    $rc = 'Ungültiger Dateityp.';
  } else {
		// Move file
		$dst_name = $dstDir.$prefix.$_FILES['uploadImage']['name'];
		if(!move_uploaded_file($_FILES['uploadImage']['tmp_name'], $dst_name))
		{
      $rc = 'Fehler beim Verschieben der hochgeladenen Datei';
    } else {
      // Kein Fehler, nichts tun
    }
  }  
  return $rc;
}


/* OLD Version with pears's HTTP_Upload
function handleUpload($dstDir, $prefix) {
  $rc = true;
  // Upload der Datei
  $upload = new http_upload('de');
  $file = $upload->getFiles('uploadImage');
  if (PEAR::isError($file)) {
    $rc = $file->getMessage();  
  } else if (!validateImageType($file->upload['ext'])) {
    $rc = 'Ungültiger Dateityp.';
  } elseif ($file->isMissing()) {
    $rc = "No file selected\n";
  } elseif ($file->isError()) {
    $rc = $file->errorMsg();
  } else if ($file->isValid()) {
    $file->setName('real', $prefix);
    $dest_dir = $dstDir;
    $dest_name = $file->moveTo($dest_dir);
    if (PEAR::isError($dest_name)) {
      $rc = $dest_name->getMessage();
    } else {
      $real = $file->getProp('real');
    }
  }  
  return $rc;
}
*/
require_once 'nachspann.php';
?>
