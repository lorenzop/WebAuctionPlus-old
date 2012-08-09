<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// login page
define('LOGIN_FORM_USERNAME', 'WA_Login_Username');
define('LOGIN_FORM_PASSWORD', 'WA_Login_Password');


NoPageCache();
// check login
function doCheckLogin(){global $config;
  if(!isset($_POST[LOGIN_FORM_USERNAME]) || !isset($_POST[LOGIN_FORM_PASSWORD])) return;
  $username = trim(stripslashes( @$_POST[LOGIN_FORM_USERNAME] ));
  $password =      stripslashes( @$_POST[LOGIN_FORM_PASSWORD] );
  session_init();
  if(CSRF::isEnabled() && !isset($_SESSION[CSRF::SESSION_KEY])){
    echo '<p style="color: red;">PHP Session seems to have failed!</p>';
    CSRF::ValidateToken();
    exit();
  }
  CSRF::ValidateToken();
  $password = md5($password);
  $config['user']->doLogin($username, $password);
  if($config['user']->isOk() && getVar('error')==''){
  	// success
    $lastpage = getLastPage();
    if(strpos($lastpage,'login')!==FALSE) $lastpage = './';
    ForwardTo($lastpage);
    exit();
  }
  unset($username, $password);
}
doCheckLogin();  


function RenderPage_login(){global $config,$html;
  $config['title'] = 'Login';
  $html->setPageFrame('basic');
  // load page html
  $html->LoadCss('login.css');
  $outputs = RenderHTML::LoadHTML('pages/login.php');
  $html->addTags(array(
    'messages'	=> '',
    'username'	=> $config['demo'] ? 'demo' : getVar(LOGIN_FORM_USERNAME),
    'password'	=> $config['demo'] ? 'demo' : '',
    'lastpage'	=> getLastPage(),
  ));
  // display error
  if(getVar('error') != '')
    $html->addTags(array(
      'messages' => str_replace('{message}', 'Login Failed', $outputs['error'])
    ));
  return($outputs['body']);
}


?>