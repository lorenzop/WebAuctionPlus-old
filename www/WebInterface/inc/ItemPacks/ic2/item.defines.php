<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// buildcraft item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'buildcraft';

$Items[1111]=array(
  'name'=>'Stone',
  'icon'=>'Stone.png',
  'pack'=>$pack);
$Items[2222]=array(
  'name'=>'Sand',
  'icon'=>'Sand.png',
  'pack'=>$pack);
//  5=>array(
//    0 =>array(
//      'name'=>'Wooden Plank (Oak)',
//      'icon'=>'Wooden_Plank_Oak.png'),
//    1 =>array(
//      'name'=>'Wooden Plank (Pine)',
//      'icon'=>'Wooden_Plank_Pine.png'),
//    2 =>array(
//      'name'=>'Wooden Plank (Birch)',
//      'icon'=>'Wooden_Plank_Birch.png'),
//    3 =>array(
//      'name'=>'Wooden Plank (Jungle)',
//      'icon'=>'Wooden_Plank_Jungle.png'),
//    -1=>array(
//      'name'=>'Wooden Plank',
//      'icon'=>'Wooden_Plank.png'),
//  ),
//  268=>array(
//    'type' =>'tool',
//    'name' =>'Wooden Sword',
//    'title'=>'Wooden Sword %damaged%',
//    'icon' =>'Wooden_Sword.png',
//    'damage'=>60,
//    'stack'=>1),

unset($Items);
?>