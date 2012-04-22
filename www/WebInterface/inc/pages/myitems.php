<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// my items page


// mail item
if($action == 'mailitem'){
  $id = ((int)getVar('id'));
  if($id <= 0){              echo '<p style="color: red;">Error: Invalid id!</p>'; exit();}
  if($user->getName() == ''){echo '<p>Error, not logged in!</p>'; exit();}
  $result = RunQuery("SELECT `playerName`,`itemId`,`itemDamage`,`qty` FROM `".$config['table prefix']."Items` WHERE ".
                     "`id`=".$id." AND `ItemTable`='Items' LIMIT 1", __file__, __line__);
  if(!$result){                    echo '<p>Error finding item!</p>'; exit();}
  if(mysql_num_rows($result) == 0){echo '<p>Item not found!</p>'; exit();}
  $row = mysql_fetch_assoc($result);
  $itemId     = $row['itemId'];
  $itemDamage = $row['itemDamage'];
  $qty        = $row['qty'];
  $stacksize = ItemFuncs::getMaxStack($itemId,$itemDamage);
  $sql = FALSE;
  // stack size to big
  while($qty > $stacksize){
  $query = "INSERT INTO `".$config['table prefix']."Items` ".
           "(`ItemTable`,`playerName`,`itemId`,`itemDamage`,`qty`) VALUES ".
           "('Mail','".mysql_san($user->getName())."',".((int)$itemId).",".((int)$itemDamage).",".((int)$stacksize).")";
    $result = RunQuery($query, __file__, __line__);
    if(!$result || mysql_affected_rows()==0){echo '<p>Error splitting stack!</p>'; exit();}
    $qty -= $stacksize;
    $sql = TRUE;
  }
  if($sql === TRUE) $sql = "`qty`=".((int)$qty).", ";
  else              $sql = '';
  // check is owner
  if(!$user->hasPerms("isAdmin")){
    if($row['playerName'] != $user->getName()){
      echo '<p>You don\'t own that item!</p>'; exit();}}
  // move item
  $query = "UPDATE `".$config['table prefix']."Items` SET ".$sql.
           "`ItemTable`='Mail' WHERE `id`=".((int)$id)." LIMIT 1";
  $result = RunQuery($query, __file__, __line__);
  if(!$result || mysql_affected_rows()!=1){
    echo '<p style="color: red;">Error mailing items! '.__line__.'</p>'; exit();}
  // move enchantments
  $query = "UPDATE `".$config['table prefix']."ItemEnchantments` SET ".
           "`ItemTable`='Mail' WHERE `ItemTableId`=".((int)$id);
  $result = RunQuery($query, __file__, __line__);
  if(!$result){
    echo '<p style="color: red;">Error mailing items! '.__line__.'</p>'; exit();}
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

if(isset($_SESSION['error'])) {
  $output.='<p style="color:red">'.$_SESSION['error'].'</p>';
  unset($_SESSION['error']);
}
if(isset($_SESSION['success'])) {
  $output.='<p style="color: green;">'.$_SESSION['success'].'</p>';
  unset($_SESSION['success']);
}

$output.='
<div class="demo_jui">
<!-- mainTable example -->
<table border="0" cellpadding="0" cellspacing="0" class="display" id="mainTable">
  <thead>
    <tr style="text-align: center; vertical-align: bottom;">
      <th>Item</th>
      <th>Qty</th>
      <th>Market Price (Each)</th>
      <th>Market Price (Total)</th>
      <th>Mail Item</th>
    </tr>
  </thead>
  <tbody>
';


// get items
$items->QueryItems();
// list items
while($itemRow = $items->getNext()){
  $Item = &$itemRow['Item'];
  $rowClass = 'gradeU';
  $output.='
    <tr class="'.$rowClass.'" style="height: 120px;">
      <td style="padding-bottom: 10px; text-align: center;">'.
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
//      <td style="text-align: center;">'.number_format((double)$auction['price'],2).'</td>
//      <td style="text-align: center;">'.number_format((double)($auction['price'] * $Item->qty),2).'</td>
  $output.='</a></td>
      <td style="text-align: center;"><b>'.((int)$Item->qty).'</b></td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;">market price<br />goes here</td>
      <td style="text-align: center;"><a href="./?page='.$config['page'].'&amp;action=mailitem&amp;id='.((int)$itemRow['id']).'" class="button">Mail it</a></td>
    </tr>
';
//  $marketPrice=getMarketPrice($id, 0);
//  $marketTotal=$marketPrice*$quantity;
//  if($marketPrice==0){
//    $marketPrice='0';
//    $marketTotal='0';
//  }
//  echo '  <tr class="gradeC">'."\n";
}
$output.='
</tbody>
</table>
</div>
';
  return($output);
}


?>