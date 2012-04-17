<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


LoadItemsClass();




function RenderPage_auctions(){global $config,$html,$user,$items; $output='';
//  require($config['paths']['local']['classes'].'auctions.class.php');
  $config['title'] = 'Current Auctions';

  // get auctions
  $query="SELECT `id`,`itemId`,`itemDamage`,`playerName`,`qty`,`price`,UNIX_TIMESTAMP(`created`) ".
         "FROM `".$config['table prefix']."Auctions`";
  $result=RunQuery($query, __file__, __line__);


$output.='
<p style="color:red">
';
if(isset($_SESSION['error'])) {
  echo  $_SESSION['error'];
  unset($_SESSION['error']);
}
$output.='
</p>
<p style="color: green;">
';
if(isset($_SESSION['success'])) {
  echo  $_SESSION['success'];
  unset($_SESSION['success']);
}
$output.='
</p>
';

$output.='
<div class="demo_jui">
<!-- mainTable example -->
<table cellpadding="0" cellspacing="0" border="0" class="display" id="mainTable">
  <thead>
    <tr valign="bottom">
      <th>Item</th>
      <th>Seller</th>
      <th>Expires</th>
      <th>Quantity</th>
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


require($config['paths']['local']['classes'].'auctions.class.php');


$output.='
</tbody>
</table>
</div>
';


  return($output);
}


?>
