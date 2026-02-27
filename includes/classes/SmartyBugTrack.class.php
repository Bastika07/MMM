<?php
require_once 'classes/PelasSmarty.class.php';

class SmartyBugTrack extends PelasSmarty{
	
	var $baseTemplateDir;
	function SmartyBugTrack(){
		$this->PelasSmarty();
		
		$this->mandantId = 'admin';
		$this->appName = 'bugtracking';

		$this->assembleTemplateDir();
		$this->assembleFallbackTemplateDir();
		$this->assembleCompileId();
	}
}
?>
