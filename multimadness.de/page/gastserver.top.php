<?php
# Only show ID-Card and then exit
if (isset($_GET['iAction']) && $_GET['iAction'] == "ServerIDCard") {
	include "pelasfront/gastserver_idcard.php";
	exit;
} ?>