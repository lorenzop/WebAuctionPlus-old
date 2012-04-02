<?php
	require 'scripts/config.php';
	require 'scripts/updateTables.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="css/<?php echo $cssFile?>.css" />
    <title>WebAuction - Login</title>
  </head>
  <body>
    <div id="holder">
      <h1>Web Auction</h1>
      <p>&nbsp;</p>
      <div id="login-box">
        <h2>Login</h2>
        <p style="color:red">
<?php
	if(isset($_GET['error'])) {
		if($_GET['error']==1){
			echo "Login Failed.";
		}
	}
?>
        </p>
        <form action="scripts/login-script.php" method="post" name="login">
          <label>Username</label><input name="Username" type="text" class="input" size="30" /><br />
          <label>Password</label><input name="Password" type="password" class="input" size="30" /><br />
          <label>&nbsp;</label><input name="Submit" type="submit" class="button" />
        </form>
      </div>
    </div>
  </body>
</html>
