<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


// check login
$username = trim(stripslashes( getVar('WA_Login_Username') ));
$password =      stripslashes( getVar('WA_Login_Password') );
$user = NULL;
if(!empty($username) && !empty($password)){
  $user = new userClass($username,md5($password));
  if($user!==NULL){
    $_SESSION[$config['session name']] = $user->getName();
    if(getVar('error')==''){
      $lastpage = getVar('lastpage');
      if(empty($lastpage)) ForwardTo('./');
      else                 ForwardTo($lastpage);
      exit();
    }
  }
}
unset($username,$password);


function RenderPage_login(){global $config,$html; $output='';
  $config['title'] = 'Login';
  $username=''; $password='';
  $html->setPageFrame('basic');
  $html->loadCss('login.css');
  if(getVar('error')!='')    $output.='<h2 style="color: #ff0000;">Login Failed</h2>'."\n";
  if($config['demo']===TRUE){$username='demo'; $password='demo';}
  $output.=
    '<div id="login-box">'."\n".
    '<div id="login-background"><img src="{path=images}wa_bg_login.png" alt="" /></div>'."\n".
    '<form action="./" name="login" method="post">'."\n".
    '<input type="hidden" name="page"     value="login" />'."\n".
    '<input type="hidden" name="lastpage" value="'.getVar('lastpage').'" />'."\n".
    '<table border="0" cellspacing="0" cellpadding="0" align="center" id="login-table">'."\n".
    "  <tr>\n".
    '    <td align="right"><label name="WA_Login_Username">Username:</label></td>'."\n".
    '    <td><input type="text"   name="WA_Login_Username" value="'.$username.'" class="input" size="30" tabindex="1" autofocus="autofocus" /></td>'."\n".
    "  </tr>\n".
    '  <tr><td style="height: 10px;"></td></tr>'."\n".
    "  <tr>\n".
    '    <td align="right"><label   name="WA_Login_Password">Password:</label></td>'."\n".
    '    <td><input type="password" name="WA_Login_Password" value="'.$password.'" class="input" size="30" tabindex="2" /></td>'."\n".
    "  </tr>\n".
    '  <tr><td style="height: 0px;"></td></tr>'."\n".
    '  <tr><td colspan="2"><input type="submit" name="Submit" value="Submit" class="button" tabindex="3" /></td>'."\n".
    "  </tr>\n".
    "</table>\n".
    "</div>\n";
    "</form>\n";
  return($output);
}


?>
