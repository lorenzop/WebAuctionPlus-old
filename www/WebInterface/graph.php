<?php
	session_start();
	if (!isset($_SESSION['User'])) {
		header("Location: login.php");
	}
	$user = $_SESSION['User'];
	require 'scripts/config.php';
	require 'scripts/itemInfo.php';
	$isAdmin = $_SESSION['Admin'];
	$queryAuctions = mysql_query("SELECT * FROM WA_Auctions");

        if (isset($_POST['Name'])) {
                $itemName = mysql_real_escape_string(stripslashes($_POST['Name']));
        } else {
                $itemName = mysql_real_escape_string(stripslashes($_GET['name']));
        }
        if (isset($_POST['Damage'])) {
                $itemDamage = mysql_real_escape_string(stripslashes($_POST['Damage']));
        } else {
                $itemDamage = mysql_real_escape_string(stripslashes($_GET['damage']));
        }

	$itemFullName = getItemName($itemName, $itemDamage);
	if ($useMySQLiConomy) {
		$queryiConomy = mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
		$iConRow = mysql_fetch_row($queryiConomy);
	}
	$queryMarket = mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemName' AND damage='$itemDamage'"); 
	$jsArrayString = "[";
	while (list($id, $name, $damage, $time, $price, $ref) = mysql_fetch_row($queryMarket)) {
		if (strlen($jsArrayString) > 3) {
			$jsArrayString = $jsArrayString . ",";
		}
		$jsArrayString = $jsArrayString . "[";
		$jsArrayString = $jsArrayString . date("\"m/d/Y H:i:s\"",$time) . "," . $price;
		$jsArrayString = $jsArrayString . "]";
	}
	$jsArrayString = $jsArrayString . "]";
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
    <script language="javascript" type="text/javascript" src="graph/jquery.jqplot.js"></script>
    <link rel="stylesheet" type="text/css" href="graph/jquery.jqplot.css" />
    <script type="text/javascript" src="graph/plugins/jqplot.highlighter.min.js"></script>
    <script type="text/javascript" src="graph/plugins/jqplot.cursor.min.js"></script>
    <script type="text/javascript" src="graph/plugins/jqplot.dateAxisRenderer.min.js"></script>
    <script type="text/javascript" charset="utf-8">
      $(document).ready(function() {
        oTable = $('#example').dataTable({
          "bJQueryUI"       : true,
          "sPaginationType" : "full_numbers"
        });
      });
    </script>
    <script class="code" type="text/javascript">
      $(document).ready(function() {
        var line1 = <?php echo $jsArrayString ?>;
        var plot1 = $.jqplot(
          'chart1',
          [line1],
          {
            title: '<?php echo $itemFullName ?> Market Price',
            axes: {
            xaxis: {
              renderer: $.jqplot.DateAxisRenderer,
              label: 'Date',
              tickOptions: {
                formatString: '%a %b %e %Y'
              }
            },
            yaxis: {
              label: 'Price',
              tickOptions: {
                formatString: '$%.2f'
              }
            }
          },
          highlighter: {
            show: true,
            sizeAdjust: 7.5
          },
          cursor: {
            show: true,
            zoom: true,
            showTooltip: true
          }
        });
      });
    </script>
  </head>
  <body>
    <div id="holder">
      <?php include("topBoxes.php"); ?>
      <h1>Web Auction</h1>
      <br/>
      </p> <!-- ??? -->
      <div id="chart1" style="height:400px; width:850px; margin:10px;"></div>
      <div class="spacer"></div>
      <?php include("footer.php"); ?>
    </div>
  </body>
</html>
