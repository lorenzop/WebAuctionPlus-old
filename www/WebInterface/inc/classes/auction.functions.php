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

  // split item stack
  $splitStack = ($qty < $Item->getItemQty());
  // create auction
  $query = "INSERT INTO `".$config['table prefix']."Auctions` (".
           "`playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, `price`, `created` )VALUES( ".
           "'".mysql_san($user->getName())."', ".((int)$Item->getItemId()).", ".((int)$Item->getItemDamage()).", ".
           ((int)$qty).", '".mysql_san($Item->getEnchantmentsCompressed())."', ".((float)$price).", NOW() )";
  $result = RunQuery($query, __file__, __line__);
  if(!$result) {echo '<p style="color: red;">Error creating auction!</p>'; exit();}
  $auctionId = mysql_insert_id();
  // subtract qty
  if($splitStack){
    if(!ItemFuncs::UpdateQty($Item->getTableRowId(), 0-$qty, FALSE)){
      echo '<p style="color: red;">Error updating item stack quantity!</p>'; exit();}
  // remove item stack
  }else{
    if(!ItemFuncs::DeleteItem( $Item->getTableRowId() )){
      echo '<p style="color: red;">Error removing item stack quantity!</p>'; exit();}
  }
  // add transaction log
//TODO: this needs to be done yet
//  $Item->qty = $qty;
//  TransactionsClass::addTransactionLog(TransactionType::Create_BuyNow, $Item, $user->getName(), '', $price);
  return(TRUE);
}


// buy/cancel auction
public static function RemoveAuction($auctionId, $qty=-1, $isBuying=FALSE){global $config,$user;








echo __file__.' '.__line__;exit();
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
//  // move enchantments
//  if(!$splitStack){
//    $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
//             "`ItemTable` = 'Items', `ItemTableId` = ".((int)$ItemTableId).
//             " WHERE `ItemTable` = 'Items' AND `ItemTableId` = ".((int)$auctionRow['id']);
//    $result = RunQuery($query, __file__, __line__);
//    if(!$result) {echo '<p style="color: red;">Error moving enchantment!</p>'; exit();}
//  }

  return(TRUE);
}


}
?>