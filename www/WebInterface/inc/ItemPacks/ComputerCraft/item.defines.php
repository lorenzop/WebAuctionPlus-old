<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Computer Craft - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'ComputerCraft';

$Items[207]=array(
  'name'=>'Computer',
  'icon'=>'Computer.png',
  'pack'=>$pack);
$Items[208]=array(
  0 =>array(
    'name'=>'Disk Drive',
    'icon'=>'Disk_Drive.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Wireless Modem',
    'icon'=>'Wireless_Modem.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Disk Drive',
    'icon'=>'Disk_Drive.png',
    'pack'=>$pack),
);
$Items[216]=array(
  0 =>array(
    'name'=>'Turtle',
    'icon'=>'Turtle.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Mining Turtle',
    'icon'=>'Mining_Turtle.png',
    'pack'=>$pack),
  2=>array(
    'name'=>'Wireless Turtle',
    'icon'=>'Wireless_Turtle.png',
    'pack'=>$pack),
  3 =>array(
    'name'=>'Wireless Mining Turtle',
    'icon'=>'Wireless_Mining_Turtle.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Turtle',
    'icon'=>'Turtle.png',
    'pack'=>$pack),
);
$Items[4256]=array(
  'type' =>'map',
  'name' =>'Floppy Disk #map#',
  'title'=>'Floppy Disk #map#',
  'icon' =>'Floppy_Disk.png',
  'pack' =>$pack);

unset($Items);
?>