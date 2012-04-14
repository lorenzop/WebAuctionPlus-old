<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}


// check login
$username = trim(stripslashes( getVar('WA_Login_Username') ));
$password =      stripslashes( getVar('WA_Login_Password') );
$user = NULL;
if(!empty($username) && !empty($password)){
  $user = new userClass($username,md5($password));
  if($user!==NULL){
    $_SESSION[$config['session name']] = $user->getName();
    $lastpage = getVar('lastpage');
    if(empty($lastpage)) ForwardTo('./');
    else                 ForwardTo($lastpage);
    exit();
  }
}
unset($username,$password);




$config['title'] = 'Login';
echo '<body>'."\n".
     '<div id="holder">'."\n".
     '<h1>Web Auction</h1>'."\n".
     '<p>&nbsp;</p>'."\n".
     '<div id="login-box">'."\n".
     '  <h2>Login</h2>'."\n".
     '  <p style="color:red">'."\n";
if(isset($_GET['error']))
  if($_GET['error']==1)
    echo 'Login Failed.';
echo '  </p>'."\n".
     '  <form action="scripts/login-script.php" method="post" name="login">'."\n".
     '    <label>Username</label><input name="WA_Login_Username" type="text" class="input" size="30" /><br />'."\n".
     '    <label>Password</label><input name="WA_Login_Password" type="password" class="input" size="30" /><br />'."\n".
     '    <label>&nbsp;</label><input name="Submit" type="submit" class="button" />'."\n".
     '  </form>'."\n".
     '</div>'."\n".
     '</div>'."\n";

?>
