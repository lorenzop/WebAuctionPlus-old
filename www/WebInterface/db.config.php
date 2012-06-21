<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


// Database config
function ConnectDB(){global $db,$config;


  $host     = '119.198.193.38';
  $port     = 25565;
  $username = 'simond';
  $password = 'chlrkd12';
  $database = 'herosimond.net';
  $config['table prefix'] = 'WA_';


  $db=@mysql_pconnect($host.($port==0?'':':'.((int)$port)),$username,$password);
  if(!$db || !@mysql_select_db($database,$db)){echo '<p>MySQL Error: '.mysql_error().'</p>'; exit();}
  mysql_query("SET names UTF8");
}


?>