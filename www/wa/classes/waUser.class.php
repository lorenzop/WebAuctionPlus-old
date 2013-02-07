<?php namespace wa;
if(!defined('\PORTAL_INDEX_FILE') || \PORTAL_INDEX_FILE!==TRUE){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
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