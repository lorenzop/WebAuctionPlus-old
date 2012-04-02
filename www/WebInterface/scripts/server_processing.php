<?php
    session_start();
    require 'config.php';
	require 'itemInfo.php';
	require 'jsonwrapper/jsonwrapper.php';
	$isAdmin = $_SESSION['Admin'];
	$canBuy = $_SESSION['canBuy'];
	/*
	 * Script:    DataTables server-side script for PHP and MySQL
	 * Copyright: 2010 - Allan Jardine
	 * License:   GPL v2 or BSD (3-point)
	 */
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( 'name', 'damage', 'player', 'quantity', 'price', 'id', 'created');
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
	/* DB table to use */
	$sTable = "WA_Auctions";
	
	/* Database connection information */
	$gaSql['user']       = $db_user;
	$gaSql['password']   = $db_pass;
	$gaSql['db']         = $db_database;
	$gaSql['server']     = $db_host;
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* 
	 * MySQL connection
	 */
	$gaSql['link'] =  mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		die( 'Could not open connection to server' );
	
	mysql_select_db( $gaSql['db'], $gaSql['link'] ) or 
		die( 'Could not select database '. $gaSql['db'] );
	
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if (isset($_GET['sSearch'])){
	if ( $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	}
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if (isset($_GET['bSearchable_'.$i])){
		if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
		}
		}
	}
	
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	if (isset($sOrder)){
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
	";
	}else{
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere
		$sLimit
	";
	
	}
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	/*
	 * Output
	 */
	if (isset($_GET['sEcho'])){
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	}
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
		$row = array();
		$quantity = $aRow[ $aColumns[3] ];
		$timeCreated = $aRow[ $aColumns[6] ];
		if((time() < $timeCreated + $auctionDurationSec)|| ($quantity == 0)){
			$itemName = $aRow[ $aColumns[0] ];
			$fullItemName = getItemName($aRow[ $aColumns[0] ], $aRow[ $aColumns[1] ]);
			$itemDamage = $aRow[ $aColumns[1] ];
			$marketPrice = getMarketPrice($aRow[ $aColumns[5] ], 1);
			if ($marketPrice > 0)
			{
				$marketPercent = round((($aRow[ $aColumns[4] ]/$marketPrice)*100), 1);
			}
			else
			{
				$marketPercent = "N/A";
			}
			if ($marketPercent == "N/A")
			{
				$marketPercent = 0;
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
			$row['DT_RowClass'] = $grade;
			$theId = $aRow[ $aColumns[5] ];
			$tempString = "";
			$queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$theId' AND itemTableId='1'");
			//print_r(mysql_fetch_row($queryEnchantLinks));
			while(list($enchId)= mysql_fetch_row($queryEnchantLinks))
				{ 
					$queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'"); 
					while(list($id, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants))
					{ 
						$tempString =$tempString."<br/>".getEnchName($enchantId)." - Level: ".$level;
					}
				
				}
			$row[] = "<a href='graph.php?name=".$aRow[ $aColumns[0] ]."&damage=".$aRow[ $aColumns[1] ]."'><img src=".getItemImage($aRow[ $aColumns[0] ], $aRow[ $aColumns[1] ])." alt=".$fullItemName."/><br/>".$fullItemName.$tempString."</a>";
			$row[] = "<img width='32px' src='http://minotar.net/avatar/".$aRow[ $aColumns[2] ]."' /><br/>".$aRow[ $aColumns[2] ];
			if ($quantity == 0){
				$row[] = "Never";
			}else{
				$row[] = date('jS M Y H:i:s', $timeCreated + $auctionDurationSec);
			}
			$row[] = $aRow[ $aColumns[3] ];
			$row[] = $aRow[ $aColumns[4] ];
			$row[] = (((double)$aRow[ $aColumns[3] ])*((double)$aRow[ $aColumns[4] ]));
			$row[] = $marketPercent;
		
			if ($canBuy == true){
				$row[] = "<form action='scripts/purchaseItem.php' method='post'><input type='text' name='Quantity' onKeyPress='return numbersonly(this, event)' class='input'><input type='hidden' name='ID' value='".$aRow[ $aColumns[5] ]."' /><input type='submit' value='Buy' class='button' /></form>";	
			}else{
				$row[] = "Can't Buy";
			}
			if ($isAdmin == true){ 
				$row[] = "<td><a class='button' href='scripts/cancelAuctionAdmin.php?id=".$aRow[ $aColumns[5] ]."'>Cancel</a></td>";
			}
			$output['aaData'][] = $row;
		}else
		{
			$user = $aRow[ $aColumns[2] ];
			$id = $aRow[ $aColumns[5] ];
			$itemName = $aRow[ $aColumns[0] ];
			$itemDamage = $aRow[ $aColumns[1] ];
			$itemQuantity = $aRow[ $aColumns[3] ];
			$queryPlayerItems =mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
			$foundItem = false;
			$stackId = 0;
			$stackQuant = 0;
			$queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$id' AND itemTableId = '1'");
			//return mysql_num_rows($queryEnchantLinks);
			$itemEnchantsArray = array ();
		
			while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks))
			{  
				$itemEnchantsArray[] = $enchIdt;
			
			}
			while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)= mysql_fetch_row($queryPlayerItems))
			{	
				if($itemName == $pitemName)
				{
					if ($pitemDamage == $itemDamage)
					{
						$queryEnchantLinksMarket = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '0' AND itemId = '$pid'");
						$marketEnchantsArray = array ();
						while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinksMarket))
						{  
							$marketEnchantsArray[] = $enchIdt;
						}	
						if((array_diff($itemEnchantsArray, $marketEnchantsArray) == null)&&(array_diff($marketEnchantsArray, $itemEnchantsArray) == null))
						{
							$foundItem = true;
							$stackId = $pid;
							$stackQuant = $pitemQuantity;
						}
					}
				}
			}
			if ($foundItem == true)
			{
				$newQuantity = $itemQuantity + $stackQuant;
				$itemQuery = mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
			}else
			{
				$itemQuery = mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
				$queryLatestAuction = mysql_query("SELECT id FROM WA_Items ORDER BY id DESC");
				list($latestId)= mysql_fetch_row($queryLatestAuction);
					
				$queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId ='1'"); 
				while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants))
				{ 
					$updateEnch = mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '0', '$latestId')");
				}
			}
			$itemDelete = mysql_query("DELETE FROM WA_Auctions WHERE id='$id'");
		}
	}
	echo json_encode( $output );
?>