<?php
error_reporting(E_ALL | E_STRICT);
define('DEFINE_INDEX_FILE',TRUE);

// get,post,cookie (highest priority last)
function getVar($name,$type='',$order=array('get','post')){$output='';
  if(!is_array($order)){$order=@explode(',',$order);}
  if(@count($order)==0 || $order==''){
    if($order!=''){$order=array($order);
    }else{$order=array('get','post');}}
  # get vars
  foreach($order as $v){
    if(     $v=='get'    && isset($_GET[$name])   ){$output=@$_GET[$name];
    }elseif($v=='post'   && isset($_POST[$name])  ){$output=@$_POST[$name];
    }elseif($v=='cookie' && isset($_COOKIE[$name])){$output=@$_COOKIE[$name];}}
  // convert type if set
  if(     $type=='str'  || $type=='string' ){return( (string) $output );
  }elseif($type=='int'  || $type=='integer'){return( (integer)$output );
  }elseif($type=='bool' || $type=='boolean'){return( (boolean)$output );}
  return($output);
}

// set defaults
$config=array(
  'page'         => '',
  'paths' => array(
    'local' => array(),
    'html'  => array()
  ),
  'title'        => '',
  'theme'        => 'default',
  'table prefix' => 'WA_',
  'iConomy' => array(
    'use'        => 'auto',
    'Table'      => 'iConomy',
  ),
  'session name' => 'WebAuctionPlus User',
);
$page   = &$config['page'];
$lpaths = &$paths['local'];
$wpaths = &$paths['http'];
$user   = &$config['user'];
// local paths
$lpaths['config']   = 'config.php';
$lpaths['includes'] = 'inc/';
$lpaths['classes']  = 'inc/';
$lpaths['pages']    = 'inc/pages/';
$lpaths['theme']    = 'inc/html/{theme}/';
// http paths
$wpaths['images']   = 'images/';
// load config
require($lpaths['config']);
// includes
require($lpaths['includes'].'inc.php');
require($lpaths['includes'].'html.php');

$page_outputs=array(
  'header'=>'',
  'css'=>'',
  'body'=>'',
  'footer'=>''
);
$page=getVar('page');
if(empty($page)){$page='home';}

// init login system
include($lpaths['classes'].'user.class.php');
if($page!='login')
  $user = new userClass();


// render page content
$page_outputs['body'] = include($lpaths['pages'].SanFilename($page).'.php');
if    ($page_outputs['body'] === TRUE ) $page_outputs['body']='';
elseif($page_outputs['body'] === FALSE) $page_outputs['body']='Page render returned FALSE';



// page header
$page_outputs['header'].=
  '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n".
  '<html>'."\n".
  '<head>'."\n".
  '  <meta http-equiv="content-type" content="text/html; charset=utf-8" />'."\n".
  '  <title>WebAuction</title>'."\n".
  '  <link rel="icon" type="image/x-icon" href="images/favicon.ico" />'."\n".
  '  <style type="text/css" title="currentStyle">'."\n".
  '  </style>'."\n".
// css
loadCss('main.css');
loadCss('table_jui.css');
loadCss($uiPack.'/jquery-ui-1.8.18.custom.css');
loadCss('jquery-ui-1.8.16.custom.css');
loadCss($cssFile.'.css');
$outputs['header'].='<style type="text/css">'."\n".
                    $outputs['css']."\n".
                    "</style>\n";
// finish header
$page_outputs['header'].=
  '  <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>'."\n".
  '  <script type="text/javascript" language="javascript" src="js/jquery.dataTables-1.9.0.min.js"></script>'."\n".
  '  <script type="text/javascript" language="javascript" src="js/inputfunc.js"></script>'."\n".
  '  <script type="text/javascript" charset="utf-8">'."\n".
  '    $(document).ready(function() {'."\n".
  '      oTable = $(\'#mainTable\').dataTable({'."\n".
//  '        "bProcessing"     : true,'."\n".
  '        "bJQueryUI": true,'."\n".
//  '        "bStateSave"      : true,'."\n".
  '        "sPaginationType": "full_numbers"'."\n".
//  '        "sAjaxSource"     : "scripts/server_processing.php"'."\n".
  '      });'."\n".
  '    } );'."\n".
  '  </script>'."\n".
  '</head>'."\n";


//<body>







?>
