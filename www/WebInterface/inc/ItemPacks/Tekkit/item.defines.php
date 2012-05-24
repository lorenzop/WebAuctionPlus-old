<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// Tekkit - item definitions


$pathPrefix  = $config['paths']['local']['item packs'];
$pathPostfix = '/item.defines.php';
include($pathPrefix.'AdditionalPipes' .$pathPostfix);
include($pathPrefix.'AdvancedMachines'.$pathPostfix);
include($pathPrefix.'Balkon'          .$pathPostfix);
include($pathPrefix.'Buildcraft'      .$pathPostfix);
include($pathPrefix.'ChargingBench'   .$pathPostfix);
include($pathPrefix.'CleverCraft'     .$pathPostfix);
include($pathPrefix.'CompactSolars'   .$pathPostfix);
include($pathPrefix.'ComputerCraft'   .$pathPostfix);
include($pathPrefix.'EnderChest'      .$pathPostfix);
include($pathPrefix.'IndustrialCraft2'.$pathPostfix);
include($pathPrefix.'NetherOres'      .$pathPostfix);
include($pathPrefix.'PowerConverters' .$pathPostfix);
include($pathPrefix.'WirelessRedstone'.$pathPostfix);


?>