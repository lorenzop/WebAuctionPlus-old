<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
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
      $this->itemType = @ItemsClass::$ItemsArray[$this->itemId]['type'];
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
  return(ItemsClass::getItemName($this->itemId));
}

// get item title
public function getItemTitle(){
  if($this->itemId<=0) return('');
  return(ItemsClass::getItemTitle($this->itemId, $this->itemDamage));
}

// get item icon file
public function getItemImage(){
  if($this->itemId<=0) return('');
  return(ItemsClass::getItemImage($this->itemId, $this->itemDamage));
}

// get percent damaged
public function getPercentDamaged(){
  if($this->itemId<=0) return('');
  $item = ItemsClass::getItemArray($this->itemId);
  if(!isset($item['damage'])) return('');
  return(ItemsClass::getPercentDamaged($this->itemDamage,$item['damage']));
}

// add enchantment
public function addEnchantment($enchName, $enchId, $level){
// check for existing enchantment
////////////////////////////////////////////////////////////////////// <-- to do
  $this->enchantments[] = array(
    'enchName' => $enchName,
    'enchId'   => $enchId,
    'level'    => $level );
}

// get enchantments array
public function getEnchantmentsArray(){
  return($this->enchantments);
}


}
?>