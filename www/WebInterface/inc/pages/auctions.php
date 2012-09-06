<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


// render ajax
if(getVar('ajax', 'boolean')){
  RenderPage_auctions_ajax();
  exit();
}


// buy an auction
if($config['action']=='buy'){
  CSRF::ValidateToken();
  // inventory is locked
  if($config['user']->isLocked()){
    echo '<center><h2>Your inventory is currently locked.<br />Please close your in game inventory and try again.</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 4);
    exit();
  }
  // buy auction
  if(AuctionFuncs::BuyAuction(
    getVar('auctionid','int','post'),
    getVar('qty',      'int','post')
  )){
    echo '<center><h2>Auction purchased successfully!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
  }
  echo $config['error']; exit();
}
if($config['action']=='cancel'){
  CSRF::ValidateToken();
  // inventory is locked
  if($config['user']->isLocked()){
    echo '<center><h2>Your inventory is currently locked.<br />Please close your in game inventory and try again.</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 4);
    exit();
  }
  // cancel auction
  if(AuctionFuncs::CancelAuction(
    getVar('auctionid','int','post')
  )){
    echo '<center><h2>Auction canceled!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
  }
  echo $config['error']; exit();
}


// render page (ajax/json)
function RenderPage_auctions_ajax(){global $config,$html;
  //file_put_contents('ajax_get.txt',print_r($_GET,TRUE));
  header('Content-Type: text/plain');
  // list auctions
  $auctions = QueryAuctions::QueryCurrent();
  $TotalDisplaying = QueryAuctions::TotalDisplaying();
  $TotalAllRows    = QueryAuctions::TotalAllRows();
  $outputRows = "{\n".
                "\t".'"iTotalDisplayRecords" : '.$TotalDisplaying.",\n".
                "\t".'"iTotalRecords" : '.       $TotalAllRows   .",\n".
                "\t".'"sEcho" : '.((int)getVar('sEcho','int')).",\n".
                "\t".'"aaData" : ['."\n";
  if($TotalDisplaying < 1){
    unset($auctions);
  }else{
    $outputRows .= "\t{\n";
    $count = 0;
    while($auction = $auctions->getNext()){
    	$Item = $auction->getItem();
  	  if(!$Item) continue;
      if($count != 0) $outputRows .= "\t},\n\t{\n";
      $count++;
      $data = array(
//      'auction id'  => (int)$auction->getTableRowId(),
        'item'        => $Item->getDisplay(),
        'seller'      => '<img src="./?page=mcskin&user='.$auction->getSeller().'" width="32" height="32" alt="" /><br />'.$auction->getSeller(),
        'price each'  => FormatPrice($auction->getPrice()),
        'price total' => FormatPrice($auction->getPriceTotal()),
        'market percent' => '--',
        'qty'         => (int)$Item->getItemQty(),
//TODO:
//allowBids
//currentBid
//currentWinner
//'created'     => $auction->getCreated(),
//'expire'      => $auction->getExpire(),
      );
      // buy button
      if($config['user']->hasPerms('canBuy'))
        $data['canBuy'] = '
          <form action="./" method="post">
          '.CSRF::getTokenForm().'
          <input type="hidden" name="page"      value="'.$config['page'].'" />
          <input type="hidden" name="action"    value="buy" />
          <input type="hidden" name="auctionid" value="'.((int)$auction->getTableRowId()).'" />
          <input type="text" name="qty" value="'.((int)$data['qty']).'" onkeypress="return numbersonly(this, event);" '.
            'class="input" style="width: 60px; margin-bottom: 5px; text-align: center;" /><br />
          <input type="submit" value="Buy" class="button" />
          </form>
';
      // cancel button
      if($config['user']->hasPerms('isAdmin'))
        $data['isAdmin'] = '
          <form action="./" method="post">
          '.CSRF::getTokenForm().'
          <input type="hidden" name="page"      value="'.$config['page'].'" />
          <input type="hidden" name="action"    value="cancel" />
          <input type="hidden" name="auctionid" value="'.((int)$auction->getTableRowId()).'" />
          <input type="submit" value="Cancel" class="button" />
          </form>
';
      // sanitize
      $data = str_replace(
        array('/' , '"' , "\r", "\n"),
        array('\/', '\"', ''  , '\n'),
        $data);
      $rowClass = 'gradeU';
//TODO:
//gradeA
//gradeC
//gradeX
//gradeU
      $outputRows .= "\t\t".'"DT_RowClass":"'.$rowClass.'",'	."\n";
      $i = -1;
      foreach($data as $v){$i++;
        if($i != 0) $outputRows .= ",\n";
        $outputRows .= "\t\t".'"'.$i.'":"'.$v.'"';
      }
      $outputRows .= "\n";
    }
    unset($auctions, $Item);
    $outputRows .= "\t}\n";
  }  
  $outputRows .= ']}'."\n";
  //file_put_contents('ajax_output.txt',$outputRows);
  echo $outputRows;
  exit();
}
// render page (html)
function RenderPage_auctions(){global $config,$html;
  $config['title'] = 'Current Auctions';
  // load page html
  $outputs = RenderHTML::LoadHTML('pages/auctions.php');
  $html->addTags(array(
    'messages' => ''
  ));
  // load javascript
  $html->addToHeader($outputs['header']);
  // display error
  if(isset($config['error']))
    $config['tags']['messages'] .= str_replace('{message}', $config['error'], $outputs['error']);
  if(isset($_SESSION['error'])){
    $config['tags']['messages'] .= str_replace('{message}', $_SESSION['error'], $outputs['error']);
    unset($_SESSION['error']);
  }
  // display success
  if(isset($_SESSION['success'])){
    $config['tags']['messages'] .= str_replace('{message}', $_SESSION['success'], $outputs['success']);
    unset($_SESSION['success']);
  }
// removed in-line listing - now handled by ajax
//  // list auctions
//  $auctions = QueryAuctions::QueryCurrent();
//  $outputRows = '';
//  while($auction = $auctions->getNext()){
//  	$Item = $auction->getItem();
//  	if(!$Item) continue;
//    $tags = array(
//      'auction id'  => (int)$auction->getTableRowId(),
//      'seller name' => $auction->getSeller(),
//      'item'        => $Item->getDisplay(),
//      'qty'         => (int)$Item->getItemQty(),
//      'price each'	=> FormatPrice($auction->getPrice()),
//      'price total'	=> FormatPrice($auction->getPriceTotal()),
//      'created'     => $auction->getCreated(),
//      'expire'      => $auction->getExpire(),
//      'market price percent' => '--',
//      'rowclass'    => 'gradeU',
//TODO:
//allowBids
//currentBid
//currentWinner
//    );
//    $htmlRow = $outputs['body row'];
//    RenderHTML::RenderTags($htmlRow, $tags);
//    $outputRows .= $htmlRow;
//  }
//  unset($auctions, $Item);
$outputRows='';
  return($outputs['body top']."\n".
         $outputRows."\n".
         $outputs['body bottom']);
}


?>