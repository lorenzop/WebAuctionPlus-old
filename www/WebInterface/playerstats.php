<?php
// TODO: Fix HTML
	session_start();
	if (!isset($_SESSION['User'])) {
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'scripts/config.php';
	require 'scripts/itemInfo.php';
	$isAdmin = $_SESSION['Admin'];
	$queryPlayers = mysql_query("SELECT id, name, itemsSold, itemsBought, earnt, spent FROM WA_Players");
	if ($useMySQLiConomy) {
		$queryiConomy = mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryMarket = mysql_query("SELECT * FROM WA_MarketPrices ORDER BY id DESC");
	$playerQuery = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
	$playerRow = mysql_fetch_row($playerQuery);
	$mailQuery = mysql_query("SELECT * FROM WA_Mail WHERE player='$user'");
	$mailCount = mysql_num_rows($mailQuery);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>WebAuction</title>
    <style type="text/css" title="currentStyle">
      @import "css/table_jui.css";
      @import "css/<?php echo $uiPack?>/jquery-ui-1.8.16.custom.css";
    </style>
    <link rel="stylesheet" type="text/css" href="css/<?php echo $cssFile?>.css" />
    <script type="text/javascript" language="javascript" src="js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf-8">
      $(document).ready(function() {
        oTable = $('#example').dataTable({
          "bJQueryUI": true,
          "sPaginationType": "full_numbers"
        });
      });
    </script>
  </head>
  <body>
    <div id="holder">
<?php
	include("topBoxes.php");
?>
      <h1>Web Auction</h1><br/>
      <h2>Player Stats</h2>
      <div class="demo_jui">
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
          <thead>
            <tr>
              <th>Player</th>
              <th>Items Sold</th>
              <th>Item Bought</th>
              <th>Money Gained</th>
			  <th>Money Spent</th>
			  <th>Total Profit</th>
            </tr>
          </thead>
          <tbody>
<?php
	$marketItems = array();
	$add = true;
	while(list($id, $name, $sold, $bought, $earnt, $spent) = mysql_fetch_row($queryPlayers)) {
	if (($earnt == 0) && ($spent == 0)){
		//dont print that person to save space.
	}else{
		?>
	
          <tr class="gradeC">
			<td>
				<img width='32px' src='http://minotar.net/avatar/<?php echo $name; ?>' /><br/><?php echo $name; ?>
			</td>
			<td>
				<?php echo $sold; ?>
			</td>
			<td>
				<?php echo $bought; ?>
			</td>
			<td>
				<?php echo $earnt; ?>
			</td>
			<td>
				<?php echo $spent; ?>
			</td>
			<td>
				<?php echo $earnt - $spent; ?>
			</td>
          </tr>
<?php
	}
	}
?>
        </tbody>
      </table>
    </div>
    <div class="spacer"></div>
<?php
	include("footer.php");
?>
    </div>
  </body>
</html>
