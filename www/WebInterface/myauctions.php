<?php

session_start();
if(!isset($_SESSION['User'])){
  header("Location: login.php");
}
$user=$_SESSION['User'];
$canSell=$_SESSION['canSell'];
require('scripts/config.php');
require('scripts/itemInfo.php');
$isAdmin=$_SESSION['Admin'];
$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
if($useMySQLiConomy){
  $queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
  $iConRow=mysql_fetch_row($queryiConomy);
}
$queryItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
$queryAuctions=mysql_query("SELECT id, name, damage, player, quantity, price, created FROM WA_Auctions WHERE player='$user'");

$playerQuery=mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
$playerRow=mysql_fetch_row($playerQuery);
$mailQuery=mysql_query("SELECT * FROM WA_Mail WHERE player='$user'");
$mailCount=mysql_num_rows($mailQuery);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/media/images/favicon.ico" />
    <title>WebAuction</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" />
    <style type="text/css" title="currentStyle">
      @import "css/table_jui.css";
      @import "css/<?php echo $uiPack?>/jquery-ui-1.8.18.custom.css";
    </style>
    <link rel="stylesheet" type="text/css" href="css/<?php echo $cssFile?>.css" />
    <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables-1.9.0.min.js"></script>
    <script type="text/javascript" charset="utf-8">
      $(document).ready(function() {
        oTable=$('#example').dataTable({
          "bJQueryUI": true,
          "sPaginationType": "full_numbers"
        });
      });
    </script>
  </head>
  <body>
    <div id="holder">
      <?php include("topBoxes.php"); ?>
      <h1>Web Auction</h1>
      <br/>
      <p style="color:red">
<?php
  if(isset($_SESSION['error'])) {
    echo $_SESSION['error'];
    unset($_SESSION['error']);
  }
?>
      </p>
      <p style="color:green">
<?php
  if(isset($_SESSION['success'])) {
    echo $_SESSION['success'];
    unset($_SESSION['success']);
  }
?>
      </p>
<?php
// TODO: printf(); or something
if($canSell==true){
?>
    <div id="new-auction-box">
      <h2>Create a new auction</h2>
      <form action="scripts/newAuction.php" method="post" name="auction">
      <table style="text-align:left;" width="100%">
      <tr>
        <td width="50%"><label>Item</label></td><td width="50%"><select name="Item" class="select">
<?php
while(list($id, $name, $damage, $player, $quantity)=mysql_fetch_row($queryItems)){
  $marketPrice=getMarketPrice($id, 0);
  if($marketPrice==0){
    $marketPrice="N/A";
  }
  echo '<option value="'.$id.'">'.getItemName($name, $damage);
  $queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId=0");
  while(list($enchId)= mysql_fetch_row($queryEnchantLinks)){
    $queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'");
    while(list($id, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants)){
      echo ' ('.getEnchName($enchantId).' '.numberToRoman($level).")";
    }
  }
  echo '('.$quantity.') (Average '.$currencyPrefix.$marketPrice.$currencyPostfix.')';
  echo '</option>'."\n";
}

?>        </select></td>
        <tr><td colspan="2" style="text-align:center;">
        <p>
<?php 
          if($isAdmin){ echo "Enter 0 as the quantity for infinite stacks (admins only)"; } 
?>
        </p>
        </td></tr>
        <tr><td><label>Quantity</label></td><td><input name="Quantity" type="text" class="input" size="10" /></td></tr>
        <tr><td><label>Price (Per Item)</label></td><td><input name="Price" type="text" class="input" size="10" /></td></tr>
        <!--<tr><td colspan="2" style="text-align:center;"><p>Leave starting bid blank to disable bidding</p></td></tr>
        <tr><td><label>Starting Bid (Per Item)</label></td><td><input name="MinBid" type="text" class="input" size="10" /></td></tr> -->
        <tr><td colspan="2" style="text-align:center;"><input name="Submit" type="submit" class="button" /></td></tr>
        </table>
      </form>
    </div>
<?php 
  } 
?>
  <h2>My Auctions</h2>
  <div class="demo_jui">
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
      <thead>
        <tr>
          <th>Item</th>
          <th>Expires</th>
          <th>Quantity</th>
          <th>Price (Each)</th>
          <th>Price (Total)</th>
          <th>% of Market Price</th>
          <th>Cancel</th>
        </tr>
      </thead>
      <tbody>
<?php

while(list($id, $name, $damage, $player, $quantity, $price, $timeCreated)=mysql_fetch_row($queryAuctions)){
  $marketPrice=getMarketPrice($id, 1);
  if($marketPrice>0){
    $marketPercent=round((($price/$marketPrice)*100), 1);
  }else{
    $marketPercent='N/A';
  }if($marketPercent=='N/A'){
    $grade='gradeU';
  }elseif($marketPercent<=50){
    $grade='gradeA';
  }elseif($marketPercent<=150){
    $grade='gradeC';
  }else{
    $grade='gradeX';
  }

  // alt="'.getItemName($name, $damage).'"
  echo '  <tr class="'.$grade.'">'."\n".
       '    <td><a href="graph.php?name='.$name.'&damage='.$damage.'">'.
           '<img src="'.getItemImage($name, $damage).'" /><br />';
  echo getItemName($name, $damage);
  $queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='".$id."' AND itemTableId=1");
  while(list($enchId)= mysql_fetch_row($queryEnchantLinks)){
    $queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='".$enchId."'");
    while(list($idj, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants)){
      echo "<br/>".getEnchName($enchantId)." - Level: ".$level;
    }
  }
  echo "</a></td>\n";
  echo '    <td>'.($quantity==0?'Never':date('jS M Y H:i:s', $timeCreated + $auctionDurationSec) )."</td>\n";
  echo '    <td>'.number_format($quantity,0).'</td>'."\n";
  echo '    <td class="center">$ '.number_format($price,2).'</td>'."\n";
  echo '    <td class="center">$ '.number_format($price*$quantity,2).'</td>'."\n";
  echo '    <td class="center">'.($marketPercent=='N/A'?'N/A':number_format($marketPercent,1).' %').'</td>'."\n";
  echo '    <td class="center"><a class="button" href="scripts/cancelAuction.php?id='.$id.'">Cancel</a></td>'."\n";
  echo "  </tr>\n";
}
echo "</tbody>\n";
echo "</table>\n";
echo "</div>\n";
echo '<div class="spacer"></div>'."\n";
include('footer.php');
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";

?>
