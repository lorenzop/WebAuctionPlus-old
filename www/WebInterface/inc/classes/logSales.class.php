<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// sales log class
class LogSales{

const LOG_NEW    = 'new';
const LOG_SALE   = 'sale';
const LOG_CANCEL = 'cancel';

const SALE_BUYNOW  = 'buynow';
const SALE_AUCTION = 'auction';


public static function addLog($logType, $saleType, $sellerName, $buyerName, $Item, $price, $allowBids, $currentWinner, $alert=0){global $config;
  $query = "INSERT INTO `".$config['table prefix']."LogSales` ( ".
           "`logType`, `saleType`, `timestamp`, `itemType`, `itemId`, `itemDamage`, `itemTitle`, `enchantments`, `seller`, `buyer`, `qty`, `price`, `alert` ) VALUES ( ".
           (($logType  == self::LOG_NEW     || $logType  == self::LOG_SALE || $logType == self::LOG_CANCEL) ? "'".mysql_san($logType )."'" : 'NULL' ).", ".
           (($saleType == self::SALE_BUYNOW || $saleType == self::SALE_AUCTION                            ) ? "'".mysql_san($saleType)."'" : 'NULL' ).", ".
           "NOW(), ".
           "'".mysql_san($Item->getItemType())."', ".
           ((int) $Item->getItemId()).", ".
           ((int) $Item->getItemDamage()).", ".
           "'".mysql_san($Item->getItemTitle())."', ".
           "'".mysql_san($Item->getEnchantmentsCompressed())."', ".
           ($sellerName == NULL ? 'NULL' : "'".mysql_san($sellerName)."'").", ".
           ($buyerName  == NULL ? 'NULL' : "'".mysql_san($buyerName )."'").", ".
           ((int) $Item->getItemQty()).", ".
           ((float) $price).", ".
           ((int) $alert)." )";
  $result = RunQuery($query, __file__, __line__);
  if(!$result || mysql_affected_rows()==0){echo '<p style="color: red;">Error logging sale!</p>'; exit();}
}


}
?>