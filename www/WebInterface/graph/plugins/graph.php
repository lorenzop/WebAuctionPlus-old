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
	$itemName = $_POST['name'];
	$itemDamage = $_POST['damage'];
	if ($useMySQLiConomy){
		$queryiConomy=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemName' AND damage='$itemDamage'"); 

	$playerQuery = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
	$playerRow = mysql_fetch_row($playerQuery);
	$mailQuery = mysql_query("SELECT * FROM WA_Mail WHERE player='$user'");
	$mailCount = mysql_num_rows($mailQuery);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/media/images/favicon.ico" />
		
		<title>WebAuction</title>
		<style type="text/css" title="currentStyle">
			@import "css/table_jui.css";
			@import "css/<?php echo $uiPack?>/jquery-ui-1.8.16.custom.css";
		</style>
        <link rel="stylesheet" type="text/css" href="css/<?php echo $cssFile?>.css" />
		<script type="text/javascript" language="javascript" src="js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
		<script type="text/javascript" src="../src/plugins/jqplot.highlighter.min.js"></script>
		<script type="text/javascript" src="../src/plugins/jqplot.cursor.min.js"></script>
		<script type="text/javascript" src="../src/plugins/jqplot.dateAxisRenderer.min.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				oTable = $('#example').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers"
				});
			} );
		</script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function(){
			var line1= $queryMarket;
  var plot1 = $.jqplot('chart1', [line1], {
      title:'Data Point Highlighting',
      axes:{
        xaxis:{
          renderer:$.jqplot.DateAxisRenderer,
          tickOptions:{
            formatString:'%b&nbsp;%#d'
          } 
        },
        yaxis:{
          tickOptions:{
            formatString:'$%.2f'
            }
        }
      },
      highlighter: {
        show: true,
        sizeAdjust: 7.5
      },
      cursor: {
        show: false
      }
  });
});
		</script>
	</head>
	<div id="holder">
		<?php include("topBoxes.php"); ?>
		<h1>Web Auction</h1>
			
        <h2>My Items</h2>
         <p style="color:red"><?php 
		 if(isset($_GET['error'])) {
	if($_GET['error']==1){
		echo "You do not own that item.";
	}}

?></p>
			<div id="chart1" style="height:300px; width:600px;"></div>
	  <div class="demo_jui">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th>Item</th>
			<th>Number Sold</th>
            <th>Market Price</th>
			<th>Value Graph</th>           
		</tr>
	</thead>
	<tbody>
	<?php
	$marketNames = array();
	while(list($id, $name, $damage, $time, $price, $ref)= mysql_fetch_row($queryMarket))
    { 
		$keyName = array_search($name.":".$damage, $marketNames);
		if (!$keyName == false){
		  //found this item id
		  
		}else{
			$marketNames[] = $name.":".$damage; 
			$fullName = getItemName($name, $damage);
			?>
			<tr class="gradeC">
				<td><img src="<?php echo getItemImage($name, $damage) ?>" alt="<?php echo $fullName ?>"/><br/><?php echo $fullName ?></td>
				<td><? echo $ref ?></td>
				<td><?php echo $price ?></td>
				<td><a href="scripts/graph.php?name=<?php echo $name ?>&damage=<? echo $damage ?>">View Graph</a></td>
			</tr>
			<?php
		}
	?>
        
    <?php } ?>
	</tbody>
</table>
			</div>
			<div class="spacer"></div>
			
			<?php include("footer.php"); ?>
		</div>
	
	</body>
</html>