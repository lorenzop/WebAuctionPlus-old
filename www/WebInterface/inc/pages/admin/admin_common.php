<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


// permissions
if(!$config['user']->isOk())              ForwardTo('./', 0);
if(!$config['user']->hasPerms('isAdmin')) ForwardTo('./', 0);

define('ADMIN_OK', TRUE);


?>