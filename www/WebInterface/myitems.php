<?php

session_start();
if(!isset($_SESSION['User'])){
  header("Location: login.php");
}
$user=$_SESSION['User'];
require('scripts/config.php');
require('scripts/itemInfo.php');
$isAdmin=$_SESSION['Admin'];
$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
if($useMySQLiConomy){
  $queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
  $iConRow=mysql_fetch_row($queryiConomy);
}
$queryItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user'"); 

$playerQuery=mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
$playerRow=mysql_fetch_row($playerQuery);
$mailQuery=mysql_query("SELECT * FROM WA_Mail WHERE player='$user'");
$mailCount=mysql_num_rows($mailQuery);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />  
    <title>WebAuction</title>
    <style type="text/css" title="currentStyle">
      @import "css/table_jui.css";
      @import "css/<?php echo $uiPack?>/jquery-ui-1.8.18.custom.css";
    </style>
        <link rel="stylesheet" type="text/css" href="css/<?php echo $cssFile?>.css" />
    <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.min-1.9.0.js"></script>
    <script type="text/javascript" charset="utf-8">
      $(document).ready(function() {
        oTable = $('#example').dataTable({
          "bJQueryUI": true,
          "sPaginationType": "full_numbers"
        });
      } );
    </script>
  </head>
  <div id="holder">

<?php
include('topBoxes.php');
echo '<h1>Web Auction</h1><br />'."\n";
echo '<h2>My Items</h2>'."\n";
echo '<p style="color: red;">'."\n";
if(isset($_GET['error'])){
  if($_GET['error']==1){
    echo 'You do not own that item.';
  }
}
echo '</p>'."\n";

echo '
<div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
  <thead>
    <tr>
      <th>Item</th>
      <th>Quantity</th>
            <th>Market Price (Each)</th>
      <th>Market Price (Total)</th>
            <th>Mail me item</th>
            
    </tr>
  </thead>
  <tbody>
';

while(list($id, $name, $damage, $player, $quantity)= mysql_fetch_row($queryItems)){ 
  $marketPrice=getMarketPrice($id, 0);
  $marketTotal=$marketPrice*$quantity;
  if($marketPrice==0){
    $marketPrice='0';
    $marketTotal='0';
  }
  echo '  <tr class="gradeC">'."\n";
  // alt="'.getItemName($name, $damage).'"
  echo '    <td><a href="graph.php?name='.$name.'&damage='.$damage.'">'.
           '<img src="'.getItemImage($name, $damage).'" /><br />';
  echo getItemName($name, $damage);
  $queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='".$id."' AND itemTableId=0"); 
  while(list($enchId)=mysql_fetch_row($queryEnchantLinks)){ 
    $queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='".$enchId."'"); 
    while(list($idj, $enchName, $enchantId, $level)=mysql_fetch_row($queryEnchants)){ 
      echo '<br />'.getEnchName($enchantId).' '.numberToRoman($level);
    }
  }
  echo '</a></td>'."\n";
  echo '    <td>'.number_format($quantity,0).'</td>'."\n";
  echo '    <td>$ '.number_format($marketPrice,2).'</td>'."\n";
  echo '    <td>$ '.number_format($marketTotal,2).'</td>'."\n";
  echo '    <td><a href="scripts/mailItem.php?id='.$id.'">Mail it</a></td>'."\n";
  echo '  </tr>'."\n";
}
echo '</tbody>'."\n";
echo '</table>'."\n";
echo '</div>'."\n";
echo '<div class="spacer"></div>'."\n";
include('footer.php');
echo '</div>'."\n";
echo '</body>'."\n";
echo '</html>'."\n";

?>
