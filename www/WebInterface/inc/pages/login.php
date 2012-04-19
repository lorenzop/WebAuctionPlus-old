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
  $output.='
<div id="login-box">
<div id="login-background"><img src="{path=images}wa_bg_login.png" alt="" /></div>
<form action="./" name="login" method="post">
<input type="hidden" name="page"     value="login" />
<input type="hidden" name="lastpage" value="'.getVar('lastpage').'" />
<table border="0" cellspacing="0" cellpadding="0" align="center" id="login-table">
  <tr>
    <td align="right"><label for="WA_Login_Username">Username:&nbsp;</label></td>
    <td><input type="text"  name="WA_Login_Username" value="'.$username.'" class="input" size="30" tabindex="1" id="WA_Login_Username" /></td>
  </tr>
  <tr><td style="height: 10px;"></td></tr>
  <tr>
    <td align="right"><label    for="WA_Login_Password">Password:&nbsp;</label></td>
    <td><input type="password" name="WA_Login_Password" value="'.$password.'" class="input" size="30" tabindex="2" id="WA_Login_Password" /></td>
  </tr>
  <tr><td style="height: 0px;"></td></tr>
  <tr><td colspan="2" align="center"><input type="submit" name="Submit" value="Submit" class="button" tabindex="3" /></td>
  </tr>
</table>
</form>
<script type="text/javascript">
function formfocus() {
  document.getElementById(\'WA_Login_Username\').focus();
}
window.onload = formfocus;
</script>
</div>
';
  return($output);
}


?>