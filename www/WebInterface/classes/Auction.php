<?php
class Auction
{
    public $id;
    public $name;
    public $damage;
	public $owner;
	public $quantity;
	public $price;
	public $marketprice;
	public $fullname;
	public $created;
	public $image;
	public $enchants;
	
	function __construct($idIn)
    {
		$query=mysql_query("SELECT * FROM WA_Auctions WHERE id='$idIn'");
        $auctionRow = mysql_fetch_object($query);
        $this->id = $auctionRow->id;
		$this->name = $auctionRow->name;
		$this->damage = $auctionRow->damage;
		$this->owner = $auctionRow->player;
		$this->quantity = $auctionRow->quantity;
		$this->price = $auctionRow->price;
		$this->created = $auctionRow->created;
		$this->marketprice = getMarketPrice($this->id, 1);
		$this->fullname = getItemName($this->name, $this->damage);
		$this->image = getItemImage($this->name, $this->damage);
		
		$queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$this->id' AND itemTableId = '1'");
		$itemEnchantsArray = array ();
		
		while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks))
		{  
			$eArray = array();
			$q = mysql_query("SELECT * FROM WA_Enchantments WHERE id = '$enchIdt'");
			list($ide, $fullnamee, $namee, $levele)= mysql_fetch_row($q);
			$eArray["id"] = $ide;
			$eArray["name"] = $namee;
			$eArray["level"] = $levele;
			$itemEnchantsArray[] = $eArray;
			
		}
		$this->enchants = $itemEnchantsArray;
    }
	public function changeQuantity($amount)
    {
		$this->quantity = $this->quantity + $amount;
        $query = mysql_query("UPDATE WA_Auctions SET quantity='$this->quantity' WHERE id='$this->id'");
    }
	
	public function delete()
    {
	  mysql_query("DELETE FROM WA_Auctions WHERE id='$this->id'");
	}
	
	public function getEnchantmentArray()
	{
		$queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$this->id' AND itemTableId = '1'");
		$itemEnchantsArray = array ();
		
		while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks))
		{  
			$itemEnchantsArray[] = $enchIdt;
			
		}
		return $itemEnchantsArray;
	}
}
?>
