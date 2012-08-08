<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Additional Pipes - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'AdditionalPipes';

$Items[4298]=array(
  'name'=>'Waterproof Redstone Pipe',
  'icon'=>'Pipe_Waterproof_Redstone.png',
  'pack'=>$pack);
$Items[4299]=array(
  'name'=>'Redstone Pipe',
  'icon'=>'Pipe_Redstone.png',
  'pack'=>$pack);
$Items[4300]=array(
  'name'=>'Advanced Insertion Pipe',
  'icon'=>'Pipe_Advanced_Insertion.png',
  'pack'=>$pack);
$Items[4301]=array(
  'name'=>'Advanced Wooden Pipe',
  'icon'=>'Pipe_Advanced_Wooden.png',
  'pack'=>$pack);
$Items[4302]=array(
  'name'=>'Distribution Pipe',
  'icon'=>'Pipe_Distribution.png',
  'pack'=>$pack);
$Items[4303]=array(
  'name'=>'Item Teleport Pipe',
  'icon'=>'Pipe_Item_Teleport.png',
  'pack'=>$pack);
$Items[4304]=array(
  'name'=>'Waterproof Teleport Pipe',
  'icon'=>'Pipe_Waterproof_Teleport.png',
  'pack'=>$pack);
$Items[4305]=array(
  'name'=>'Power Teleport Pipe',
  'icon'=>'Pipe_Power_Teleport.png',
  'pack'=>$pack);

unset($Items);
?>