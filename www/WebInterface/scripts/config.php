<?php


/* Database config */

$db_host		= 'mywebsite.com'; //change these!!
$db_user		= 'username';//change these!!
$db_pass		= 'password1234';//change these!!
$db_database	= 'minecraft'; //change these!!

/* Market Price config */

$maxSellPrice = 10000; //this is per item
$marketDaysMin	= 30; //number of past days to take the average of the sales for to work out market price (bigger number for smaller servers)

/* Design config */

$uiPack = "start"; //name of the jquery ui pack you would like to use, "start" or "dark-hive" come installed by default find more @ http://jqueryui.com/themeroller/
$cssFile = "main"; //will be collecting a list of cool css files, but "main" is my default one

/* Auction fees and config */

$auctionLength = 10; //days before auction is ended, and items returned to owner
$auctionFee = 1; //% of the market price you are charged to auction the item
$chargeAdmins = false; //whether web admins get charged fees

/* Storage config */

$costPerItemPerDay = 0.20; //cost to store 1 item for a day, per item not per stack eg. 64 x Wool + 10 x Stone = 14.8 per day when cost = 0.20
$numberOfChecksPerDay = 4; //checks take a while, for whoever happens to open the page when a check is needed, but more checks make it harder to avoid the cost.

/* Currency config */

$currencyPrefix = "$"; //appears in front of cost values eg. "$" would make $10
$currencyPostfix = ""; //appears after the cost values eg. "Pounds" would make 10 Pounds

/* iConomy config */

$useMySQLiConomy = false; //you you have iConomy data in another table in the same database?
$iConTableName = "iConomy"; //"iConomy" is the default table name when using MySQL with iConomy

/* Mail config */

$sendPurchaceToMail = false; //if false send to my items, if true add to mail

/* Twitter config */

$useTwitter = false;
$consumerKey = "";
$consumerSecret = "";
$accessToken = "";
$accessTokenSecret = "";

/* End config */

$currencyPostfix = " ".$currencyPostfix;
$marketTimeMin = $marketDaysMin * 86400;
$auctionDurationSec = $auctionLength * 86400;
$link = mysql_connect($db_host,$db_user,$db_pass);
if (!$link){die("Unable to connect to database".mysql_error());}

mysql_select_db($db_database,$link);
mysql_query("SET names UTF8");

?>
