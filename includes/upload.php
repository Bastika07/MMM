<?php

// Execution-Limit auf 20 Stunden setzen
set_time_limit(72000);

function handle_archiv_upload($dst, $kategorie) {
  global $nLoginID, $KATEGORIE_ARCH_VIDEOS, $KATEGORIE_ARCH_BILDER;
  	
  PELAS::logging("handling upload of file: '{$_FILES['userfile']['name']}'", 'archivupload', $nLoginID);

  $uploadfile = UPLOADDIR.$_FILES['userfile']['name'];
  
  $rc = false;
  
  if (!is_dir(UPLOADDIR) && !mkdir(UPLOADDIR))
    echo "Fileupload error (Directory)";
  else if ($_FILES['userfile']['error'] != UPLOAD_ERR_OK)
    PELAS::logging("Fileupload error: '{$_FILES['userfile']['name']}': errorCode {$_FILES['userfile']['error']}", 'archivupload', $nLoginID);
  else {  
    //var_dump($_FILES);
    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {      
      mkdir ($dst, 0777);

      switch ($_FILES['userfile']['type']) {
        case 'image/pjpeg':           
        case 'image/jpeg':
          rename ($uploadfile, $dst.$_FILES['userfile']['name']);

          $cmd = DJPEG." ".escapeshellarg($dst.$_FILES['userfile']['name'])." | ".PNMSCALE." -xysize 130 97 | ".CJPEG." -progressive -outfile ".escapeshellarg($dst."tn_".$_FILES['userfile']['name']).""; 
          exec($cmd);
          chmod($dst.$_FILES['userfile']['name'], 0664);
          chmod($dst."tn_".$_FILES['userfile']['name'], 0664);
          
          $rc = $_FILES['userfile']['name'];
          break;
        case 'application/x-zip-compressed':
        case 'application/octet-stream':        
        case 'application/zip' && $kategorie == $KATEGORIE_ARCH_BILDER:        
          preg_match('/.*(\..*)/', $_FILES['userfile']['name'], $m);

          if (strtolower($m[1]) == '.zip') {            
            // Zip-File, entpacken
            PELAS::logging("'{$_FILES['userfile']['name']}' is ZIP-File", 'archivupload', $nLoginID);
            $cmd = UNZIP." -o -j ".escapeshellarg($uploadfile)." -d $dst";            
            //var_dump($cmd);
            exec($cmd, $o);            
            $rc = array();
            //var_dump($o);
            
            foreach ($o as $value) {
              if (preg_match("#(?:inflating|extracting): $dst(.*)#i", $value, $m)) {
                $cmd = NICE." ".DJPEG." ".escapeshellarg($dst.$m[1])." | ".NICE." ".PNMSCALE." -ysize 97 | ".NICE." ".CJPEG." -progressive -outfile ".escapeshellarg($dst."tn_".$m[1]).""; 
                //var_dump($cmd);
                exec($cmd);
                chmod($dst.$m[1], 0664);
                chmod($dst."tn_".$m[1], 0664);
                array_push($rc, $m[1]);
              }              
            }  
            unlink($uploadfile);   
          } else {
            echo "unknown octet-stream";
            PELAS::logging("unknown octet-stream while processing:'{$_FILES['userfile']['name']}'", 'archivupload', $nLoginID);
          }
          //unlink($uploadfile);
          break;
        case 'video/x-msvideo': // avi
        case 'video/avi': 	// avi
        case 'video/x-ms-wmv':  // wmv
        case 'video/mpeg':      // mpg
	case 'audio/mpeg':	// mp3
	case 'application/zip' && $kategorie == $KATEGORIE_ARCH_VIDEOS:
          PELAS::logging("'{$_FILES['userfile']['name']}' is MEDIA-file", 'archivupload', $nLoginID);
          rename ($uploadfile, $dst.$_FILES['userfile']['name']);
          chmod($dst.$_FILES['userfile']['name'], 0664);
          $rc = $_FILES['userfile']['name'];          
          break;
        default:
          PELAS::logging("unknown type '{$_FILES['userfile']['type']}' for: '{$_FILES['userfile']['name']}', Kategorie: $kategorie", 'archivupload', $nLoginID);
          //unlink($uploadfile);
          break;
      }
    } else {
      PELAS::logging("Fileupload error: '{$_FILES['userfile']['name']}'. Could not move {$_FILES['userfile']['tmp_name']} to {$uploadfile}", 'archivupload', $nLoginID);
      echo "Fileupload error (Copy2dir)";
    }
  }
  return $rc;
}
?>
