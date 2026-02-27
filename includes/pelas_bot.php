<?
include "t0xirc.php";

class pelas_bot extends t0xirc_bot {
	function pelas_bot($login, $pass) {
		parent::t0xirc_bot($login, $pass);
	}
}

function Send2Bot($sIRC_Channel, $sText) {
	//escapen, momentan überflüssig
	//$sIRC_Channel = ereg_replace("'","`",$sIRC_Channel);
	//$sText = ereg_replace("'","`",$sText);

	//#######################################
	//# Achtung: Senden an Bot momentan deaktiviert
	
	/*$mybot =& new pelas_bot("pelas", "stusskopf");
	$mybot->connect() or die("Unable to connect\n");
	$mybot->pubact("- ".$sText, $sIRC_Channel);
	$mybot->disconnect();*/
}
?>
