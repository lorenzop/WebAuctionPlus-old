<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


function RenderPage_createauction(){global $config,$html,$user,$settings; $output='';
  $id = getVar('id');
  $itemRow = ItemFuncs::QueryItem($user->getName(),$id);
  $Item = &$itemRow['Item'];
$html->addToHeader('
<script type="text/javascript" language="javascript">
function updateTotal(thisfield,otherfieldid){
  otherfield = document.getElementById(otherfieldid);
  document.getElementById("temp").innerHTML = (thisfield.value * otherfield.value);
//  $("temp").update( thisfield.value * otherfield.value );
}
</script>




');
$output.='
<!-- mainTable example -->
<form action="./" method="get">
<input type="hidden" name="page" value="'.$config['page'].'" />
<input type="hidden" name="id" value="'.getVar('id','int').'" />
<input type="hidden" name="" value="" />
<input type="hidden" name="" value="" />
<table border="0" cellpadding="0" cellspacing="0" id="createauctionTable">
<tr><td align="center"><h2>Create a New Sale</h2></td></tr>
<tr><td align="center"><div class="input" style="width: 150px; padding-top: 15px; padding-bottom: 15px; text-align: center;" />
'.
// add enchantments to this link!
//  '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'">'.
  '<img src="images/item_icons/'.$Item->getItemImage().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
  '<br /><b>'.$Item->getItemName().'</b></div></td></tr>
<tr><td height="20"></td></tr>

<tr><td align="center"><b>You have <font size="+2">'.((int)$Item->qty).'</font> items</b></td></tr>
<tr><td><table border="0" cellpadding="0" cellspacing="10" align="center">
<tr>
  <td align="right" ><b>Quantity:</b></td>
  <td><input type="text" name="qty" value="'.((int)$Item->qty).'" id="qty" class="input" style="text-align: center;" '.
    'onkeypress="return numbersonly(this, event);" onchange="updateTotal(this,\'price\');" /></td>
</tr>
<tr>
  <td align="right" ><b>Price Each:</b></td>
  <td><input type="text" name="price" value="" id="price" class="input" style="text-align: center;" '.
    'onkeypress="return numbersonly(this, event);" onchange="updateTotal(this,\'qty\');" /></td>
</tr>
<tr>
  <td align="right" ><b>Price Total:</b></td>
  <td>&nbsp;&nbsp;<font size="+2"><b>$ <span id="temp" class="temp"></span></b></font></td>
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



return($output);

//  require($config['paths']['local']['classes'].'auctions.class.php');
//  $auctions=new AuctionsClass();
//  $config['title'] = 'Current Auctions';
//
//if(isset($_SESSION['error'])) {
//  $output.='<p style="color:red">'.$_SESSION['error'].'</p>';
//  unset($_SESSION['error']);
//}
//if(isset($_SESSION['success'])) {
//  $output.='<p style="color: green;">'.$_SESSION['success'].'</p>';
//  unset($_SESSION['success']);
//}
//
//
//
//// get auctions
//$auctions->QueryAuctions();
//// list auctions
//while($auction = $auctions->getNext()){
//  $Item = &$auction['Item'];
//  $rowClass = 'gradeU';
//  $output.='
//    <tr class="'.$rowClass.'" style="height: 120px;">
//      <td style="padding-bottom: 10px; text-align: center;">'.
//// add enchantments to this link!
////        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'">'.
//        '<img src="images/item_icons/'.$Item->getItemImage().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
//        '<br /><b>'.$Item->getItemName().'</b>';
//  if($Item->itemType=='tool'){
//    $output.='<br />'.$Item->getPercentDamaged().' % damaged';
//    foreach($Item->getEnchantmentsArray() as $ench){
//      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
//    }
//  }
//  $output.='</a></td>
//      <td style="text-align: center;"><img src="./?page=mcface&amp;username='.$auction['playerName'].'" width="32" alt="" /><br />'.$auction['playerName'].'</td>
//      <td style="text-align: center;">expires date<br />goes here</td>
//      <td style="text-align: center;"><b>'.((int)$Item->qty).'</b></td>
//      <td style="text-align: center;">'.@$settings['Currency Prefix'].number_format((double)$auction['price'],2).@$settings['Currency Postfix'].'</td>
//      <td style="text-align: center;">'.@$settings['Currency Prefix'].number_format((double)($auction['price'] * $Item->qty),2).@$settings['Currency Postfix'].'</td>
//      <td style="text-align: center;">market price<br />goes here</td>
//      <td style="text-align: center;">'.
//      ($user->hasPerms('canBuy')?
//        '<form action="./" method="post">'.
//        '<input type="hidden" name="page" value="purchaseItem" />'.
//        '<input type="hidden" name="auctionid" value="'.((int)$auction['id']).'" />'.
//        '<input type="text" name="qty" value="1" onkeypress="return numbersonly(this, event);" class="input" style="width: 60px; margin-bottom: 5px; text-align: center;" /><br />'.
//        '<input type="submit" value="Buy" class="button" /></form>'
//      :$output.="Can't Buy").'</td>
//      '.($user->hasPerms('isAdmin')?
//        '<td style="text-align: center;"><a href="scripts/cancelAuctionAdmin.php?id='.((int)$Item->itemId).'" class="button">Cancel</a></td>':'').'
//    </tr>
//';
//}
//$output.='
//</tbody>
//</table>
//';
//  return($output);
}


?>