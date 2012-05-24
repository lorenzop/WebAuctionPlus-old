<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Clever Craft - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'clevercraft';

$Items[235]=array(
  'name'=>'Crafting Table II',
  'icon'=>'Crafting_Table_II.png',
  'pack'=>$pack);

unset($Items);
?>