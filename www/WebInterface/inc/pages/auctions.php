<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


function RenderPage_auctions(){global $config,$html; $output='';
  include($config['paths']['local']['classes'].'auctions.class.php');
  $config['title'] = 'Login';

$output.='sdfgjkdfhgkdnhlndshkldnlgh';
return($output);

}


/*
echo $user->Money;
echo '<br />';
echo 'has permissions: ';
echo $user->hasPerms('canBuy')?'canBuy':'';
echo $user->hasPerms('canSell')?'canSell':'';
echo $user->hasPerms('isAdmin')?'isAdmin':'';
exit();






$queryAuctions = mysql_query("SELECT * FROM WA_Auctions");





      <?php include("topBoxes.php"); ?>
      <h1>Web Auction</h1>
      <br />
      <h2>Current Auctions</h2>
      <p style="color:red">
< ?php
if(isset($_SESSION['error'])) {
  echo  $_SESSION['error'];
  unset($_SESSION['error']);
}
echo "</p>\n<p style=\"color: green;\">\n";
if(isset($_SESSION['success'])) {
  echo  $_SESSION['success'];
  unset($_SESSION['success']);
}
echo "</p>\n";

exit();
echo '<div class="demo_jui">'."\n".
     '<!-- mainTable example -->'."\n".
     '<table cellpadding="0" cellspacing="0" border="0" class="display" id="mainTable">'."\n".
     '  <thead>'."\n".
     '    <tr>'."\n".
     '      <th>Item</th>'."\n".
     '      <th>Seller</th>'."\n".
     '      <th>Expires</th>'."\n".
     '      <th>Quantity</th>'."\n".
     '      <th>Price (Each)</th>'."\n".
     '      <th>Price (Total)</th>'."\n".
     '      <th>% of Market Price</th>'."\n".
     '      <th>Buy</th>'."\n";


if ($isAdmin == true) {
  print("<th>Cancel</th>");
}
echo '    </tr>'."\n".
     '  </thead>'."\n".
     '  <tbody>'."\n";


require('scripts/server_processing.php');


echo '</tbody>'."\n".
     '</table>'."\n".
     '</div>'."\n".
     '<div class="spacer"></div>'."\n";
include('footer.php');
echo '</div>'."\n".
     '</body>'."\n".
     '</html>'."\n";
*/

?>
