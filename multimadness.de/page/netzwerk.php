<div class="right">

<?php
$path = './pelas/bilder_upload/netzwerk/';
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
<div class="left">
<h1>Netzwerk Client-Setup</h1>
<p>
  <table border=0>
  <tr>
    <td>IP-Einstellungen:</td>
    <td>DHCP</td>
  </tr>
  <tr>
    <td>Intranet:</td>
    <td>www.multimadness.de</td>
</table>
</p>
<br>

<h1>Internet</h1>
<p align="justify">
Im Hintergrund arbeiten zwei Internetleitungen. Für euch sind alle Ports freigegeben, ihr könnt also alle Internetdienste nutzen. Da sich alle Teilnehmer die Bandbreite teilen müssen bitten wir aber darum, dass ihr keine bandbreitenintensive Internet-Dienste wie Downloads, Streaming und ähnliches nutzt bzw. nur dann, wenn es unbedingt notwendig ist. Ladet eure Updates, Programme etc. bitte Zuhause herunter, dann wird die Internet-Performance für alle akzeptabel sein.
</p>
<br>

<h1>Online zocken</h1>
<p align="justify">
Die gute Nachricht zuerst: Alle internetbasierten Spiele sind für euch freigeschaltet. Um die Pings und damit die Spielbarkeit im akzeptablen Rahmen zu halten, schicken wir diesen Datenverkehr über eine separate Leitung ins Internet. Da einige Turnierspiele über das Internet laufen müssen, spielt die aufwendigen Spiele bitte nach Möglichkeit im LAN.
</p>
<br />

<h1>Client-Switches</h1>
<p align="justify">
Für jeden Gast steht am Client-Switch ein 1 Gbit-Port bereit. Die Switche stehen am Ende der Reihen, ein ausreichend langes Netzwerkkabel ist also erforderlich (10m).
</p>
<br />

<h1>Eure Server</h1>
<p align="justify">
Für eure Server gibt es einen Gastserverbereich. Dort schließt ihr euren Server über 1Gbit direkt an den Gigabit-Backbone an. Ihr könnt <b>auf Anfrage ggf. einen 10 Gigabit Port erhalten.</b> Schickt uns bei Interesse bitte rechtzeitig (2 Monate vor der LAN) eine E-Mail, dann klären wir, ob es im spezifischen Fall möglich ist. Über die Gastserveranmeldung registriert ihr euren Server kostenlos für das Netzwerk und erhaltet spezielle IP-Adressen sowie DNS-Namen.
</p>
<p><a href="?page=31" class="arrow">Zur Gastserveranmeldung</a></p>
<br />

<h1>Intranet</h1>
<p align="justify">
Unser Intranet läuft über die normale Website www.multimadness.de. Hier findet die Turnierverwaltung etc. statt. Aufgrund des öffentlichen Forums ist auch während der LAN auf die Umgangsformen und Einhaltung aller Gesetze zu achten. 
</p>
</div>

<div class="clear"></div>