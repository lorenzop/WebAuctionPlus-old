<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}



$queryAuctions = mysql_query("SELECT * FROM WA_Auctions");





echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n".
     '<html>'."\n".
     '  <head>'."\n".
     '    <meta http-equiv="content-type" content="text/html; charset=utf-8" />'."\n".
     '    <title>WebAuction</title>'."\n".
     '    <link rel="icon" type="image/x-icon" href="images/favicon.ico" />'."\n".
     '    <style type="text/css" title="currentStyle">'."\n".
     '      @import "css/table_jui.css";'."\n".
     '      @import "css/<?php echo $uiPack?>/jquery-ui-1.8.18.custom.css";'."\n".
     '    </style>'."\n".
     '    <link rel="stylesheet" type="text/css" href="css/'.$cssFile.'.css" />'."\n".
     '    <script type="text/javascript" language="javascript" src="js/jquery-1.7.2.min.js"></script>'."\n".
     '    <script type="text/javascript" language="javascript" src="js/jquery.dataTables-1.9.0.min.js"></script>'."\n".
     '    <script type="text/javascript" language="javascript" src="js/inputfunc.js"></script>'."\n".
     '    <script type="text/javascript" charset="utf-8">'."\n".
     '      $(document).ready(function() {'."\n".
     '        oTable = $('#mainTable').dataTable({'."\n".
//     '          "bProcessing"     : true,'."\n".
     '          "bJQueryUI": true,'."\n".
//     '          "bStateSave"      : true,'."\n".
     '          "sPaginationType": "full_numbers"'."\n".
//     '          "sAjaxSource"     : "scripts/server_processing.php"'."\n".
     '        });'."\n".
     '      } );'."\n".
     '    </script>'."\n".
     '  </head>'."\n".
     '  <body>'."\n".
     '    <div id="holder">'."\n";
      <?php include("topBoxes.php"); ?>
      <h1>Web Auction</h1>
      <br />
      <h2>Current Auctions</h2>
      <p style="color:red">
<?php
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


?>
