<?php namespace wa;
if(!defined('PORTAL_INDEX_FILE') || \PORTAL_INDEX_FILE!==TRUE){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// web auction plus
define('wa\WA_VERSION', '3.0.0');



//$pass = new \psm\PassCrypt();
//echo $pass->hash('pass');
//exit();



\psm\ClassLoader::registerClassPath('wa', __DIR__.DIR_SEP.'classes');

//\psm\Page::addPath(__DIR__.'/pages');





\psm\DB::addDB('main', \psm\PATH_ROOT.DIR_SEP.'config.php');
$db = \psm\DB::getDB('main');






// load config.php
$config = \psm\config::loadConfig('config.php');






$user = waUser::getUserSession($db);
echo '<br /><br /><br /><pre>'.print_r($user, TRUE).'</pre>';


$portal = \psm\portal::getPortal();
$portal->genericRender();

//\psm\page::LoadPage($portal->getPage());



//WA3_Mailbox
//WA3_Players
//WA3_Selling
//WA3_Settings
//WA3_
//WA3_


?>