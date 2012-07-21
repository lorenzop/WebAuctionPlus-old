<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class is an item stack object
class ItemDAO{


public $tableRowId = 0;
public $itemId     = 0;
public $itemDamage = 0;
public $qty        = 0;
public $enchantments = array();


function __construct($tableRowId=0, $itemId=0, $itemDamage=0, $qty=0, $enchantments=array()){
  $this->tableRowId = (int) $tableRowId;
  $this->itemId     = (int) $itemId;
  $this->itemDamage = (int) $itemDamage;
  $this->qty        = (int) $qty;
  $this->enchantments = self::parseEnchantments($enchantments);
}


// parse enchantments string from db
public static function parseEnchantments($enchStr) {
  // already an array
  if(is_array($enchStr)) return($enchStr);
  if(gettype($enchStr) != 'string') return(array());
  if(empty($enchStr)) return(array());
  $output = array();
  $lines = explode(',', $enchStr);
  foreach($lines as $line){
    $parts = explode(':', $line);
    if(count($parts) != 2) continue;
    $output[$parts[0]] = $parts[1];
  }
  return($output);
}


// get item type id
public function getItemId(){
  return((int)$this->itemId);
}
// get item damage value
public function getItemDamage(){
  return((int)$this->itemDamage);
}
// get item qty
public function getItemQty(){
  return((int)$this->qty);
}


// get item name
public function getItemName(){
  if($this->itemId<=0) return('');
  return(ItemFuncs::getItemName($this->itemId, $this->itemDamage));
}
// get item title
public function getItemTitle(){
  if($this->itemId<=0) return('');
  return(ItemFuncs::getItemTitle($this->itemId, $this->itemDamage));
}


// get item icon file
public function getItemImageUrl(){
  if($this->itemId<=0) return('');
  return(ItemFuncs::getItemImageUrl($this->itemId, $this->itemDamage));
}


// get damage/charged percent string
public function getDamagedChargedStr(){
  return(ItemFuncs::getDamagedChargedStr($this->itemId, $this->itemDamage));
}
// get percent damaged
//public function getPercentDamaged(){
//  if($this->itemId<=0) return('');
//  $item = ItemFuncs::getItemArray($this->itemId);
//  if(!isset($item['damage'])) return('');
//  return(ItemFuncs::getPercentDamagedStr($this->itemDamage,$item['damage']));
//}
//public function getPercentDamageStr(){
//  $damaged = $this->getPercentDamaged();
//  if( ((string)$damaged) == '0') return('Brand New!');
//  else                           return((string)$damaged);
//}
// get percent charged
//public function getItemDamageStr(){
//  return(ItemFuncs::getPercentChargedStr($this->itemId, $this->itemDamage));
////  $damaged = $this->getPercentDamaged();
//  if( ((string)$damaged) == '0') return('Fully Charged!');
//  else                           return((string)$damaged);
//}


// add enchantment
public function addEnchantment($enchId, $level){
  $this->enchantments[$enchId] = $level;
}


}
?>