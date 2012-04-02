<?php
class Item
{
    public $id;
    public $name;
    public $damage;
	public $owner;
	public $quantity;
	public $marketprice;
	public $fullname;
	public $maxstack;
	public $enchants;
	
	function __construct($idIn)
    {
		$query=mysql_query("SELECT * FROM WA_Items WHERE id='$idIn'");
        $itemRow = mysql_fetch_object($query);
        $this->id = $itemRow->id;
		$this->name = $itemRow->name;
		$this->damage = $itemRow->damage;
		$this->owner = $itemRow->player;
		$this->quantity = $itemRow->quantity;
		$this->marketprice = getMarketPrice($this->id, 0);
		$this->fullname = getItemName($this->name, $this->damage);
		$this->maxstack = getItemMaxStack($this->name);
		
		$queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$this->id' AND itemTableId = '0'");
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
        $query = mysql_query("UPDATE WA_Items SET quantity='$this->quantity' WHERE id='$this->id'");
    }
	
	public function delete()
    {
	  mysql_query("DELETE FROM WA_Items WHERE id='$this->id'");
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
