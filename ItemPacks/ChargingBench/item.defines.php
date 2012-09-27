<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Charging Bench - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'chargingbench';

$Items[187]=array(
  0 =>array(
    'name'=>'Charging Bench Mk1',
    'icon'=>'Charging_Bench_Mk1.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Charging Bench Mk2',
    'icon'=>'Charging_Bench_Mk2.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Charging Bench Mk3',
    'icon'=>'Charging_Bench_Mk3.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Charging Bench',
    'icon'=>'Charging_Bench_Mk1.png',
    'pack'=>$pack),
);

unset($Items);
?>