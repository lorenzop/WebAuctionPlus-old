<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Compact Solars - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'CompactSolars';

$Items[187]=array(
  0 =>array(
    'name'=>'Low Voltage Solar Array',
    'icon'=>'Solar_Array_Low_Voltage.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Medium Voltage Solar Array',
    'icon'=>'Solar_Array_Medium_Voltage.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'High Voltage Solar Array',
    'icon'=>'Solar_Array_High_Voltage.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Solar Array',
    'icon'=>'Solar_Array_Low_Voltage.png',
    'pack'=>$pack),
);

unset($Items);
?>