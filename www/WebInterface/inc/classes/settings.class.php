<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class handles settings stored in the database
class SettingsClass{


public static function LoadSettings(){global $config,$settings;
  $query = "SELECT `name`,`value` FROM `".$config['table prefix']."Settings`";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p>Failed to load settings from database.</p>'; exit();}
  if(mysql_num_rows($result) == 0) return;
  while($row = mysql_fetch_assoc($result))
    $settings[$row['name']] = $row['value'];
}


public static function SaveSetting($name, $value=FALSE){global $config,$settings;
  if($value === FALSE) $value = @$settings[$name];
  else                 $settings[$name] = $value;
  echo 'SaveSettings function not finished!';
}


// set defaults / type
public static function setDefault($name, $default='', $type=''){global $config;
  if(empty($config['settings'][$name])){
    $config['settings'][$name] = $default;
  }else{
    if(empty($type)) $type = gettype($default);
    if(    $type=='string' ) $config['settings'][$name] = (string)  $config['settings'][$name];
    elseif($type=='integer') $config['settings'][$name] = (integer) $config['settings'][$name];
    elseif($type=='double' ) $config['settings'][$name] = (float)   $config['settings'][$name];
    elseif($type=='boolean') $config['settings'][$name] = toBoolean($config['settings'][$name]);
  }
}

// get setting
public static function getSetting($name){global $config;
  if(isset($config['settings'][$name])) return($config['settings'][$name]);
  else                                  return(NULL);
}


}


function getSetting($name){global $config;
  return(SettingsClass::getSetting($name));
}


?>