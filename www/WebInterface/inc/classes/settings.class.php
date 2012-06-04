<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class handles settings stored in the database
class SettingsClass{


public static function LoadSettings(){global $config;
  $query = "SELECT `name`,`value` FROM `".$config['table prefix']."Settings`";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p>Failed to load settings from database.</p>'; exit();}
  if(mysql_num_rows($result) == 0) return;
  while($row = mysql_fetch_assoc($result))
    $config['settings'][$row['name']] = array(
      'value'	=> $row['value'],
      'changed'	=> FALSE,
    );
}


public static function SaveSettings(){global $config;
  echo '<h1>SaveSettings function not finished!</h1>';
}


// set defaults / type
public static function setDefault($name, $default='', $type='', $setIfEmpty=TRUE){global $config;
  // set default
  if($setIfEmpty){
    if(empty($config['settings'][$name]['value']))
      $config['settings'][$name]['value'] = $default;
  }else{
    if(!isset($config['settings'][$name]['value']))
      $config['settings'][$name]['value'] = $default;
  }
  // set type
  if(empty($type)) $type = gettype($default);
  if(    $type=='string' ) $config['settings'][$name]['value'] = (string)  $config['settings'][$name]['value'];
  elseif($type=='integer') $config['settings'][$name]['value'] = (integer) $config['settings'][$name]['value'];
  elseif($type=='double' ) $config['settings'][$name]['value'] = (float)   $config['settings'][$name]['value'];
  elseif($type=='boolean') $config['settings'][$name]['value'] = toBoolean($config['settings'][$name]['value']);
}

// get setting
public static function getString($name){global $config;
  if(isset($config['settings'][$name]['value']))
    return($config['settings'][$name]['value']);
  else
    return(NULL);
}


public static function setSetting($name, $value){global $config;
  $config['settings'][$name]['value']   = $value;
  $config['settings'][$name]['changed'] = TRUE;
}


}
?>