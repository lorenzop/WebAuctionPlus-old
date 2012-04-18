<?php
class AuctionsClass{

public $currentAuctionId = 0;
protected $result  = FALSE;
private   $tempRow = FALSE;

function __construct(){
}

// get auctions
public function QueryAuctions(){global $config;
  $currentAuctionId = 0;
  $tempRow = FALSE;
  $query="SELECT ".
         "Auctions.`id`         AS `auctionId`, ".
         "Auctions.`itemId`     AS `itemId`, ".
         "Auctions.`itemDamage` AS `itemDamage`, ".
         "Auctions.`playerName` AS `playerName`, ".
         "Auctions.`qty`        AS `qty`, ".
         "Auctions.`price`      AS `price`, ".
         "UNIX_TIMESTAMP(Auctions.`created`) AS `created`, ".
         "ItemEnch.`enchName`   AS `enchName`, ".
         "ItemEnch.`enchId`     AS `enchId`, ".
         "ItemEnch.`level`      AS `level` ".
         "FROM `".     $config['table prefix']."Auctions` `Auctions` ".
         "LEFT JOIN `".$config['table prefix']."ItemEnchantments` `ItemEnch` ".
         "ON  Auctions.`id`       = ItemEnch.`ItemTableId` ".
         "AND ItemEnch.`ItemTable`='Auctions' ".
         "ORDER BY Auctions.`id` ASC";
//echo '<pre><font color="white">'.$query."</font></pre>";
  $this->result=RunQuery($query, __file__, __line__);
}

// get next auction row
public function getNext(){
  $tempRow = &$this->tempRow;
  $output  = array();
  // get first row
  if($tempRow==FALSE) $tempRow = mysql_fetch_assoc($this->result);
  if($tempRow==FALSE) return(FALSE);
  $currentAuctionId = $tempRow['auctionId'];
  $output['auctionId']  = $currentAuctionId;
  $output['playerName'] = $tempRow['playerName'];
  $output['price']      = $tempRow['price'];
  $output['created']    = $tempRow['created'];
  // create item object
  $output['Item']       = new ItemClass(array(
    'itemId'     => $tempRow['itemId'],
    'itemDamage' => $tempRow['itemDamage'],
    'qty'        => $tempRow['qty'] ));
  // get first enchantment
  if(!empty($tempRow['enchName']))
    $output['Item']->addEnchantment($tempRow['enchName'], $tempRow['enchId'], $tempRow['level'] );
  // get more rows (enchantments)
  while($tempRow = mysql_fetch_assoc($this->result)){
    if($tempRow['auctionId'] != $currentAuctionId) break;
    $output['Item']->addEnchantment($tempRow['enchName'], $tempRow['enchId'], $tempRow['level']);
  }
  if(count($output)==0) $output=FALSE;
  return($output);
}


}
















/*
global $config,$user,$items;
if(@$config['auction duration']==0) $config['auction duration']=86400 * 7; // default 1 week
$isAdmin=$user->hasPerms('isAdmin');
$canBuy =$user->hasPerms('canBuy');





function getMarketPrice($itemTableId, $tableId){
  $table = '';
  switch ($tableId){
    case 0:
      $table = 'WA_Items';
      break;
    case 1:
      $table = 'WA_Auctions';
      break;
    case 2:
      $table = 'WA_Mail';
      break;
    case 3:
      $table = 'WA_SellPrice';
      break;
  }
  $queryItem = RunQuery("SELECT * FROM $table WHERE id='$itemTableId'", __file__, __line__);
  $itemRow = mysql_fetch_row($queryItem);
  $itemId = $itemRow[1];
  $itemDamage = $itemRow[2];
  $foundIt = false;
  $queryMarket = '';
  //return $itemId;

  $queryEnchantLinks = RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId = '$itemTableId' AND itemTableId = '$tableId'", __file__, __line__);
  //return mysql_num_rows($queryEnchantLinks);
  $itemEnchantsArray = array();
  while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks)){  
    $itemEnchantsArray[] = $enchIdt;
  }
  $queryEnchantLinksMarket = RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '4'", __file__, __line__);
  $base = isTrueDamage($itemId, $itemDamage);
  if ($base > 0){
    if (mysql_num_rows($queryEnchantLinks) == 0){
      $queryMarket1=RunQuery("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC", __file__, __line__);
      $maxId = -1;
      $foundIt = false;
      //echo 'first';
      while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1)){
        $queryMarket2 = RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'", __file__, __line__);
        if (mysql_num_rows($queryMarket2)== 0){
          if ($idm > $maxId){
            $maxId = $idm;
            $foundIt = true;
          }
        }
      }
      if ($foundIt){
        $queryMarket=RunQuery("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC", __file__, __line__);
        $foundIt = true;
      }
    }else{
      $queryMarket1=RunQuery("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC", __file__, __line__);
      $maxId = -1;
      $foundIt = false;
      //echo 'second';
      while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1)){
        $marketEnchantsArray = array ();
        $queryMarket2 = RunQuery("SELECT enchId FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'", __file__, __line__);
        while(list($enchIdt)= mysql_fetch_row($queryMarket2)){
          if ($idm > $maxId){
            $marketEnchantsArray[] = $enchIdt;
            
          }
        }
        if((array_diff($itemEnchantsArray, $marketEnchantsArray) == null)&&(array_diff($marketEnchantsArray, $itemEnchantsArray) == null)){
          $maxId = $idm;
          $foundIt = true;
        }
        //print_r($itemEnchantsArray);
      }
      if ($foundIt){
        $queryMarket=RunQuery("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC", __file__, __line__);
        $foundIt = true;
      }
    }
  }else{
    $queryMarket=RunQuery("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='$itemDamage' ORDER BY id DESC", __file__, __line__);
    $foundIt = true;
  }
  if ($foundIt==false){
    //market price not found
    //echo 'cant find';
    return 0;
  }else{
    //found get first item
    $rowMarket = mysql_fetch_row($queryMarket);
    $marketId = $rowMarket[0];
    if ($base > 0){$marketPrice = ($rowMarket[4]/$base)*($base - $itemDamage);
    }else{         $marketPrice = $rowMarket[4];}
    return round($marketPrice, 2);
  }
}













// Script:    DataTables server-side script for PHP and MySQL
// Copyright: 2010 - Allan Jardine
// License:   GPL v2 or BSD (3-point)

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
// Easy set variables

// Array of database columns which should be read and sent back to DataTables. Use a space where
// you want to insert a non-database field (for example a counter or static image)
$aColumns=array(
  0=>'itemId',
  1=>'itemDamage',
  2=>'playerName',
  3=>'qty',
  4=>'price',
  5=>'id',
  6=>'UNIX_TIMESTAMP(`created`) AS `created`'
);

// Indexed column (used for fast and accurate table cardinality)
$sIndexColumn="id";

// DB table to use
$sTable="WA_Auctions";




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
$rResult=RunQuery($sQuery, __file__, __line__);

// Data set length after filtering
$sQuery=" SELECT FOUND_ROWS() ";
$rResultFilterTotal=RunQuery($sQuery, __file__, __line__);
$aResultFilterTotal=mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal=$aResultFilterTotal[0];

// Total data set length
$sQuery=" SELECT COUNT(".$sIndexColumn.") FROM $sTable ";
$rResultTotal=RunQuery($sQuery, __file__, __line__);
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
  $quantity   =$aRow['qty'];
  $timeCreated=$aRow['created'];
  if($timeCreated==0){
echo 'no created date set!';
exit();
  }
  if($quantity == 0 || (time()-$timeCreated) < $config['auction duration']){
    $itemName    =$aRow['itemId'];
    $fullItemName=$items->getItemHtmlTitle($aRow['itemId'], $aRow['itemDamage']);
    $itemDamage  =$aRow['itemDamage'];
    $marketPrice =getMarketPrice($aRow['itemId'], 1);
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



return;



    $queryEnchantLinks=RunQuery("SELECT enchId FROM WA_EnchantLinks WHERE itemId='$theId' AND itemTableId='1'", __file__, __line__);
    //print_r(mysql_fetch_row($queryEnchantLinks));
    while(list($enchId)= mysql_fetch_row($queryEnchantLinks)){ 
      $queryEnchants=RunQuery("SELECT * FROM WA_Enchantments WHERE id='$enchId'", __file__, __line__); 
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
      $row[]=date('jS M Y H:i:s', $timeCreated+$config['auction duration']);
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
echo 'expiring auction';
exit();
    $user        =$aRow['playerName'];
    $id          =$aRow['id'];
    $itemName    =$aRow['itemId'];
    $itemDamage  =$aRow['itemDamage'];
    $itemQuantity=$aRow['qty'];
    $queryPlayerItems=RunQuery("SELECT * FROM WA_Items WHERE player='$user'", __file__, __line__);
    $foundItem=FALSE;
    $stackId=0;
    $stackQuant=0;
    $queryEnchantLinks=RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId='1'", __file__, __line__);
    //return mysql_num_rows($queryEnchantLinks);
    $itemEnchantsArray=array ();

    while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)=mysql_fetch_row($queryEnchantLinks)){  
      $itemEnchantsArray[]=$enchIdt;
    }

    while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)=mysql_fetch_row($queryPlayerItems)){  
      if($itemName==$pitemName){
        if($pitemDamage==$itemDamage){
          $queryEnchantLinksMarket=RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemTableId='0' AND itemId='$pid'", __file__, __line__);
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
      $itemQuery=RunQuery("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'", __file__, __line__);
    }else{
      $itemQuery=RunQuery("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$itemQuantity')", __file__, __line__);
      $queryLatestAuction=RunQuery("SELECT id FROM WA_Items ORDER BY id DESC", __file__, __line__);
      list($latestId)= mysql_fetch_row($queryLatestAuction);

      $queryEnchants=RunQuery("SELECT * FROM WA_EnchantLinks WHERE itemId='$id' AND itemTableId ='1'", __file__, __line__); 
      while(list($idk,$enchIdk, $tableIdk, $itemIdk)= mysql_fetch_row($queryEnchants)){
        $updateEnch=RunQuery("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES ('$enchIdk', '0', '$latestId')", __file__, __line__);
      }
    }
    $itemDelete=RunQuery("DELETE FROM WA_Auctions WHERE id='$id'", __file__, __line__);
  }

}
*/


?>