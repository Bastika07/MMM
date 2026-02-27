<?php
include_once "dblib.php";
include_once "session.php";
include_once "format.php";
include_once "language.inc.php";

echo "<a name=\"checkpoint\"><h1>$str[abgeben]</h1>\n";

if ($Parent < 1) {
	$Parent = $nInhaltID;
}

InhaltAnlegen($KATEGORIE_NEWSCOMMENT, $Parent);

?>