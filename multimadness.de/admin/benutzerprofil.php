<?php
/* Benutzerprofil ansehen */
require('controller.php');
require_once('dblib.php');
$iRecht = array('TEAMMEMBER');
require('checkrights.php');
include('format.php');
include('admin/vorspann.php');

 

if ( $_GET["userid"] > 0) 
	{
		$profil = $_GET["userid"];
	} 
else 
	{
		$profil = $loginID;
	}

$result = DB::query("select u.*, s.sizeCode, s.sizeDesc from USER u, acc_tshirt s where u.USERID = '".intval($profil)."' AND u.SHIRTSIZE = s.sizeCode");
$row = $result->fetch_array();

$sql = "select e.*,
			t.*
		from USER_EXT e,
			user_ext_team t
		where e.USERID = '".intval($profil)."'
			and t.id = e.TEILTEAMID
";
$result1 = DB::query($sql);
$row1 = $result1->fetch_array();

?>

<h1>Benutzerprofil von: <?= db2display($row['LOGIN']); ?></h1>

<table cellspacing="0" cellpadding="0" border="0" width="500">
	<tr>
		<td class="navbar">
			<table width="100%" cellspacing="1" cellpadding="3" border="0">
				<tr><td class="navbar" colspan="2"><b>Benutzerdaten</b></td></tr>
				<tr><td class="dblau" rowspan="18" valign="top" align="center"> <?= displayUserPic($row['USERID']); ?></td>
					<td class="dblau">Login:</td><td class="hblau"><?= db2display($row['LOGIN']); ?></td>
				</tr>
				<tr><td class="dblau">Teilteam:</td><td class="hblau"><?= db2display($row1['description']); ?></td></tr>
				<tr><td class="dblau">Aufgabe:</td><td class="hblau"><?= db2display($row1['AUFGABE']); ?></td></tr>
				<tr><td class="dblau" colspan="2"><b>Persönliche Daten</b></td></tr>
				<tr><td class="dblau">Vorname:</td><td class="hblau"><?= db2display($row['NAME']); ?></td></tr>
				<tr><td class="dblau">Nachname:</td><td class="hblau"><?= db2display($row['NACHNAME']); ?></td></tr>
				<tr><td class="dblau">Geburtstag:</td><td class="hblau"><?= dateDisplay2Short($row1['GEBURTSTAG']);?></td></tr>
				<tr><td class="dblau">Strasse:</td><td class="hblau"><?= db2display($row['STRASSE']); ?></td></tr>
				<tr><td class="dblau">PLZ, Ort:</td><td class="hblau"><?= db2display($row['PLZ']);?>, <?= db2display($row['ORT']); ?></td></tr>
				<tr><td class="dblau">Telefon:</td><td class="hblau"><?= db2display($row1['TELEFON']); ?></td></tr>
				<tr><td class="dblau">Mobil:</td><td class="hblau"><?= db2display($row1['MOBIL']); ?></td></tr>
				<tr><td class="dblau">Email-Adresse:</td><td class="hblau"><?= db2display($row['EMAIL']); ?></td></tr>
				<tr><td class="dblau">Skype:</td><td class="hblau"><?= db2display($row['SKYPE']); ?></td></tr>
				<tr><td class="dblau">URL (mit http://):</td><td class="hblau"><?= db2display($row['HOMEPAGE']); ?></td></tr>
				<tr><td class="dblau">T-Shirt GröÃŸe:</td><td class="hblau"><?= db2display($row['sizeDesc']); ?></td></tr>
				<tr>
					<td class="dblau">Status:</td><td class="hblau">
						<? 	
							if ($row1['INAKTIV'] == '1')
								{
									echo 'inaktiv';
								}
							else 
								{
									echo 'aktiv';
								}
						?> 
					</td>
				</tr>
				<tr><td class="dblau"></td>
					<td class="dblau">
						<a href="benutzerdetails.php?id=
							<?= db2display($row['USERID']); 
							?>">Profil ändern
						</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<?
	
include('nachspann.php');
?>
		