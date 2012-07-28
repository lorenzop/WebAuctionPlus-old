<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class QueryItems{

protected $result = FALSE;


function __construct(){
}


// query inventory item stacks
public static function QueryInventory($playerName){
  if(empty($playerName)) return(FALSE);
  $class = new QueryItems();
  $class->doQuery("LOWER(`playerName`) = '".mysql_san($playerName)."'");
  return($class);
}
// query single item stack
public static function QuerySingle($playerName, $id){
  if(empty($playerName)) return(FALSE);
  $class = new QueryItems();
  $class->doQuery("LOWER(`playerName`) = '".mysql_san($playerName)."' AND `id` = ".((int)$id)." ");
  return($class->getNext());
}


protected function doQuery($WHERE){global $config;
  if(empty($WHERE)) {$this->result = FALSE; return;}
  $query="SELECT `id`, `itemId`, `itemDamage`, `qty`, `enchantments` ".
         "FROM `".$config['table prefix']."Items` `Items` ".
         "WHERE `ItemTable` = 'Items' AND ".$WHERE.
         "ORDER BY `id` ASC";
  $this->result = RunQuery($query, __file__, __line__);
}


// get next item
public function getNext(){
  if(!$this->result) return(FALSE);
  $row = mysql_fetch_assoc($this->result);
  if(!$row) return(FALSE);
  // new item dao
  return(new ItemDAO(
    $row['id'],
    $row['itemId'],
    $row['itemDamage'],
    $row['qty'],
    $row['enchantments']
  ));
}





//public static function QueryItem($playerName,$id){global $user;
//  if(empty($playerName) || $id<1) return(FALSE);
//  $items = new ItemsClass();
//  $items->QueryItems($user->getName(),"LOWER(`Items`.`playerName`)='".mysql_san(strtolower($playerName))."' AND `Items`.`id`=".((int)$id));
//  $itemRow = $items->getNext();
//  unset($items);
//  return($itemRow);
//}





}
?>