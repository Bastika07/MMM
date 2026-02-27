<div class="right">

<?php
$path = './pelas/bilder_upload/verpflegung/';
$alledateien = scandir($path); //Ordner "files" auslesen
$counter = 1;
	
	foreach ($alledateien as $datei) { // Ausgabeschleife
	   //echo $datei."<br />"; //Ausgabe Einzeldatei
	   if($datei<>"." and $datei <> ".."){
			echo  '<a href="'.$path.$datei.'" data-lightbox="mmm-lokation"><img class="sidebar" src="'.$path.$datei.'" width="200" alt="MultiMadness Verpflegung" /></a><br/><br />';
	   }

	};
 
?>

  	<?php include "multimadness/sidebar_sponsoren.php"; ?>

</div>
  <h1>Verpflegung</h1><br />
<div class="left">
<p align="justify">
Am MultiMadness-Kiosk könnt Ihr gekühlte Getränke und Bockwurst erwerben.</p>
<p align="justify">
Auch in diesem Jahr wird es wieder eine <b>Cocktailbar</b> geben, in der wir euch frische Cocktails mixen.</p>
<p align="justify">
Neu ist die Möglichkeit mit EC, Kreditkarte, Smartphone zu bezahlen.</p>
<!-- <p align="justify">
Samstagabend kommt zusätzlich das Team von <b>F*Burger</b> mit ihrem Food-Truck und brät für euch leckere MMM-Burger!</p>-->
<p align="justify">
Daneben stellen wir für euch den <b>Holzkohle-Kultgrill</b> bereit, auf dem ihr euer selbst mitgebrachtes Fleisch grillen könnt. Kohle stellen wir in begrenzten Umfang. </p>
<p align="justify">
</p>
<br />
<h2>Angebot rund um die Uhr</h2>

<table>
	<col width="325px" />
    <col width="50px" />
  <tr>
    <td><i>Wiener Würstchen + Toast</i></td>
    <td>2,50€</td>
  </tr>
  <tr>
    <td><i>2x Wiener Würstchen + Toast</i></td>
    <td>4,00€</td>
  </tr>
   <tr>
    <td><i>halbes belegtes Brötchen (Samstag- und Sonntagmorgen, solange der Vorrat reicht)</i></td>
    <td>1,00€</td>
  </tr>
  <tr>
    <td><i>Kartoffelsalat </i></td>
    <td>2,00€</td>
  </tr>
<tr>
    <td><i>Schokoriegel </i></td>
    <td>1,00€</td>
  </tr>
  <tr>
    <td><i>Astra Kiezmische 0,33l </i></td>
    <td>1,50€</td>
  </tr>
  <tr>
    <td><i>Astra Urtyp 0,33l </i></td>
    <td>1,50€</td>
  </tr>
  <tr>
    <td><i>Cola, Fanta, Sprite 0,5l </i></td>
    <td>2,00€</td>
  </tr>
  <tr>
    <td><i>Mineralwasser 1,0l </i></td>
    <td>1,00€</td>
  </tr>
  <tr>
    <td><i>Becher Kaffee </i></td>
    <td>1,00€</td>
  </tr>
  <tr>
    <td><i>Red Bull 0,25l </i></td>
    <td>2,50€</td>
  </tr>
  <tr>
    <td><i>Pfand, Flasche </i></td>
    <td>0,50€</td>
  </tr>
  <tr>
    <td><i>Pfand, Dose </i></td>
    <td>0,50€</td>
  </tr>
  <tr>
    <td><i>Pfand, Becher </i></td>
    <td>2,00€</td>
  </tr>
</table>
<br />

<h2>Cocktailbar</h2>
<p>In der Cocktailbar gibt es für euch rund um die Uhr frische Cocktails und Kurze.</p>
<p>
<table>
		<col width="325px" />
    <col width="50px" />
  <tr>
    <td><i>Sex on the Beach</i></td>
    <td>7,00€</td>
  </tr>
  <tr>
    <td><i>Duty Queen</i></td>
    <td>7,00€</td>
  </tr>
  <tr>
    <td><i>Long Island Iced Tea</i></td>
    <td>8,00€</td>
  </tr>
	<tr>
    <td><i>Tequila Sunrise</i></td>
    <td>7,00€</td>
  </tr>
  <tr>
    <td><i>Pina Colada</i></td>
    <td>7,00€</td>
  </tr>
  <tr>
    <td><i>Kyiv Mule</i></td>
    <td>6,00€</td>
  </tr>
  <tr>
    <td><i>Dark Windy</i></td>
    <td>6,00€</td>
  </tr>
  <tr>
    <td><i>Cuba Libre</i></td>
    <td>6,00€</td>
  </tr>
  <tr>
    <td><i>San Francisco (alkoholfrei)</i></td>
    <td>4,00€</td>
  </tr>
  <tr>
    <td><i>Virgin Colada (alkoholfrei)</i></td>
    <td>4,00€</td>
  </tr>
  <tr>
    <td><i>Berliner Luft 2cl</i></td>
    <td>1,00€</td>
  </tr>
</table>
</p>
<br />

<!-- <h2>F*Burger</h2>

<p>Unser Food-Truck wird Samstagabend in der Zeit von 18:00 Uhr bis 0:00 Uhr für euch die leckeren Burger zubereiten.</p>
<p><a class="arrow" href="https://www.facebook.com/burger.hamburg">Facebook-Page von F*Burger</a></p>
<br />
--> 
<h2>Fressbuden in der Umgebung</h2>

<p>Schaut auf unserer Umgebungskarte nach unserer reichhaltigen Auswahl an Fressbuden in der Umgebung.</p>
<p><a class="arrow" href="?page=46">Zur Umgebungskarte</a></p>

</div>
<div class="clear"></div>