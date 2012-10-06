<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// admin - home


function RenderPage_admin_home(){
  $output=include('inc/pages/admin/menu.php');
  $output.='<center>Future site of the admin page</center>';
  return($output);
}


?>