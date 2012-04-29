<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


function CreateAuction($id,$qty,$price){global $config,$user;
  if(!$user->hasPerms('canSell'))
    $config['error'] = 'You don\'t have permission to sell.';
  if($id <= 0) return;
//  if (!itemAllowed($item->name, $item->damage)){
//    $_SESSION['error'] = $item->fullname.' is not allowed to be sold.';
//    header("Location: ../myauctions.php");
//  }
  $qty = floor($qty);
  if($qty   <= 0){$config['error'] = 'Invalid qty!';   return;}
  if($price <= 0){$config['error'] = 'Invalid price!'; return;}
global $maxSellPrice;
  if($price > $maxSellPrice){$config['error'] = 'Over max sell price of $ '.$maxSellPrice.' !'; return;}
  // get item row
  $itemRow = ItemFuncs::QueryItem($user->getName(),$id);
  if($itemRow === FALSE){ $config['error'] = 'Item not found!'; return;}
  $Item = &$itemRow['Item'];
  if($qty > $Item->qty){$config['error'] = 'You don\'t have that many!'; return;}
  // split item stack
  $splitStack = ($qty < $Item->qty);

  // create auction
  $query = "INSERT INTO `".$config['table prefix']."Auctions` (".
           "`playerName`,`itemId`,`itemDamage`,`qty`,`price`,`created` )VALUES( ".
           "'".mysql_san($user->getName())."',".((int)$Item->itemId).",".((int)$Item->itemDamage).",".
           ((int)$qty).",".((double)$price).",NOW() )";
//echo '<p>'.$query.'</p>';$auctionId=0;
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p style="color: red;">Error creating auction!</p>'; exit();}
  $auctionId = mysql_insert_id();
  // subtract qty
  if($splitStack){
    $query = "UPDATE `".$config['table prefix']."Items` SET `qty`=`qty` - ".((int)$qty)." WHERE `id` = ".((int)$itemRow['id'])." LIMIT 1";
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error updating item stack quantity!</p>'; exit();}
  // remove item stack
  }else{
    $query = "DELETE FROM `".$config['table prefix']."Items` WHERE `id` = ".((int)$itemRow['id'])." LIMIT 1";
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error removing item stack!</p>'; exit();}
  }
  // copy enchantments
  if($splitStack){
    foreach($Item->getEnchantmentsArray() as $v){
      $query = "INSERT INTO `".$config['table prefix']."ItemEnchantments` (".
               "`ItemTable`,`ItemTableId`,`enchName`,`enchId`,`level`) VALUES(".
               "'Auctions',".((int)$auctionId).",".
               "'".mysql_san($v['enchName'])."','".mysql_san($v['enchId'])."',".((int)$v['level']).")";
//echo '<p>'.$query.'</p>';
      $result = RunQuery($query, __file__, __line__);
      if(!$result){echo '<p style="color: red;">Error creating enchantment!</p>'; exit();}
    }
  // move enchantments
  }else{
    $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
             "`ItemTable` = 'Auctions', `ItemTableId` = ".((int)$auctionId).
             " WHERE `ItemTable` = 'Items' AND `ItemTableId` = ".((int)$itemRow['id']);
//echo '<p>'.$query.'</p>';
    $result = RunQuery($query, __file__, __line__);
    if(!$result){echo '<p style="color: red;">Error moving enchantment!</p>'; exit();}
  }

$lastpage = getVar('lastpage');
if(empty($lastpage)) $lastpage = './';
echo '<center>Auction created successfully!<br /><a href="'.$lastpage.'">Back to last page</a></center>';
exit();

}
if($config['action']=='newauction')
  CreateAuction( getVar('id'), getVar('qty'), getVar('price') );



////	$minBid = mysql_real_escape_string(stripslashes(round($_POST['MinBid'], 2)));
//$minBid=0;
//	$allowBids = 1;
//	if (mysql_real_escape_string(stripslashes($_POST['MinBid'])) == ""){
//		$allowBids = 0;
//	}

//					if ($isAdmin){						
//						if ($chargeAdmins){
//							$itemFee = (($item->marketprice/100)*$auctionFee)*$sellQuantity;
//						}else{
//							$itemFee = 0;
//						}
//						if ($player->money >= $itemFee){
//							$item->changeQuantity(0 - $sellQuantity);
//							$player->spend($itemFee, $useMySQLiConomy, $iConTableName);
//$itemQuery = mysql_query("INSERT INTO WA_Auctions (name, damage, player, quantity, price, created, allowBids, currentBid, currentWinner) ".
//                         "VALUES ('$item->name', '$item->damage', '$item->owner', '$sellQuantity', '$sellPrice', NOW(), '$allowBids', '$minBid', '$item->owner')");
//							$queryLatestAuction = mysql_query("SELECT id FROM WA_Auctions ORDER BY id DESC");
//							list($latestId)= mysql_fetch_row($queryLatestAuction);
//							if ($item->quantity == 0)
//							{
//								$item->delete();
//							}
//							if ($useTwitter == true){
//								try{
//								$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
//								if ($sellQuantity == 0){
//									$twitQuant = "Infinite";
//								}else{
//									$twitQuant = $sellQuantity;
//								}
//								$twitter->send('[WA] Auction Created: '.$user.' is selling '.$twitQuant.' x '.$itemFullName.' for '.$currencyPrefix.$sellPrice.$currencyPostfix.' each. At '.date("H:i:s").' #webauction');
//								}catch (Exception $e){
//									//normally means you reached the daily twitter limit.
//								}
//							}
//							$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$item->id' AND itemTableId ='0'"); 
//							while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
//							{ 
//								$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '1', '$latestId')");
//							}
//						
//							$_SESSION['success'] = "You auctioned $sellQuantity $itemFullName for ".$currencyPrefix.$sellPrice.$currencyPostfix." each, the fee was ".$currencyPrefix.$itemFee.$currencyPostfix;
//							header("Location: ../myauctions.php");
//						}else
//						{
//						$_SESSION['error'] = 'Fee cost '.$currencyPrefix.$itemFee.$currencyPostfix.', you did not have enough money.';
//						header("Location: ../myauctions.php");
//						}
//					}else{
//						if ($sellQuantity > 0){							
//							$itemFee = (($item->marketprice/100)*$auctionFee)*$sellQuantity;
//							if ($player->money >= $itemFee){
//								$item->changeQuantity(0 - $sellQuantity);
//								$player->spend($itemFee, $useMySQLiConomy, $iConTableName);
//$itemQuery = mysql_query("INSERT INTO WA_Auctions (name, damage, player, quantity, price, created, allowBids, currentBid, currentWinner) ".
//                         "VALUES ('$item->name', '$item->damage', '$item->owner', '$sellQuantity', '$sellPrice', NOW(), '$allowBids', '$minBid', '$item->owner')");
//								$queryLatestAuction = mysql_query("SELECT id FROM WA_Auctions ORDER BY id DESC");
//								list($latestId)= mysql_fetch_row($queryLatestAuction);
//								if ($item->quantity == 0)
//								{
//									$item->delete();
//								}
//								if ($useTwitter == true){
//									try{
//									$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
//									$twitter->send('[WA] Auction Created: '.$user.' is selling '.$sellQuantity.' x '.$itemFullName.' for '.$currencyPrefix.$sellPrice.$currencyPostfix.' each. At '.date("H:i:s").'. '.$shortLinkToAuction.' #webauction');
//									}catch (Exception $e){
//										//normally means you reached the daily twitter limit.
//									}
//								}
//								$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$item->id' AND itemTableId ='0'"); 
//								while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
//								{ 
//									$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '1', '$latestId')");
//								}
//						
//								$_SESSION['success'] = "You auctioned $sellQuantity $itemFullName for ".$currencyPrefix.$sellPrice.$currencyPostfix." each, the fee was ".$currencyPrefix.$itemFee.$currencyPostfix;
//								header("Location: ../myauctions.php");
//							}else
//							{
//							$_SESSION['error'] = 'Fee cost '.$currencyPrefix.$itemFee.$currencyPostfix.', you did not have enough money.';
//							header("Location: ../myauctions.php");
//							}
//						}else
//						{
//							$_SESSION['error'] = 'Quantity was not an integer.';
//							header("Location: ../myauctions.php");
//						}
//					}
//				}else
//				{
//				    $_SESSION['error'] = 'You do not have enough of that item.';
//					header("Location: ../myauctions.php");
//				}
//			}else
//			{
//				$_SESSION['error'] = 'Quantity was not an integer.';
//				header("Location: ../myauctions.php");
//			}
//		}else
//		{
//			$_SESSION['error'] = 'Price was not an integer.';
//			header("Location: ../myauctions.php");
//		}
//	}
//	}


function RenderPage_createauction(){global $config,$html,$user,$settings; $output='';
  $id         = getVar('id');
  $qty        = getVar('qty');
  $priceEach  = getVar('price');
  $itemRow = ItemFuncs::QueryItem($user->getName(),$id);
  if($itemRow === FALSE) return('<h2 style="text-align: center;">The item you\'re trying to sell couldn\'t be found!</h2>');
//echo '<pre>';print_r($itemRow);exit();
  $Item    = &$itemRow['Item'];
  if(empty($qty)) $qty = $Item->qty;
  $qty = (int)$qty;
  $priceEach  = (double)$priceEach;
  if($priceEach == 0){
    $priceEach  = '';
    $priceTotal = '';
  }else{
    $priceTotal = (double)($priceEach * ((double)$qty));
  }
//$html->addToHeader('
//<script type="text/javascript" language="javascript">
//function updateTotal(thisfield,otherfieldid){
//  otherfield = document.getElementById(otherfieldid);
//  document.getElementById("temp").innerHTML = (thisfield.value * otherfield.value);
////  $("temp").update( thisfield.value * otherfield.value );
//}
//</script>
//');
//if(isset($_SESSION['error'])) {
//  $output.='<p style="color:red">'.$_SESSION['error'].'</p>';
//  unset($_SESSION['error']);
//}
//if(isset($_SESSION['success'])) {
//  $output.='<p style="color: green;">'.$_SESSION['success'].'</p>';
//  unset($_SESSION['success']);
//}


$lastpage = getVar('lastpage');
if(empty($lastpage)) $lastpage = @$_SERVER['HTTP_REFERER'];

$output.='
<!-- mainTable example -->
<form action="./" method="get">
<input type="hidden" name="page"     value="'.$config['page'].'" />
<input type="hidden" name="action"   value="newauction" />
<input type="hidden" name="lastpage" value="'.$lastpage.'" />
<input type="hidden" name="id"       value="'.getVar('id','int').'" />
<table border="0" cellpadding="0" cellspacing="0" id="createauctionTable">
';
// input errors
if(!isset($config['error']))
  if(!$user->hasPerms('canSell'))
    $config['error'] = 'You don\'t have permission to sell.';
if(isset($config['error']))
  $output.='<tr><td align="center" style="padding-top: 20px; color: red; font-size: larger;">'.$config['error'].'</td></tr>';
$output.='
<tr><td align="center"><h2>Create a New Auction</h2></td></tr>
<tr><td align="center"><div class="input" style="width: 150px; padding-top: 15px; padding-bottom: 15px; text-align: center;" />
'.
// add enchantments to this link!
//  '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'">'.
  '<img src="images/item_icons/'.$Item->getItemImage().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
  '<br /><b>'.$Item->getItemName().'</b><font size="-2"><b>';
foreach($itemRow['Item']->getEnchantmentsArray() as $v){
  $output.='<br />'.$v['enchName'].' '.$v['level'];
}
$output.='</b></font></div></td></tr>
<tr><td height="20"></td></tr>

<tr><td align="center"><b>You have <font size="+2">'.((int)$Item->qty).'</font> items</b></td></tr>
<tr><td><table border="0" cellpadding="0" cellspacing="10" align="center">
<tr>
  <td align="right" ><b>Quantity:</b></td>
  <td><div style="position: absolute; margin-top: 10px; margin-left: 8px; font-size: larger; font-weight: bold;">x</div>'.
    '<input type="text" name="qty" value="'.((int)$qty).'" id="qty" class="input" style="width: 160px; text-align: center;" '.
    'onkeypress="return numbersonly(this, event);" onchange="updateTotal(this,\'price\');" /></td>
</tr>
<tr>
  <td align="right" ><b>Price Each:</b></td>
  <td><div style="position: absolute; margin-top: 8px; margin-left: 8px; font-size: larger; font-weight: bold;">$</div>'.
    '<input type="text" name="price" value="'.$priceEach.'" id="price" class="input" style="width: 160px; text-align: center;" '.
    'onkeypress="return numbersonly(this, event);" onchange="updateTotal(this,\'qty\');" /></td>
</tr>
<tr>
  <td align="right" ><b>Price Total:</b></td>
  <td><font size="+2"><b><div style="position: absolute;">$</div><div id="temp" class="temp" style="width: 185px; text-align: center;">&nbsp;'.$priceTotal.'&nbsp;</div></b></font></td>
</tr>
</table></td></tr>

<tr><td height="20"></td></tr>
<tr><td colspan="2" align="center">&nbsp;&nbsp;<b>Description:</b> (optional)</td></tr>
<tr><td height="10"></td></tr>
<tr><td colspan="2" align="center"><textarea name="" class="input" style="width: 80%; height: 55px;" readonly>Coming soon!</textarea></td></tr>
<tr><td height="30"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Create Auction" class="input" /></td></tr>
<tr><td height="30"></td></tr>
</table>
</form>
';
  unset($itemRow,$Item);
  return($output);
}


?>