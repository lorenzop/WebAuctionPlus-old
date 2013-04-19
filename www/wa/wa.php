<?php namespace wa;
use \psm\Portal as Portal;
global $ClassCount; $ClassCount++;
class module_wa extends Portal\Module {

	// WebAuctionPlus
	const module_name = 'wa';
	const module_title = 'WebAuctionPlus';
	const module_title_html = 'WebAuction<sup>Plus</sup>';
	const version = '3.0.5';


//	public function __construct() {
//		parent::__construct();
//$pass = new \psm\PassCrypt();
//echo $pass->hash('pass');
//exit();
// load config.php
//$config = \psm\config::loadConfig('config.php');
// load database config
//\psm\pxdb\dbPool::LoadConfig();
//$db = \psm\pxdb\dbPool::getDB('main');
//$user = waUser::getUserSession($db);
//echo '<br /><br /><br /><pre>'.print_r($user, TRUE).'</pre>';
//	}


	public function Init() {
		Portal::LoadPage();
		Portal::LoadAction();
		$engine = Portal::getEngine();
		$engine->setSiteTitle('WebAuctionPlus');
		$engine->setPageTitle('Home');
		$engine->Display();
	}


	// get module name
	public function getModName() {
		return self::module_name;
	}
	public static function getModuleName() {
		return self::module_name;
	}
	// get version
	public function getModVersion() {
		return self::version;
	}
	public static function getVersion() {
		return self::version;
	}


	public function getModTitle() {
		return self::module_title;
	}
	public function getModTitleHtml() {
		return self::module_title_html;
	}


}
?>