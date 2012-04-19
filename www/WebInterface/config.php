<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
$config['version']='1.0.3';


// Database config
function ConnectDB(){global $db,$config;
  $host     = 'localhost';
  $port     = 3306;
  $username = 'minecraft';
  $password = 'password123';
  $database = 'minecraft';
  $config['table prefix'] = 'WA_';
  $db=@mysql_pconnect($host.($port==0?'':':'.((int)$port)),$username,$password);
  if(!$db || !@mysql_select_db($database,$db)){echo '<p>MySQL Error: '.mysql_error().'</p>'; exit();}
  mysql_query("SET names UTF8");
}

$config['site title'] = 'WebAuctionPlus'; // website title

// Market Price config
$maxSellPrice  = 10000; //this is per item
$marketDaysMin = 30;    //number of past days to take the average of the sales for to work out market price (bigger number for smaller servers)
$marketTimeMin = $marketDaysMin * 86400;

// Design config
$config['ui Pack'] = 'redmond'; //name of the jquery ui pack you would like to use, "start" or "dark-hive" come installed by default find more @ http://jqueryui.com/themeroller/

// Auction fees and config
$auctionLength = 14; //days before auction is ended, and items returned to owner
// will move this setting to the database
$config['auction duration'] = $auctionLength * 86400;
$auctionFee = 0; //% of the market price you are charged to auction the item
$chargeAdmins = false; //whether web admins get charged fees

// Storage config
//$costPerItemPerDay = 0.00; //cost to store 1 item for a day, per item not per stack eg. 64 x Wool + 10 x Stone = 14.8 per day when cost = 0.20
//$numberOfChecksPerDay = 4; //checks take a while, for whoever happens to open the page when a check is needed, but more checks make it harder to avoid the cost.

// Currency config
$currencyPrefix = '$ '; //appears in front of cost values eg. "$" would make $10
$currencyPostfix = ''; //appears after the cost values eg. "Pounds" would make 10 Pounds

// iConomy config
$config['iConomy']['use']   = 'auto';    // ( true / false / 'auto' )  you you have iConomy data in another table in the same database?
$config['iConomy']['table'] = 'iConomy'; // "iConomy" is the default table name when using MySQL with iConomy

// Mail config
$sendPurchaceToMail = false; //if false send to my items, if true add to mail

// Twitter config
$useTwitter = false;
$consumerKey = '';
$consumerSecret = '';
$accessToken = '';
$accessTokenSecret = '';


?>