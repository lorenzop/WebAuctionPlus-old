<?php namespace wa;
global $ClassCount; $ClassCount++;
class waUser extends \psm\User {

	// wa player
	protected $eMail = NULL;
	protected $Money = 0.0;
//	protected $permissions = array();
	protected $invLocked   = NULL;

	// player stats
	protected $totalItemsSold   = 0;
	protected $totalItemsBought = 0;
	protected $totalEarnt       = 0.0;
	protected $totalSpent       = 0.0;


	public static function getUserSession($db=NULL, $tableName='') {
		return parent::getUserSession($db, $tableName);
	}
	public function __construct() {
		$this();
	}


}
?>