<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// my items page


// mail item stack
if($config['action'] == 'mailitem'){
  ItemFuncs::MailStack( getVar('id',  'int') );
  if(!empty($config['error'])){
    echo '<p style="color: red;">'.$config['error'].'</font>';
    exit();
  }
}


function RenderPage_myitems(){global $config,$html,$user; $output='';
  $UseAjaxSource = FALSE;
  $items = new ItemsClass();
  $config['title'] = 'My Items';

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
<table border="0" cellpadding="0" cellspacing="0" class="display" id="mainTable">
  <thead>
    <tr style="text-align: center; vertical-align: bottom;">
      <th>Item</th>
      <th>Qty</th>
      <th>Market Price (Each)</th>
      <th>Market Price (Total)</th>
      <th>Sell Item</th>
      <th>Mail Item</th>
    </tr>
  </thead>
  <tbody>
';


// get items
$items->QueryItems($user->getName(),'');
// list items
while($itemRow = $items->getNext()){
  $Item = &$itemRow['Item'];
  $rowClass = 'gradeU';
  $output.='
    <tr class="'.$rowClass.'" style="height: 120px;">
      <td style="padding-bottom: 10px; text-align: center;">'.
// add enchantments to this link!
//        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'">'.
        '<img src="'.$Item->getItemImageUrl().'" alt="'.$Item->getItemTitle().'" style="margin-bottom: 5px;" />'.
        '<br /><b>'.$Item->getItemName().'</b>';
  if($Item->itemType=='tool'){
    $output.='<br />'.$Item->getPercentDamaged();
    foreach($Item->getEnchantmentsArray() as $ench){
      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
    }
  }
//      <td style="text-align: center;">'.number_format((double)$auction['price'],2).'</td>
//      <td style="text-align: center;">'.number_format((double)($auction['price'] * $Item->qty),2).'</td>
  $output.='</a></td>
      <td style="text-align: center;"><b>'.((int)$Item->qty).'</b></td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;"><a href="./?page=createauction&amp;id='.((int)$itemRow['id']).'" class="button" data-toggle="modal">Sell it</a></td>
      <td style="text-align: center;"><a href="./?page='.$config['page'].'&amp;action=mailitem&amp;id='.((int)$itemRow['id']).'" class="button">Mail it</a></td>
    </tr>
    <div style="display: none;" id="myModal" class="modal hide fade">
            <div class="modal-header">
              <button class="close" data-dismiss="modal">×</button>
              <h3>Modal Heading</h3>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
              <a href="#" class="btn" data-dismiss="modal">Close</a>
              <a href="#" class="btn btn-primary">Save changes</a>
            </div>
          </div>

<script src="js/bootstrap.js"></script>


';
//  $marketPrice=getMarketPrice($id, 0);
//  $marketTotal=$marketPrice*$quantity;
//  if($marketPrice==0){
//    $marketPrice='0';
//    $marketTotal='0';
//  }
//  echo '  <tr class="gradeC">'."\n";
}
unset($items);
$output.='
</tbody>
</table>
';
  return($output);
}


?>
