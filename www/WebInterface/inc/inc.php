<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


NoPageCache();
// load item classes
require($lpaths['classes'].'csrf.class.php');
require($lpaths['classes'].'enum.dao.php');
require($lpaths['classes'].'item.dao.php');
require($lpaths['classes'].'auction.dao.php');
require($lpaths['classes'].'item.functions.php');
require($lpaths['classes'].'auction.functions.php');
require($lpaths['classes'].'queryitems.class.php');
require($lpaths['classes'].'queryauctions.class.php');
require($lpaths['classes'].'logSales.class.php');


// no page cache
function NoPageCache(){
  if(defined('NOPAGECACHE_HAS_RUN')) return;
  if(headers_sent()) return;
  @header('Expires: Mon, 26 Jul 1990 05:00:00 GMT');
  @header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
  @header('Cache-Control: no-store, no-cache, must-revalidate');
  @header('Cache-Control: post-check=0, pre-check=0', false);
  @header('Pragma: no-cache');
  define('NOPAGECACHE_HAS_RUN', TRUE);
}  


// php sessions
function session_init(){
  if(defined('SESSION_INIT_HAS_RUN')) return;
  if(function_exists('session_status'))
    if(session_status() == PHP_SESSION_ACTIVE) return;
  session_start();
  define('SESSION_INIT_HAS_RUN', TRUE);
}


// get last page
function getLastPage(){global $config;
  if(!empty($config['lastpage'])) return($config['lastpage']);
  $lastpage = getVar('lastpage');
  if(empty($lastpage))
    $lastpage = @$_SERVER['HTTP_REFERER'];
  elseif(startsWith($lastpage,'page-'))
    $lastpage = './?page='.substr($lastpage,strlen('page-'));
  if(empty($lastpage)) $lastpage = './';
  $config['lastpage'] = $lastpage;
  return($lastpage);
}


// starts with
function startsWith($haystack, $needle, $ignoreCase=FALSE){
  if(empty($haystack) || empty($needle)) return(FALSE);
  if($ignoreCase){
    $haystack = strtolower($haystack);
    $needle   = strtolower($needle);}
  return(substr($haystack, 0, strlen($needle)) === $needle);
}
// ends with
function endsWith($haystack, $needle, $ignoreCase=FALSE){
  if(empty($haystack) || empty($needle)) return(FALSE);
  if($ignoreCase){
    $haystack = strtolower($haystack);
    $needle   = strtolower($needle);}
  $length = strlen($needle);
  if($length == 0) return(FALSE);
  return(substr($haystack, 0-$length) === $needle);
}


// format price
function FormatPrice($price){global $config;
  return( SettingsClass::getString('Currency Prefix').
          number_format((double)$price,2).
          SettingsClass::getString('Currency Postfix') );
}


// render time
function GetTimestamp(){
  $qtime=explode(' ',microtime()); return($qtime[0]+$qtime[1]);}
function GetRenderTime($roundnum=3){global $qtime;
  if($qtime==0){return(0);}
  return(round(GetTimestamp()-$qtime,$roundnum));}
$config['qtime']=GetTimestamp();


// forward to url (caution: doesn't exit if headers already sent)
function ForwardTo($url,$delay=0){
  if(headers_sent() || $delay!=0){echo '<header><meta http-equiv="refresh" content="'.((int)$delay).';url='.$url.'"></header>';
  }else{header('HTTP/1.0 302 Found'); header('Location: '.$url);} exit();}
// scroll to bottom
function ScrollToBottom(){
  echo '<script type="text/javascript"><!--//'."\n".
       'document.scrollTop=document.scrollHeight; '.
       'window.scroll(0,document.body.offsetHeight); '.
       '//--></script>';
}


// database functions
function RunQuery($query,$_file='',$_line=0){global $db,$num_queries;
  if(!$db){ConnectDB();
    if(!$db){echo '<p>Database not connected..</p>'; exit();}}
  $result=mysql_query($query,$db); $num_queries++;
  if(!$result){echo '<p>MySQL ERROR - File: '.$_file.' Line: '.$_line.' '.mysql_error().'</p><p>'.$query.'</p>'; exit();}
  return($result);
}
// sanitize for mysql
function mysql_san($text){global $db;
  if(!$db){ConnectDB();
    if(!$db){echo '<p>Database not connected..</p>'; exit();}}
  // san an array
  if(is_array($text)){return(array_map(__METHOD__,$text));}
  if(empty($text)){return('');}
  return(mysql_real_escape_string($text));}
//function getConfig($name,$default=''){global $config;
//  $result=QueryDB("SELECT `value` FROM `".$config['table prefix']."Data` WHERE `name`='".mysql_real_escape_string($name)."'",__file__,__line__);
//  if(mysql_num_rows($result)==0){
//    $result=QueryDB("INSERT INTO `".$config['table prefix']."Data` (`name`,`value`) VALUES ('".mysql_real_escape_string($name)."','".mysql_real_escape_string($default)."')",__file__,__line__);
//    return($default);
//  }else{
//    $row=mysql_fetch_assoc($result);
//    return(@$row['value']);
//  }
//}


// trim / from path
function trimPath($path){
  $path=str_replace('\\','/',$path);
  while(substr($path,0,1)=='/'){$path=substr($path,1);}
  while(substr($path,-1)=='/'){$path=substr($path,0,-1);}
  return($path);}
// sanitize file names
function SanFilename($filename){
  if(is_array($filename)){return(array_map(__METHOD__,$filename));}
  $filename=trim($filename);
  if(empty($filename)){return('');}
  // shouldn't contain /
  if(strpos($filename,'/')!==FALSE){die('stop SanFilename() '.$filename);}
  // remove dots from front and end
  while(substr($filename,0,1)=='.'){$filename=substr($filename,1);}
  while(substr($filename,-1)=='.'){$filename=substr($filename,0,-1);}
  // clean string
  $filename=str_replace(str_split(preg_replace("/([[:alnum:]\(\)_\.'& +?=-]*)/","_",$filename)),"_",$filename);
  return(trim($filename));}
// format file size
function fromBytes($size){
  if($size<0){$size=0;}
  if(      $size<1024){         return(round($size              ,0).'&nbsp;Bytes');
  }else if($size<1048576){      return(round($size/1024         ,2).'&nbsp;KB');
  }else if($size<1073741824){   return(round($size/1048576      ,2).'&nbsp;MB');
  }else if($size<1099511627776){return(round($size/1073741824   ,2).'&nbsp;GB');
  }else{                        return(round($size/1099511627776,2).'&nbsp;TB');
}}


// string to seconds
function toSeconds($text){
  $a=substr($text,-1);
  if     ($a=='m') return( ((int)$text)*60       );
  else if($a=='h') return( ((int)$text)*3600     );
  else if($a=='d') return( ((int)$text)*86400    );
  else if($a=='w') return( ((int)$text)*604800   );
  else if($a=='n') return( ((int)$text)*2592000  );
  else if($a=='y') return( ((int)$text)*31536000 );
  else             return(  (int)$text           );
}
// seconds to string
function fromSeconds($seconds){$output='';
  if($seconds>31536000){
    $t=floor($seconds/31536000); $seconds=$seconds%31536000;
    $output.=' '.$t.' Year'; if($t>1){$output.='s';}}
  if($seconds>86400){
    $t=floor($seconds/86400); $seconds=$seconds%86400;
    $output.=' '.$t.' Day'; if($t>1){$output.='s';}}
  if($seconds>3600){
    $t=floor($seconds/3600); $seconds=$seconds%3600;
    $output.=' '.$t.' Hour'; if($t>1){$output.='s';}}
  if($seconds>60){
    $t=floor($seconds/60); $seconds=$seconds%60;
    $output.=' '.$t.' Minute'; if($t>1){$output.='s';}}
  if($seconds>0){
    $output.=' '.$seconds.' Second'; if($seconds>1){$output.='s';}}
  return(trim($output));}


// to roman numerals
function numberToRoman($num){
  if($num > 15) return((string)$num);
  $num = ((int)$num);
  $result = '';
  $lookup = array(
    'M' => 1000,
    'CM'=> 900,
    'D' => 500,
    'CD'=> 400,
    'C' => 100,
    'XC'=> 90,
    'L' => 50,
    'XL'=> 40,
    'X' => 10,
    'IX'=> 9,
    'V' => 5,
    'IV'=> 4,
    'I' => 1);
  foreach($lookup as $roman=>$value){
    $matches = intval($num / $value);
    $result .= str_repeat($roman, $matches);
    $num = $num % $value;
  }
  return $result;
}


//// send email
//function send_mail($to_email,$body,$subject,$from_email,$from_name=''){$headers='';
//  // Common Headers
//  if($from_name==''){$from=$from_email;}else{$from=$from_name.' <'.$from_email.'>';}
//  $headers.='From: '.       $from."\r\n";
//  $headers.='Reply-To: '.   $from."\r\n";
//  $headers.='Return-Path: '.$from."\r\n";
//  $headers.='Message-ID: <'.time().'-'.$from_email.">\r\n";
//  $headers.='X-Mailer: PHP v'.phpversion()."\r\n"; // help avoid spam-filters
//  // prep text
//  $body=strip_tags(str_replace("\r",'',$body));
//  $body=str_replace("<br>\r\n","\n",$body)."\r\n\r\n";
//  // send the email
//  ini_set('sendmail_from',$from_email); // force the from address
//  $mail_sent=mail($to_email,$subject,"\r\n".$body,$headers);
//  ini_restore('sendmail_from');
//  return($mail_sent);
//}


//// get root domain for cookies
//function GetCookieDomain(){
//  $cookie_domain=$_SERVER['SERVER_NAME'];
//  if($cookie_domain==''){$cookie_domain=$_SERVER['HTTP_HOST'];}
//  while(substr($cookie_domain,0,1)=='.'){$cookie_domain=substr($cookie_domain,1);}
//  while(substr($cookie_domain,-1)=='.'){$cookie_domain=substr($cookie_domain,0,-1);}
//  $a=explode('.',$cookie_domain);
//  if(count($a)>2){$cookie_domain=$a[count($a)-2].'.'.$a[count($a)-1];
//  }else{          $cookie_domain=implode('.',$a);} $a='';
//  if(empty($cookie_domain)) return('');
//  else                      return('.'.$cookie_domain);
//}


//function indent($json) {
//    $result      = '';
//    $pos         = 0;
//    $strLen      = strlen($json);
//    $indentStr   = '  ';
//    $newLine     = "\n";
//    $prevChar    = '';
//    $outOfQuotes = true;
//    for ($i=0; $i<=$strLen; $i++) {
//        // Grab the next character in the string.
//        $char = substr($json, $i, 1);
//        // Are we inside a quoted string?
//        if ($char == '"' && $prevChar != '\\') {
//            $outOfQuotes = !$outOfQuotes;
//        // If this character is the end of an element, 
//        // output a new line and indent the next line.
//        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
//            $result .= $newLine;
//            $pos --;
//            for ($j=0; $j<$pos; $j++) {
//                $result .= $indentStr;
//            }
//        }
//        // Add the character to the result string.
//        $result .= $char;
//        // If the last character was the beginning of an element, 
//        // output a new line and indent the next line.
//        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
//            $result .= $newLine;
//            if ($char == '{' || $char == '[') {
//                $pos ++;
//            }
//            for ($j = 0; $j < $pos; $j++) {
//                $result .= $indentStr;
//            }
//        }
//        $prevChar = $char;
//    }
//    return $result;
//}


?>
