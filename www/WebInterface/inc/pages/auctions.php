<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// current auctions page


// buy an auction
if($config['action']=='buy'){
  CSRF::ValidateToken();
  if(AuctionsClass::RemoveAuction(
    getVar('auctionid','int','post'),
    getVar('qty',      'int','post'),
    TRUE
  )){
    echo '<center><h2>Auction purchased successfully!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
  }
}
if($config['action']=='cancel'){
  CSRF::ValidateToken();
  if(AuctionsClass::RemoveAuction(
    getVar('auctionid','int','post'),
    -1,
    FALSE
  )){
    echo '<center><h2>Auction canceled!</h2><br /><a href="'.getLastPage().'">Back to last page</a></center>';
    ForwardTo(getLastPage(), 2);
    exit();
  }
}


function RenderPage_auctions(){global $config,$html;
  $UseAjaxSource = FALSE;
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
  // list auctions
  $auctions = new AuctionsClass();
  $auctions->QueryAuctions();
  $outputRows = '';
  while($auction = $auctions->getNext()){
    $Item = &$auction['Item'];
    $tags = array(
      'auction id'		=> ((int)$auction['id']),
      'auction seller name'	=> $auction['playerName'],
      'auction expire'		=> 'expires date<br />goes here',
      'auction qty'		=> ((int)$Item->qty),
      'auction price each'	=> FormatPrice($auction['price']),
      'auction price total'	=> FormatPrice($auction['price'] * $Item->qty),
      'item title'		=> $Item->getItemTitle(),
      'item name'			=> $Item->getItemName(),
      'item image url'		=> $Item->getItemImageUrl(),
      'market price percent'	=> 'market price<br />goes here',
      'rowclass'			=> 'gradeU',
    );
    $htmlRow = $outputs['body row'];
    RenderHTML::RenderTags($htmlRow, $tags);
    $outputRows .= $htmlRow;
  }
  unset($auctions, $Item);
  return($outputs['body top']."\n".
         $outputRows."\n".
         $outputs['body bottom']);
}


?>