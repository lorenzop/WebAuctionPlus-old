<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class userClass{

protected $UserId        = 0;
protected $UserName      = '';
protected $numMail       = 0;
protected $Money         = 0.0;
protected $ItemsSold     = 0;
protected $ItemsBought   = 0;
protected $Earnt         = 0.0;
protected $Spent         = 0.0;
protected $CanBuy        = false;
protected $CanSell       = false;
protected $Admin         = false;

public function __construct(){global $config;
  session_start();
  if(!isset($_SESSION[$config['session name']])) ForwardTo('login.php');
  $UserName = $_SESSION[$config['session name']];


  if($UserName!='') $this->UserName=$UserName;
  unset($UserName);
  // validate player
  $result=RunQuery("SELECT `id`,`money`,`itemsSold`,`itemsBought`,`earnt`,`spent`,`canBuy`,`canSell`,`isAdmin` ".
                   "FROM `".$config['table prefix']."Players` WHERE ".
                   "`name`='".mysql_san($UserName)."'", __file__, __line__);
  if($result){
    $row=mysql_fetch_assoc($result);
    $this->UserId      = ((int)    $row['id']         );
    $this->Money       = ((double) $row['money']      );
    $this->ItemsSold   = ((int)    $row['itemsSold']  );
    $this->ItemsBought = ((int)    $row['itemsBought']);
    $this->Earnt       = ((double) $row['earnt']      );
    $this->Spent       = ((double) $row['spent']      );
    $this->CanBuy      = ((boolean)$row['canBuy']     );
    $this->CanSell     = ((boolean)$row['canSell']    );
    $this->Admin       = ((boolean)$row['isAdmin']    );
  }else{
    echo mysql_error($db);
    exit();
  }
  // use iconomy table
  if ($config['iConomy']['use']===true || $config['iConomy']['use']==='auto'){
    global $db;
    $result=mysql_query("SELECT `balance` FROM `".mysql_san($config['iConomy']['table'])."` WHERE ".
                        "`username`='".mysql_san($this->UserName)."' LIMIT 1", $db);
    if($result){
      $row=mysql_fetch_assoc($result);
      $this->Money=((double)$row['balance']);
    }else{
      // table not found
      if(mysql_errno($db)==1146){
        $config['iConomy']['use']=false;
      }else echo mysql_error($db);
    }
    unset($result, $row);
  }
}

// money
public function getMoney(){
  if($this->Money==0 && !$this->QueriedPlayer) $this->QueryPlayer();
  return($this->Money);
}
// money actions
public function saveMoney($useMySQLiConomy, $iConTableName){
//  if ($useMySQLiConomy){
//    $query = mysql_query("UPDATE `".mysql_san($iConTableName)."` SET ".
//                         "`balance`=".((double)$this->money)" WHERE ".
//                         "`username`='".mysql_san($this->UserName)."' LIMIT 1");
//echo mysql_errno();
//exit();
//  }else{
//    $query = mysql_query("UPDATE `".$config['table prefix']."Players` SET ".
//                         "`money`=".((double)$this->money)." WHERE ".
//                         "`name`='".mysql_san($this->UserName)."' LIMIT 1");
//  }
//  if ($useMySQLiConomy){
//    $query = mysql_query("UPDATE $iConTableName SET balance='$this->money' WHERE username='$this->name'");
//  }else{
//    $query = mysql_query("UPDATE WA_Players SET money='$this->money' WHERE name='$this->name'");
//  }
}
public function spend($amount, $useMySQLiConomy, $iConTableName){
//  $this->money = $this->money - $amount;
//  $this->saveMoney($useMySQLiConomy, $iConTableName);
//  $this->spent = $this->spent + $amount;
//  $query = mysql_query("UPDATE WA_Players SET spent='$this->spent' WHERE name='$this->name'");
}
public function earn($amount, $useMySQLiConomy, $iConTableName){
//  $this->money = $this->money + $amount;
//  $this->saveMoney($useMySQLiConomy, $iConTableName);
//  $this->earnt = $this->earnt + $amount;
//  $query = mysql_query("UPDATE WA_Players SET earnt='$this->earnt' WHERE name='$this->name'");
}









}
?>
