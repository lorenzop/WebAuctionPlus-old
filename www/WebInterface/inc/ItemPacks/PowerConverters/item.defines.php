<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Power Converters - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'PowerConverters';

$Items[123]=array(
  0 =>array(
    'name'=>'Engine Generator (LV)',
    'icon'=>'Engine_Generator_LV.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Engine Generator (MV)',
    'icon'=>'Engine_Generator_MV.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Engine Generator (HV)',
    'icon'=>'Engine_Generator_HV.png',
    'pack'=>$pack),
  3 =>array(
    'name'=>'Oil Fabricator',
    'icon'=>'Fabricator_Oil.png',
    'pack'=>$pack),
  4 =>array(
    'name'=>'Energy Link',
    'icon'=>'Energy_Link.png',
    'pack'=>$pack),
  5 =>array(
    'name'=>'Lava Fabricator',
    'icon'=>'Fabricator_Lava.png',
    'pack'=>$pack),
  6 =>array(
    'name'=>'Geothermal Generator Mk2',
    'icon'=>'Geothermal_Generator_Mk2.png',
    'pack'=>$pack),
  7 =>array(
    'name'=>'Water Strainer',
    'icon'=>'Water_Strainer.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Nether Coal',
    'icon'=>'Nether_Coal.png',
    'pack'=>$pack),
);

unset($Items);
?>