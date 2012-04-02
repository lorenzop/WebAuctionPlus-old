<?php
class Market
{
    public $id;
    public $name;
    public $damage;
	public $price;
	public $fullname;
	public $time;
	public $image;
	public $ref;
	public $enchants;
	
	function __construct($idIn)
    {
		$query=mysql_query("SELECT * FROM WA_MarketPrices WHERE id='$idIn'");
        $marketRow = mysql_fetch_object($query);
        $this->id = $marketRow->id;
		$this->name = $marketRow->name;
		$this->damage = $marketRow->damage;
		$this->time = $marketRow->time;
		$this->price = $marketRow->marketprice;
		$this->fullname = getItemName($this->name, $this->damage);
		$this->image = getItemImage($this->name, $this->damage);
		$this->ref = $marketRow->ref;
		
		$queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$this->id' AND itemTableId = '4'");
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
	
}
?>
