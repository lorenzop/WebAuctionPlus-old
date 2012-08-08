<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Ender Chest - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'EnderChest';

$Items[178]=array(
  'name'=>'Ender Chest',
  'icon'=>'Ender_Chest.png',
  'pack'=>$pack);

unset($Items);
?>