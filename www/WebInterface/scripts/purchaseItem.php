<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = trim($_SESSION['User']);
	$canBuy = $_SESSION['canBuy'];
	if ($canBuy == false){
		$_SESSION['error'] = 'You do not have permission to buy that.';
		header("Location: ../index.php");
	}
	require 'config.php';
	require 'itemInfo.php';
	require_once '../classes/Auction.php';
    require_once '../classes/EconAccount.php';
	if ($useTwitter == true){require_once 'twitter.class.php';}

    $itemId = mysql_real_escape_string(stripslashes($_POST['ID']));
    $numberLeft = 0;

    $player = new EconAccount($user, $useMySQLiConomy, $iConTableName);
	$auction = new Auction($itemId);

    $owner = new EconAccount($auction->owner, $useMySQLiConomy, $iConTableName);
	
	$itemEnchantsArray = $auction->getEnchantmentArray();
	
    if (is_numeric($_POST['Quantity'])&& ($_POST['Quantity'] > 0))
    {
	    $buyQuantity = mysql_real_escape_string(stripslashes(round(abs($_POST['Quantity']))));
    }
    elseif ($_POST['Quantity'] <= 0)
    {
        $_SESSION['error'] = "Please enter a quantity greater than 0";
        header("Location: ../index.php");
        return;
    }
    else{
        $buyQuantity = $auction->quantity;
    }
	$toDelete = false;
    $totalPrice = round($auction->price*$buyQuantity, 2);
	$numberLeft = $auction->quantity-$buyQuantity;

	if (($numberLeft < 0)&&($auction->quantity > 0)){
        $_SESSION['error'] = "You are attempting to purchase more than the maximum available";
		header("Location: ../index.php");
	}
	else{

	if ($player->money >= $totalPrice){
		if ($user != $auction->owner){
			$timeNow = time();
			$player->spend($totalPrice, $useMySQLiConomy, $iConTableName);
			$owner->earn($totalPrice, $useMySQLiConomy, $iConTableName);
            $alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$auction->owner', '$buyQuantity', '$auction->price', '$user', '$auction->fullname')");

			if ($sendPurchaceToMail){
				$maxStack = getItemMaxStack($auction->name, $auction->damage);
				while($buyQuantity > $maxStack)
				{
					$buyQuantity -= $maxStack;
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$auction->name', '$auction->damage', '$user', '$maxStack')");
					$queryLatestAuction = mysql_query("SELECT id FROM WA_Mail ORDER BY id DESC");
					list($latestId)= mysql_fetch_row($queryLatestAuction);
					$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$auction->id' AND itemTableId=1"); 
					while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
					{ 
						$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$auction->id' AND itemTableId ='1'"); 
						while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
						{ 
							$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '2', '$latestId')");
						}
					}
				}
				if ($buyQuantity > 0)
				{
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$auction->name', '$auction->damage', '$user', '$buyQuantity')");
					$queryLatestAuction = mysql_query("SELECT id FROM WA_Mail ORDER BY id DESC");
					list($latestId)= mysql_fetch_row($queryLatestAuction);
					$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$auction->id' AND itemTableId=1"); 
					while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
					{ 
						$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$auction->id' AND itemTableId ='1'"); 
						while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
						{ 
							$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '2', '$latestId')");
						}
					}
				}
				$queryLatestAuction = mysql_query("SELECT id FROM WA_Mail ORDER BY id DESC");
				list($latestId)= mysql_fetch_row($queryLatestAuction);
			}else{
				$queryPlayerItems =mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
				$foundItem = false;
				$stackId = 0;
				$stackQuant = 0;
				while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)= mysql_fetch_row($queryPlayerItems))
				{	
					if($auction->name == $pitemName)
					{
						if ($auction->damage == $pitemDamage)
						{
							$queryEnchantLinksMarket = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '0' AND itemId = '$pid'");
							$marketEnchantsArray = array ();
							while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinksMarket))
							{  
								$marketEnchantsArray[] = $enchIdt;
							}	
							if((array_diff($itemEnchantsArray, $marketEnchantsArray) == null)&&(array_diff($marketEnchantsArray, $itemEnchantsArray) == null)){
								$foundItem = true;
								$stackId = $pid;
								$stackQuant = $pitemQuantity;
							}
						}
					}
				}
				if ($foundItem == true)
				{
					$newQuantity = $buyQuantity + $stackQuant;
					$itemQuery = mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
				}else
				{
					$itemQuery = mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$auction->name', '$auction->damage', '$user', '$buyQuantity')");
					$queryLatestAuction = mysql_query("SELECT id FROM WA_Items ORDER BY id DESC");
					list($latestId)= mysql_fetch_row($queryLatestAuction);
					
						$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$auction->id' AND itemTableId ='1'"); 
						while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
						{ 
							$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '0', '$latestId')");
						}
					
				}
			}
			if ($auction->quantity > 0){
				if ($numberLeft != 0)
				{
					$itemDelete = mysql_query("UPDATE WA_Auctions SET quantity='$numberLeft' WHERE id='$auction->id'");
				}else{
					$toDelete = true;
				}
			}
			$logPrice = mysql_query("INSERT INTO WA_SellPrice (name, damage, time, buyer, seller, quantity, price) VALUES ('$auction->name', '$auction->damage', '$timeNow', '$user', '$auction->owner', '$buyQuantity', '$auction->price')");
			$queryLatestAuction = mysql_query("SELECT id FROM WA_SellPrice ORDER BY id DESC");
			list($latestId)= mysql_fetch_row($queryLatestAuction);
			
				$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$itemId' AND itemTableId ='1'"); 
				while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
				{ 
					$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '3', '$latestId')");
				}
			$base = isTrueDamage($auction->name, $auction->damage);
			if ($base > 0){
				$queryEnchantLinksMarket = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '4'");
				$foundIt = false;
				if (count($itemEnchantsArray) == 0){
					$queryMarket1=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$auction->name' AND damage='0' ORDER BY id DESC");
					$maxId = -1;
					while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1))
					{	
						$queryMarket2 = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'");
						if (mysql_num_rows($queryMarket2)== 0){
							if ($idm > $maxId){
								$maxId = $idm;
								$foundIt = true;
							}	
						}
					}
					if ($foundIt){
						$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC");
						$foundIt = true;
					}
				}
				else {
					$queryMarket1=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$auction->name' AND damage='0' ORDER BY id DESC");
					$maxId = -1;
					$foundIt = false;
					while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1))
					{
						$marketEnchantsArray = array ();
						$queryMarket2 = mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'");
						while(list($enchIdt)= mysql_fetch_row($queryMarket2))
						{
							if ($idm > $maxId){
								$marketEnchantsArray[] = $enchIdt;
							
							}
						}
						if((array_diff($itemEnchantsArray, $marketEnchantsArray) == null)&&(array_diff($marketEnchantsArray, $itemEnchantsArray) == null)){
							$maxId = $idm;
							$foundIt = true;
						}
					
					}
					if ($foundIt){
						$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC");
						$foundIt = true;
					}
				
				}
				if ($foundIt == false){
						$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE id = '-1' ORDER BY id DESC");
					}

			}else{
				$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$auction->name' AND damage='$auction->damage' ORDER BY id DESC");	

			}
			$countMarket = mysql_num_rows($queryMarket);
			if ($countMarket == 0){
				//market price not found
				$newMarketPrice = $auction->price;
				$marketCount = $buyQuantity;
			}else{
				//found get first item
				
				$rowMarket = mysql_fetch_row($queryMarket);
				$marketId = $rowMarket[0];
				$marketPrice = $rowMarket[4];
				$marketCount = $rowMarket[5];
				$newMarketPrice = (($marketPrice*$marketCount)+$totalPrice)/($marketCount+$buyQuantity);
				$marketCount = $marketCount+$buyQuantity;
				
			}
			if ($base > 0){
				
				$newMarketPrice = ($newMarketPrice/($base - $itemDamage))*$base;
				
				$insertMarketPrice = mysql_query("INSERT INTO WA_MarketPrices (name, damage, time, marketprice, ref) VALUES ('$auction->name', '0', '$timeNow', '$newMarketPrice', '$marketCount')");
				$queryLatestAuction = mysql_query("SELECT id FROM WA_MarketPrices ORDER BY id DESC");
				list($latestId)= mysql_fetch_row($queryLatestAuction);
				
					$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$auction->id' AND itemTableId ='1'"); 
					while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
					{ 
						$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '4', '$latestId')");
					}
				
			}else{
				
				$insertMarketPrice = mysql_query("INSERT INTO WA_MarketPrices (name, damage, time, marketprice, ref) VALUES ('$auction->name', '$auction->damage', '$timeNow', '$newMarketPrice', '$marketCount')");
			}
			if ($useTwitter == true){
				try{
				$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
				$twitter->send('[WA] Item Bought: '.$user.' bought '.$buyQuantity.' x '.$auction->fullname.' for '.$currencyPrefix.$itemPrice.$currencyPostfix.' each from '.$itemOwner.'. At '.date("H:i:s").'. '.$shortLinkToAuction.' #webauction');
				}catch (Exception $e){
			   		//may have reached twitter daily limit
				}
			}
			$player->buyItem($buyQuantity);
			$owner->sellItem($buyQuantity);
            $_SESSION['success'] = "You purchased $buyQuantity $auction->fullname from $auction->owner for ".$currencyPrefix.$totalPrice.$currencyPostfix.".";
			if ($toDelete){
				$auction->delete();
			}
			header("Location: ../index.php");

		}else {
            $_SESSION['error'] = 'You cannnot buy your own items.';
			header("Location: ../index.php");
		}
	}else{
        $_SESSION['error'] = 'You do not have enough money.';
        header("Location: ../index.php");
	}
	}

?>