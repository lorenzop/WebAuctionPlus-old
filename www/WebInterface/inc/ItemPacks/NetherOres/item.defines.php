<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Nether Ores - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'NetherOres';

$Items[135]=array(
  0 =>array(
    'name'=>'Nether Coal',
    'icon'=>'Nether_Coal.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Nether Diamond',
    'icon'=>'Nether_Diamond.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Nether Gold Ore',
    'icon'=>'Nether_Gold_Ore.png',
    'pack'=>$pack),
  3 =>array(
    'name'=>'Nether Iron Ore',
    'icon'=>'Nether_Iron_Ore.png',
    'pack'=>$pack),
  4 =>array(
    'name'=>'Nether Lapis Lazuli',
    'icon'=>'Nether_Lapis_Lazuli.png',
    'pack'=>$pack),
  5 =>array(
    'name'=>'Nether Redstone Ore',
    'icon'=>'Nether_Redstone_Ore.png',
    'pack'=>$pack),
  6 =>array(
    'name'=>'Nether Copper Ore',
    'icon'=>'Nether_Copper_Ore.png',
    'pack'=>$pack),
  7 =>array(
    'name'=>'Nether Tin Ore',
    'icon'=>'Nether_Tin_Ore.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Nether Ore',
    'icon'=>'Nether_Coal.png',
    'pack'=>$pack),
);

unset($Items);
?>