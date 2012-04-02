<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
		require 'config.php';
	$queryAuctions=mysql_query("SELECT * FROM WA_Auctions");

	while(list($id, $name, $damage, $player, $quantity, $price)= mysql_fetch_row($queryAuctions))
    { 
		$itemDelete = mysql_query("DELETE FROM WA_Auctions WHERE id='$id'");
		$count = count($names);
		$found = false;
		for ($i = 0; $i < $count; $i ++)
		{
			if (($names[$i] == $name)&&($damages[$i] == $damage)&&($players[$i] == $player)&&($prices[$i] == $price)){
				$quantities[$i] += $quantity;
				echo "stacked".$name;
				$found = true;
			}
		}	
		if ($found == false){
			$names[]  = $name;
			$damages[] = $damage;
			$players[] = $player;
			$quantities[] = $quantity;
			$prices[] = $price;
		}
	}
	echo "<pre>";
	print_r($names);
    echo "</pre>";
	echo "<pre>";
	print_r($quantity);
    echo "</pre>";
	$fullCount = count($names);
	for ($i = 0; $i < $fullCount; $i ++)
	{
		$insertAgain = mysql_query("INSERT INTO WA_Auctions (name, damage, player, quantity, price) VALUES ('$names[$i]', '$damages[$i]', '$players[$i]', '$quantities[$i]', '$prices[$i]')");
	}
?>