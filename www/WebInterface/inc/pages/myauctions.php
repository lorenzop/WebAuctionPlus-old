<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// my auctions page


function RenderPage_myauctions(){global $config,$html,$user; $output='';
  $UseAjaxSource = FALSE;
  require($config['paths']['local']['classes'].'auctions.class.php');
  $auctions=new AuctionsClass();
  $config['title'] = 'My Auctions';

$html->addToHeader('
  <script type="text/javascript" language="javascript" charset="utf-8">
  $(document).ready(function() {
    oTable = $(\'#mainTable\').dataTable({
      "sZeroRecords"      : "No auctions to display",
      "bJQueryUI"         : true,
      "bStateSave"        : true,
      "iDisplayLength"    : 5,
      "aLengthMenu"       : [[5, 10, 30, 100, -1], [5, 10, 30, 100, "All"]],
      "sPaginationType"   : "full_numbers",
      "sPagePrevEnabled"  : true,
      "sPageNextEnabled"  : true,
'.($UseAjaxSource?'
      "bProcessing"       : true,
      "sAjaxSource"       : "scripts/server_processing.php",
':'').'
    });
  } );
  </script>
');
//$html->addToHeader('
//    <script type="text/javascript" charset="utf-8">
//      $(document).ready(function() {
//        oTable=$(\'#example\').dataTable({
//          "bJQueryUI": true,
//          "sPaginationType": "full_numbers"
//        });
//      });
//    </script>
//');

if(isset($_SESSION['error'])) {
  $output.='<p style="color:red">'.$_SESSION['error'].'</p>';
  unset($_SESSION['error']);
}
if(isset($_SESSION['success'])) {
  $output.='<p style="color: green;">'.$_SESSION['success'].'</p>';
  unset($_SESSION['success']);
}

$output.='
<!-- mainTable example -->
<table border="0" cellpadding="0" cellspacing="0" class="display" id="mainTable">
  <thead>
    <tr style="text-align: center; vertical-align: bottom;">
      <th>Item</th>
      <th>Expires</th>
      <th>Qty</th>
      <th>Price (Each)</th>
      <th>Price (Total)</th>
      <th>Percent of<br />Market Price</th>
      <th>Cancel</th>
    </tr>
  </thead>
  <tbody>
';


if($user->hasPerms('canSell')){
//$queryItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
//$output.='
//    <div id="new-auction-box">
//      <h2>Create a new auction</h2>
//      <form action="scripts/newAuction.php" method="post" name="auction">
//      <table style="text-align:left;" width="100%">
//      <tr>
//        <td width="50%"><label>Item</label></td><td width="50%"><select name="Item" class="select">
//';
//while(list($id, $name, $damage, $player, $quantity)=mysql_fetch_row($queryItems)){
//  $marketPrice=getMarketPrice($id, 0);
//  if($marketPrice==0){
//    $marketPrice="N/A";
//  }
//  echo '<option value="'.$id.'">'.getItemName($name, $damage);
//  $queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId=0");
//  while(list($enchId)= mysql_fetch_row($queryEnchantLinks)){
//    $queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'");
//    while(list($id, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants)){
//      echo ' ('.getEnchName($enchantId).' '.numberToRoman($level).")";
//    }
//  }
//  echo '('.$quantity.') (Average '.$currencyPrefix.$marketPrice.$currencyPostfix.')';
//  echo '</option>'."\n";
//}
//
//        </select></td>
//        <tr><td colspan="2" style="text-align:center;">
//        <p>
//          if($isAdmin){ echo "Enter 0 as the quantity for infinite stacks (admins only)"; } 
//        </p>
//        </td></tr>
//        <tr><td><label>Quantity</label></td><td><input name="Quantity" type="text" class="input" size="10" /></td></tr>
//        <tr><td><label>Price (Per Item)</label></td><td><input name="Price" type="text" class="input" size="10" /></td></tr>
//        <!--<tr><td colspan="2" style="text-align:center;"><p>Leave starting bid blank to disable bidding</p></td></tr>
//        <tr><td><label>Starting Bid (Per Item)</label></td><td><input name="MinBid" type="text" class="input" size="10" /></td></tr> -->
//        <tr><td colspan="2" style="text-align:center;"><input name="Submit" type="submit" class="button" /></td></tr>
//        </table>
//      </form>
//    </div>
//';
}


// get my auctions
$auctions->QueryAuctions( "`playerName`='".mysql_san($user->getName())."'" );
// list auctions
while($auction = $auctions->getNext()){
//  $marketPrice=getMarketPrice($id, 1);
//  if($marketPrice>0){
//    $marketPercent=round((($price/$marketPrice)*100), 1);
//  }else{
//    $marketPercent='N/A';
//  }if($marketPercent=='N/A'){
//    $grade='gradeU';
//  }elseif($marketPercent<=50){
//    $grade='gradeA';
//  }elseif($marketPercent<=150){
//    $grade='gradeC';
//  }else{
//    $grade='gradeX';
//  }
  $Item = &$auction['Item'];
  $rowClass = 'gradeU';
  $output.='
    <tr class="'.$rowClass.'" style="height: 120px;">
      <td style="padding-bottom: 10px; text-align: center;">'.
// ($quantity==0?'Never':date('jS M Y H:i:s', $timeCreated + $auctionDurationSec) ).'</td>
//    <td class="center">'.($marketPercent=='N/A'?'N/A':number_format($marketPercent,1).' %').'</td>
// add enchantments to this link!
//        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'">'.
        '<img src="images/item_icons/'.$Item->getItemImage().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
        '<br /><b>'.$Item->getItemName().'</b>';
  if($Item->itemType=='tool'){
    $output.='<br />'.$Item->getPercentDamaged().' % damaged';
    foreach($Item->getEnchantmentsArray() as $ench){
      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
    }
  }
  $output.='</a></td>
      <td style="text-align: center;">expires date<br />goes here</td>
      <td style="text-align: center;"><b>'.((int)$Item->qty).'</b></td>
      <td style="text-align: center;">'.number_format((double)$auction['price'],2).'</td>
      <td style="text-align: center;">'.number_format((double)($auction['price'] * $Item->qty),2).'</td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;"><a href="./?page='.$config['page'].'&amp;action=cancel&amp;auctionid='.((int)$auction['id']).'" class="button">Cancel</a></td>
    </tr>
';
}
unset($auctions);
$output.='
</tbody>
</table>
';
  return($output);
}


?>