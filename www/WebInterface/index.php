<?php
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
  'table prefix'     => 'WA_',
  'iConomy']['use']   = 'auto',
  'iConomy']['Table'] = 'iConomy',
  'session name']     = 'WebAuctionPlus User',
  
);
// load config
require('config.php');
// includes
require('inc/inc.php');
require('inc/html.php');

// init login system
include('inc/user.class.php');
$user = new userClass();


echo $user->getMoney();
exit();

require('scripts/itemInfo.php');
//require('classes/EconAccount.php');
require('scripts/updateTables.php');

include('inc/pages/auctions.php');

?>
