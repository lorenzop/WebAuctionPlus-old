<?php namespace wa;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}
	else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class module_wa extends \psm\Portal\Module {

	// WebAuctionPlus
	const module_name = 'wa';
	const module_title = 'WebAuctionPlus';
	const module_title_html = 'WebAuction<sup>Plus</sup>';
	const version = '3.0.3';


	public function __construct() {
		parent::__construct();

//$pass = new \psm\PassCrypt();
//echo $pass->hash('pass');
//exit();

// load config.php
//$config = \psm\config::loadConfig('config.php');

		// load database config
		\psm\DB\DB::addDB(
			'wa main',
			\psm\Paths::getLocal('root').DIR_SEP.'config.php'
		);

//$db = \psm\DB\DB::getDB('main');
//$user = waUser::getUserSession($db);
//echo '<br /><br /><br /><pre>'.print_r($user, TRUE).'</pre>';

	}


	public function Init() {
		$this->_LoadPage();
		$this->engine->Display();
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


}
?>