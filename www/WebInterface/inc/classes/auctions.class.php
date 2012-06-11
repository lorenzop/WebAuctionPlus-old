<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class is a group of functions to handle auctions
class AuctionsClass{

public $currentId  = 0;
protected $result  = FALSE;
protected $tempRow = FALSE;

function __construct(){
}


// get auctions
public function QueryAuctions($WHERE='', $limit=-1){global $config;
  $this->currentId = 0;
  $tempRow = FALSE;
  // WHERE
  $WHERE = trim($WHERE);
  if(!empty($WHERE)){
    if(!startsWith($WHERE, 'WHERE ', TRUE))
      $WHERE = 'WHERE '.$WHERE;
    $WHERE .= ' ';
  }
  // LIMIT
  if($limit > 0) $limit = ' LIMIT '.((int)$limit);
  else           $limit = '';
  $query = "SELECT ".
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
           "AND ItemEnch.`ItemTable` = 'Auctions' ".$WHERE.
           "ORDER BY Auctions.`id` ASC".$limit;
  $this->result = RunQuery($query, __file__, __line__);
}


// get next auction row
public function getNext(){
  $tempRow = &$this->tempRow;
  $output  = array();
  // get first row
  if($tempRow == FALSE) $tempRow = mysql_fetch_assoc($this->result);
  if($tempRow == FALSE) return(FALSE);
  $this->currentId       = ((int)  $tempRow['id']);
  $output['id']          = ((int)  $tempRow['id']);
  $output['playerName']  = $tempRow['playerName'];
  $output['price']       = ((float)$tempRow['price']);
  $output['price total'] = ((float)$tempRow['price']) * ((float)$tempRow['qty']);
  $output['created']     = ((int)  $tempRow['created']);
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
  if(count($output) == 0) $output=FALSE;
  return($output);
}


// create new auction
public static function CreateAuction($id, $qty, $price, $desc){global $config,$user;
  if($id < 1) return(FALSE);
  // has canSell permissions
  if(!$user->hasPerms('canSell')) {$config['error'] = 'You don\'t have permission to sell.'; return(FALSE);}
  // validate args
  $qty = floor((int)$qty);
  if($qty   <= 0){$config['error'] = 'Invalid qty!';   return(FALSE);}
  if($price <= 0){$config['error'] = 'Invalid price!'; return(FALSE);}
  if(!empty($desc)){
    $desc = preg_replace('/<[^>]*>/', '', $desc);
    $desc = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', strip_tags($desc) );
  }
//  if (!itemAllowed($item->name, $item->damage)){
//    $_SESSION['error'] = $item->fullname.' is not allowed to be sold.';
//    header("Location: ../myauctions.php");
//  }
  $maxSellPrice = SettingsClass::getDouble('Max Sell Price');
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
  $result = RunQuery($query, __file__, __line__);
  if(!$result) {echo '<p style="color: red;">Error creating auction!</p>'; exit();}
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
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error moving enchantment!</p>'; exit();}
  }
  // log new buynow/auction
  $Item->qty = $qty;
  TransactionsClass::addTransactionLog(TransactionType::Create_BuyNow, $Item, $user->getName(), '', $price);
  return(TRUE);
}


// buy/cancel auction
public static function RemoveAuction($auctionId, $qty=-1, $isBuying=FALSE){global $config,$user;
  // has canBuy permissions
  if($isBuying && !$user->hasPerms('canBuy')){
    $config['error'] = 'You don\'t have permission to buy.'; return(FALSE);}
  // validate args
  $auctionId = floor((int)$auctionId);
  if($auctionId < 1) {$config['error'] = 'Invalid auction id!'; return(FALSE);}
  if($isBuying){
    $qty = floor((int)$qty);
    if($qty < 1) {$config['error'] = 'Invalid qty!'; return(FALSE);}
  }

  // get item from db
  $auctionsClass = new AuctionsClass();
  $auctionsClass->QueryAuctions("Auctions.`id`=".((int)$auctionId), 1);
  $auctionRow = $auctionsClass->getNext();
  if($auctionRow === FALSE) {$config['error'] = 'Auction not found!'; return(FALSE);}
  $Item = &$auctionRow['Item'];

  // buying validation
  if($isBuying){
    if($qty > $Item->qty) {$qty = $Item->qty; $config['error'] = 'Not that many for sale!'; return(FALSE);}
    $priceTotal = ((float)$auctionRow['price']) * ((float)$qty);
    $maxSellPrice = SettingsClass::getDouble('Max Sell Price');
    if(((float)$auctionRow['price']) > $maxSellPrice){
      $config['error'] = 'Over max sell price of $ '.$maxSellPrice.' !'; return(FALSE);}
    if($priceTotal > $user->Money){
      $config['error'] = 'You don\'t have enough money!';                return(FALSE);}
    // is item allowed
//    if (!itemAllowed($item->name, $item->damage)){
//      $_SESSION['error'] = $item->fullname.' is not allowed to be sold.';
//      header("Location: ../myauctions.php");
//    }
  // canceling validation
  }else{
    // isAdmin or owns auction
    if( !$user->hasPerms('isAdmin') && $auctionRow['playerName']!=$user->getName() ) {
      $config['error'] = 'You don\'t own that auction!'; return(FALSE);}
    $qty = $Item->qty;
  }

  // make payment from buyer to seller
  if($isBuying)
    UserClass::MakePayment(
      $user->getName(),
      $auctionRow['playerName'],
      $priceTotal,
      'Bought auction '.((int)$auctionRow['id']).' '.$Item->getItemTitle().' x'.((int)$Item->qty)
    );

  // split auction stack
  $splitStack = ($qty < $Item->qty);
// TODO: check for existing stack in inventory
  // create item
  $ItemTableId = ItemFuncs::CreateItem('Items', $user->getName(), $Item->itemId, $Item->itemDamage, $qty,$Item->getEnchantmentsArray() );
  // subtract qty
  if($splitStack){
    $query = "UPDATE `".$config['table prefix']."Auctions` SET `qty`=`qty` - ".((int)$qty)." WHERE `id` = ".((int)$auctionRow['id'])." LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result) {echo '<p style="color: red;">Error updating auction stack quantity!</p>'; exit();}
  // remove auction
  }else{
    $query = "DELETE FROM `".$config['table prefix']."Auctions` WHERE `id` = ".((int)$auctionRow['id'])." LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result) {echo '<p style="color: red;">Error removing item stack!</p>'; exit();}
  }
  // move enchantments
  if(!$splitStack){
    $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
             "`ItemTable` = 'Items', `ItemTableId` = ".((int)$ItemTableId).
             " WHERE `ItemTable` = 'Items' AND `ItemTableId` = ".((int)$auctionRow['id']);
    $result = RunQuery($query, __file__, __line__);
    if(!$result) {echo '<p style="color: red;">Error moving enchantment!</p>'; exit();}
  }

  return(TRUE);
}


}
?>