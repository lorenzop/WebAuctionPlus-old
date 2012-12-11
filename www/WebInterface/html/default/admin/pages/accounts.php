<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// settings page
$blocks = new HTMLFile();


$blocks->addBlock('header','
<script type="text/javascript" language="javascript" charset="utf-8">
$(document).ready(function() {
  oTable = $(\'#mainTable\').dataTable({
    "oLanguage": {
      "sEmptyTable"     : "&nbsp;<br />No accounts to display<br />&nbsp;",
      "sZeroRecords"    : "&nbsp;<br />No accounts to display<br />&nbsp;",
    },
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
');


//$blocks->addBlock('css','
//');


$blocks->addBlock('body','
{include:admin/menu.php}
<div style="float: left; width: 70%;">
<!-- {messages} -->
<table border="1" cellpadding="0" cellspacing="0" class="display" id="mainTable">
  <thead>
    <tr style="text-align: center; vertical-align: bottom;">
      <th>Id</th>
      <th>Player Name</th>
      <th>Money</th>
      <th style="width: 250px;">Permissions</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>

<tr>
  <td>1</td>
  <td>lorenzop</td>
  <td>$ 1000</td>
  <td><table border="0" cellpadding="0" cellspacing="5"><tr>
    <td style="width: 70px; background-color: #bbffbb; font-size: small;">canBuy</td>
    <td style="width: 70px; background-color: #bbffbb; font-size: small;">canSell</td>
    <td style="width: 70px; background-color: #ffbbbb; font-size: small;">isAdmin</td>
  </tr></table></td>
  <td></td>
</tr>

<tr>
  <td>2</td>
  <td>TheNytangel</td>
  <td>$ 1000</td>
  <td><table border="0" cellpadding="0" cellspacing="5"><tr>
    <td style="width: 70px; background-color: #bbffbb; font-size: small;">canBuy</td>
    <td style="width: 70px; background-color: #bbffbb; font-size: small;">canSell</td>
    <td style="width: 70px; background-color: #ffbbbb; font-size: small;">isAdmin</td>
  </tr></table></td>
  <td></td>
</tr>

<tr>
  <td>3</td>
  <td>Notch</td>
  <td>$ 1000</td>
  <td><table border="0" cellpadding="0" cellspacing="5"><tr>
    <td style="width: 70px; background-color: #bbffbb; font-size: small;">canBuy</td>
    <td style="width: 70px; background-color: #bbffbb; font-size: small;">canSell</td>
    <td style="width: 70px; background-color: #ffbbbb; font-size: small;">isAdmin</td>
  </tr></table></td>
  <td></td>
</tr>

  </tbody>
</table></div>
');
//<table border="0" cellpadding="5" cellspacing="0" align="center" class="formtable" style="width: 70%; margin-bottom: 30px;">
///<tr><td align="right">Total Auctions:</td><td>{total auctions}</td></tr>
//<tr><td align="right">Total Buy-Nows:</td><td>{total buynows}</td></tr>
//<tr><td align="right">Total Items For Sale:</td><td>{total items for sale}</td></tr>
//<tr><td align="right">Total Accounts:</td><td>{total accounts}</td></tr>
//
//<tr><td height="10"></td></tr>
//</table>
//
//<!-- troubleshooting -->
//<table border="0" cellpadding="5" cellspacing="0" align="center" class="formtable" style="width: 250px; margin-bottom: 30px; float: right; margin-right: 20px;">
//<tr><td align="center"><font size="+2">Troubleshooting</font></td></tr>
//
//<tr><td><ul>
//  <li><a href="./?page=mcskin&user=lorenzop&view=body&testing=1">Test mcskin.php</a><font size="-1"> - This should show a bunch of gibberish if working. Error messages are bad, but helpful to find the problem.</font></li>
//</ul></td></tr>
//
//<tr><td height="10"></td></tr>
//</table>


$outputs['body row']='
    <tr class="{rowclass}" style="height: 120px;">
      <td style="padding-bottom: 10px; text-align: center;">{item}</td>
      <td style="text-align: center;">{expire}</td>
      <td style="text-align: center;"><b>{qty}</b></td>
      <td style="text-align: center;">{price each}</td>
      <td style="text-align: center;">{price total}</td>
      <td style="text-align: center;">{market price percent}</td>
      <td style="text-align: center;">
        <form action="./" method="post">
        {token form}
        <input type="hidden" name="page"      value="{page}" />
        <input type="hidden" name="action"    value="cancel" />
        <input type="hidden" name="auctionid" value="{auction id}" />
        <input type="submit" value="Cancel" class="button" />
        </form>
    </tr>
';
// ($quantity==0?'Never':date('jS M Y H:i:s', $timeCreated + $auctionDurationSec) ).'</td>
//    <td class="center">'.($marketPercent=='N/A'?'N/A':number_format($marketPercent,1).' %').'</td>
// add enchantments to this link!
//        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'">'.

//<td style="text-align: center;"><a href="./?id='.((int)$Item->itemId).'" class="button">Cancel</a></td>

// add enchantments to this link!
//        '<a href="./?page=graph&amp;name='.$Item->itemId.'&amp;damage='.$Item->itemDamage.'"></a>'.

//  if($Item->itemType=='tool'){
//    $output.='<br />'.$Item->getDamagedChargedStr();
//    foreach($Item->getEnchantmentsArray() as $ench){
//      $output.='<br /><span style="font-size: smaller;"><i>'.$ench['enchName'].' '.numberToRoman($ench['level']).'</i></span>';
//    }
//  }


return($blocks);
?>