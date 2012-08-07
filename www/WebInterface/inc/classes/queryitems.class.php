<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class QueryItems{

protected $result = FALSE;


// query inventory item stacks
public static function QueryInventory($playerName){
  if(empty($playerName)) {$this->result = FALSE; return(FALSE);}
  $class = new QueryItems();
  $class->doQuery("LOWER(`playerName`) = '".mysql_san($playerName)."'");
  if(!$class->result) return(FALSE);
  return($class);
}
// query single item stack
public static function QuerySingle($playerName, $id){
  if(empty($playerName)) {$this->result = FALSE; return(FALSE);}
  $class = new QueryItems();
  $class->doQuery("LOWER(`playerName`) = '".mysql_san($playerName)."' AND `id` = ".((int)$id));
  if(!$class->result) return(FALSE);
  return($class->getNext());
}
// query
protected function doQuery($WHERE){global $config;
  if(empty($WHERE)) {$this->result = FALSE; return;}
  $query="SELECT `id`, `itemId`, `itemDamage`, `qty`, `enchantments` ".
         "FROM `".$config['table prefix']."Items` `Items` ".
         "WHERE ".$WHERE." ORDER BY `id` ASC";
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


}
?>