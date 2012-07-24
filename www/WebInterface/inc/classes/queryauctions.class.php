<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class QueryAuctions{

protected $result = FALSE;


function __construct(){
}


// get auctions
public static function QueryCurrent(){
//public static function QueryCurrent($WHERE='', $limit=-1){global $config;
  $class = new QueryAuctions();
  $class->doQuery();
  return($class);
}
// query single auction
public static function QuerySingle($id){
  if($id < 1) return(FALSE);
  $class = new QueryAuctions();
  $class->doQuery( "`id` = ".((int)$id) );
  return($class->getNext());
}


protected function doQuery($WHERE=FALSE){global $config;
//  if(empty($WHERE)) {$this->result = FALSE; return;}
  $query = "SELECT `id`, `playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, ".
           "`price`, `created`, `allowBids`, `currentBid`, `currentWinner` ".
           "FROM `".$config['table prefix']."Auctions` ".
//           "WHERE ".$WHERE." ".
           "ORDER BY `id` ASC";
  $this->result = RunQuery($query, __file__, __line__);
}


//  // WHERE
//  $WHERE = trim($WHERE);
//  if(!empty($WHERE)){
//    if(!startsWith($WHERE, 'WHERE ', TRUE))
//      $WHERE = 'WHERE '.$WHERE;
//    $WHERE .= ' ';
//  }
//  // LIMIT
//  if($limit > 0) $limit = ' LIMIT '.((int)$limit);
//  else           $limit = '';
//  $query = "SELECT ".
//           "Auctions.`id`         AS `id`, ".
//           "Auctions.`playerName` AS `playerName`, ".
//           "Auctions.`itemId`     AS `itemId`, ".
//           "Auctions.`itemDamage` AS `itemDamage`, ".
//           "Auctions.`qty`        AS `qty`, ".
//           "Auctions.`price`      AS `price`, ".
//           "UNIX_TIMESTAMP(Auctions.`created`) AS `created`, ".
//           "ItemEnch.`enchName`   AS `enchName`, ".
//           "ItemEnch.`enchId`     AS `enchId`, ".
//           "ItemEnch.`level`      AS `level` ".
//           "FROM `".     $config['table prefix']."Auctions` `Auctions` ".
//           "LEFT JOIN `".$config['table prefix']."ItemEnchantments` `ItemEnch` ".
//           "ON  Auctions.`id`          = ItemEnch.`ItemTableId` ".
//           "AND ItemEnch.`ItemTable` = 'Auctions' ".$WHERE.
//           "ORDER BY Auctions.`id` ASC".$limit;
//  $this->result = RunQuery($query, __file__, __line__);
//}


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