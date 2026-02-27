<?php

require_once 'classes/PelasSmarty.class.php';

class SmartyAdmin extends PelasSmarty {
	
	var $baseTemplateDir;
	var $baseCompileDir;

  function SmartyAdmin() {

    $this->PelasSmarty();
    
    $this->baseTemplateDir = SMARTY_BASE_DIR.'/templates/%s/';
    $this->baseCompileDir  = SMARTY_BASE_DIR.'/templates_c/%s/';
    
    $this->template_dir = sprintf($this->baseTemplateDir, 'admin');
    $this->fallbackTemplateDir = $this->template_dir;
    
    $this->compile_dir = sprintf($this->baseCompileDir, 'admin');
    $this->fallbackCompileDir = $this->compile_dir;

		$this->caching = false;
		$this->debugging = false;
		$this->assign('app_name','PELASAdmin');		
   }
}
?>