<div class="right">
	<div class="box">
    <?php include "pelasfront/accounting_loginfield.php"; ?>
  </div>
  
  	<?php include "multimadness/sidebar_sponsoren.php"; ?>
  
</div>
<div class="left">

  <h1>Sponsoren und Partner</h1>
  
	<p></p>
	<p></p>
<?php
include_once "dblib.php";
// sponsoren sichtbar?
$sql = "select 
	  STRINGWERT 
	from 
	  CONFIG 
	where 
	  PARAMETER = 'SPONSOREN_AKTIV' AND
	  MANDANTID = $nPartyID";
$result = DB::query($sql);
$row = $result->fetch_assoc();
// checken, ob get-variable on
if ($row['STRINGWERT'] == "J") {
	
	$sql = "select 
						s.Name, 
						s.Website, 
						s.Logo, 
						s.Text,
						s.MandantID
					from 
						sponsoren s
					where 
						s.MandantID = $nPartyID
					order by s.sortierung";
	$rows = DB::getRows($sql);
	foreach ($rows as $key => $row) {
		echo '<h1>'.$row['Name'].'</h1>';
		echo '<img src="'.$row['Logo'].'" style="width:300px; margin:0px 25px 50px 0px; float:left;" />';
		echo $row['Text'];
		echo '<p><a class="arrow" href="'.$row['Website'].'" target="_blank" rel="noopener">'.$row['Name'].'</a></p>';
		echo '<br /><br /><br />';
	}
	
} else {
	 echo '<h4>Derzeit sind keine Sponsoren gelistet!</h4>';
}
?>

</div>
<div class="clear"></div>