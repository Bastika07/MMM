<?
require_once("dblib.php");
require("session.php");
$fd[r] = fopen($_POST[url], "r");
   $contents = fread($fd[r], 102400);
fclose ($fd[r]);

$fd[w] = fopen($DOCUMENT_ROOT."/userbild/".$nLoginID.".".$_POST[ext], "w");
   fwrite($fd[w], $contents);
fclose($fd[w]);
?>
