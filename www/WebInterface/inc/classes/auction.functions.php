<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class is a group of functions to handle auctions
class AuctionFuncs{


// create new auction
public static function CreateAuction($id, $qty, $price, $desc){global $config, $user;
  if($id < 1) return(FALSE);
  // has canSell permissions
  if(!$user->hasPerms('canSell')) {$config['error'] = 'You don\'t have permission to sell.'; return(FALSE);}
  // sanitize args
  $qty = floor((int)$qty);
  if($qty   <= 0){  $config['error'] = 'Invalid qty!';   return(FALSE);}
  if($price <= 0.0){$config['error'] = 'Invalid price!'; return(FALSE);}
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
  // query item
  $Item = QueryItems::QuerySingle($user->getName(), $id);
  if(!$Item){$config['error'] = 'Item not found!'; return(FALSE);}
  if($qty > $Item->getItemQty()){$qty = $Item->getItemQty(); $config['error'] = 'You don\'t have that many!'; return(FALSE);}
  // merge with existing auction
///////////////////////////////////////////////////////////
//TODO: will have a function to check for existing auctions
///////////////////////////////////////////////////////////

  // create auction
  $query = "INSERT INTO `".$config['table prefix']."Auctions` (".
           "`playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, `price`, `created` )VALUES( ".
           "'".mysql_san($user->getName())."', ".((int)$Item->getItemId()).", ".((int)$Item->getItemDamage()).", ".
           ((int)$qty).", '".mysql_san($Item->getEnchantmentsCompressed())."', ".((float)$price).", NOW() )";
  $result = RunQuery($query, __file__, __line__);
  if(!$result) {echo '<p style="color: red;">Error creating auction!</p>'; exit();}
  $auctionId = mysql_insert_id();
  // update qty / remove item stack
  if(!ItemFuncs::RemoveItem( $Item->getTableRowId(), ($qty<$Item->getItemQty() ? $qty : -1) )){
    echo '<p style="color: red;">Error removing item stack quantity!</p>'; exit();}
  // add transaction log
//TODO: this needs to be done yet
//  $Item->qty = $qty;
//  TransactionsClass::addTransactionLog(TransactionType::Create_BuyNow, $Item, $user->getName(), '', $price);
  return(TRUE);
}


// buy auction
public static function BuyAuction($auctionId, $qty){global $config, $user;
  // validate args
  $auctionId = (int) $auctionId;
  if($auctionId < 1) {$config['error'] = 'Invalid auction id!'; return(FALSE);}
  $qty = (int) $qty;
  if($qty < 1) {$config['error'] = 'Invalid qty!'; return(FALSE);}
  // has canBuy permissions
  if(!$user->hasPerms('canBuy')) {$config['error'] = 'You don\'t have permission to buy.'; return(FALSE);}
  // query auction
  $auction = QueryAuctions::QuerySingle($auctionId);
  if(!$auction) {$config['error'] = 'Auction not found!'; return(FALSE);}
  $Item = $auction->getItem();
//  // is item allowed
//  if (!itemAllowed($item->name, $item->damage)){
//    $_SESSION['error'] = $item->fullname.' is not allowed to be sold.';
//    header("Location: ../myauctions.php");
//  }
  // buying validation
  if($auction->getSeller()==$user->getName()){$config['error'] = 'Can\'t buy from yourself!'; return(FALSE);}
  if($qty > $Item->getItemQty()) {$qty = $Item->getItemQty(); $config['error'] = 'Not that many for sale!'; return(FALSE);}
//  $priceTotal = $auction->getPriceTotal();
  $maxSellPrice = SettingsClass::getDouble('Max Sell Price');
  $sellPrice = $auction->getPrice();
  $priceQty = ((int)$auction->getPrice()) * $qty;
  if($sellPrice > $maxSellPrice) {$config['error'] = 'Over max sell price of $ '.$maxSellPrice.' !'; return(FALSE);}
  if($priceQty > $user->getMoney()) {$config['error'] = 'You don\'t have enough money!';                return(FALSE);}
  // make payment from buyer to seller
  UserClass::MakePayment(
    $user->getName(),
    $auction->getSeller(),
    $priceQty,
    'Bought auction '.((int)$auction->getTableRowId()).' '.$Item->getItemTitle().' x'.((int)$Item->getItemQty())
  );
  // remove auction
  if(!self::RemoveAuction($auctionId, ($qty<$Item->getItemQty() ? $qty : -1) )){
    echo '<p style="color: red;">Error removing/updating auction!</p>'; exit();}
  // add to inventory
  $tableRowId = ItemFuncs::AddCreateItem($user->getName(), $Item);
  if(!$tableRowId){echo '<p style="color: red;">Error adding item to your inventory!</p>'; exit();}
  // transaction log
//TODO: TransactionLog::addLog();
  return(TRUE);
}
public static function CancelAuction($auctionId){global $config, $user;
  // validate args
  $auctionId = floor((int)$auctionId);
  if($auctionId < 1) {$config['error'] = 'Invalid auction id!'; return(FALSE);}
  // query auction
  $auction = QueryAuctions::QuerySingle($auctionId);
  if(!$auction) {$config['error'] = 'Auction not found!'; return(FALSE);}
  // isAdmin or owns auction
  if( !$user->hasPerms('isAdmin') && $auction->getSeller() != $user->getName() ) {
    $config['error'] = 'You don\'t own that auction!'; return(FALSE);}
  // remove auction
  self::RemoveAuction($auctionId, -1);
  // add item to inventory
  $tableRowId = ItemFuncs::AddCreateItem($auction->getSeller(), $auction->getItem());
  // transaction log
//TODO: TransactionLog::addLog();
  return(TRUE);
}
// update qty / remove auction
protected static function RemoveAuction($auctionId, $qty=-1){global $config;
  if($auctionId < 1) return(FALSE);
  // remove auction
  if($qty < 0){
    $query = "DELETE FROM `".$config['table prefix']."Auctions` WHERE `id` = ".((int)$auctionId)." LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error removing auction!</p>'; exit();}
  // subtract qty
  }else{
    $query = "UPDATE `".$config['table prefix']."Auctions` SET `qty` = `qty` - ".((int)$qty)." WHERE `id` = ".((int)$auctionId)." LIMIT 1";
    $result = RunQuery($query, __file__, __line__);
    if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error updating auction!</p>'; exit();}
  }
  return(TRUE);
}


}
?>