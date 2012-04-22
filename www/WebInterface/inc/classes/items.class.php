<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class ItemsClass{

public $currentId  = 0;
protected $result  = FALSE;
private   $tempRow = FALSE;

function __construct(){
}

// get auctions
public function QueryItems($args=array()){global $config;
  $this->currentId = 0;
  $tempRow = FALSE;
  $query="SELECT ".
         "Items.`id`         AS `id`, ".
         "Items.`itemId`     AS `itemId`, ".
         "Items.`itemDamage` AS `itemDamage`, ".
         "Items.`qty`        AS `qty`, ".
         "ItemEnch.`enchName`   AS `enchName`, ".
         "ItemEnch.`enchId`     AS `enchId`, ".
         "ItemEnch.`level`      AS `level` ".
         "FROM `".     $config['table prefix']."Items` `Items` ".
         "LEFT JOIN `".$config['table prefix']."ItemEnchantments` `ItemEnch` ".
         "ON  Items.`id`           = ItemEnch.`ItemTableId` ".
         "WHERE Items.`ItemTable`  = 'Items' ".
         "AND ItemEnch.`ItemTable` = 'Items' ".
         @$args['WHERE'].' '.
         "ORDER BY Items.`id` ASC";
//echo '<pre><font color="white">'.$query."</font></pre>";
  $this->result=RunQuery($query, __file__, __line__);
}

// get next auction row
public function getNext(){
  $tempRow = &$this->tempRow;
  $output  = array();
  // get first row
  if($tempRow==FALSE) $tempRow = mysql_fetch_assoc($this->result);
  if($tempRow==FALSE) return(FALSE);
  $this->currentId      = $tempRow['id'];
  $output['id']         = $tempRow['id'];
  // create item object
  $output['Item'] = new ItemClass(array(
    'itemId'     => $tempRow['itemId'],
    'itemDamage' => $tempRow['itemDamage'],
    'qty'        => $tempRow['qty'] ));
  // get first enchantment
  if(!empty($tempRow['enchName']))
    $output['Item']->addEnchantment($tempRow['enchName'], $tempRow['enchId'], $tempRow['level'] );
  // get more rows (enchantments)
  while($tempRow = mysql_fetch_assoc($this->result)){
    if($tempRow['id'] != $this->currentId) break;
    $output['Item']->addEnchantment($tempRow['enchName'], $tempRow['enchId'], $tempRow['level']);
  }
  if(count($output)==0) $output=FALSE;
  return($output);
}


}
?>