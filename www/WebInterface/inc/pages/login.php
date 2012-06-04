<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


// check login
$username = trim(stripslashes( getVar('WA_Login_Username') ));
$password =      stripslashes( getVar('WA_Login_Password') );
$user = NULL;
if(!empty($username) && !empty($password)){
  $user = new userClass($username,md5($password));
  if($user!==NULL){
    if(getVar('error')==''){
      $lastpage = getVar('lastpage');
      if(empty($lastpage)) ForwardTo('./');
      else                 ForwardTo($lastpage);
      exit();
    }
  }
}
unset($username,$password);


function RenderPage_login(){global $config,$html;
  $config['title'] = 'Login';
  $html->setPageFrame('basic');
  // load page html
  $html->LoadCss('login.css');
  $outputs = RenderHTML::LoadHTML('pages/login.php');
  $html->addTags(array(
    'messages'	=> '',
    'username'	=> getVar('username'),
    'password'	=> '',
    'lastpage'	=> getVar('lastpage'),
  ));
  if($config['demo'])
    $html->addTags(array(
      'username' => 'demo',
      'password' => 'demo'
    ));
  // display error
  if(getVar('error') != '')
    $html->addTags(array(
      'messages' => str_replace('{message}', 'Login Failed', $outputs['error'])
    ));
  return($outputs['body']);
}


?>