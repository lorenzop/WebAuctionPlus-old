<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// auction object
class AuctionDAO{


protected $tableRowId    = 0;
protected $playerName    = '';
protected $Item          = FALSE;
protected $price         = 0.0;
protected $created       = 0;
protected $allowBids     = FALSE;
protected $currentBid    = 0.0;
protected $currentWinner = '';


function __construct($tableRowId=0, $playerName='', $Item=FALSE, $price=0.0, $created=0, $allowBids=FALSE, $currentBid=0.0, $currentWinner=''){
  $this->tableRowId = (int)$tableRowId;
  $this->playerName = $playerName;
  $this->Item       = $Item;
  $this->price      = (float)$price;
  $this->created    = (int)$created;
  $this->allowBids  = (boolean)$allowBids;
  $this->currentBid = (float)$currentBid;
  $this->currentWinner = $currentWinner;
}


// get table row id
public function getTableRowId(){
  return((int)$this->tableRowId);
}
// get seller
public function getSeller(){
  return($this->playerName);
}
// get item
public function getItem(){
  return($this->Item);
}
// get price
public function getPrice(){
  return($this->price);
}
public function getPriceTotal(){
  if(!$this->Item) return('ERROR');
  $qty = $this->Item->getItemQty();
  return( ((float)$this->price) * ((float)$qty) );
}
// get date created
public function getCreated(){
  return($this->created);
}
// get date expire
public function getExpire(){
  return('expires date<br />goes here');
}


}
?>