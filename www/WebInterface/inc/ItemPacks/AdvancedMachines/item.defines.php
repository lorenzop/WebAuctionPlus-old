<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Advanced Machines - item definitions
$Items = &ItemFuncs::$Items;
$pack  = 'AdvancedMachines';

$Items[188]=array(
  0 =>array(
    'name'=>'Rotary Macerator',
    'icon'=>'Rotary_Macerator.png',
    'pack'=>$pack),
  1 =>array(
    'name'=>'Singularity Compressor',
    'icon'=>'Singularity_Compressor.png',
    'pack'=>$pack),
  2 =>array(
    'name'=>'Centrifuge Extractor',
    'icon'=>'Centrifuge_Extractor.png',
    'pack'=>$pack),
  -1=>array(
    'name'=>'Unknown Machine',
    'icon'=>'Rotary_Macerator.png',
    'pack'=>$pack),
);

unset($Items);
?>