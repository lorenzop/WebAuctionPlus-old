<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// admin - home
require('admin_common.php');
if(!defined('ADMIN_OK')){echo 'Permission Denied!'; exit();}


function RenderPage_admin_home(){global $config;
  // load page html
  $config['title'] = 'Dashboard';
  $outputs = RenderHTML::LoadHTML('pages/admin/home.php');
  if(!empty($outputs['css']))
    $config['html']->AddCss($outputs['css']);
  $output = $outputs['body'];
  $tags = array(
    'total auctions'       => 'N/A',
    'total buynows'        => 'N/A',
    'total items for sale' => 'N/A',
    'total accounts'       => 'N/A',
  );
  RenderHTML::RenderTags($output, $tags);
//  $output.='<center>Future site of the admin page</center>';
  return($output);
}


?>