<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Nuclear Control - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'NuclearControl';

$Items[192]=array(
  0 =>array(
    'name'=>'Thermal Monitor',
    'icon'=>'Thermal_Monitor.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Industrial Alarm',
    'icon'=>'Alarm_Industrial.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Howler Alarm',
    'icon'=>'Alarm_Howler.png',
    'pack'=>$pack),
  3 =>array(
    'name'=>'Remote Thermal Monitor',
    'icon'=>'Remote_Thermal_Monitor.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Nuclear Item',
    'icon'=>'Thermal_Monitor.png',
    'pack'=>$pack),
);
$Items[31256]=array(
  'name'=>'Thermometer',
  'icon'=>'Thermometer.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[31257]=array(
  'type' =>'tool',
  'name' =>'Digital Thermometer',
  'title'=>'Digital Thermometer %charged%',
  'icon' =>'Thermometer_Digital.png',
  'damage'=>101,
  'stack'=>1,
  'pack'=>$pack);
$Items[31258]=array(
  'name'=>'Remote Sensor Kit',
  'icon'=>'Remote_Sensor_Kit.png',
  'stack'=>1,
  'pack'=>$pack);
$Items[31259]=array(
  'name'=>'Sensor Location Card',
  'icon'=>'Sensor_Location_Card.png',
  'stack'=>1,
  'pack'=>$pack);

unset($Items);
?>