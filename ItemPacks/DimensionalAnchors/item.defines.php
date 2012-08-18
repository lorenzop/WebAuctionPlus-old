<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Dimensional Anchors - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'DimensionalAnchors';

$Items[4095]=array(
  'name'=>'Dimensional Anchor',
  'icon'=>'Dimensional_Anchor.png',
  'pack'=>$pack);

unset($Items);
?>