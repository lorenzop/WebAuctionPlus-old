<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// general functions to handle items


// item tables enum
class ItemTables{
  const Items    = 1;
  const Auctions = 2;
  const Mail     = 3;
  public $value = NULL;
  public function toString(){
    if(func_num_args() == 0) return($this->toString($value));
    $ItemTable = ((int)$func_get_arg(0));
    if($ItemTable == ItemTables::Items)    return("Items");
    if($ItemTable == ItemTables::Auctions) return("Auctions");
    if($ItemTable == ItemTables::Mail)     return("Mail");
  }
  public function fromString(){
    if(func_num_args() == 0) return($this->fromString($value));
    $ItemTable = lcase(func_get_arg(0));
    if($ItemTable == 'items')    return($this->Items);
    if($ItemTable == 'auctions') return($this->Auctions);
    if($ItemTable == 'mail')     return($this->Mail);
    return(NULL);
  }
}


// item functions
class ItemFuncs{

static $Items=array();

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
  if(isset($item['type']))
    return($item['type']);
  else
    return('');
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
    if(isset($item['title'])) $title=$item['title'];
    else                      $title=$item['name'];
    if(isset($item['damage'])){
      $title=str_replace('%damaged%',self::getPercentDamaged($itemDamage,$item['damage']).'% damaged',$title);
      $title=str_replace('#map#',' '.$itemDamage,$title);
    }
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
  return(round(( ((float)$itemDamage)/((float)$maxDamage) )*100.0, 1));
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
  $items->QueryItems($user->getName(),"`Items`.`playerName`='".mysql_san($playerName)."' AND `Items`.`id`=".((int)$id));
  $itemRow = $items->getNext();
  unset($items);
  return($itemRow);
}


}
?>