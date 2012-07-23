<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// general functions to handle items


// item tables enum
class ItemTables extends Enum{
  function __construct(){
    self::$enumValues = array(
      'items'    => 1,
      'auctions' => 2,
      'mail'     => 3
    );
    self::construct(func_get_args());
  }
}


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
  if(isset($item['type']) && $item['type'] == 'map')
    $name=str_replace('#map#',$itemDamage,$name);
  return($name);
}


// get item title
public static function getItemTitle($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(!is_array($item) || count($item) <= 0) return('Invalid');
  if(isset($item['title'])) $title = $item['title'];
  else                      $title = $item['name'];
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
  RenderHTML::RenderTags($output, $tags);
  RenderHTML::Block($output, 'has enchantments', (count($enchantments)>0) );
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
  $charged = ( ( ((float)$maxDamage)-((float)$itemDamage) )/((float)$maxDamage) )*100.0;
  if($charged > 0 && (string)round($charged,1) == '0') return( (string)round($charged,2) );
  else                                                 return( (string)round($charged,1) );
}
private static function getPercentChargedStr($itemDamage, $maxDamage){
  $charged = self::getPercentCharged($itemDamage, $maxDamage);
  if( ((string)$charged) == '0') return('Fully Charged!');
  else                           return(((string)$charged).' % charged');
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


//// create item
//public static function CreateItem($ItemTable, $playerName, $itemId, $itemDamage, $qty, $ench=array()){global $config;
//  $query = "INSERT INTO `".$config['table prefix']."Items` (".
//           "`ItemTable`,`playerName`,`itemId`,`itemDamage`,`qty`) VALUES (".
//           "'".mysql_san(ItemTables::ValidateStr($ItemTable))."',".
//           "'".mysql_san($playerName)."',".
//           ((int)$itemId).",".
//           ((int)$itemDamage).",".
//           ((int)$qty).")";
//  $result = RunQuery($query, __file__, __line__);
//  if(!$result){echo '<p style="color: red;">Error creating item stack!</p>'; exit();}
//  $ItemTableId = mysql_insert_id();
//  self::CreateEnchantments($ench, 'Items', $ItemTableId);
//  return($ItemTableId);
//}


// update qty
public static function UpdateQty($itemId, $qty, $fixed=TRUE){global $config;
  // set qty
  if($fixed) $query = "`qty` = ".((int)$qty);
  // add/subtract
  else $query = "`qty` = `qty` + ".((int)$qty);
  $query = "UPDATE `".$config['table prefix']."Items` SET ".$query." WHERE `id`=".((int)$itemId)." LIMIT 1";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error updating item stack!</p>'; exit();}
  return($result);
}


// delete item
public static function DeleteItem($itemId){global $config;
  $query = "DELETE FROM `".$config['table prefix']."Items` WHERE `id` = ".((int)$itemId)." LIMIT 1";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error removing item stack!</p>'; exit();}
  return($result);
}


//// create new enchantments
//public static function CreateEnchantments($ench, $ItemTable, $ItemTableId){global $config;
//  if(!is_array($ench)) return(FALSE);
//  $newEnch = array();
//  foreach($ench as $v){
//    $query = "INSERT INTO `".$config['table prefix']."ItemEnchantments` (".
//             "`ItemTable`,`ItemTableId`,`enchName`,`enchId`,`level`) VALUES(".
//             "'".mysql_san(ItemTables::ValidateStr($ItemTable))."',".((int)$ItemTableId).",".
//             "'".mysql_san($v['enchName'])."',".((int)$v['enchId']).",".((int)$v['level']).")";
////echo '<p>'.$query.'</p>';
//    $result = RunQuery($query, __file__, __line__);
//    if(!$result){echo '<p style="color: red;">Error creating enchantment!</p>'; exit();}
//    $newEnch[mysql_insert_id()] = $v;
//  }
//  return($newEnch);
//}


//// mail item stack to player
//public static function MailStack($id){global $config,$user;
//  if($id <= 0){$config['error'] = 'Invalid item id!'; return(FALSE);}
//  // get item from db
//  $itemRow = ItemFuncs::QueryItem($user->getName(),$id);
//  if($itemRow === FALSE){$config['error'] = 'Item not found!'; return(FALSE);}
//  $Item = &$itemRow['Item'];
//// this isn't even needed right now!
//// QueryItem above already searches for items only owned by that player
////  // check is owner
////  if(!$user->hasPerms("isAdmin")){
////    if(!$user->nameEquals($itemRow['playerName'])){
////      $config['error'] = 'You don\'t own that item!'; return(FALSE);}}
//  // stack size to big
//  $stacksize = ItemFuncs::getMaxStack($Item->itemId,$Item->itemDamage);
//  $didSplit = FALSE;
//  while($Item->qty > $stacksize){
//    // split stack
//    ItemFuncs::CreateItem('Mail', $user->getName(), $Item->itemId, $Item->itemDamage, $stacksize, $Item->getEnchantmentsArray());
//    $Item->qty -= $stacksize;
//    $didSplit = TRUE;
//  }
//  // move item
//  $query = "UPDATE `".$config['table prefix']."Items` SET ".
//           ($didSplit?"`qty`=".((int)$Item->qty).",":'').
//           "`ItemTable`='Mail' WHERE `ItemTable`='Items' AND `id`=".((int)$id)." LIMIT 1";
//  $result = RunQuery($query, __file__, __line__);
//  if(!$result || mysql_affected_rows()!=1){
//    $config['error'] = 'Error mailing items! '.__line__; return(FALSE);}
//  // move enchantments
//  $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
//           "`ItemTable`='Mail' WHERE `ItemTable`='Items' AND `ItemTableId`=".((int)$id);
//  $result = RunQuery($query, __file__, __line__);
//  if(!$result){$config['error'] = 'Error mailing items! '.__line__; return(FALSE);}
//  ForwardTo('./?page=myitems');
//}


}
?>