<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'scripts/config.php';
	require 'scripts/itemInfo.php';
	$isAdmin = $_SESSION['Admin'];
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user'"); 

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
			} );
		</script>
	</head>
	<div id="holder">
		<?php include("topBoxes.php"); ?>
		<h1>Web Auction</h1>
			<br/>
        <h2>My Items</h2>
         <p style="color:red"><?php 
		 if(isset($_GET['error'])) {
	if($_GET['error']==1){
		echo "You do not own that item.";
	}}

?></p>
			
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
	<?php
	while(list($id, $name, $damage, $player, $quantity)= mysql_fetch_row($queryItems))
    { 
		$marketPrice = getMarketPrice($id, 0);
		$marketTotal = $marketPrice*$quantity;
		if ($marketPrice == 0)
		{
			$marketPrice = "0";
			$marketTotal = "0";
		}
	?>
        <tr class="gradeC">
			<td><a href="graph.php?name=<?php echo $name."&damage=".$damage ?>"><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo getItemName($name, $damage) ?>"/><br/>
			<?php echo getItemName($name, $damage) ?>
			<?php 
				$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId=0"); 
				while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
				{ 
					$queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'"); 
					while(list($idj, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants))
					{ 
						echo "<br/>".getEnchName($enchantId)." - Level: ".$level;
					}
				
				}
				
			?>
			</a></td>
			<td><?php echo $quantity ?></td>
            <td><?php echo $marketPrice ?></td>
			<td><?php echo $marketTotal ?></td>
            <td><a href="scripts/mailItem.php?id=<?php echo $id ?>">Mail it</a></td>
            

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