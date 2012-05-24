<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class is an item stack object
class ItemClass{

public $itemType   = '';
public $itemId     = 0;
public $itemDamage = 0;
public $qty        = 0;
public $enchantments = array();

function __construct($Item=array()){global $config;
  if(count($Item) > 0){
    if(isset($Item['itemType']))   $this->itemType   = $Item['itemType'];
    if(isset($Item['itemId']))     $this->itemId     = $Item['itemId'];
    if(isset($Item['itemDamage'])) $this->itemDamage = $Item['itemDamage'];
    if(isset($Item['qty']))        $this->qty        = $Item['qty'];
    if($this->itemId>0 && empty($this->itemType))
      $this->itemType = @ItemFuncs::$Items[$this->itemId]['type'];
    if(isset($Item['enchantments']) && is_array($Item['enchantments'])){
echo '** set enchantments here **';
      foreach($Item['enchantments'] as $ench){
        // check for existing enchantment
      }
    }
  }
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
//public function getItemImage(){
//  if($this->itemId<=0) return('');
//  return(ItemFuncs::getItemImage($this->itemId, $this->itemDamage));
//}
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
public function addEnchantment($enchName, $enchId, $level, $enchTableId=0){
// check for existing enchantment
////////////////////////////////////////////////////////////////////// <-- to do
  $a = array(
    'enchName' => $enchName,
    'enchId'   => $enchId,
    'level'    => $level
  );
  if($enchTableId == 0) $this->enchantments[]             = $a;
  else                  $this->enchantments[$enchTableId] = $a;
}

// get enchantments array
public function getEnchantmentsArray(){
  return($this->enchantments);
}

// item has enchantments
public function hasEnchantments(){
echo 'hasEnchantments function not finished yet!!!';
exit();
}


}
?>