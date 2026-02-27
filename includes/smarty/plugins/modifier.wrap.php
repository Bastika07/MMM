<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     wrap
 * Purpose:  wrap a string of text at a given length, preserve HTML-Tags
 * -------------------------------------------------------------
 */
function smarty_modifier_wrap($string, $wrap_at, $char = '<br>', $forced = false) {
  if ($forced) {
    $string = wordwrap($string, $wrap_at, $char, 1);
  } else {
    $string = preg_replace('%(\s*)([^>]{'.$wrap_at.',})(<|$)%e',
                           "'\\1'.wordwrap('\\2', '".$wrap_at."', '$char', 1).'\\3'", $string);
  }
  return $string;
}
?>
