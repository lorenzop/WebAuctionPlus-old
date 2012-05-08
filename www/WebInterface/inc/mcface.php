<?php


$username = @$_GET['username'];
$username = str_replace('\\', '', str_replace('/', '', htmlspecialchars($username) ));
// image cache control
image_cache_control( substr(@$_SERVER['SCRIPT_NAME'], strrpos(@$_SERVER['SCRIPT_NAME'],'/')+1) );
// get image
$image = @file_get_contents('http://minotar.net/avatar/'.$username);
// load default face
if(empty($image)) $image = file_get_contents('images/default_face.png');
header('Content-type: image/png');
echo $image;
exit();

// image cache control
function image_cache_control($file){
  $expire_time = 10800;
  $filemtime = filemtime($file);
  // browser cache 3 hours
  header('Cache-Control: private, max-age='.$expire_time.', pre-check='.$expire_time);
  header('Pragma: private');
  header('Expires: '.date(DATE_RFC822,time() + $expire_time));
  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
    strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==$filemtime &&
    time()-strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < $expire_time ){
    // send the last mod time of the file back
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $filemtime).' GMT', true, 304);
    header('Content-type: image/png');
    exit();
  }
  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $filemtime).' GMT');
}


?>