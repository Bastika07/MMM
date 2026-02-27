<?php
if (!function_exists('PostToHost')) {
	function PostToHost($path, $referer, $data_to_send) {
	   GLOBAL $sPelasHost, $sPelasHostOhne;

	   $host = getenv("HTTP_HOST");

	   $sDataSend = "";

	   foreach ($data_to_send as $key => $value) {
	     $sDataSend = $sDataSend . "$key=".urlencode($value)."&";
	   }

	   //echo "<p>Data posting: $sDataSend</p>";   

	   $fp = fsockopen($sPelasHostOhne, 80);
	   fputs($fp, "POST $sPelasHost$path HTTP/1.1\n");
	   fputs($fp, "Host: $host\n");
	   fputs($fp, "Referer: http://$host$referer\n");
	   fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
	   fputs($fp, "Content-length: ".strlen($sDataSend)."\n");
	   fputs($fp, "Connection: close\n\n");
	   fputs($fp, "$sDataSend\n");
	   while(!feof($fp)) {
	      $res = $res.fgets($fp, 128);
	   }
	   fclose($fp);

	   return substr(strstr(substr(substr($res, strpos ($res, "\r\n\r\n")), 4), "\n"), 1, -7);
	}
}

if (!function_exists('DataPostToHost')) {
	function DataPostToHost($path, $referer, $data_to_send) {
		// Posts Multipart-Forms
		GLOBAL $sPelasHost, $sPelasHostOhne;

		$dc = 0;
		$bo="-----------------------------305242850528394";

		$fp = fsockopen($sPelasHostOhne, 80, $errno, $errstr);
		if (!$fp) { 
		      echo "errno: $errno \n";
		      echo "errstr: $errstr\n";
		      return $result;
		}

		$host = getenv("HTTP_HOST");

		fputs($fp, "POST $sPelasHost$path HTTP/1.1\n");
		fputs($fp, "Host: $host\n");
		fputs($fp, "Referer: http://$host$referer\n");
		fputs($fp, "User-Agent: Mozilla/4.05C-SGI [en] (X11; I; IRIX 6.5 IP22)\n");
		fputs($fp, "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, image/png, */*\n");
		fputs($fp, "Accept-Charset: iso-8859-1,*,utf-8\n");
		fputs($fp, "Content-type: multipart/form-data; boundary=$bo\n");

		foreach($data_to_send as $key=>$val) {
		      $ds =sprintf("%s\nContent-Disposition: form-data; name=\"%s\"\n\n%s\n",$bo,$key,$val);
		      $dc += strlen($ds);
		}

		$dc += strlen($bo)+3;
		fputs($fp, "Content-length: $dc \n");
		fputs($fp, "\n"); 

		foreach($data_to_send as $key=>$val) {
		      $ds =sprintf("%s\nContent-Disposition: form-data; name=\"%s\"\n\n%s\n",$bo,$key,$val);
		      fputs($fp, $ds );
		}
		$ds = $bo."--\n" ;

		fputs($fp, $ds);

		while(!feof($fp)) {
		      $res .= fread($fp,1);
		}
		fclose($fp);

		echo "Res: $res";

	}
}

?>
