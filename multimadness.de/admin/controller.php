<?php
if ($_SERVER['SERVER_ADDR'] == "10.10.250.201") {
	# Intranet!
	set_include_path('/var/www/includes/');
} else {
	# Dieses Biest ist nur dazu da, einen Include-Pfad für 0815 Webhoster zu setzen
	set_include_path('/var/www/vhosts/hosting103794.af995.netcup.net/includes/');
}
?>