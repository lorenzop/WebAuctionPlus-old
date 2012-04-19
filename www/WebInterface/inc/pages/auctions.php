<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


function RenderPage_auctions(){global $config,$html,$user,$items; $output='';
  require($config['paths']['local']['classes'].'auctions.class.php');
  $auctions=new AuctionsClass();
  $config['title'] = 'Current Auctions';
  // get auctions
  $auctions->QueryAuctions();

$output.='
<p style="color:red">
';
if(isset($_SESSION['error'])) {
  echo  $_SESSION['error'];
  unset($_SESSION['error']);
}
$output.='
</p>
<p style="color: green;">
';
if(isset($_SESSION['success'])) {
  echo  $_SESSION['success'];
  unset($_SESSION['success']);
}
$output.='
</p>
';

$output.='
<div class="demo_jui">
<!-- mainTable example -->
<table border="0" cellpadding="0" cellspacing="0" class="display" id="mainTable">
  <thead>
    <tr style="text-align: center; vertical-align: bottom;">
      <th>Item</th>
      <th>Seller</th>
      <th>Expires</th>
      <th>Qty</th>
      <th>Price (Each)</th>
      <th>Price (Total)</th>
      <th>Percent of<br />Market Price</th>
      <th>Buy</th>
';
if($user->hasPerms('isAdmin')){
$output.='
      <th>Cancel</th>
';
}
$output.='
    </tr>
  </thead>
  <tbody>
';
// list auctions
while($auction = $auctions->getNext()){
  $Item = &$auction['Item'];
  $output.='
    <tr style="height: 120px;">
';
// add enchantments to this link!
  $output.='<td style="padding-bottom: 10px; text-align: center;"><a href="graph.php?name='.$Item->itemId.'&damage='.$Item->itemDamage.'">'.
           '<img src="images/item_icons/'.$Item->getItemImage().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
           '<br /><b>'.$Item->getItemName().'</b>';
  if($Item->itemType=='tool'){
    $output.='<br />'.$Item->getPercentDamaged().' % damaged';
    foreach($Item->getEnchantmentsArray() as $ench){
      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
    }
  }
  $output.='</a></td>
      <td style="text-align: center;"><img src="./?page=mcface&username='.$auction['playerName'].'" width="32" alt="" /><br />'.$auction['playerName'].'</td>
      <td style="text-align: center;">expires</td>
      <td style="text-align: center;">'.((int)$Item->qty).'</td>
      <td style="text-align: center;">'.number_format((double)$auction['price'],2).'</td>
      <td style="text-align: center;">'.number_format((double)($auction['price'] * $Item->qty),2).'</td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;">'.
      ($user->hasPerms('canBuy')?
        '<form action="./" method="post">'.
        '<input type="hidden" name="page" value="purchaseItem" />'.
        '<input type="hidden" name="itemId" value="'.$Item->itemId.'" />'.
        '<input type="text" name="qty" value="1" onKeyPress="return numbersonly(this, event);" class="input" style="width: 60px; text-align: center;" /><br />'.
        '<input type="submit" value="Buy" class="button" /></form>'
      :$output.="Can't Buy").'</td>
      '.($user->hasPerms('isAdmin')?
        '<td style="text-align: center;"><a href="scripts/cancelAuctionAdmin.php?id='.$Item->itemId.'" class="button">Cancel</a></td>':'').'
    </tr>
';
}
$output.='
</tbody>
</table>
</div>
';
  return($output);
}


?>