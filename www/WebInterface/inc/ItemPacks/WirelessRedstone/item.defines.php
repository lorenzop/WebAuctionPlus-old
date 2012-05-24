<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Wireless Redstone - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'WirelessRedstone';

$Items[126]=array(
  'name'=>'Wireless Transmitter',
  'icon'=>'Wireless_Transmitter.png',
  'pack'=>$pack);
$Items[127]=array(
  'name'=>'Wireless Receiver',
  'icon'=>'Wireless_Receiver.png',
  'pack'=>$pack);

unset($Items);
?>