<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class QueryAuctions{

protected $result = FALSE;


// get auctions
public static function QueryCurrent(){
  $class = new QueryAuctions();
  $class->doQuery();
  if(!$class->result) return(FALSE);
  return($class);
}
// get my auctions
public static function QueryMy(){global $user;
  if(!$user->isOk()) {$this->result = FALSE; return(FALSE);}
  $class = new QueryAuctions();
  $class->doQuery( "`playerName` = '".mysql_san($user->getName())."'" );
  if(!$class->result) return(FALSE);
  return($class);
}
// query single auction
public static function QuerySingle($id){
  if($id < 1) {$this->result = FALSE; return(FALSE);}
  $class = new QueryAuctions();
  $class->doQuery( "`id` = ".((int)$id) );
  if(!$class->result) return(FALSE);
  return($class->getNext());
}
// query
protected function doQuery($WHERE=''){global $config;
  $query = "SELECT `id`, `playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, ".
           "`price`, UNIX_TIMESTAMP(`created`) AS `created`, `allowBids`, `currentBid`, `currentWinner` ".
           "FROM `".$config['table prefix']."Auctions` ".
           (empty($WHERE) ? '' : "WHERE ".$WHERE." ").
           "ORDER BY `id` ASC";
  $this->result = RunQuery($query, __file__, __line__);
}


// get next auction
public function getNext(){
  if(!$this->result) return(FALSE);
  $row = mysql_fetch_assoc($this->result);
  if(!$row) return(FALSE);
  // new auction dao
  return(new AuctionDAO(
    $row['id'],
    $row['playerName'],
    new ItemDAO(
      -1,
      $row['itemId'],
      $row['itemDamage'],
      $row['qty'],
      $row['enchantments']
    ),
    $row['price'],
    $row['created'],
    $row['allowBids']!=0,
    $row['currentBid'],
    $row['currentWinner']
  ));
}


}
?>