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
  $query = "SELECT ".(getVar('ajax','bool')?"SQL_CALC_FOUND_ROWS ":'').
           "`id`, `playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, ".
           "`price`, UNIX_TIMESTAMP(`created`) AS `created`, `allowBids`, `currentBid`, `currentWinner` ".
           "FROM `".$config['table prefix']."Auctions` ";
  // where
  if(is_array($WHERE)){
    $query_where = $WHERE;
  }else{
    $query_where = array();
    if(!empty($WHERE)) $query_where[] = $WHERE;
  }
  // ajax search
  $sSearch = getVar('sSearch');
  if(!empty($sSearch))
    $query_where[] = "(`itemTitle` LIKE '%".mysql_san($sSearch)."%' OR ".
                     "`playerName` LIKE '%".mysql_san($sSearch)."%')";
  // build where string
  if(count($query_where) == 0) $query_where = '';
  else $query_where = 'WHERE '.implode(' AND ', $query_where);
  // ajax sorting
  $query_order = '';
  if(isset($_GET['iSortCol_0'])){
  	$order_cols = array(
  	  0 => "`itemTitle`",
  	  1 => "`playerName`",
  	  2 => "`price`",
  	  3 => "(`price` * `qty`)",
  	  4 => "1", // market
  	  5 => "`qty`",
  	);
    $iSortingCols = getVar('iSortingCols', 'int');
    for($i = 0; $i < $iSortingCols; $i++){
      $iSortCol = getVar('iSortCol_'.$i, 'int');
      if(!getVar('bSortable_'.$iSortCol, 'bool')) continue;
      if(!isset($order_cols[$iSortCol])) continue;
      if(!empty($query_order)) $query_order .= ', ';
      $query_order .= $order_cols[$iSortCol].' '.mysql_san(getVar('sSortDir_'.$i, 'str'));
    }
  }
  if(empty($query_order)) $query_order = "`id` ASC";
  $query_order = ' ORDER BY '.$query_order;
  // pagination
  $query_limit = '';
  if(isset($_GET['iDisplayStart'])){
    $start =  getVar('iDisplayStart',  'int');
    $length = getVar('iDisplayLength', 'int');
    if($length != -1) $query_limit = ' LIMIT '.((int)$start).', '.((int)$length);
  }
  $query .= $query_where.
            $query_order.
            $query_limit;
  $this->result = RunQuery($query, __file__, __line__);
}
public static function TotalDisplaying(){
  $query = "SELECT FOUND_ROWS()";
  $result = RunQuery($query, __file__, __line__);
  if(!$result) return(0);
  $row = mysql_fetch_row($result);
  if(count($row) != 1) return(0);
  return($row[0]);
}
public static function TotalAllRows(){global $config;
  $query = "SELECT COUNT(*) as `count` FROM `".$config['table prefix']."Auctions`";
  $result = RunQuery($query, __file__, __line__);
  if(!$result) return(0);
  $row = mysql_fetch_row($result);
  if(count($row) != 1) return(0);
  return($row[0]);
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