<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class handles item object manipulation
class QueryItems{

protected $result  = FALSE;


function __construct(){
}


public static function QueryInventory($playerName){
  if(empty($playerName)) return(FALSE);
  $class = new QueryItems();
  $class->doQuery("LOWER(`playerName`) = '".mysql_san($playerName)."'");
  return($class);
}


protected function doQuery($WHERE){global $config;
  if(empty($WHERE)) return;
  $query="SELECT `id`, `itemId`, `itemDamage`, `qty`, `enchantments` ".
         "FROM `".$config['table prefix']."Items` `Items` ".
         "WHERE `ItemTable` = 'Items' AND ".$WHERE.
         "ORDER BY `id` ASC";
  $this->result = RunQuery($query, __file__, __line__);
}


// get next auction row
public function getNext(){
  if($this->result == FALSE) return(FALSE);
  $row = mysql_fetch_assoc($this->result);
  if($row == FALSE) return(FALSE);
  // new item dao
  return(new ItemDAO(
    $row['id'],
    $row['itemId'],
    $row['itemDamage'],
    $row['qty'],
    $row['enchantments']
  ));
}


}
?>