<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class handles multi-language messages
class LanguageClass{


public static function LoadLanguage(){global $config;
}


// set defaults / type
public static function setDefaults(){global $config;
//  foreach($tempArray as $name => $message){
//    if(empty($config['languages'][$name]))
//      $config['languages'][$name] = $message;
//  }
}


// get message
public static function getMessage($name){global $config;
  if(isset($config['languages'][$config['language']][$name]))
    return($config['languages'][$config['language']][$name]);
  else
    return(NULL);
}


}
?>