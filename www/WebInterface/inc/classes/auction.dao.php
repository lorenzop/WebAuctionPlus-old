<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// auction object
class AuctionDAO{


protected $tableRowId    = -1;
protected $playerName    = '';
protected $Item          = FALSE;
protected $price         = 0.0;
protected $created       = -1;
protected $allowBids     = FALSE;
protected $currentBid    = 0.0;
protected $currentWinner = '';


function __construct($tableRowId=0, $playerName='', $Item=FALSE, $price=0.0, $created=0, $allowBids=FALSE, $currentBid=0.0, $currentWinner=''){
  $this->tableRowId = ($tableRowId<1  ? -1 : (int)  $tableRowId);
  $this->playerName = (string)$playerName;
  $this->Item       = $Item;
  $this->price      = ($price<0.0     ? 0.0: (float)$price);
  $this->created    = ($created<0     ? -1 : (int)  $created);
  $this->allowBids  = (boolean)$allowBids;
  $this->currentBid = ($currentBid<0.0? 0.0: (float)$currentBid);
  $this->currentWinner = (string)$currentWinner;
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
public function getItemCopy(){
  return($this->Item->getCopy());
}
// get price
public function getPrice(){
  return( (float)$this->price );
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