<?php
	session_start();
	$past = time() - 100;
	unset($_SESSION['User']);
	unset($_SESSION['canBuy']);
	unset($_SESSION['canSell']);
	unset($_SESSION['Admin']);
	//setcookie(User, gone, $past);
	header("Location: index.php");
?>
