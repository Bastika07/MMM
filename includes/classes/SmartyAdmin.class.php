<?php
/* Für den Administrationsbereich angepasste Smarty-Klasse. */

require_once('classes/PelasSmarty.class.php');


define('MANDANTID', 'admin');


class SmartyAdmin extends PelasSmarty {

    var $baseTemplateDir;

    function SmartyAdmin() {
	$this->PelasSmarty('');
	$this->assembleTemplateDir();
	$this->assembleFallbackTemplateDir();
	$this->assembleCompileId();
    }
}
?>
