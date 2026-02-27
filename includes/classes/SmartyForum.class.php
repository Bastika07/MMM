<?php
/* Für das Forum angepasste Smarty-Klasse. */

class SmartyForum extends PelasSmarty {

    var $baseTemplateDir;

    function SmartyForum() {
	$this->PelasSmarty();
	$this->appName = 'forum';

	$this->setTemplateDir();
	$this->setFallbackTemplateDir();
	$this->setCompileId();
    }
}
?>
