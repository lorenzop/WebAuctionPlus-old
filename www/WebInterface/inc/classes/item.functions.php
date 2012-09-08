<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// general functions to handle items


// item functions
class ItemFuncs{

// all items info
static $Items = array();
static $Enchantments = array();


// get item array
public static function getItemArray($itemId=0, $itemDamage=0){
  // unknown item id
  if(!isset(self::$Items[$itemId])){
    $item = self::$Items[-1];
    $item['name'] = str_replace('%itemid%', $itemId, $item['name']);
    return($item);
  }
  // found item id
  $item = self::$Items[$itemId];
  // is single item
  if(!isset($item[-1])) return($item);
  // found item by damage id
  if(isset($item[$itemDamage])) return($item[$itemDamage]);
  // unknown damage id
  return($item[-1]);
}


// get item type
public static function getItemType($itemId=0){
  $item = self::getItemArray($itemId);
  if(isset($item['type'])) return($item['type']);
  return('');
}


// get item name
public static function getItemName($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(!is_array($item) || count($item) <= 0) return('Invalid');
  $name = $item['name'];
  // multi-language
  if(is_array($name)){
    $lang = SettingsClass::getString('Language');
    if(isset($name[$lang]))		$name = $name[$lang];
    else if(isset($name['en']))	$name = $name['en'];
    else						$name = reset($name);
  }
  if(@$item['type'] == 'map')
    $name=str_replace('#map#', $itemDamage, $name);
  return($name);
}


// get item title
public static function getItemTitle($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(!is_array($item) || count($item) <= 0) return('Invalid');
  if(isset($item['title'])) $title = $item['title'];
  else                      $title = $item['name'];
  // multi-language
  if(is_array($title)){
    $lang = SettingsClass::getString('Language');
    if(isset($title[$lang]))		$title = $title[$lang];
    else if(isset($title['en']))	$title = $title['en'];
    else							$title = reset($title);
  }
  if(@$item['type'] == 'tool'){$title=str_replace('%damaged%', self::getPercentDamagedStr($itemDamage,$item['damage']), $title);
                               $title=str_replace('%charged%', self::getPercentChargedStr($itemDamage,$item['damage']), $title);}
  if(@$item['type'] == 'map' ) $title=str_replace('#map#'    , $itemDamage                                            , $title);
  return($title);
}


// get enchantment title
public static function getEnchantmentTitle($enchId=-1){
  if(isset(self::$Enchantments[$enchId]))
    return(self::$Enchantments[$enchId]);
  return(self::$Enchantments[-1]);
//  return('Unknown Enchantment #'.$enchId);
}


// display item html
public static function getDisplay($tableRowId, $itemId, $itemDamage, $qty, $enchantments){
  // load html
  $outputs = RenderHTML::LoadHTML('display.php');
  if(!is_array($outputs)) {echo 'Failed to load html!'; exit();}
  // render enchantments
  $enchOutput = '';
  foreach($enchantments as $enchId=>$level){
    $htmlRow = $outputs['enchantment'];
    $tags = array(
      'ench id'    => $enchId,
      'ench name'  => self::getEnchantmentTitle($enchId),
      'ench title' => self::getEnchantmentTitle($enchId),
      'ench level' => numberToRoman($level)
    );
    RenderHTML::RenderTags($htmlRow, $tags);
    $enchOutput .= $htmlRow;
  }
  // render item block
  $output = $outputs['item'];
  $tags = array(
    'table row id'       => $tableRowId,
    'item id'            => $itemId,
    'item damage'        => self::getDamagedChargedStr($itemId, $itemDamage),
    'item qty'           => $qty,
    'enchantments'       => $enchOutput,
    'item name'          => self::getItemName    ($itemId, $itemDamage),
    'item title'         => self::getItemTitle   ($itemId, $itemDamage),
    'item image url'     => self::getItemImageUrl($itemId, $itemDamage),
    'market price each'  => 'market price<br />goes here',
    'market price total' => 'market price<br />goes here',
  );
  $itemType = self::getItemType($itemId);
  if($itemType != 'tool') $tags['enchantments'] = '';
  RenderHTML::RenderTags($output, $tags);
  RenderHTML::Block($output, 'has damage'      , !empty($tags['item damage'])  );
  RenderHTML::Block($output, 'has enchantments', !empty($tags['enchantments']) );
  return($output);
}


// get item icon file
public static function getItemImageUrl($itemId=0, $itemDamage=0){global $config;
  $item = self::getItemArray($itemId, $itemDamage);
  if(!is_array($item) || count($item) <= 0) return('Invalid');
  if(isset($item['icon'])) $icon = $item['icon'];
  else                     $icon = $item['name'].'.png';
  // pack location
  $pack = '';
  if(isset($item['pack'])) $pack = $item['pack'];
  if(empty($pack))         $pack = 'default';
  return(str_replace('{pack}',$pack,$config['paths']['http']['item packs']).$icon);
}


// get full item title
//public static function getItemDamageStr($itemId=0, $itemDamage=0){
//  $item = self::getItemArray($itemId, $itemDamage);
//  if(!is_array($item) || count($item) <= 0) return('');
//  if(isset($item['title'])) $title = $item['title'];
//  else                      $title = $item['name'];
//  if(@$item['damage'] == 'tool') {$title=str_replace('%damaged%', self::getPercentDamagedStr($itemDamage,$item['damage']), $title);
//                                  $title=str_replace('%charged%', self::getPercentChargedStr($itemDamage,$item['damage']), $title);}
//  if(@$item['damage'] == 'map' )  $title=str_replace('#map#'    , ' '.$itemDamage                                        , $title);
//  return($title);
//}


// get damage/charged percent string
public static function getDamagedChargedStr($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(!is_array($item) || count($item) <= 0) return('Invalid');
  if(@$item['type'] != 'tool' && @$item['type'] != 'map') return('');
  if(isset($item['title'])) $title = $item['title'];
  else                      $title =@$item['name'];
  if(empty($title)) return('Invalid');
  $maxDamage = @$item['damage'];
  if($maxDamage == 0) return('');
  if(strpos($title,'%damaged%') !== FALSE)
    return(self::getPercentDamagedStr($itemDamage, $maxDamage));
  elseif(strpos($title,'%charged%') !== FALSE)
    return(self::getPercentChargedStr($itemDamage, $maxDamage));
  //elseif(strpos($title,'#map#') !== FALSE)
  return('');
}


// get percent damaged
private static function getPercentDamaged($itemDamage, $maxDamage){
  $damaged = ( ((float)$itemDamage)/((float)$maxDamage) )*100.0;
  if($damaged > 0 && (string)round($damaged,1) == '0') return( (string)round($damaged,2) );
  else                                                 return( (string)round($damaged,1) );
}
private static function getPercentDamagedStr($itemDamage, $maxDamage){
  $damaged = self::getPercentDamaged($itemDamage, $maxDamage);
  if( ((string)$damaged) == '0') return('Brand New!');
  else                           return(((string)$damaged).' % damaged');
}


// get percent charged
private static function getPercentCharged($itemDamage, $maxDamage){
  $a = ((float)$maxDamage) - ((float)$itemDamage);
  $b = ((float)$maxDamage) - 1.0;
  $charged = ($a / $b) * 100.0;
  if($charged > 0 && (string)round($charged,1) == '0') return( (string) round($charged, 2) );
  else                                                 return( (string) round($charged, 1) );
}
private static function getPercentChargedStr($itemDamage, $maxDamage){
  $charged = self::getPercentCharged($itemDamage, $maxDamage);
  if( ((string)$charged) == '0') return('Fully Charged!');
  else                           return( ((string)$charged).' % charged' );
}


// get max stack size
public static function getMaxStack($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(!is_array($item) || count($item) <= 0) return(64);
  if(isset($item['stack'])) $stacksize = $item['stack'];
  else                      $stacksize = 64;
  if($stacksize < 1)  $stacksize = 1;
  if($stacksize > 64) $stacksize = 64;
  return($stacksize);
}


// update qty / create item
public static function AddCreateItem($playerName, $Item){global $config;
  // find existing stack
  $query = "SELECT `id` FROM `".$config['table prefix']."Items` WHERE ".
           "`playerName`='".mysql_san($playerName)."' AND ".
           "`itemId` = ".    ((int)$Item->getItemId())." AND ".
           "`itemDamage` = ".((int)$Item->getItemDamage())." AND ".
           "`enchantments` = '".mysql_san($Item->getEnchantmentsCompressed())."' ".
           "LIMIT 1";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error finding item stack!</p>'; exit();}
  if(mysql_num_rows($result) > 0){
    $row = mysql_fetch_assoc($result);
    $tableRowId = (int)$row['id'];
    // add qty to existing stack
    $query = "UPDATE `".$config['table prefix']."Items` SET ".
             "`qty`=`qty`+".((int)$Item->getItemQty()).", ".
             "`itemTitle` = '".mysql_san($Item->getItemTitle())."' ".
             "WHERE `id` = ".((int)$tableRowId)." AND `playerName`='".mysql_san($playerName)."' LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error updating item stack!</p>'; exit();}
    return($tableRowId);
  }
  // create new stack
  $query = "INSERT INTO `".$config['table prefix']."Items` (".
           "`playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, `itemTitle`) VALUES (".
           "'".mysql_san($playerName)."', ".
           ((int)$Item->getItemId()).", ".
           ((int)$Item->getItemDamage()).", ".
           ((int)$Item->getItemQty()).", ".
           "'".mysql_san($Item->getEnchantmentsCompressed())."', ".
           "'".mysql_san($Item->getItemTitle())."')";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error creating item stack!</p>'; exit();}
  $tableRowId = mysql_insert_id();
  return($tableRowId);
}
// update qty / remove item stack
public static function RemoveItem($tableRowId, $qty=-1){global $config;
  if($tableRowId < 1) return(FALSE);
  // remove item stack
  if($qty < 0){
    $query = "DELETE FROM `".$config['table prefix']."Items` WHERE `id` = ".((int)$tableRowId)." LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error removing item stack!</p>'; exit();}
  // subtract qty
  }else{
    $query = "UPDATE `".$config['table prefix']."Items` SET `qty`=`qty`-".((int)$qty)." WHERE `id` = ".((int)$tableRowId)." LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error updating item stack!</p>'; exit();}
  }
  return(TRUE);
}


}
?>