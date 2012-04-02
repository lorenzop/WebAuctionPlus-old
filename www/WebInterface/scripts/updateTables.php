<?php
    $now = time();
	
	add_column_if_not_exist("WA_SellPrice", "seller", "VARCHAR( 255 ) NULL");
	add_column_if_not_exist("WA_SellPrice", "buyer", "VARCHAR( 255 ) NULL");
	add_column_if_not_exist("WA_Players", "canBuy", "INT(11) NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Players", "canSell", "INT(11) NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Players", "isAdmin", "INT(11) NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Players", "itemsSold", "INT(11) NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Players", "itemsBought", "INT(11) NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Players", "earnt", "DOUBLE NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Players", "spent", "DOUBLE NOT NULL DEFAULT  '0'");
	add_column_if_not_exist("WA_Auctions", "created", "INT(11) NULL");
	add_column_if_not_exist("WA_Auctions", "allowBids", "INT(1) NOT NULL DEFAULT '0'");
	add_column_if_not_exist("WA_Auctions", "currentBid", "DOUBLE NULL");
	add_column_if_not_exist("WA_Auctions", "currentWinner", "VARCHAR( 255 ) NULL");


function add_column_if_not_exist($table, $column, $column_attr){
    $exists = false;
    $columns = mysql_query("show columns from $table");
    while($c = mysql_fetch_assoc($columns)){
        if($c['Field'] == $column){
            $exists = true;
            break;
        }
    }      
    if(!$exists){
        mysql_query("ALTER TABLE `$table` ADD `$column`  $column_attr");
    }
}

?>