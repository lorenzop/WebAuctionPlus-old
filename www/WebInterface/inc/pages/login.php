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
  $html->loadCss('bootstrap.css');
  if(getVar('error')!='')    $output.='<div class="container"><div class="row">
        <div class="span6 offset3"><div class="alert alert-block alert-error fade in">
            <button class="close" data-dismiss="alert">&times;</button>
            <h4 class="alert-heading">Oh snap!</h4>
            <p>You entered a wrong Username and/or Password!</p>
          </div></div></div></div>';
  if($config['demo']===TRUE){$username='demo'; $password='demo';}
  $output.='
 <div class="container">
 <div class="row">
        <div class="span6 offset3">
  <form class="well" action="./" name="login" method="post">
                 <h2><strong>Login</strong></h1> <hr>
				 <input type="hidden" name="page"     value="login" />
                <input type="hidden" name="lastpage" value="' . getVar('lastpage' ). '" />   				
				<label for="WA_Login_Username">Username</label>
   				 <input type="text" name="WA_Login_Username" value="' . $username . '" class="span3 id="WA_Login_Username"" placeholder="Minecraft Username">
   				 <span class="help-block">Your Minecraft Login Username/Player Name.</span>
   				 
				 <label for="WA_Login_Password">Password</label>
   				 <input type="password" name="WA_Login_Password" value="'.$password.'" class="span3 id="WA_Login_Password"" placeholder="Password">
   				 <span class="help-block">To set a password type /wa password {password} in game.</span>
   				 <hr />
   				 <div class="buttonfix">
   				 <button type="submit" class="btn btn-large" name="submit" value="submit"><i class="icon-ok"></i> &nbsp;Login</button>
   				 </div>
   			  </form></div></div>
			  <script type="text/javascript">
function formfocus() {
  document.getElementById(\'WA_Login_Username\').focus();
}
window.onload = formfocus;
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"></script>
    <script src="js/bootstrap.js"></script>
	<script>
		$(".alert").alert();
	</script>
   		  </p>
        </div> </div>
      </div>'
	  ;
  return($output);
}


?>
