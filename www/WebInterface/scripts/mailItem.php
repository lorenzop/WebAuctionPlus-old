<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'config.php';
	require 'itemInfo.php';
	
	$itemId = mysql_real_escape_string(stripslashes($_GET['id']));
	$queryItems=mysql_query("SELECT * FROM WA_Items WHERE id='$itemId'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity)= mysql_fetch_row($queryItems);
	$maxStack = getItemMaxStack($itemName, $itemDamage);
	
	if ($user == $itemOwner){
		while($itemQuantity > $maxStack)
		{
			$itemQuantity -= $maxStack;
			$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$maxStack')");
			$queryLatestAuction = mysql_query("SELECT id FROM WA_Mail ORDER BY id DESC");
			list($latestId)= mysql_fetch_row($queryLatestAuction);
			$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$itemId' AND itemTableId=0"); 
			while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
			{ 
				$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$itemId' AND itemTableId ='0'"); 
				while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
				{ 
					$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '2', '$latestId')");
				}
			}
		}
		if ($itemQuantity > 0)
		{
			$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
			$queryLatestAuction = mysql_query("SELECT id FROM WA_Mail ORDER BY id DESC");
			list($latestId)= mysql_fetch_row($queryLatestAuction);
			$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$itemId' AND itemTableId=0"); 
			while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
			{ 
				$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$itemId' AND itemTableId ='0'"); 
				while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
				{ 
					$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '2', '$latestId')");
				}
			}
		}
		$itemDelete = mysql_query("DELETE FROM WA_Items WHERE id='$itemId'");
		header("Location: ../myitems.php");
	}else {
		header("Location: ../myitems.php?error=1");
	}

?>