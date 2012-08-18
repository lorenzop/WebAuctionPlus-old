<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Tube Stuff - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'TubeStuff';

$Items[194]=array(
  0 =>array(
    'name'=>'Buffer',
    'icon'=>'Buffer.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Auto Crafting Table II',
    'icon'=>'Automatic_Crafting_Table_MkII.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Black Hole Chest',
    'icon'=>'Black_Hole_Chest.png',
    'pack'=>$pack),
  3 =>array(
    'name'=>'Incinerator',
    'icon'=>'Incinerator.png',
    'pack'=>$pack),
  4 =>array(
    'name'=>'Duplicator',
    'icon'=>'Duplicator.png',
    'pack'=>$pack),
  5 =>array(
    'name'=>'Retrievulator',
    'icon'=>'Retrievulator.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Item',
    'icon'=>'Automatic_Crafting_Table_MkII.png',
    'pack'=>$pack),
);
$Items[7614]=array(
  'name'=>'Retriever Jammer',
  'icon'=>'Retriever_Jammer.png',
  'pack'=>$pack);

unset($Items);
?>