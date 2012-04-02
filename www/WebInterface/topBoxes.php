<div id="profile-box">
  <table cellspacing="3px">
    <tr>
      <td>
        <img width="64px" src="http://minotar.net/avatar/<?php echo $user ?>" />
      </td>
      <td>
        <p>Name: &nbsp;&nbsp;<?php echo $user?><?php if ($isAdmin == "true"){ echo " ADMIN"; } ?><br/>
<?php
// TODO: printf();
// TODO: get rid of table for formating, use css instead
	if ($useMySQLiConomy) {
?>
Money: &nbsp;
<?php
		echo $currencyPrefix.$iConRow['2'].$currencyPostfix;
?>
<br />
<?php
	} else {
?>
Money: &nbsp;
<?php
	echo $currencyPrefix.$playerRow['3'].$currencyPostfix;;
?>
<br />
<?php
	}
?>
Mail: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
	echo $mailCount;
?>
<br />
<?php
	echo date('jS M Y H:i:s');
?> 
<br />
        </p>
      </td>
    </tr>
  </table>
</div>
<div id="link-box">
  <p>
    <a href="index.php">Home</a><br />
    <a href="myitems.php">My Items</a><br />
    <a href="myauctions.php">My Auctions</a><br />
	<a href="playerstats.php">Player Stats</a><br />
    <a href="info.php">Item Info</a><br />
    <a href="transactionLog.php">Transaction Log</a><br />
    <a href="logout.php">Logout</a>
  </p>
</div>
