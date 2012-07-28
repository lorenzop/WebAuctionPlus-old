<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// enum object


// example enum
//class test extends Enum{
//  function __construct(){
//    self::$enumValues = array(
//      'test1'     => 1,
//      'test2'     => 2
//    );
//    self::construct(func_get_args());
//  }
//}


abstract class Enum{

protected $value = FALSE;
protected static $enumValues = array();


//function __construct(){
protected function construct($args){
  self::$enumValues = array_change_key_case(self::$enumValues, CASE_LOWER);
  if(!isset(self::$enumValues['_default_']))
    self::$enumValues = array('_default_' => FALSE) + self::$enumValues;
  // value argument
  if(count($args)==1)
    $this->value = self::Validate($args[0]);
}


// set value
public function setValue($value){
  $this->value = self::Validate($value);
  return($this->value);
}


// validate input
public static function Validate($value){
  $type = gettype($value);
  if($type == 'string'){
    $value = strtolower($value);
    if(isset(self::$enumValues[$value])) return(self::$enumValues[$value]);
    else return(FALSE);
  }elseif($type == 'integer')
echo 'fail?';
//return( self::fromString(self::toString($ItemTable))   );
  elseif($type == 'object')
//return( self::toString  ($ItemTable->value)            );
echo 'fail?';
  return(FALSE);
}
public static function ValidateStr($value){
  return(getString(self::Validate($value)));
}


// get value
public function getValue($type='str'){
  //if(    $type=='str'   || $type=='string' )
  if($type=='int'   || $type=='integer')
    return( (int)$this->value );
  return( self::getString($this->value) );
  //return($this->value);
}
public static function getString($value){
  if($value == FALSE) return(FALSE);
  return(array_search($value, self::$enumValues));
}


//public static function toString($value){
//  if($value == self::Items   ) return("Items"   );
//  if($value == self::Auctions) return("Auctions");
//  if($value == self::Mail    ) return("Mail"    );
//  return(FALSE);
//}
//public static function fromString($str){
//  $str = strtolower($str);
//  if($str == 'items'   ) return(self::Items   );
//  if($str == 'auctions') return(self::Auctions);
//  if($str == 'mail'    ) return(self::Mail    );
//  return(-1);
//}


}
?>