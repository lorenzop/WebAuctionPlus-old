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
  -1=>array(
    'name'=>'Unknown Item',
    'icon'=>'Automatic_Crafting_Table_MkII.png',
    'pack'=>$pack),
);

unset($Items);
?>
