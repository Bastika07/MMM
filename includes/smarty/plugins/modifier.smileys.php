<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     wordwrap
 * Purpose:  wrap a string of text at a given length
 * -------------------------------------------------------------
 */
function smarty_modifier_smileys($str, $path = '')
{
  $smileys = array(":)" => "sm1.gif",
                           ";)" => "sm2.gif", 
                           ":D" => "sm3.gif", 
                           ":(" => "sm4.gif",
                           ":-X" => "sm5.gif", 
                           ":-zzz" => "sm6.gif",
	                         ":-rtfm" => "sm7.gif",
	                         "%-)" => "sm8.gif",
	                         ":-xxx" => "sm9.gif",
	                         ":-no" => "sm10.gif", 
	                         ":-yes" => "sm11.gif", 
	                         ":-ah" => "sm12.gif",
	                         ":-nixpeil" => "sm13.gif", 
	                         ":-grr" => "sm14.gif", 
	                         ":-zap" => "sm15.gif",
	                         ":-hae" => "sm16.gif", 
	                         ":rolleyes:" => "sm17.gif", 
	                         ":P" => "sm18.gif",
	                         ":gay:" => "sm19.gif", 
	                         ":ohh:" => "sm20.gif", 
	                         ":gaga:" => "sm21.gif",
	                         ":kicker:" => "sm21.png",
	                         ":kotz:" => "sm22.gif", 
	                         ":lach:" => "sm23.gif", 
	                         ":stink:" => "sm24.gif");
  foreach ($smileys as $key => $val) {
    $str = str_replace($key, "<img src=\"$path$val\" border=\"0\"> ", $str);
  }
	return $str;
}


?>
