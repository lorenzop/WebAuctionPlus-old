<?php
class EconAccount
{
    public $id;
    public $name;
    public $money;
	public $itemsSold;
	public $itemsBought;
	public $earnt;
	public $spent;

    function __construct($user, $useMySQLiConomy, $iConTableName)
    {
	   $queryPlayer = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
	   $row = mysql_fetch_object($queryPlayer);
	   $this->itemsSold = $row->itemsSold;
	   $this->itemsBought = $row->itemsBought;
	   $this->earnt = $row->earnt;
	   $this->spent = $row->spent;
       if ($useMySQLiConomy){
            $query=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
            $iConRow = mysql_fetch_object($query);
            $this->id = $iConRow->id;
            $this->name = $iConRow->username;
            $this->money = $iConRow->balance;
	   }else{    
            $this->id = $row->id;
            $this->name = $row->name;
            $this->money = $row->money;
        }
    }
	public function saveMoney($useMySQLiConomy, $iConTableName)
    {
        if ($useMySQLiConomy){
            $query = mysql_query("UPDATE $iConTableName SET balance='$this->money' WHERE username='$this->name'");
	    }else{
            $query = mysql_query("UPDATE WA_Players SET money='$this->money' WHERE name='$this->name'");
        }
    }
	
	public function spend($amount, $useMySQLiConomy, $iConTableName)
	{
		$this->money = $this->money - $amount;
		$this->saveMoney($useMySQLiConomy, $iConTableName);
		$this->spent = $this->spent + $amount;
		$query = mysql_query("UPDATE WA_Players SET spent='$this->spent' WHERE name='$this->name'");
	}
	
	public function earn($amount, $useMySQLiConomy, $iConTableName)
	{
		$this->money = $this->money + $amount;
		$this->saveMoney($useMySQLiConomy, $iConTableName);
		$this->earnt = $this->earnt + $amount;
		$query = mysql_query("UPDATE WA_Players SET earnt='$this->earnt' WHERE name='$this->name'");
	}
	
	public function sellItem($number){
		$this->itemsSold = $this->itemsSold + $number;
		$query = mysql_query("UPDATE WA_Players SET itemsSold='$this->itemsSold' WHERE name='$this->name'");
	}
	
	public function buyItem($number){
		$this->itemsBought = $this->itemsBought + $number;
		$query = mysql_query("UPDATE WA_Players SET itemsBought='$this->itemsBought' WHERE name='$this->name'");
	}
}
?>
