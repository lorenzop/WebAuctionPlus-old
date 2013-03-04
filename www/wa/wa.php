<?php namespace wa;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';} else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class module_wa extends \psm\Portal\Module {

	// WebAuctionPlus
	const module_name = 'wa';
	const version = '3.0.0';


	public function __construct() {
		parent::__construct();

//$pass = new \psm\PassCrypt();
//echo $pass->hash('pass');
//exit();

		$portal = \psm\Portal::getPortal();
		\psm\Portal\Page::addPath(__DIR__.DIR_SEP.'pages');

// load config.php
//$config = \psm\config::loadConfig('config.php');
//
//\psm\DB\DB::addDB('main', \psm\PATH_ROOT.DIR_SEP.'config.php');
//$db = \psm\DB\DB::getDB('main');
//
//$user = waUser::getUserSession($db);
//echo '<br /><br /><br /><pre>'.print_r($user, TRUE).'</pre>';
//
//\psm\page::LoadPage($portal->getPage());

		$portal->genericRender();

//WA3_Mailbox
//WA3_Players
//WA3_Selling
//WA3_Settings
//WA3_
//WA3_

	}




	public function getName() {
		return self::module_name;
	}

	public function getVersion() {
		return self::version;
	}


}
?>