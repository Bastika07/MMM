<?php
if ($_SERVER['SERVER_ADDR'] == "10.10.250.201") {
	# Intranet!
	if ($_SERVER['HTTPS'])
		$base = "https://www.lan.multimadness.de/";
	else
		$base = "http://www.lan.multimadness.de/";
			
} else {
	if ($_SERVER['HTTPS'])
		$base = 'https://'.$_SERVER['HTTP_HOST'].'/'; # Frontend Base
	else
		$base = 'http://'.$_SERVER['HTTP_HOST'].'/'; # Frontend Base
}
	
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Team MultiMadness Forever - Adminbereich</title>
	<base href="<?= $base; ?>admin/">
  <link rel="stylesheet" type="text/css" href="style/style.css">
	<link rel="stylesheet" type="text/css" href="style/datepicker.css" />

	<script type="text/javascript" src="overlib/overlib.js"></script>
	<script type="text/javascript" src="js/datepicker.js"></script>
</head>

<body>

<?php
	if (isset($menu_deactivate) && $menu_deactivate == true) {
		# Menu nicht anzeigen! ?>
		<div style="margin:10px;">
<?php	} else { ?>
	<div id="menu">
  	<?php require "admin/navigation.php"; ?>
	</div>
  
  <div id="content"> <!-- Content Anfang -->
<?php } 

#
# Warnmeldung für Arbeiten im Internet, wenn Party aktiv
# AKTUELL DEAKTIVIERT, DA PARTY IM INTERNET VERWALTET WIRD
#
if (LOCATION == "internet" && 1 == 2) {
	$sql = "select
					count(*) as anzahl,
					cast(p.beschreibung AS CHAR) as beschreibung,
					p.terminVon,
					p.terminBis,
					p.teilnehmer,
					p.partyId
			from 
					party p
			where 
				DATE(terminVon) <= CURDATE()
				AND DATE(terminBis) >= CURDATE()";
				
	$res = @mysql_query($sql);
	if ($res)
		if ($row = mysql_fetch_assoc($res))
			if ($row['anzahl'] > 0) {
				# Warnmeldung ausgeben
				?>
					<table width="100%" border="0" cellspacing="0" cellpadding="14" bgcolor="red">
					<tr>
						<td align="center" style="color:#000000; font-size: 17px;"><b>ACHTUNG:</b><br>
						Du bist dabei im Internet-Admin zu arbeiten! <br>
						Hier d&uuml;rfen keine Teilnehmer / G&auml;ste eingecheckt werden!<br>
						Bitte benutze das Intranet: <a href="https://www.lan.multimadness.de/admin/" style="color: #000000;">https://www.lan.multimadness.de/admin/</a>.</td>
					</tr>
					</table>
				<?php
			
			}
}

?>