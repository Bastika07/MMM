<div class="right">

<?php
$path = './pelas/bilder_upload/location/';
$alledateien = scandir($path); //Ordner "files" auslesen
$counter = 1;
	
	foreach ($alledateien as $datei) { // Ausgabeschleife
	   //echo $datei."<br />"; //Ausgabe Einzeldatei
	   if($datei<>"." and $datei <> ".."){
			echo  '<a href="'.$path.$datei.'" data-lightbox="mmm-lokation"><img class="sidebar" src="'.$path.$datei.'" width="200" alt="MultiMadness Lokation" /></a><br/><br />';
	   }

	};
 
?>

  	<?php include "multimadness/sidebar_sponsoren.php"; ?>
  
</div>
<div class="left">
  <h1>Lokation</h1>
  <p align="justify">
  Die MultiMadness findet in der <b>Sch&uuml;tzenhalle Maschen</b> (Maschener Sch&uuml;tzenstrasse 50, 21220 Seevetal-Maschen) statt. Hier ist
  Platz f&uuml;r <b>200 Spieler</b> plus Team.</p>
  <br>
  <h1>Rahmenprogramm</h1>
  <p align="justify">In der Halle ist ein Kicker vorhanden und vor der Halle stellen wir euch einen stattlichen Grill inkl. Anz&uuml;nder und Kohle in begrenztem Umfang.</p>
  <br>
  <h1>Parken</h1>
  <p align="justify">
  Es befindet sich ein Parkplatz f&uuml;r alle PKWs direkt vor der Halle. Bitte parkt nicht in den markierten Zonen.
  Die markierten Zonen halten den Weg in den hinteren Parkplatzbereich bzw. zum Eingang frei.
  </p>
  <br>
  <h1>Schlafen</h1>
  <p align="justify">
 Es gibt einen großen, von den Spielern abgetrennten Schlafraum. Achtung: Bringt euch eine Isomatte oder Feldliege mit - beides maximal 1m breit pro Person. 
  </p>
  
  <br>
  <h1>Wegbeschreibung</h1>
  <p align="justify">Zum Autobahnkreuz Maschen kommt Ihr bundesweit &uuml;ber die A1 und A7, von L&uuml;neburg aus auf
  der A39. Die Autobahnabfahrt Maschen ist von da an ausgeschildert.</p>
  <p align="justify" style="text-align:center">
	<a target="_blank" href="https://www.google.de/maps/dir//Maschener+Sch%C3%BCtzenstra%C3%9Fe+50,+21220+Seevetal/@53.39467,10.0521813,17z/data=!4m8!4m7!1m0!1m5!1m1!1s0x47b1947bfda873d1:0xcbebc4a3ccab205c!2m2!1d10.05437!2d53.39467"><img class="sidebar" src="gfx/GoogleMaps.jpg" alt="Die Schützenhalle Maschen" /></a>
	&nbsp;&nbsp;&nbsp;&nbsp;
		<a target="_blank" href="https://www.bing.com/maps?osid=7b0bfbf4-65d2-4945-85c7-84e1167dbeb0&cp=53.394738~10.05447&lvl=16&v=2&sV=2&form=S00027"><img class="sidebar" src="gfx/BingMaps.png" alt="Die Schützenhalle Maschen" /></a>
  </p>
</div>
<div class="clear"></div>