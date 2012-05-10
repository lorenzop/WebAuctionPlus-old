<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// general functions to handle items


// item tables enum
class ItemTables{
  const Items    = 1;
  const Auctions = 2;
  const Mail     = 3;
  public $value = NULL;
  public function setValue($ItemTable){
    $type = gettype($ItemTable);
    if(    $type == 'string' ) $this->value = self::fromString(   $ItemTable);
    elseif($type == 'integer') $this->value = self::Validate((int)$ItemTable);
    return($this->value);
  }
  public function getValue($type='string'){
    if(    $type=='str'   || $type=='string' ) return( self::toString($this->value) );
    elseif($type=='int'   || $type=='integer') return(           (int)$this->value  );
    return($this->value);
  }
  public static function toString($value){
    if($value == self::Items)    return("Items"   );
    if($value == self::Auctions) return("Auctions");
    if($value == self::Mail)     return("Mail"    );
    return(NULL);
  }
  public static function fromString($str){
    $str = strtolower(func_get_arg(0));
    if($str == 'items')    return(self::Items   );
    if($str == 'auctions') return(self::Auctions);
    if($str == 'mail')     return(self::Mail    );
    return(-1);
  }
  public static function Validate($ItemTable){
    $type = gettype($ItemTable);
    if(    $type == 'string' ) return( self::toString(self::fromString($ItemTable)) );
    elseif($type == 'integer') return( self::fromString(self::toString($ItemTable)) );
    elseif($type == 'object' ) return( self::toString($ItemTable->value)            );
    return(NULL);
  }
  public static function ValidateStr($ItemTable){
    $type = gettype($ItemTable);
    if(    $type == 'string' ) return( self::Validate($ItemTable)        );
    elseif($type == 'integer') return( self::toString($ItemTable)        );
    elseif($type == 'object' ) return( self::toString($ItemTable->value) );
    return(NULL);
  }
}


// item functions
class ItemFuncs{

// all items info
static $Items = array();

// get item array
public static function getItemArray($itemId=0, $itemDamage=0){
  if(isset(self::$Items[$itemId])){
    $item = self::$Items[$itemId];
    if(isset($item[-1])){
      if(isset($item[$itemDamage]))
        return($item[$itemDamage]);
      else
        return($item[-1]);
    }else
      return($item);
  }else
    return(FALSE);
}

// get item type
public static function getItemType($itemId=0){
  $item = self::getItemArray($itemId);
  if(isset($item['type'])) return($item['type']);
  else                     return('');
}

// get item name
public static function getItemName($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(is_array($item) && count($item)>0){
    $name = $item['name'];
    if(isset($item['type']) && $item['type']=='map')
      $name=str_replace('#map#',$itemDamage,$name);
    return($name);
  }else
    return('');
}

// get item title
public static function getItemTitle($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(is_array($item) && count($item)>0){
    if(isset($item['title'])) $title = $item['title'];
    else                      $title = $item['name'];
    if(@$item['damage'] == 'tool') $title=str_replace('%damaged%', self::getPercentDamagedStr($itemDamage,$item['damage']).'% damaged', $title);
    if(@$item['damage'] == 'map' ) $title=str_replace('#map#'    , ' '.$itemDamage, $title);
    return($title);
  }else
    return('');
}

// get item icon file
public static function getItemImage($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(is_array($item) && count($item)>0){
    if(isset($item['icon']))
      return($item['icon']);
    else
      return($item['name']);
  }else
    return('');
}

// get percent damaged
public static function getPercentDamaged($itemDamage, $maxDamage){
  $damaged = ( ((float)$itemDamage)/((float)$maxDamage) )*100.0;
  if($damaged>0 && (string)round($damaged,1) == '0') return( (string)round($damaged,2) );
  else                                               return( (string)round($damaged,1) );
}
public static function getPercentDamagedStr($itemDamage, $maxDamage){
  $damaged = self::getPercentDamaged($itemDamage, $maxDamage);
  if( ((string)$damaged) == '0') return('Brand New!');
  else                           return(((string)$damaged).' % damaged');
  return;
}

// get max stack size
public static function getMaxStack($itemId=0, $itemDamage=0){
  $item = self::getItemArray($itemId, $itemDamage);
  if(is_array($item) && count($item)>0){
    if(isset($item['stack'])) $stacksize = $item['stack'];
    else                      $stacksize = 64;
    if($stacksize < 1)  $stacksize = 1;
    if($stacksize > 64) $stacksize = 64;
    return($stacksize);
  }else
    return(64);
}

// query single stack
public static function QueryItem($playerName,$id){global $user;
  if($id<1) return(FALSE);
  $items = new ItemsClass();
  $items->QueryItems($user->getName(),"LOWER(`Items`.`playerName`)='".mysql_san(strtolower($playerName))."' AND `Items`.`id`=".((int)$id));
  $itemRow = $items->getNext();
  unset($items);
  return($itemRow);
}

// create item
public static function CreateItem($ItemTable, $playerName, $itemId, $itemDamage, $qty, $ench=array()){global $config;
  $query = "INSERT INTO `".$config['table prefix']."Items` (".
           "`ItemTable`,`playerName`,`itemId`,`itemDamage`,`qty`) VALUES (".
           "'".mysql_san(ItemTables::ValidateStr($ItemTable))."',".
           "'".mysql_san($playerName)."',".
           ((int)$itemId).",".
           ((int)$itemDamage).",".
           ((int)$qty).")";
//echo '<p>'.$query.'</p>';
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error creating item stack!</p>'; exit();}
  $ItemTableId = mysql_insert_id();
  self::CreateEnchantments($ench, 'Items', $ItemTableId);
  return($ItemTableId);
}

// update qty
public static function UpdateQty($itemId, $qty, $fixed=TRUE){global $config;
  // set qty
  if($fixed) $query = "`qty` = ".((int)$qty);
  // add/subtract
  else $query = "`qty` = `qty` + ".((int)$qty);
  $query = "UPDATE `".$config['table prefix']."Items` SET ".$query." WHERE `id`=".((int)$itemId)." LIMIT 1";
//echo '<p>'.$query.'</p>';
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error updating item stack!</p>'; exit();}
  return($result);
}

// delete item
public static function DeleteItem($itemId){global $config;
  $query = "DELETE FROM `".$config['table prefix']."Items` WHERE `id` = ".((int)$itemId)." LIMIT 1";
//echo '<p>'.$query.'</p>';
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error removing item stack!</p>'; exit();}
  return($result);
}

// create new enchantments
public static function CreateEnchantments($ench, $ItemTable, $ItemTableId){global $config;
  if(!is_array($ench)) return(FALSE);
  $newEnch = array();
  foreach($ench as $v){
    $query = "INSERT INTO `".$config['table prefix']."ItemEnchantments` (".
             "`ItemTable`,`ItemTableId`,`enchName`,`enchId`,`level`) VALUES(".
             "'".mysql_san(ItemTables::ValidateStr($ItemTable))."',".((int)$ItemTableId).",".
             "'".mysql_san($v['enchName'])."',".((int)$v['enchId']).",".((int)$v['level']).")";
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error creating enchantment!</p>'; exit();}
    $newEnch[mysql_insert_id()] = $v;
  }
  return($newEnch);
}

// mail item stack to player
public static function MailStack($id){global $config,$user;
  if($id <= 0){$config['error'] = 'Invalid item id!'; return(FALSE);}
  // get item from db
  $itemRow = ItemFuncs::QueryItem($user->getName(),$id);
  if($itemRow === FALSE){$config['error'] = 'Item not found!'; return(FALSE);}
  $Item = &$itemRow['Item'];
  $stacksize = ItemFuncs::getMaxStack($Item->itemId,$Item->itemDamage);
  // check is owner
  if(!$user->hasPerms("isAdmin")){
    if(!$user->nameEquals($itemRow['playerName'])){
      $config['error'] = 'You don\'t own that item!'; return(FALSE);}}
  // stack size to big
  $didSplit = FALSE;
  $sql = '';
  while($Item->qty > $stacksize){
    // split stack
    ItemFuncs::CreateItem('Mail', $user->getName(), $Item->itemId, $Item->itemDamage, $stacksize, $Item->getEnchantmentsArray());
    $Item->qty -= $stacksize;
    $didSplit = TRUE;
  }
  if($didSplit) $sql = "`qty`=".((int)$qty).", ";
  // move item
  $query = "UPDATE `".$config['table prefix']."Items` SET ".$sql.
           "`ItemTable`='Mail' WHERE `ItemTable`='Items' AND `id`=".((int)$id)." LIMIT 1";
  $result = RunQuery($query, __file__, __line__);
  if(!$result || mysql_affected_rows()!=1){
    $config['error'] = 'Error mailing items! '.__line__; return(FALSE);}
  // move enchantments
  $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
           "`ItemTable`='Mail' WHERE `ItemTable`='Items' AND `ItemTableId`=".((int)$id);
  $result = RunQuery($query, __file__, __line__);
  if(!$result){$config['error'] = 'Error mailing items! '.__line__; return(FALSE);}
  ForwardTo('./?page=myitems');
}


}
?>