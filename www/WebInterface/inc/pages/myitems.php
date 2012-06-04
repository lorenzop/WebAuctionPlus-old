<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// my items page


// mail item stack
if($config['action'] == 'mailitem'){
  ItemFuncs::MailStack( getVar('id',  'int') );
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
  // list items
  $items = new ItemsClass();
  $items->QueryItems($config['user']->getName(),'');
  $outputRows = '';
  while($itemRow = $items->getNext()){
    $Item = &$itemRow['Item'];
    $tags = array(
      'item row id'		=> ((int)$itemRow['id']),
      'item qty'			=> ((int)$Item->qty),
      'item title'		=> $Item->getItemTitle(),
      'item name'			=> $Item->getItemName(),
      'item image url'		=> $Item->getItemImageUrl(),
      'market price each'	=> 'market price<br />goes here',
      'market price total'	=> 'market price<br />goes here',
//number_format((double)$auction['price'],2)
//number_format((double)($auction['price'] * $Item->qty),2)
//  $marketPrice=getMarketPrice($id, 0);
//  $marketTotal=$marketPrice*$quantity;
//  if($marketPrice==0){
//    $marketPrice='0';
//    $marketTotal='0';
//  }
//  echo '  <tr class="gradeC">'."\n";
      'rowclass'			=> 'gradeU',
    );
    $htmlRow = $outputs['body row'];
    RenderHTML::RenderTags($htmlRow, $tags);
    $outputRows .= $htmlRow;
  }
  unset($items, $Item);
  return($outputs['body top']."\n".
         $outputRows."\n".
         $outputs['body bottom']);
}


?>