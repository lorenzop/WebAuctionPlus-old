<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


function RenderPage_auctions(){global $config,$html,$user,$settings; $output='';
  $UseAjaxSource = FALSE;
  $auctions=new AuctionsClass();
  $config['title'] = 'Current Auctions';

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
//  var info = $(\'.dataTables_info\')
//  $(\'tfoot\').append(info);
  </script>
');

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


// get auctions
$auctions->QueryAuctions();
// list auctions
while($auction = $auctions->getNext()){
  $Item = &$auction['Item'];
  $rowClass = 'gradeU';
  $output.='
    <tr class="'.$rowClass.'" style="height: 120px;">
      <td style="padding-bottom: 10px; text-align: center;">'.
// add enchantments to this link!
//        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'"></a>'.
        '<img src="images/item_icons/'.$Item->getItemImage().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
        '<br /><b>'.$Item->getItemName().'</b>';
  if($Item->itemType=='tool'){
    $output.='<br />'.$Item->getPercentDamagedString();
    foreach($Item->getEnchantmentsArray() as $ench){
      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
    }
  }
  $output.='</td>
      <td style="text-align: center;"><img src="./?page=mcface&amp;username='.$auction['playerName'].'" width="32" alt="" /><br />'.$auction['playerName'].'</td>
      <td style="text-align: center;">expires date<br />goes here</td>
      <td style="text-align: center;"><b>'.((int)$Item->qty).'</b></td>
      <td style="text-align: center;">'.FormatPrice($auction['price']             ).'</td>
      <td style="text-align: center;">'.FormatPrice($auction['price'] * $Item->qty).'</td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;">'.
      ($user->hasPerms('canBuy')?
        '<form action="./" method="get">'.
        '<input type="hidden" name="page" value="buyauction" />'.
        '<input type="hidden" name="action" value="buy" />'.
        '<input type="hidden" name="auctionid" value="'.((int)$auction['id']).'" />'.
        '<input type="text" name="qty" value="'.((int)$Item->qty).'" onkeypress="return numbersonly(this, event);" '.
          'class="input" style="width: 60px; margin-bottom: 5px; text-align: center;" /><br />'.
        '<input type="submit" value="Buy" class="button" />'.
        '</form>'
      :$output.="Can't Buy").'</td>
      '.($user->hasPerms('isAdmin')?
//        '<td style="text-align: center;"><a href="./?id='.((int)$Item->itemId).'" class="button">Cancel</a></td>':'').'
        '<td style="text-align: center;"><input type="button" value="Cancel" class="button"'.
        ' onclick="alert(\'Im sorry, this feature has been temporarily left out to get other things working. This button will be working again in the next update.\');" /></td>':'').'
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