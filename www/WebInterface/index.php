<?php
error_reporting(E_ALL | E_STRICT);
define('DEFINE_INDEX_FILE',TRUE);

// get,post,cookie (highest priority last)
function getVar($name,$type='',$order=array('get','post')){$output='';
  if(!is_array($order)){$order=@explode(',',$order);}
  if(@count($order)==0 || $order=='')
    if($order!='') $order = array($order);
    else           $order = array('get','post');
  # get vars
  foreach($order as $v){
    if(    $v=='get'    && isset($_GET[$name])   ) $output=@$_GET[$name];
    elseif($v=='post'   && isset($_POST[$name])  ) $output=@$_POST[$name];
    elseif($v=='cookie' && isset($_COOKIE[$name])) $output=@$_COOKIE[$name];
  }
  // convert type if set
  if(    $type=='str'   || $type=='string' ) return( (string)  $output  );
  elseif($type=='int'   || $type=='integer') return( (integer) $output  );
  elseif($type=='float' || $type=='double' ) return( (float)   $output  );
  elseif($type=='bool'  || $type=='boolean') return( toBoolean($output) );
  return($output);
}
function toBoolean($value){
  $tempValue = strtolower($value);
  if($tempValue=='t' || $tempValue=='true' ) return(TRUE);
  if($tempValue=='y' || $tempValue=='yes'  ) return(TRUE);
  if($tempValue=='f' || $tempValue=='false') return(FALSE);
  if($tempValue=='n' || $tempValue=='no'   ) return(FALSE);
  return( (boolean)$value );
}

// get page name
$page   = getVar('page');
$action = getVar('action');
if(empty($page)) $page='home';

// mcface
if($page=='mcface'){require('inc/mcface.php'); exit();}

// set defaults
$config=array(
  'settings'     => array(),
  'page'         => &$page,
  'action'       => &$action,
  'paths' => array(
    'local'      => array(),
    'http'       => array()
  ),
  'demo'         => FALSE,
  'title'        => '',
  'theme'        => 'default',
  'ui Pack'      => 'redmond',
  'table prefix' => 'WA_',
  'iConomy' => array(
    'use'        => 'auto',
    'Table'      => 'iConomy'
  ),
  'session name' => 'WebAuctionPlus User'
);
$settings = &$config['settings'];
$paths    = &$config['paths'];
$lpaths   = &$config['paths']['local'];
$wpaths   = &$config['paths']['http'];
$user     = &$config['user'];
// local paths
$lpaths['config']     = 'config.php';
$lpaths['includes']   = 'inc/';
$lpaths['classes']    = 'inc/classes/';
$lpaths['pages']      = 'inc/pages/';
$lpaths['theme']      = 'html/{theme}/';
$lpaths['item packs'] = 'inc/ItemPacks/';
// http paths
$wpaths['images']     = 'html/{theme}/images/';
$wpaths['item packs'] = 'inc/ItemPacks/{pack}/icons/';
// load config
require($lpaths['config']);
require('db.config.php');

// includes
require($lpaths['includes'].'inc.php');
$qtime = GetTimestamp();
$page=SanFilename($page);

// load settings
require($lpaths['classes'].'settings.class.php');
SettingsClass::LoadSettings();
// default settings
//SettingsClass::setDefault('Default Language'		, 'en');
SettingsClass::setDefault('Currency Prefix'		, '$ ');
SettingsClass::setDefault('Currency Postfix'		, '');
SettingsClass::setDefault('Custom Descriptions'		, FALSE);
//SettingsClass::setDefault('Allow Enchanted Items'	, TRUE);

// load item packs
require($lpaths['item packs'].'default/item.defines.php');
foreach(explode(',',SettingsClass::getString('Item Packs')) as $v){
  $t=trim($v); if(empty($v)) continue;
  require($lpaths['item packs'].SanFilename($v).'/item.defines.php');
}

// load template engine
require($lpaths['classes'].'html.class.php');
$page_outputs = array();
$tags         = array();
$html = new RenderHtml($page_outputs, $tags);

// init login system
include($lpaths['classes'].'user.class.php');
if($page!='login') $user = new userClass();

// render page content
$page_outputs['body'] = include($lpaths['pages'].$page.'.php');
if($page_outputs['body']==TRUE){
  $a='RenderPage_'.$page;
  $page_outputs['body']=$a();
}
if    ($page_outputs['body'] === TRUE ) $page_outputs['body']='';
elseif($page_outputs['body'] === FALSE) $page_outputs['body']='Unable to load page, render returned FALSE';
$html->Display();


?>