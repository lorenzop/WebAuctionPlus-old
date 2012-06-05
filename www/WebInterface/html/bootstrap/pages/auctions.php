<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page
$outputs=array();


$outputs['header']='
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
    });
  } );
  </script>
';
//      "bProcessing"       : true,
//      "sAjaxSource"       : "./?page={page}&server_processing=true",
//  var info = $(\'.dataTables_info\')
//  $(\'tfoot\').append(info);


$outputs['body top']='
{messages}
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
{if permission[canBuy]}
      <th>Buy</th>
{endif}
{if permission[isAdmin]}
      <th>Cancel</th>
{endif}
    </tr>
  </thead>
  <tbody>
';


$outputs['body row']='
    <tr class="{rowclass}" style="height: 120px;">
      <td style="padding-bottom: 10px; text-align: center;">'.
        '<img src="{item image url}" alt="{item title}" style="margin-bottom: 5px;" /><br /><b>{item name}</b></td>
      <td style="text-align: center;"><img src="http://minotar.net/avatar/{auction seller name}" width="32" alt="" /><br />{auction seller name}</td>
      <td style="text-align: center;">{auction expire}</td>
      <td style="text-align: center;"><b>{auction qty}</b></td>
      <td style="text-align: center;">{auction price each}</td>
      <td style="text-align: center;">{auction price total}</td>
      <td style="text-align: center;">{market price percent}</td>
{if permission[canBuy]}
      <td style="text-align: center;">
        <form action="./" method="get">
        <input type="hidden" name="page"      value="{page}" />
        <input type="hidden" name="action"    value="buy" />
        <input type="hidden" name="auctionid" value="{auction id}" />
        <input type="text" name="qty" value="{auction qty}" onkeypress="return numbersonly(this, event);" '.
          'class="input" style="width: 60px; margin-bottom: 5px; text-align: center;" /><br />
        <input type="submit" value="Buy" class="button" />
        </form>
      </td>
{endif}
{if permission[isAdmin]}
        <td style="text-align: center;"><input type="button" value="Cancel" class="button"'.
        ' onclick="alert(\'Im sorry, this feature has been temporarily left out to get other things working. This button will be working again in the next update.\');" /></td>
{endif}
    </tr>
';
//<td style="text-align: center;"><a href="./?id='.((int)$Item->itemId).'" class="button">Cancel</a></td>

// add enchantments to this link!
//        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'"></a>'.

//  if($Item->itemType=='tool'){
//    $output.='<br />'.$Item->getDamagedChargedStr();
//    foreach($Item->getEnchantmentsArray() as $ench){
//      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
//    }
//  }


$outputs['body bottom']='
</tbody>
</table>
';


$outputs['error']='
<h2 style="color: #ff0000; text-align: center;">{message}</h2>
';
$outputs['success']='
<h2 style="color: #00ff00; text-align: center;">{message}</h2>
';


return($outputs);
?>