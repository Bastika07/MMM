<div class="right">
	<div class="box">
    <?php include "pelasfront/accounting_loginfield.php"; ?>
  </div>
  
  	<?php include "multimadness/sidebar_sponsoren.php"; ?>
  
</div>
<div class="left">
<h1>Team Madness</h1>
<p>Hinter dem Team Madness verbergen sich alle aktiven Mitglieder, sowie Ehrenmitglieder des Vereins Madness 4 ever e.V.<br>Neben Originalen, wie Mad und Krisko, die Euch schon die allerersten MultiMadness-Partys präsentiert haben, könnt Ihr auch neuere Gesichter entdecken. <br>
Von der ersten öffentlichen Party im Dörphus Hörsten bis zu den rasant wachsenden Events im legendären Schützenhaus Maschen. Diese Geschichte soll fortgeschrieben werden!<br>
Wir haben keine Kosten und Mühen gescheut, um aus den ganz "alten Hasen" von damals und einer jungen Truppe ein neues Team zu formen: Team Madness wird euch das Feeling präsentieren, das die ersten, letzten und künftigen MultiMadnesses ausgemacht hat und ausmachen wird.</p>
<?php
ob_start();
include "pelasfront/teamliste.php";
ob_flush();
?>
</div>
<div class="clear"></div>