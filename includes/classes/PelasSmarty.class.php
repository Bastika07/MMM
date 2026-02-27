<?php
/* Smarty-Anpassungen für PELAS. */

require_once('constants.php');
require_once(SMARTY_CLASS);


class PelasSmarty extends SmartyBC {

    var $appName;
    var $templateSubDir;
    var $templateBaseDir;
    var $fallbackTemplateDir;

    function PelasSmarty($appName='default') {
	global $sLang, $str;
	# Wird in hostconfig.php gesetzt.
	#$this->Smarty();
	parent::__construct();

	$this->cache_dir = SMARTY_BASE_DIR . 'cache/';
	$this->config_dir = SMARTY_BASE_DIR . 'configs/';
	$this->plugins_dir[] = SMARTY_BASE_DIR . 'plugins/'; 
	$this->compile_dir = SMARTY_BASE_DIR . 'templates_c/';

	$this->templateBaseDir = SMARTY_BASE_DIR . 'templates/';

	if (defined('MANDANTID')) {
	    $this->templateSubDir = MANDANTID;
	} else {
	    $this->templateSubDir = 'default';
	}

	$this->appName = $appName;

	$this->assembleTemplateDir();
	$this->assembleFallbackTemplateDir();
	$this->assembleCompileId();

	$this->caching = false;
	$this->cache_lifetime = 0;
	$this->use_sub_dirs = true;
	$this->debugging = false;
		
	$this->assign('pelasHost', PELASHOST);
	$this->assign('pelasDir', PELASDIR);

	$this->assign('filename', $_SERVER['PHP_SELF']);
	
	$this->assignByRef('appName', $this->appName);
	$this->assignByRef('templateDir', $this->template_dir);
	$this->assignByRef('fallbackTemplateDir', $this->fallbackTemplateDir);
	$this->assignByRef('compileId', $this->compile_id);
		
	# I18N
	$this->assign('lang', $sLang);
	$this->assignByRef('str', $str);
    }

    function displayWithFallback($template, $cacheId=null, $compileId=null) {
	# Sicherstellen, dass Pfade etc. gesetzt sind.
	$this->assembleTemplateDir();
	$this->assembleFallbackTemplateDir();
	$this->assembleCompileId();

	if ($this->templateExists($template)) {
	    $this->assign('fallback', false);
	} else {
	    $this->assign('fallback', true);
	    $this->template_dir = $this->fallbackTemplateDir;
	}
	$this->display($template, $cacheId, $compileId);
    }

    function isCachedWithFallback($template, $cacheId=null, $compileId=null) {
	# Sicherstellen, dass Pfade etc. gesetzt sind.
	$this->assembleTemplateDir();
	$this->assembleFallbackTemplateDir();
	$this->assembleCompileId();

	if ($this->templateExists($template)) {
	    $this->assign('fallback', false);
	} else {
	    $this->assign('fallback', true);
	    $this->template_dir = $this->fallbackTemplateDir;
	}
	return $this->is_cached($template, $cacheId, $compileId);
    }

    function assembleCompileId() {
	$this->compile_id = sprintf('%s|%s', $this->templateSubDir, $this->appName);
    }

    function assembleTemplateDir() {
	$this->template_dir = sprintf('%s%s/%s', $this->templateBaseDir, $this->templateSubDir, $this->appName);
    }

    function assembleFallbackTemplateDir() {
	$this->fallbackTemplateDir = sprintf('%sdefault/%s', $this->templateBaseDir, $this->appName);
    }
}
?>
