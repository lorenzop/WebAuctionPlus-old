<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// item info page


//function RenderPage_info(){global $config; $output='';
//  $config['title'] = 'Item Info';
//  $output.='<h1 style="text-align: center;">** Under Construction **</h1>';
//  return($output);


//// TODO: Fix HTML
//session_start();
//if(!isset($_SESSION['User'])){
//  header("Location: login.php");
//}
//$user=$_SESSION['User'];
//require('scripts/config.php');
//require('scripts/itemInfo.php');
//require_once('classes/Market.php');
//$isAdmin=$_SESSION['Admin'];
//$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
//if($useMySQLiConomy){
//  $queryiConomy=mysql_query("SELECT `balance` FROM $iConTableName WHERE username='$user'");
//  $iConRow = mysql_fetch_assoc($queryiConomy);
//}
//$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices ORDER BY id DESC");
//$playerQuery=mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
//$playerRow=mysql_fetch_row($playerQuery);
//$mailQuery=mysql_query("SELECT * FROM WA_Mail WHERE player='$user'");
//$mailCount=mysql_num_rows($mailQuery);
//? >
//<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
//<html>
//  <head>
//    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
//    <title>WebAuction</title>
//    <link rel="icon" type="image/x-icon" href="images/favicon.ico" />
//    <style type="text/css" title="currentStyle">
//      @import "css/table_jui.css";
//      @import "css/<?php echo $uiPack? >/jquery-ui-1.8.18.custom.css";
//    </style>
//    <link rel="stylesheet" type="text/css" href="css/< ?php echo $cssFile? >.css" />
//    <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>
//    <script type="text/javascript" language="javascript" src="js/jquery.dataTables-1.9.0.min.js"></script>
//    <script type="text/javascript" charset="utf-8">
//      $(document).ready(function() {
//        oTable=$('#example').dataTable({
//          "bJQueryUI": true,
//          "sPaginationType": "full_numbers"
//        });
//      });
//    </script>
//  </head>
//  <body>
//    <div id="holder">
//< ?php
//include("topBoxes.php");
//? >
//<h1>Web Auction</h1><br/>
//<h2>Item Info</h2>
//<div class="demo_jui">
//  <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
//    <thead>
//      <tr>
//        <th>Item</th>
//        <th>Number Sold</th>
//        <th>Market Price</th>
//        <th>Value Graph</th>
//      </tr>
//    </thead>
//    <tbody>
//< ?php
//$marketItems=array();
//$add=TRUE;
//while(list($id, $name, $damage, $time, $price, $ref)=mysql_fetch_row($queryMarket)){
//  $market=new Market($id);
//  foreach($marketItems as $mar){
//    if($market->name==$mar->name && $market->damage==$mar->damage){
//      if(count(array_diff($market->enchants, $mar->enchants))==0){
//        $add=FALSE;
//      }
//    }
//  }
//  if(!$add){continue;}
//  $marketItems[]=$market;
//  //echo "<pre>";
//  //print_r($marketItems);
//  //echo "</pre>";
//  echo '  <tr class="gradeC">'."\n";
//  // alt="'.$market->fullname.'"
//  echo '    <td><a href="graph.php?name='.$market->name.'&damage='.$market->damage.'"><img src="'.$market->image.'" /><br />';
//  echo $market->fullname;
//  foreach ($market->enchants as $ench) {
//    echo '<br />'.getEnchName($ench['name']).' '.numberToRoman($ench['level']);
//  }
//  echo '</a></td>'."\n";
//  echo '    <td>'.number_format($market->ref,0).'</td>'."\n";
//  echo '    <td>$ '.number_format($market->price,2).'</td>'."\n";
//  echo '    <td><form action="graph.php" method="POST">'.
//              '<input type="hidden" name="Name" value="'.$market->name.'" />'.
//              '<input type="hidden" name="Damage" value="'.$market->damage.'" />'.
//              '<input type="submit" value="View Graph" class="button" /></form>'."\n";
//  echo '  </tr>'."\n";
//}
//echo '</tbody>'."\n";
//echo '</table>'."\n";
//echo '</div>'."\n";
//echo '<div class="spacer"></div>'."\n";
//include("footer.php");
//echo '</div>'."\n";
//echo '</body>'."\n";
//echo '</html>'."\n";
//
//
//}


?>