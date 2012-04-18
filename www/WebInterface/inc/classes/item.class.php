<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class ItemClass{

public $itemId     = 0;
public $itemDamage = 0;
public $qty        = 0;
public $enchantments = array();

function __construct($Item=array()){global $config;
  if(count($Item) > 0){
    if(isset($Item['itemId']))     $this->itemId     = $Item['itemId'];
    if(isset($Item['itemDamage'])) $this->itemDamage = $Item['itemDamage'];
    if(isset($Item['qty']))        $this->qty        = $Item['qty'];
    if(isset($Item['enchantments']) && is_array($Item['enchantments'])){
echo '** set enchantments here **';
      foreach($Item['enchantments'] as $ench){
        // check for existing enchantment
      }
    }
  }
}


// get item name
public function getItemName($itemId=0){
  $Item=$this->getItemArray($itemId);
  if(!is_array($Item) || count($Item)==0){
    return('');
  }else{
    return($Item['name']);
  }
}


// get item title
public function getItemTitle($itemId=0, $itemDamage=0){
  $Item=$this->getItemArray($itemId);
  if(!is_array($Item) || count($Item)==0){
    return('');
  }else{
print_r($Item);
    return($Item['name']);
  }
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


}
?>