<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


if(!$config['user']->isOk()) ForwardTo('./', 0);


if($config['action']=='newauction'){
  CSRF::ValidateToken();
  // inventory is locked
  if($config['user']->isLocked()){
    echo '<center><h2>Your inventory is currently locked.<br />Please close your in game inventory and try again.</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 4);
    exit();
  }
  if(AuctionFuncs::Sell(
    getVar('id'   ,'int'   ,'post'),
    getVar('qty'  ,'int'   ,'post'),
    getVar('price','double','post'),
    getVar('desc' ,'string','post')
  )){
    echo '<center><h2>Auction created successfully!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
  }
}

// inventory is locked
if($config['user']->isLocked()){
  echo '<center><h2>Your inventory is currently locked.<br />Please close your in game inventory and try again.</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
  ForwardTo(getLastPage(), 4);
  exit();
}


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


function RenderPage_sell(){global $config,$html,$user,$settings; $output='';
  $id        = getVar('id', 'int');
  $qty       = getVar('qty');
  $priceEach = getVar('price', 'double');
  // query item
  $Item = QueryItems::QuerySingle($user->getName(), $id);
  if(!$Item) return('<h2 style="text-align: center;">The item you\'re trying to sell couldn\'t be found!</h2>');
//echo '<pre>';print_r($Item);exit();
  if(empty($qty)) $qty = $Item->getItemQty();
  if($priceEach == 0.0){
    $priceEach  = '';
    $priceTotal = '';
  }else{
    $priceTotal = ((double)$priceEach) * ((double)$qty);
  }
$html->addToHeader('
<script type="text/javascript" language="javascript">
function updateTotal(thisfield,otherfieldid){
  otherfield = document.getElementById(otherfieldid);
  document.getElementById("pricetotal").innerHTML = (thisfield.value * otherfield.value);
//  $("pricetotal").update( thisfield.value * otherfield.value );
}
</script>
');
//if(isset($_SESSION['error'])) {
//  $output.='<p style="color:red">'.$_SESSION['error'].'</p>';
//  unset($_SESSION['error']);
//}
//if(isset($_SESSION['success'])) {
//  $output.='<p style="color: green;">'.$_SESSION['success'].'</p>';
//  unset($_SESSION['success']);
//}


$output.='
<!-- mainTable example -->
<form action="./" method="post">
{token form}
<input type="hidden" name="page"     value="'.$config['page'].'" />
<input type="hidden" name="action"   value="newauction" />
<input type="hidden" name="lastpage" value="'.getLastPage().'" />
<input type="hidden" name="id"       value="'.getVar('id','int').'" />
<table border="0" cellpadding="0" cellspacing="0" id="createauctionTable">
';
// input errors
if(!isset($config['error']))
  if(!$user->hasPerms('canSell'))
    $config['error'] = 'You don\'t have permission to sell.';
if(isset($config['error']))
  $output.='<tr><td align="center" style="padding-top: 20px; color: red; font-size: larger;">'.$config['error'].'</td></tr>';
// add enchantments to this link!
//  '<a href="./?page=graph&amp;name='.((int)$Item->getItemId()).'&amp;damage='.$Item->getItemDamage().'">'.' .
$output.='
<tr><td align="center"><h2>Create a New Auction</h2></td></tr>
<tr><td align="center"><div class="input" style="width: 150px; padding-top: 15px; padding-bottom: 15px; text-align: center;">'.$Item->getDisplay().'</div></td></tr>
<tr><td height="20"></td></tr>

<tr><td align="center"><b>You have <font size="+2">'.((int)$Item->getItemQty()).'</font> items</b></td></tr>
<tr><td><table border="0" cellpadding="0" cellspacing="10" align="center">
<tr>
  <td align="right"><b>Quantity:</b></td>
  <td><div style="position: absolute; margin-top: 10px; margin-left: 8px; font-size: larger; font-weight: bold;">x</div>'.
    '<input type="text" name="qty" value="'.((int)$qty).'" id="qty" class="input" style="width: 160px; text-align: center;" '.
    'onkeypress="return numbersonly(this, event);" onchange="updateTotal(this,\'price\');" /></td>
</tr>
<tr>
  <td align="right"><b>Price Each:</b></td>
  <td><div style="position: absolute; margin-top: 8px; margin-left: 8px; font-size: larger; font-weight: bold;">'.SettingsClass::getString('Currency Prefix').'</div>'.
    '<input type="text" name="price" value="'.$priceEach.'" id="price" class="input" style="width: 160px; text-align: center;" '.
    'onkeypress="return numbersonly(this, event);" onchange="updateTotal(this,\'qty\');" />'.
    '<b>&nbsp;'.SettingsClass::getString('Currency Postfix').'</b></td>
</tr>
<tr>
  <td align="right"><b>Price Total:</b></td>
  <td><div style="position: absolute; margin-top: 8px; margin-left: 8px; font-size: larger; font-weight: bold;">'.SettingsClass::getString('Currency Prefix').'</div>'.
    '<div id="pricetotal" class="input" style="float: left; width: 160px; text-align: center; font-size: larger; font-weight: bold;">&nbsp;</div>'.
    '<div style="margin-top: 8px;"><b>&nbsp;'.SettingsClass::getString('Currency Postfix').'</b></div></td>
</tr>
</table></td></tr>
<tr><td height="20"></td></tr>
';

// custom descriptions
if(SettingsClass::getString('Custom Descriptions')) $output.='
<tr><td colspan="2" align="center">&nbsp;&nbsp;<b>Description:</b> (optional)</td></tr>
<tr><td height="10"></td></tr>
<tr><td colspan="2" align="center"><textarea name="desc" class="input" style="width: 80%; height: 55px;" readonly>Coming soon!</textarea></td></tr>
<tr><td height="30"></td></tr>
';

$output.='
<tr><td colspan="2" align="center"><input type="submit" value="Create Auction" class="input" /></td></tr>
<tr><td height="30"></td></tr>
</table>
</form>
';
  unset($Item);
  return($output);
}


?>