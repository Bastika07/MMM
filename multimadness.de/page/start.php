<div class="right">
	<div class="box">
    <?php include "pelasfront/accounting_loginfield.php"; ?>
  </div>
	<div class="box">
    <?php include "pelasfront/online.php"; ?>
  </div>

  	<?php 
			$SHOW_SMALL_BTN = TRUE;
			include "pelasfront/vote_choices.php"; 
		?>
  
</div>
<div class="left">
<ul class="bxslider_news">
		<?php
		$newsBoard = 25;
		$newsLimiter = 4; # Auf 1 Newseintrag limiteren
		include "forum_news.php";
		?>
</ul>

<i class="fa fa-newspaper-o"></i><a href="?page=2"> Alle News anzeigen</a>

</div>
<div class="clear"></div>
<br />
<?php
$path = './pelas/bilder_upload/slider/';
$alledateien = scandir($path); //Ordner "files" auslesen
$counter = 1;
 
	echo '<ul class="bxslider_fotoslider">';
	
	foreach ($alledateien as $datei) { // Ausgabeschleife
	   //echo $datei."<br />"; //Ausgabe Einzeldatei
	   if($datei<>"." and $datei <> ".."){
			if ($counter == 1){
				echo '<li>';
			}
			echo '<a href="'.$path.$datei.'" data-lightbox="mmm-fotoslider"><img class="fotoslider" src="'.$path.$datei.'"></a>';
			if ($counter == 3){
				echo '</li>';
				$counter = 1;
			} else {
				$counter++;
			}
	   }

	};
	echo '</ul>';
 
?>

<?php
include_once "dblib.php";
//Reservierung offen?
$sql = "select 
	  STRINGWERT 
	from 
	  CONFIG 
	where 
	  PARAMETER = 'SPONSOREN_AKTIV' AND
	  MANDANTID = $nPartyID";
$result = DB::query($sql);
$row = mysql_fetch_assoc($result);
// checken, ob get-variable on
if ($row['STRINGWERT'] == "J") {
		$sql = "select 
					s.Website, 
					s.Logo_Slider
				from 
					sponsoren s
				where 
					s.MandantID = $nPartyID
				order by rand()";
	$rows = DB::getRows($sql);
	echo '<div style="text-align:center; vertical-align:center; padding:30px;">';
	foreach ($rows as $key => $row) {
		echo '<a target="_blank" href="'.$row['Website'].'" target="_blank" rel="noopener"><img src="'.$row['Logo_Slider'].'" style="max-width:150px; max-height:150px; margin:0px 20px 0px 0px"></a>';
	}
	echo '</div>';
}
?>
