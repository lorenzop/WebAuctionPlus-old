<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class SettingsClass{


public static function LoadSettings(){global $config,$settings;
  $query = "SELECT `name`,`value` FROM `".$config['table prefix']."Settings`";
  $result = RunQuery($query, __file__, __line__);
  if(!$result){echo '<p>Failed to load settings from database.</p>'; exit();}
  if(mysql_num_rows($result) == 0) return;
  while($row = mysql_fetch_assoc($result)){
    $settings[$row['name']] = $row['value'];
  }
}


public static function SaveSetting($name, $value=FALSE){global $config,$settings;
  if($value === FALSE) $value = @$settings[$name];
  else                 $settings[$name] = $value;

}


}
?>