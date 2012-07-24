<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// my items page


if(!$config['user']->isOk()) ForwardTo('./', 0);


// mail item stack
if($config['action'] == 'mailitem'){
  CSRF::ValidateToken();
  ItemFuncs::MailStack( getVar('id','int','post') );
  if(!empty($config['error'])){
    echo '<p style="color: red;">'.$config['error'].'</font>';
    exit();
  }
}


function RenderPage_myitems(){global $config,$html;
  $UseAjaxSource = FALSE;
  $config['title'] = 'My Items';
  // load page html
  $outputs = RenderHTML::LoadHTML('pages/myitems.php');
  if(!is_array($outputs)) {echo 'Failed to load html!'; exit();}
  // load javascript
  $html->addToHeader($outputs['header']);
  // display error
  $msg = '';
  if(isset($config['error']))
    $msg .= str_replace('{message}', $config['error'], $outputs['error']);
  if(isset($_SESSION['error'])){
    $msg .= str_replace('{message}', $_SESSION['error'], $outputs['error']);
    unset($_SESSION['error']);
  }
  // display success
  if(isset($_SESSION['success'])){
  	$msg .= str_replace('{message}', $_SESSION['success'], $outputs['success']);
    unset($_SESSION['success']);
  }
  $outputs['body top'] = str_replace('{messages}', $msg, $outputs['body top']);
  // list items
  $Items = QueryItems::QueryInventory($config['user']->getName());
  if($Items == FALSE) {echo 'Unable to query items!'; exit();}
  $outputRows = '';
  while($Item = $Items->getNext()) {
    $tags = array(
      'item display'       => $Item->getDisplay(),
      'item row id'        => $Item->getItemId(),
      'item qty'           => $Item->getItemQty(),
      'market price each'  => 'market price<br />goes here',
      'market price total' => 'market price<br />goes here',
//number_format((double)$auction['price'],2)
//number_format((double)($auction['price'] * $Item->qty),2)
//  $marketPrice=getMarketPrice($id, 0);
//  $marketTotal=$marketPrice*$quantity;
//  if($marketPrice==0){
//    $marketPrice='0';
//    $marketTotal='0';
//  }
//  echo '  <tr class="gradeC">'."\n";
      'rowclass'           => 'gradeU',
    );
    $htmlRow = $outputs['body row'];
    RenderHTML::RenderTags($htmlRow, $tags);
    $outputRows .= $htmlRow;
  }
  unset($Items, $Item);
  return($outputs['body top']."\n".
         $outputRows."\n".
         $outputs['body bottom']);
}


?>