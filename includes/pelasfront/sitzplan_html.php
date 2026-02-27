<?php
Header ("Pragma: no-cache");
header ("Cache-Control: no-cache, must-revalidate");
readfile ("sitzbild/sitzplan_html_".$nPartyID."_".$ebene.".txt");
?>