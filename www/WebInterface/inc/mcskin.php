<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


function getDefaultSkin(){
  // default skin
  return(base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAABGdBTUEAALGPC/xhBQAAAwBQTFRFAAAAHxALIxcJJBgIJBgKJhgL'.
    'JhoKJxsLJhoMKBsKKBsLKBoNKBwLKRwMKh0NKx4NKx4OLR0OLB4OLx8PLB4RLyANLSAQLyIRMiMQMyQRNCUSOigUPyoVKCgoPz8/'.
    'JiFbMChyAFtbAGBgAGhoAH9/Qh0KQSEMRSIOQioSUigmUTElYkMvbUMqb0UsakAwdUcvdEgvek4za2trOjGJUj2JRjqlVknMAJmZ'.
    'AJ6eAKioAK+vAMzMikw9gFM0hFIxhlM0gVM5g1U7h1U7h1g6ilk7iFo5j14+kF5Dll9All9BmmNEnGNFnGNGmmRKnGdIn2hJnGlM'.
    'nWpPlm9bnHJcompHrHZaqn1ms3titXtnrYBttIRttolsvohst4Jyu4lyvYtyvY5yvY50xpaA////AAAAAAAAAAAAAAAAAAAAAAAA'.
    'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
    'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
    'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
    'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
    'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
    'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
    'PSUN6AAAAQB0Uk5T////////////////////////////////////////////////////////////////////////////////////'.
    '////////////////////////////////////////////////////////////////////////////////////////////////////'.
    '////////////////////////////////////////////////////////////////////////////////////////////////////'.
    '////////////////////////////////////////////////////////AFP3ByUAAAAYdEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2'.
    'My4zNqnn4iUAAAKjSURBVEhLpZSLVtNAEIYLpSlLSUITLCBaGhNBQRM01M2mSCoXNUURIkZFxQvv/wz6724Wij2HCM7J6UyS/b+d'.
    'mZ208rsww6jiqo4FhannZb5yDqjaNgDVwE/8JAmCMqF6fwGwbU0CKjD/+oAq9jcM27gxAFpNQxU3Bwi9Ajy8fgmGZuvaGAcIuwFA'.
    '12CGce1jJESr6/Ot1i3Tnq5qptFqzet1jRA1F2XHWQFAs3RzwTTNhQd3rOkFU7c0DijmohRg1TR9ZmpCN7/8+PX954fb+sTUjK7V'.
    'LKOYi1IAaTQtUrfm8pP88/vTw8M5q06sZoOouSgHEDI5vrO/eHK28el04yxf3N8ZnyQooZiLfwA0arNb6d6bj998/+vx8710a7bW'.
    '4E2Uc1EKsEhz7WiQBK9eL29urrzsB8ngaK1JLDUXpYAkGSQH6e7640fL91dWXjxZ33138PZggA+Sz0WQlAL4gmewuzC1uCenqXev'.
    'MPWc9XrMX/VXh6Hicx4ByHEeAfRg/wtgSMAvz+CKEkYAnc5SpwuD4z70PM+hUf+4348ixF7EGItjxmQcCx/Dzv/SOkuXAF3PdT3G'.
    'IujjGLELNYwxhF7M4oi//wsgdlYZdMXCmEUUSsSu0OOBACMoBTiu62BdRPEjYxozXFyIpK7IAE0IYa7jOBRqGlOK0BFq3Kdpup3D'.
    'thFwP9QDlBCGKEECoHEBEDLAXHAQMQnI8jwFYRQw3AMOQAJoOADoAVcDAh0HZAKQZUMZdC43kdeqAPwUBEsC+M4cIEq5KEEBCl90'.
    'mR8CVR3nxwCdBBS9OAe020UGnXb7KcxzPY9SXoEEIBZtgE7UDgBKyLMhgBS2YdzjMJb4XHRDAPiQhSGjNOxKQIZTgC8BiMECgarx'.
    'prjjO0OXiV4MAf4A/x0nbcyiS5EAAAAASUVORK5CYII='
  ));
}


function FlipImage(&$img){
  $size_x = imagesx($img);
  $size_y = imagesy($img);
  $temp = imagecreatetruecolor($size_x, $size_y);
  $x = imagecopyresampled($temp, $img, 0, 0, ($size_x-1), 0, $size_x, $size_y, 0-$size_x, $size_y);
  return $temp;
}


// render skin (head / body / back)
function RenderSkin($data, $view='head', $cache=TRUE){
  if($view != 'body' && $view != 'back') $view = 'head';
  // create image
  $source = imagecreatefromstring($data);
  if($view == 'head')
    $rendered = imagecreatetruecolor(60, 60);
  else // body or back
    $rendered = imagecreatetruecolor(120, 240);
  $b = 60;
  $s = 8;
  // fill new image with pink
  $pink = imagecolorallocate($rendered, 255, 0, 255);
  imagefilledrectangle($rendered, 0, 0, 120, 240, $pink);
  // set transparent color
  if($view != 'head')
    imagecolortransparent($rendered, $pink);
  // create flipped version
  $fsource = FlipImage($source);
  // imagecopyresampled( dst_image , src_image , dst_x , dst_y , src_x , src_y , dst_w , dst_h , src_w , src_h )

  // full body
  if($view == 'body'){
    // head
    imagecopyresampled($rendered, $source, $b / 2, 0, $s, $s, $b, $b, $s, $s);
    // head accesory
    imagecopyresampled($rendered, $source, $b / 2, 0, $s * 5, $s, $b, $b, $s, $s);
    // body
    imagecopyresampled($rendered, $source, $b / 2, $b, $s * 2.5, $s * 2.5, $b, $b * 1.5, $s, $s * 1.5);
    // left arm
    imagecopyresampled($rendered, $source, $b * 1.5, $b, $s * 5.5, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);
    // right arm
    imagecopyresampled($rendered, $fsource, 0, $b, $s * 2, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);
    // left leg
    imagecopyresampled($rendered, $source, $b / 2, $b * 2.5, $s / 2, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);
    // right leg
    imagecopyresampled($rendered, $fsource, $b * 1, $b * 2.5, $s * 7, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);

  // full back
  } elseif($view == 'back'){
    // head
    imagecopyresampled($rendered, $source, $b / 2, 0, $s * 3, $s, $b, $b, $s, $s);
    // head accesory
    imagecopyresampled($rendered, $source, $b / 2, 0, $s * 7, $s, $b, $b, $s, $s);
    // body
    imagecopyresampled($rendered, $source, $b / 2, $b, $s * 4, $s * 2.5, $b, $b * 1.5, $s, $s * 1.5);
    // left arm
    imagecopyresampled($rendered, $source, $b * 1.5, $b, $s * 6.5, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);
    // right arm
    imagecopyresampled($rendered, $fsource, 0, $b, $s * 1, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);
    // left leg
    imagecopyresampled($rendered, $source, $b * 1, $b * 2.5, $s * 1.5, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);
    // right leg
    imagecopyresampled($rendered, $fsource, $b / 2, $b * 2.5, $s * 6, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);

  // head only
  }else{
    // head
    imagecopyresampled($rendered, $source, 0, 0, $s, $s, $b, $b, $s, $s);
  }

  if(!isset($_GET['testing'])){
    // browser cache 3 hours
    $expire_time = 3*60*60;
    if(!$cache) $expire_time = 0 - $expire_time;
    header('Cache-Control: private, max-age='.$expire_time.', pre-check='.$expire_time);
    header('Pragma: private');
    header('Expires: '.@date(DATE_RFC822,time() + $expire_time));
    // display rendered image
    header('Content-type: image/png');
  }  
  imagepng($rendered);
  imagedestroy($source);
  imagedestroy($rendered);
  exit();
}


$view     = getVar('view');
$username = getVar('user');
$username = str_replace('\\', '', str_replace('/', '', htmlspecialchars($username) ));
if(empty($username)){
  $data = getDefaultSkin();
  $cache = FALSE;
}else{  
  $data = @file_get_contents('http://www.minecraft.net/skin/'.$username.'.png');
  $cache = TRUE;
  if($data == FALSE) {$data = getDefaultSkin(); $cache = FALSE;}
}  
// display head only
RenderSkin($data, @$_GET['view'], $cache);
exit();


?>