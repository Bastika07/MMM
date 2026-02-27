<div class="right">
	<div class="box">
    <?php include "pelasfront/accounting_loginfield.php"; ?>
  </div>
  
  	<?php include "multimadness/sidebar_sponsoren.php"; ?>
  
</div>
<div class="left">
<h1>Umfrage</h1>
<?php
ob_start();
include "pelasfront/vote.php";
ob_flush();
?>
</div>
<div class="clear"></div>