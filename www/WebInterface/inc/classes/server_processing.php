<?php

$isAdmin=$_SESSION['Admin'];
$canBuy=$_SESSION['canBuy'];
// Script:    DataTables server-side script for PHP and MySQL
// Copyright: 2010 - Allan Jardine
// License:   GPL v2 or BSD (3-point)

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
// Easy set variables

// Array of database columns which should be read and sent back to DataTables. Use a space where
// you want to insert a non-database field (for example a counter or static image)
$aColumns=array(
  0=>'name',
  1=>'damage',
  2=>'player',
  3=>'quantity',
  4=>'price',
  5=>'id',
  6=>'UNIX_TIMESTAMP(`created`) AS `created`'
);

// Indexed column (used for fast and accurate table cardinality)
$sIndexColumn="id";

// DB table to use
$sTable="WA_Auctions";

// Database connection information
$gaSql['user']      =$db_user;
$gaSql['password']  =$db_pass;
$gaSql['db']        =$db_database;
$gaSql['server']    =$db_host;

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
// If you just want to use the basic configuration for DataTables with PHP server-side, there is
// no need to edit below this line

// MySQL connection
$gaSql['link']= mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
  die( 'Could not open connection to server' );

mysql_select_db( $gaSql['db'], $gaSql['link'] ) or 
  die( 'Could not select database '. $gaSql['db'] );

// Paging
$sLimit="";
if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength']!=-1){
  $sLimit="LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
    mysql_real_escape_string( $_GET['iDisplayLength'] );
}

// Ordering
if(isset($_GET['iSortCol_0'])){
  $sOrder="ORDER BY ";
  for($i=0; $i<intval($_GET['iSortingCols']); $i++){
    if($_GET['bSortable_'.intval($_GET['iSortCol_'.$i])]=='true'){
      $sOrder.=$aColumns[intval( $_GET['iSortCol_'.$i] ) ]."
         ".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
    }
  }

  $sOrder=substr_replace( $sOrder, "", -2 );
  if($sOrder=="ORDER BY"){
    $sOrder='';
  }
}

// Filtering
// NOTE this does not match the built-in DataTables filtering which does it
// word by word on any field. It's possible to do here, but concerned about efficiency
// on very large tables, and MySQL's regex functionality is very limited
$sWhere="";
if(isset($_GET['sSearch'])){
  if($_GET['sSearch']!=''){
    $sWhere="WHERE (";
    for($i=0; $i<count($aColumns); $i++){
      $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
    }
    $sWhere=substr_replace( $sWhere, "", -3 );
    $sWhere .= ')';
  }
}
// Individual column filtering
for($i=0; $i<count($aColumns); $i++){
  if(isset($_GET['bSearchable_'.$i])){
    if($_GET['bSearchable_'.$i]=='true' && $_GET['sSearch_'.$i]!=''){
      if ($sWhere==''){
        $sWhere="WHERE ";
      }else{
        $sWhere.=" AND ";
      }
      $sWhere.=$aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
    }
  }
}

// SQL queries
// Get data to display
if (isset($sOrder)){
$sQuery=" SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)).
        " FROM $sTable $sWhere $sOrder $sLimit ";
}else{
  $sQuery=" SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)).
          " FROM $sTable $sWhere $sLimit ";
}
$rResult=mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());

// Data set length after filtering
$sQuery=" SELECT FOUND_ROWS() ";
$rResultFilterTotal=mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
$aResultFilterTotal=mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal=$aResultFilterTotal[0];

// Total data set length
$sQuery=" SELECT COUNT(".$sIndexColumn.") FROM $sTable ";
$rResultTotal=mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
$aResultTotal=mysql_fetch_array($rResultTotal);
$iTotal=$aResultTotal[0];

// Output
if (isset($_GET['sEcho'])){
  $output=array(
    "sEcho"               => intval($_GET['sEcho']),
    "iTotalRecords"       => $iTotal,
    "iTotalDisplayRecords"=> $iFilteredTotal,
    "aaData"              => array()
  );
}

if(mysql_num_rows($rResult)==0){
  return;
}

while($aRow=mysql_fetch_assoc($rResult)){
  $row=array();
  $quantity   =$aRow['quantity'];
  $timeCreated=$aRow['created'];
  if($quantity == 0 || (time()-$timeCreated)<$auctionDurationSec){
    $itemName    =$aRow['name'];
    $fullItemName=getItemName($aRow['name'], $aRow['damage']);
    $itemDamage  =$aRow['damage'];
    $marketPrice =getMarketPrice($aRow['id'], 1);
    if($marketPrice>0){
      $marketPercent=round((($aRow['price']/$marketPrice)*100), 1);
    }else{
      $marketPercent='N/A';
    }
    if($marketPercent=='N/A'){
      $marketPercent=0;
      $grade='gradeU';
    }else if ($marketPercent <= 50){
      $grade='gradeA';
    }else if ($marketPercent <= 150){
      $grade='gradeC';
    }else{
      $grade='gradeX';
    }
    $row['DT_RowClass']=$grade;
    $theId=$aRow['id'];
    $tempString='';
    $queryEnchantLinks=mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$theId' AND itemTableId='1'");
    //print_r(mysql_fetch_row($queryEnchantLinks));
    while(list($enchId)= mysql_fetch_row($queryEnchantLinks)){ 
      $queryEnchants=mysql_query("SELECT * FROM WA_Enchantments WHERE id='$enchId'"); 
      while(list($id, $enchName, $enchantId, $level)= mysql_fetch_row($queryEnchants)){ 
        $tempString=$tempString.'<br />'.getEnchName($enchantId).' '.numberToRoman($level);
      }

    }
    // alt="'.$fullItemName.'"
    $row[]='<a href="graph.php?name='.$aRow['name'].'&damage='.$aRow['damage'].'">'.
           '<img src="'.getItemImage($aRow['name'],$aRow['damage']).'" /><br />'.
           $fullItemName.$tempString.'</a>';
    $row[]='<img width="32" src="scripts/mcface.php?username='.$aRow['player'].'" /><br />'.
           $aRow['player'];
    if($quantity==0){
      $row[]='Never';
    }else{
      $row[]=date('jS M Y H:i:s', $timeCreated+$auctionDurationSec);
    }
    $row[]=number_format($aRow['quantity'],0);
    $row[]='$ '.number_format($aRow['price'],2);
    $row[]='$ '.number_format( ((double)$aRow['quantity']) * ((double)$aRow['price']) ,2);
    if($marketPercent=='N/A'){
      $row[]='N/A';
    }else{
      $row[]=number_format($marketPercent,1).' %';
    }

    if($canBuy===TRUE){
      $row[]='<form action="scripts/purchaseItem.php" method="POST">'.
             '<input type="text" name="Quantity" onKeyPress="return numbersonly(this, event);" class="input" />'.
             '<input type="hidden" name="ID" value="'.$aRow['id'].'" />'.
             '<input type="submit" value="Buy" class="button" /></form>';
    }else{
      $row[]="Can't Buy";
    }

    // display row
    echo '<tr class="'.$row['DT_RowClass'].'">'."\n";
    echo '  <td>'.$row[0].'</td>'."\n";
    echo '  <td>'.$row[1].'</td>'."\n";
    echo '  <td>'.$row[2].'</td>'."\n";
    echo '  <td>'.$row[3].'</td>'."\n";
    echo '  <td>'.$row[4].'</td>'."\n";
    echo '  <td>'.$row[5].'</td>'."\n";
    echo '  <td>'.$row[6].'</td>'."\n";
    echo '  <td>'.$row[7].'</td>'."\n";
    if($isAdmin===TRUE){ 
      echo '  <td><a class="button" href="scripts/cancelAuctionAdmin.php?id='.$aRow['id'].'">Cancel</a></td>'."\n";
    }
    echo "</tr>\n";;

  }else{
    $user        =$aRow['player'];
    $id          =$aRow['id'];
    $itemName    =$aRow['name'];
    $itemDamage  =$aRow['damage'];
    $itemQuantity=$aRow['quantity'];
    $queryPlayerItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
    $foundItem=FALSE;
    $stackId=0;
    $stackQuant=0;
    $queryEnchantLinks=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId='1'");
    //return mysql_num_rows($queryEnchantLinks);
    $itemEnchantsArray=array ();

    while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)=mysql_fetch_row($queryEnchantLinks)){  
      $itemEnchantsArray[]=$enchIdt;
    }

    while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)=mysql_fetch_row($queryPlayerItems)){  
      if($itemName==$pitemName){
        if($pitemDamage==$itemDamage){
          $queryEnchantLinksMarket=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemTableId='0' AND itemId='$pid'");
          $marketEnchantsArray=array();
          while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinksMarket)){  
            $marketEnchantsArray[]=$enchIdt;
          }  
          if((array_diff($itemEnchantsArray, $marketEnchantsArray)==null) && (array_diff($marketEnchantsArray, $itemEnchantsArray)==null)){
            $foundItem=TRUE;
            $stackId=$pid;
            $stackQuant=$pitemQuantity;
          }
        }
      }
    }
    if($foundItem==TRUE){
      $newQuantity=$itemQuantity + $stackQuant;
      $itemQuery=mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
    }else{
      $itemQuery=mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')");
      $queryLatestAuction=mysql_query("SELECT id FROM WA_Items ORDER BY id DESC");
      list($latestId)= mysql_fetch_row($queryLatestAuction);

      $queryEnchants=mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId ='1'"); 
      while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants)){
        $updateEnch=mysql_query("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '0', '$latestId')");
      }
    }
    $itemDelete=mysql_query("DELETE FROM WA_Auctions WHERE id='$id'");
  }

}


?>
