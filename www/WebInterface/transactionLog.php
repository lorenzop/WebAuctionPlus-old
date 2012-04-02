<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'scripts/config.php';
	require 'scripts/itemInfo.php';
	require 'scripts/updateTables.php';
	$isAdmin = $_SESSION['Admin'];
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryMySales=mysql_query("SELECT * FROM WA_SellPrice WHERE seller='$user'");
	$queryMyPurchases=mysql_query("SELECT * FROM WA_SellPrice WHERE buyer='$user'");

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
		<script type="text/javascript" language="javascript" src="js/dataTables.Sort.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				oTable = $('#example').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers",
					"aoColumns": [
						{ "sType": "date-euro" },
						null,
						null,
						null,
						null,
						null,
						null
					]
				});
				oTable = $('#example2').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers",
					"aoColumns": [
						{ "sType": "date-euro" },
						null,
						null,
						null,
						null,
						null,
						null
					]
				});
			} );
		</script>
	</head>
	<div id="holder">
		<?php include("topBoxes.php"); ?>
		<h1>Web Auction</h1>
		<br/>
		<h2>My Items Bought</h2>
        
			
	  <div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th>Time</th>
			<th>Item</th>
			<th>Seller</th>
            <th>Quantity</th>
            <th>Price (Each)</th>
			<th>Price (Total)</th>	
			<th>% of Market Price</th>
		</tr>
	</thead>
	<tbody>
	<?php
	while(list($id, $name, $damage, $time, $quantity, $price, $seller, $buyer)= mysql_fetch_row($queryMyPurchases))
    { 
		$marketPrice = getMarketPrice($id, 3);
		$timeFormat = date('jS M Y H:i:s', $time);	
		
		if ($marketPrice > 0)
		{
			$marketPercent = round((($price/$marketPrice)*100), 1);
		}
		else
		{
			$marketPercent = "N/A";
		}
		if ($marketPercent == "0")
		{
			$grade = "gradeU";
		}
		else if ($marketPercent <= 50)
		{
			$grade = "gradeA";
		}
		else if ($marketPercent <= 150)
		{
			$grade = "gradeC";
		}
		else
		{
			$grade = "gradeX";
		}
	?>
    	
        <tr class="<?php echo $grade ?>">
			<td><?php echo $timeFormat ?></td>
			<td><a href="graph.php?name=<?php echo $name."&damage=".$damage ?>"><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo getItemName($name, $damage) ?>"/><br/><?php echo getItemName($name, $damage); 
			$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId=3");
			while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
			{
				$queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'");
				while(list($idj, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants))
				{
					echo "<br/>".getEnchName($enchantId)." - Level: ".$level;
				}
			}
			?></a></td>
			<td><img width="32px" src="http://minotar.net/avatar/<?php echo $seller ?>" /><br/><?php echo $seller ?></td>
			<td><?php echo $quantity ?></td>
			<td class="center"><?php echo $price ?></td>
			<td class="center"><?php echo $price*$quantity ?></td>
			<td class="center"><?php echo $marketPercent ?></td>
		</tr>
    <?php } ?>
	</tbody>
</table>	
        <h2>My Items Sold</h2>
        
	</div>		
	  <div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example2">
	<thead>
		<tr>
			<th>Date</th>
			<th>Item</th>
			<th>Buyer</th>
            <th>Quantity</th>
            <th>Price (Each)</th>
			<th>Price (Total)</th>	
			<th>% of Market Price</th>
		</tr>
	</thead>
	<tbody>
	<?php
	while(list($id, $name, $damage, $time, $quantity, $price, $seller, $buyer)= mysql_fetch_row($queryMySales))
    { 
		$marketPrice = getMarketPrice($id, 3);
		$timeFormat = date('jS M Y H:i:s', $time);
		if ($marketPrice > 0)
		{
			$marketPercent = round((($price/$marketPrice)*100), 1);
		}
		else
		{
			$marketPercent = "N/A";
		}
		if ($marketPercent == "0")
		{
			$grade = "gradeU";
		}
		else if ($marketPercent <= 50)
		{
			$grade = "gradeA";
		}
		else if ($marketPercent <= 150)
		{
			$grade = "gradeC";
		}
		else
		{
			$grade = "gradeX";
		}
	?>
    	
        <tr class="<?php echo $grade ?>">
			<td><?php echo $timeFormat ?></td>
			<td><a href="graph.php?name=<?php echo $name."&damage=".$damage ?>"><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo getItemName($name, $damage) ?>"/><br/><?php echo getItemName($name, $damage); 
			$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId=3");
			while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
			{
				$queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'");
				while(list($idj, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants))
				{
					echo "<br/>".getEnchName($enchantId)." - Level: ".$level;
				}
			}
			?></a></td>
			<td><img width="32px" src="http://minotar.net/avatar/<?php echo $buyer ?>" /><br/><?php echo $buyer ?></td>
			<td><?php echo $quantity ?></td>
			<td class="center"><?php echo $price ?></td>
			<td class="center"><?php echo $price*$quantity ?></td>
			<td class="center"><?php echo $marketPercent ?></td>
		</tr>
    <?php } ?>
	</tbody>
</table>
			</div>
			<div class="spacer"></div>
			
			<?php include("footer.php"); ?>
		</div>
	
	</body>
</html>