<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// IronChests - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'IronChests';

$Items[181]=array(
  0 =>array(
    'name'=>'Iron Chest',
    'icon'=>'Chest_Iron.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Gold Chest',
    'icon'=>'Chest_Gold.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Diamond Chest',
    'icon'=>'Chest_Diamond.png',
    'pack'=>$pack),
  3 =>array(
    'name'=>'Copper Chest',
    'icon'=>'Chest_Copper.png',
    'pack'=>$pack),
  4 =>array(
    'name'=>'Silver Chest',
    'icon'=>'Chest_Silver.png',
    'pack'=>$pack),
  5 =>array(
    'name'=>'Crystal Chest',
    'icon'=>'Chest_Crystal.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Item',
    'icon'=>'Chest_Iron.png',
    'pack'=>$pack),
);
$Items[19757]=array(
  'name'=>'Iron to Gold Chest Upgrade',
  'icon'=>'Chest_Upgrade_Iron_to_Gold.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[19758]=array(
  'name'=>'Gold to Diamond Chest Upgrade',
  'icon'=>'Chest_Upgrade_Gold_to_Diamond.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[19759]=array(
  'name'=>'Copper to Silver Chest Upgrade',
  'icon'=>'Chest_Upgrade_Copper_to_Silver.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[19760]=array(
  'name'=>'Silver to Gold Chest Upgrade',
  'icon'=>'Chest_Upgrade_Silver_to_Gold.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[19761]=array(
  'name'=>'Copper to Iron Chest Upgrade',
  'icon'=>'Chest_Upgrade_Copper_to_Iron.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[19762]=array(
  'name'=>'Diamond to Crystal Chest Upgrade',
  'icon'=>'Chest_Upgrade_Diamond_to_Crystal.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[29763]=array(
  'name'=>'Normal chest to Iron Chest Upgrade',
  'icon'=>'Chest_Upgrade_Normal_to_Iron.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[29764]=array(
  'name'=>'Normal chest to Copper Chest Upgrade',
  'icon'=>'Chest_Upgrade_Normal_to_Copper.png',
  'stack'=>1,
  'pack'=>$pack);

unset($Items);
?>