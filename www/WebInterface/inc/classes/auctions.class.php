<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class is a group of functions to handle auctions
class AuctionsClass{

public $currentId  = 0;
protected $result  = FALSE;
private   $tempRow = FALSE;

function __construct(){
}


// get auctions
public function QueryAuctions($WHERE=''){global $config;
  $this->currentId = 0;
  $tempRow = FALSE;
  $query="SELECT ".
         "Auctions.`id`         AS `id`, ".
         "Auctions.`playerName` AS `playerName`, ".
         "Auctions.`itemId`     AS `itemId`, ".
         "Auctions.`itemDamage` AS `itemDamage`, ".
         "Auctions.`qty`        AS `qty`, ".
         "Auctions.`price`      AS `price`, ".
         "UNIX_TIMESTAMP(Auctions.`created`) AS `created`, ".
         "ItemEnch.`enchName`   AS `enchName`, ".
         "ItemEnch.`enchId`     AS `enchId`, ".
         "ItemEnch.`level`      AS `level` ".
         "FROM `".     $config['table prefix']."Auctions` `Auctions` ".
         "LEFT JOIN `".$config['table prefix']."ItemEnchantments` `ItemEnch` ".
         "ON  Auctions.`id`          = ItemEnch.`ItemTableId` ".
         "AND ItemEnch.`ItemTable` = 'Auctions' ".
         (empty($WHERE)?'':'WHERE '.$WHERE.' ').
         "ORDER BY Auctions.`id` ASC";
  $this->result=RunQuery($query, __file__, __line__);
}


// get next auction row
public function getNext(){
  $tempRow = &$this->tempRow;
  $output  = array();
  // get first row
  if($tempRow==FALSE) $tempRow = mysql_fetch_assoc($this->result);
  if($tempRow==FALSE) return(FALSE);
  $this->currentId      = ((int)  $tempRow['id']);
  $output['id']         = ((int)  $tempRow['id']);
  $output['playerName'] = $tempRow['playerName'];
  $output['price']      = ((float)$tempRow['price']);
  $output['price total']= ((float)$tempRow['price']) * ((float)$tempRow['qty']);
  $output['created']    = ((int)  $tempRow['created']);
  // create item object
  $output['Item'] = new ItemClass(array(
    'itemId'     => ((int)$tempRow['itemId']),
    'itemDamage' => ((int)$tempRow['itemDamage']),
    'qty'        => ((int)$tempRow['qty']) ));
  // get first enchantment
  if(!empty($tempRow['enchName']))
    $output['Item']->addEnchantment($tempRow['enchName'], ((int)$tempRow['enchId']), ((int)$tempRow['level']) );
  // get more rows (enchantments)
  while($tempRow = mysql_fetch_assoc($this->result)){
    if($tempRow['id'] != $this->currentId) break;
    $output['Item']->addEnchantment($tempRow['enchName'], ((int)$tempRow['enchId']), ((int)$tempRow['level']) );
  }
  if(count($output)==0) $output=FALSE;
  return($output);
}


// create new auction
public static function CreateAuction($id, $qty, $price, $desc){global $config,$user;
  if($id < 1) return(FALSE);
  // has canSell permissions
  if(!$user->hasPerms('canSell')){$config['error'] = 'You don\'t have permission to sell.'; return(FALSE);}
  // validate args
  $qty = floor((int)$qty);
  if($qty   <= 0){$config['error'] = 'Invalid qty!';   return(FALSE);}
  if($price <= 0){$config['error'] = 'Invalid price!'; return(FALSE);}
  if(!empty($desc)) $desc = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', strip_tags($desc) );
//  if (!itemAllowed($item->name, $item->damage)){
//    $_SESSION['error'] = $item->fullname.' is not allowed to be sold.';
//    header("Location: ../myauctions.php");
//  }
global $maxSellPrice;
  if($price > $maxSellPrice){$config['error'] = 'Over max sell price of $ '.$maxSellPrice.' !'; return(FALSE);}
  // get item from db
  $itemRow = ItemFuncs::QueryItem($user->getName(),$id);
  if($itemRow === FALSE){$config['error'] = 'Item not found!'; return(FALSE);}
  $Item = &$itemRow['Item'];
  if($qty > $Item->qty){$qty = $Item->qty; $config['error'] = 'You don\'t have that many!'; return(FALSE);}
  // merge with existing auction
///////////////////////////////////////////////////////////
//TODO: will have a function to check for existing auctions
///////////////////////////////////////////////////////////
  // split item stack
  $splitStack = ($qty < $Item->qty);
  // create auction
  $query = "INSERT INTO `".$config['table prefix']."Auctions` (".
           "`playerName`,`itemId`,`itemDamage`,`qty`,`price`,`created` )VALUES( ".
           "'".mysql_san($user->getName())."',".((int)$Item->itemId).",".((int)$Item->itemDamage).",".
           ((int)$qty).",".((float)$price).",NOW() )";
//echo '<p>'.$query.'</p>';$auctionId=0;
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error creating auction!</p>'; exit();}
  $auctionId = mysql_insert_id();
  // subtract qty
  if($splitStack){
    $result = ItemFuncs::UpdateQty($id, 0-$qty, FALSE);
    if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error updating item stack quantity!</p>'; exit();}
  // remove item stack
  }else{
    $result = ItemFuncs::DeleteItem($itemRow['id']);
    if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error removing item stack quantity!</p>'; exit();}
  }
  // copy enchantments
  if($splitStack){
    ItemFuncs::CreateEnchantments($Item->getEnchantmentsArray(), 'Auctions', $auctionId);
  // move enchantments
  }else{
    $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
             "`ItemTable` = 'Auctions', `ItemTableId` = ".((int)$auctionId).
             " WHERE `ItemTable` = 'Items' AND `ItemTableId` = ".((int)$itemRow['id']);
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error moving enchantment!</p>'; exit();}
  }
  return(TRUE);
}


// buy/cancel auction
public static function RemoveAuction($auctionId, $qty, $isBuying=TRUE){global $config,$user;
  if($auctionId < 1) return(FALSE);
  // is buying
  if($isBuying){
    // has canBuy permissions
    if(!$user->hasPerms('canBuy')){
      $config['error'] = 'You don\'t have permission to buy.'; return(FALSE);}
  // is canceling
  }else{
    // is owner or has isAdmin permissions
    if(FALSE || !$user->hasPerms('isAdmin') ){
      $config['error'] = 'You don\'t have permission to cancel this auction.'; return(FALSE);}
echo 'canceling auctions is not finished!!';
exit();
  }
  // validate args
  $qty = floor((int)$qty);
  if($qty <= 0){$config['error'] = 'Invalid qty!'; return(FALSE);}
//  if($price <= 0){$config['error'] = 'Invalid price!'; return(FALSE);}
//  if (!itemAllowed($item->name, $item->damage)){
//    $_SESSION['error'] = $item->fullname.' is not allowed to be sold.';
//    header("Location: ../myauctions.php");
//  }
//global $maxSellPrice;
//  if($price > $maxSellPrice){$config['error'] = 'Over max sell price of $ '.$maxSellPrice.' !'; return(FALSE);}

  // get item from db
  $auctionsClass = new AuctionsClass();
  $auctionsClass->QueryAuctions("Auctions.`id`=".((int)$auctionId));
  $auctionRow = $auctionsClass->getNext();
  if($auctionRow === FALSE){$config['error'] = 'Auction not found!'; return(FALSE);}
  $Item = &$auctionRow['Item'];
  if($qty > $Item->qty){$qty = $Item->qty; $config['error'] = 'Not that many for sale!'; return(FALSE);}

  // make payment from buyer to seller
  UserClass::MakePayment($user->getName(), $auctionRow['playerName'], ((float)$auctionRow['price']) * ((float)$qty), 'Bought auction '.((int)$auctionRow['id']).' '.$Item->getItemTitle().' x'.((int)$Item->qty) );

  // split auction stack
  $splitStack = ($qty < $Item->qty);
  // create item
  $ItemTableId = ItemFuncs::CreateItem('Items', $user->getName(), $Item->itemId, $Item->itemDamage, $qty,$Item->getEnchantmentsArray() );
  // subtract qty
  if($splitStack){
    $query = "UPDATE `".$config['table prefix']."Auctions` SET `qty`=`qty` - ".((int)$qty)." WHERE `id` = ".((int)$auctionRow['id'])." LIMIT 1";
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error updating auction stack quantity!</p>'; exit();}
  // remove auction
  }else{
    $query = "DELETE FROM `".$config['table prefix']."Auctions` WHERE `id` = ".((int)$auctionRow['id'])." LIMIT 1";
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error removing item stack!</p>'; exit();}
  }
  // copy enchantments
  if($splitStack){
// already done by ItemFuncs::CreateItem()
//    foreach($Item->getEnchantmentsArray() as $v){
//      $query = "INSERT INTO `".$config['table prefix']."ItemEnchantments` (".
//               "`ItemTable`,`ItemTableId`,`enchName`,`enchId`,`level`) VALUES(".
//               "'Items',".((int)$auctionId).",".
//               "'".mysql_san($v['enchName'])."','".mysql_san($v['enchId'])."',".((int)$v['level']).")";
//echo '<p>'.$query.'</p>';
//      $result = RunQuery($query, __file__, __line__);
//      if(!$result){echo '<p style="color: red;">Error creating enchantment!</p>'; exit();}
//    }
  // move enchantments
  }else{
    $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
             "`ItemTable` = 'Items', `ItemTableId` = ".((int)$ItemTableId).
             " WHERE `ItemTable` = 'Items' AND `ItemTableId` = ".((int)$auctionRow['id']);
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error moving enchantment!</p>'; exit();}
  }
  return(TRUE);
}


}









//function getMarketPrice($itemTableId, $tableId){
//  $table = '';
//  switch ($tableId){
//    case 0:
//      $table = 'WA_Items';
//      break;
//    case 1:
//      $table = 'WA_Auctions';
//      break;
//    case 2:
//      $table = 'WA_Mail';
//      break;
//    case 3:
//      $table = 'WA_SellPrice';
//      break;
//  }
//  $queryItem = RunQuery("SELECT * FROM $table WHERE id='$itemTableId'", __file__, __line__);
//  $itemRow = mysql_fetch_row($queryItem);
//  $itemId = $itemRow[1];
//  $itemDamage = $itemRow[2];
//  $foundIt = false;
//  $queryMarket = '';
//  //return $itemId;
//
//  $queryEnchantLinks = RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId = '$itemTableId' AND itemTableId = '$tableId'", __file__, __line__);
//  //return mysql_num_rows($queryEnchantLinks);
//  $itemEnchantsArray = array();
//  while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks)){  
//    $itemEnchantsArray[] = $enchIdt;
//  }
//  $queryEnchantLinksMarket = RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '4'", __file__, __line__);
//  $base = isTrueDamage($itemId, $itemDamage);
//  if ($base > 0){
//    if (mysql_num_rows($queryEnchantLinks) == 0){
//      $queryMarket1=RunQuery("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC", __file__, __line__);
//      $maxId = -1;
//      $foundIt = false;
//      //echo 'first';
//      while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1)){
//        $queryMarket2 = RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'", __file__, __line__);
//        if (mysql_num_rows($queryMarket2)== 0){
//          if ($idm > $maxId){
//            $maxId = $idm;
//            $foundIt = true;
//          }
//        }
//      }
//      if ($foundIt){
//        $queryMarket=RunQuery("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC", __file__, __line__);
//        $foundIt = true;
//      }
//    }else{
//      $queryMarket1=RunQuery("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC", __file__, __line__);
//      $maxId = -1;
//      $foundIt = false;
//      //echo 'second';
//      while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1)){
//        $marketEnchantsArray = array ();
//        $queryMarket2 = RunQuery("SELECT enchId FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'", __file__, __line__);
//        while(list($enchIdt)= mysql_fetch_row($queryMarket2)){
//          if ($idm > $maxId){
//            $marketEnchantsArray[] = $enchIdt;
//          }
//        }
//        if((array_diff($itemEnchantsArray, $marketEnchantsArray) == null)&&(array_diff($marketEnchantsArray, $itemEnchantsArray) == null)){
//          $maxId = $idm;
//          $foundIt = true;
//        }
//        //print_r($itemEnchantsArray);
//      }
//      if ($foundIt){
//        $queryMarket=RunQuery("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC", __file__, __line__);
//        $foundIt = true;
//      }
//    }
//  }else{
//    $queryMarket=RunQuery("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='$itemDamage' ORDER BY id DESC", __file__, __line__);
//    $foundIt = true;
//  }
//  if ($foundIt==false){
//    //market price not found
//    //echo 'cant find';
//    return 0;
//  }else{
//    //found get first item
//    $rowMarket = mysql_fetch_row($queryMarket);
//    $marketId = $rowMarket[0];
//    if ($base > 0){$marketPrice = ($rowMarket[4]/$base)*($base - $itemDamage);
//    }else{         $marketPrice = $rowMarket[4];}
//    return round($marketPrice, 2);
//  }
//}







//// Script:    DataTables server-side script for PHP and MySQL
//// Copyright: 2010 - Allan Jardine
//// License:   GPL v2 or BSD (3-point)
//$aColumns=array(
//  0=>'itemId',
//  1=>'itemDamage',
//  2=>'playerName',
//  3=>'qty',
//  4=>'price',
//  5=>'id',
//  6=>'UNIX_TIMESTAMP(`created`) AS `created`'
//);
//$sIndexColumn="id";
//// DB table to use
//$sTable="WA_Auctions";
//
//
//// Paging
//$sLimit="";
//if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength']!=-1){
//  $sLimit="LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
//    mysql_real_escape_string( $_GET['iDisplayLength'] );
//}
//
//// Ordering
//if(isset($_GET['iSortCol_0'])){
//  $sOrder="ORDER BY ";
//  for($i=0; $i<intval($_GET['iSortingCols']); $i++){
//    if($_GET['bSortable_'.intval($_GET['iSortCol_'.$i])]=='true'){
//      $sOrder.=$aColumns[intval( $_GET['iSortCol_'.$i] ) ]."
//         ".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
//    }
//  }
//
//  $sOrder=substr_replace( $sOrder, "", -2 );
//  if($sOrder=="ORDER BY"){
//    $sOrder='';
//  }
//}
//
//// Filtering
//// NOTE this does not match the built-in DataTables filtering which does it
//// word by word on any field. It's possible to do here, but concerned about efficiency
//// on very large tables, and MySQL's regex functionality is very limited
//$sWhere="";
//if(isset($_GET['sSearch'])){
//  if($_GET['sSearch']!=''){
//    $sWhere="WHERE (";
//    for($i=0; $i<count($aColumns); $i++){
//      $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
//    }
//    $sWhere=substr_replace( $sWhere, "", -3 );
//    $sWhere .= ')';
//  }
//}
//// Individual column filtering
//for($i=0; $i<count($aColumns); $i++){
//  if(isset($_GET['bSearchable_'.$i])){
//    if($_GET['bSearchable_'.$i]=='true' && $_GET['sSearch_'.$i]!=''){
//      if ($sWhere==''){
//        $sWhere="WHERE ";
//      }else{
//        $sWhere.=" AND ";
//      }
//      $sWhere.=$aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
//    }
//  }
//}
//
//// SQL queries
//// Get data to display
//if (isset($sOrder)){
//$sQuery=" SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)).
//        " FROM $sTable $sWhere $sOrder $sLimit ";
//}else{
//  $sQuery=" SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)).
//          " FROM $sTable $sWhere $sLimit ";
//}
//$rResult=RunQuery($sQuery, __file__, __line__);
//
//// Data set length after filtering
//$sQuery=" SELECT FOUND_ROWS() ";
//$rResultFilterTotal=RunQuery($sQuery, __file__, __line__);
//$aResultFilterTotal=mysql_fetch_array($rResultFilterTotal);
//$iFilteredTotal=$aResultFilterTotal[0];
//
//// Total data set length
//$sQuery=" SELECT COUNT(".$sIndexColumn.") FROM $sTable ";
//$rResultTotal=RunQuery($sQuery, __file__, __line__);
//$aResultTotal=mysql_fetch_array($rResultTotal);
//$iTotal=$aResultTotal[0];
//
//// Output
//if (isset($_GET['sEcho'])){
//  $output=array(
//    "sEcho"               => intval($_GET['sEcho']),
//    "iTotalRecords"       => $iTotal,
//    "iTotalDisplayRecords"=> $iFilteredTotal,
//    "aaData"              => array()
//  );
//}
//
//if(mysql_num_rows($rResult)==0){
//  return;
//}
//
//while($aRow=mysql_fetch_assoc($rResult)){
//  $row=array();
//  $quantity   =$aRow['qty'];
//  $timeCreated=$aRow['created'];
//  if($timeCreated==0){
//echo 'no created date set!';
//exit();
//  }
//  if($quantity == 0 || (time()-$timeCreated) < $config['auction duration']){
//    $itemName    =$aRow['itemId'];
//    $fullItemName=$items->getItemHtmlTitle($aRow['itemId'], $aRow['itemDamage']);
//    $itemDamage  =$aRow['itemDamage'];
//    $marketPrice =getMarketPrice($aRow['itemId'], 1);
//    if($marketPrice>0){
//      $marketPercent=round((($aRow['price']/$marketPrice)*100), 1);
//    }else{
//      $marketPercent='N/A';
//    }
//    if($marketPercent=='N/A'){
//      $marketPercent=0;
//      $grade='gradeU';
//    }else if ($marketPercent <= 50){
//      $grade='gradeA';
//    }else if ($marketPercent <= 150){
//      $grade='gradeC';
//    }else{
//      $grade='gradeX';
//    }
//    $row['DT_RowClass']=$grade;
//    $theId=$aRow['id'];
//    $tempString='';
//
//
//return;
//
//
//    $queryEnchantLinks=RunQuery("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$theId' AND itemTableId='1'", __file__, __line__);
//    //print_r(mysql_fetch_row($queryEnchantLinks));
//    while(list($enchId)= mysql_fetch_row($queryEnchantLinks)){ 
//      $queryEnchants=RunQuery("SELECT * FROM WA_Enchantments WHERE id='$enchId'", __file__, __line__); 
//      while(list($id, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants)){ 
//        $tempString=$tempString.'<br />'.getEnchName($enchantId).' '.numberToRoman($level);
//      }
//
//    }
//    // alt="'.$fullItemName.'"
//    $row[]='<a href="graph.php?name='.$aRow['name'].'&damage='.$aRow['damage'].'">'.
//           '<img src="'.getItemImage($aRow['name'],$aRow['damage']).'" /><br />'.
//           $fullItemName.$tempString.'</a>';
//    $row[]='<img width="32" src="scripts/mcface.php?username='.$aRow['player'].'" /><br />'.
//           $aRow['player'];
//    if($quantity==0){
//      $row[]='Never';
//    }else{
//      $row[]=date('jS M Y H:i:s', $timeCreated+$config['auction duration']);
//    }
//    $row[]=number_format($aRow['quantity'],0);
//    $row[]='$ '.number_format($aRow['price'],2);
//    $row[]='$ '.number_format( ((float)$aRow['quantity']) * ((float)$aRow['price']) ,2);
//    if($marketPercent=='N/A'){
//      $row[]='N/A';
//    }else{
//      $row[]=number_format($marketPercent,1).' %';
//    }



//echo 'expiring auction';
//exit();
//    $user        =$aRow['playerName'];
//    $id          =$aRow['id'];
//    $itemName    =$aRow['itemId'];
//    $itemDamage  =$aRow['itemDamage'];
//    $itemQuantity=$aRow['qty'];
//    $queryPlayerItems=RunQuery("SELECT * FROM WA_Items WHERE player='$user'", __file__, __line__);
//    $foundItem=FALSE;
//    $stackId=0;
//    $stackQuant=0;
//    $queryEnchantLinks=RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId='1'", __file__, __line__);
//    //return mysql_num_rows($queryEnchantLinks);
//    $itemEnchantsArray=array ();
//
//    while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)=mysql_fetch_row($queryEnchantLinks)){  
//      $itemEnchantsArray[]=$enchIdt;
//    }
//
//    while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)=mysql_fetch_row($queryPlayerItems)){  
//      if($itemName==$pitemName){
//        if($pitemDamage==$itemDamage){
//          $queryEnchantLinksMarket=RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemTableId='0' AND itemId='$pid'", __file__, __line__);
//          $marketEnchantsArray=array();
//          while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinksMarket)){  
//            $marketEnchantsArray[]=$enchIdt;
//          }  
//          if((array_diff($itemEnchantsArray, $marketEnchantsArray)==null) && (array_diff($marketEnchantsArray, $itemEnchantsArray)==null)){
//            $foundItem=TRUE;
//            $stackId=$pid;
//            $stackQuant=$pitemQuantity;
//          }
//        }
//      }
//    }
//    if($foundItem==TRUE){
//      $newQuantity=$itemQuantity + $stackQuant;
//      $itemQuery=RunQuery("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'", __file__, __line__);
//    }else{
//      $itemQuery=RunQuery("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')", __file__, __line__);
//      $queryLatestAuction=RunQuery("SELECT id FROM WA_Items ORDER BY id DESC", __file__, __line__);
//      list($latestId)= mysql_fetch_row($queryLatestAuction);
//
//      $queryEnchants=RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId ='1'", __file__, __line__); 
//      while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants)){
//        $updateEnch=RunQuery("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '0', '$latestId')", __file__, __line__);
//      }
//    }
//    $itemDelete=RunQuery("DELETE FROM WA_Auctions WHERE id='$id'", __file__, __line__);
//  }


?>