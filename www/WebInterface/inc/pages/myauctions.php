<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// my auctions page


if(!$config['user']->isOk()) ForwardTo('./', 0);


if($config['action']=='cancel'){
  CSRF::ValidateToken();
  if(AuctionsClass::RemoveAuction(
    getVar('auctionid','int','post'),
    -1,
    FALSE
  )){
    echo '<center><h2>Auction canceled!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
  }
}


function RenderPage_myauctions(){global $config,$html; $output='';
  $UseAjaxSource = FALSE;
  $config['title'] = 'My Auctions';
  // load page html
  $outputs = RenderHTML::LoadHTML('pages/myauctions.php');
  $html->addTags(array(
    'messages' => ''
  ));
  // load javascript
  $html->addToHeader($outputs['header']);
  // display error
  if(isset($config['error']))
    $config['tags']['messages'] .= str_replace('{message}', $config['error'], $outputs['error']);
  if(isset($_SESSION['error'])){
    $config['tags']['messages'] .= str_replace('{message}', $_SESSION['error'], $outputs['error']);
    unset($_SESSION['error']);
  }
  // display success
  if(isset($_SESSION['success'])){
    $config['tags']['messages'] .= str_replace('{message}', $_SESSION['success'], $outputs['success']);
    unset($_SESSION['success']);
  }
  // list my auctions
  $auctions = new AuctionsClass();
  $auctions->QueryAuctions( "`playerName`='".mysql_san($config['user']->getName())."'" );
  $outputRows = '';
  while($auction = $auctions->getNext()){
    $Item = &$auction['Item'];
    $tags = array(
      'auction id'		=> ((int)$auction['id']),
      'auction expire'		=> 'expires date<br />goes here',
      'auction qty'		=> ((int)$Item->qty),
      'auction price each'	=> FormatPrice($auction['price']),
      'auction price total'	=> FormatPrice($auction['price'] * $Item->qty),
      'item title'		=> $Item->getItemTitle(),
      'item name'			=> $Item->getItemName(),
      'item image url'		=> $Item->getItemImageUrl(),
      'market price percent'	=> 'market price<br />goes here',
      'rowclass'			=> 'gradeU',
    );
//  if($Item->itemType=='tool'){
//    $output.='<br />'.$Item->getDamagedChargedStr();
//    foreach($Item->getEnchantmentsArray() as $ench){
//      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
//    }
//  }
//$marketPrice=getMarketPrice($id, 1);
//if($marketPrice>0){
//  $marketPercent=round((($price/$marketPrice)*100), 1);
//}else{
//  $marketPercent='N/A';
//}if($marketPercent=='N/A'){
//  $grade='gradeU';
//}elseif($marketPercent<=50){
//  $grade='gradeA';
//}elseif($marketPercent<=150){
//  $grade='gradeC';
//}else{
//  $grade='gradeX';
//}
    $htmlRow = $outputs['body row'];
    RenderHTML::RenderTags($htmlRow, $tags);
    $outputRows .= $htmlRow;
  }
  unset($auction, $Item);
  return($outputs['body top']."\n".
         $outputRows."\n".
         $outputs['body bottom']);
}


//if($user->hasPerms('canSell')){
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


//oTable.fnGetPosition(
//  $(\'#auctionrow'.((int)$auction['id']).'\').click
//).slideUp();


//DataTables constructor
//oTable = $('#mainTable').dataTable({
//"bProcessing": true,
//"bServerSide": true,
//"iDisplayLength": 50,
//"bLengthChange": false,
//"sAjaxSource": "datatables_comments_list.php",
//"sPaginationType": "full_numbers",
//"aaSorting": [[ 0, "desc" ]],
//"fnDrawCallback": function() {
//  //bind the click handler script to the newly created elements held in the table
//	$('.flagsmileysad').bind('click',auctioncancelclick);
//}
//});


//<script>
//$("button").click(function(){
//  $(this).slideUp();
//});
//</script>


?>