<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// CSRF - cross-site request forgery
//
// modified and classified by lorenzop of PoiXson
// originally from http://halls-of-valhalla.org
//
// To use:
//   1. Include this file
//   2. Add getTokenURL() to URLs with GET parameters
//   3. Add getTokenForm() to forms
//   4. When validating GET and POST data, execute validateCSRFToken().


class CSRF{

const session_key = 'csrf token';


// get token
public static function getToken(){
  if(!isset($_SESSION[self::session_key]) || empty($_SESSION[self::session_key]))
    $_SESSION[self::session_key] = self::GenerateToken();
  return($_SESSION[self::session_key]);
}
// generate new token
protected static function GenerateToken(){
  return(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff)
  ));
}


// validate token
public static function ValidateToken(){
  if(!self::isValidToken()){
    header('Location: ./'); exit();}
}
protected static function isValidToken(){
  if(isset($_POST[self::session_key])) return(self::getToken() === $_POST[self::session_key]);
  if(isset($_GET [self::session_key])) return(self::getToken() === $_GET [self::session_key]);
  return false;
}


// token for url
public static function getTokenURL(){
  return('&amp;token='.self::getToken());
}
// token for form
public static function getTokenForm(){
  return '<input type="hidden" name="token" value="'.self::getToken().'" />';
}


}
?>
