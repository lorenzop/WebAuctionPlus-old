<?php

echo '<div id="profile-box">'."\n";
echo '<table cellspacing="3px">'."\n";
echo '<tr><td><img width="64px" src="http://minotar.net/avatar/'.$user.'" /></td>'."\n";
echo '<td><p>Name: &nbsp;&nbsp;'.$user.($isAdmin=="true"?' ADMIN':'')."<br />\n";

// TODO: printf();
// TODO: get rid of table for formating, use css instead
if($useMySQLiConomy){
  echo 'Money: &nbsp;'.$currencyPrefix.$iConRow['2'].$currencyPostfix."<br />\n";
}else{
  echo 'Money: &nbsp'.$currencyPrefix.$playerRow['3'].$currencyPostfix."<br />\n";
}
echo 'Mail: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$mailCount."<br />\n";
echo date('jS M Y H:i:s')."<br />\n";
echo "</p></td></tr>\n";
echo "</table>\n";
echo "</div>\n";
echo '<div id="link-box">'."\n";
echo "<p>\n";
echo '<a href="index.php">Home</a><br />'."\n";
echo '<a href="myitems.php">My Items</a><br />'."\n";
echo '<a href="myauctions.php">My Auctions</a><br />'."\n";
echo '<a href="playerstats.php">Player Stats</a><br />'."\n";
echo '<a href="info.php">Item Info</a><br />'."\n";
echo '<a href="transactionLog.php">Transaction Log</a><br />'."\n";
echo '<a href="logout.php">Logout</a>'."\n";
echo "</p>\n";
echo "</div>\n";
