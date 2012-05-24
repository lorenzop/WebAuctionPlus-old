<?php if(!defined('DEFINE_INDEX_FILE')){if(headers_sent()){echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}else{header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
// this class handles user accounts and sessions
class UserClass{


protected $UserId        = 0;
protected $Name          = '';
public    $numMail       = 0;
public    $Money         = 0.0;
public    $ItemsSold     = 0;
public    $ItemsBought   = 0;
public    $Earnt         = 0.0;
public    $Spent         = 0.0;
protected $permissions   = array();


function __construct($username=NULL, $password=NULL){global $config;
  $loginUrl = './?page=login';
  session_start();
  $query = '';
  if($username===NULL || $password===NULL){
    if(isset($_SESSION[$config['session name']])){
      $this->Name = trim($_SESSION[$config['session name']]);
      $query = "WHERE LOWER(`playerName`)='".mysql_san(strtolower($this->Name))."'";
    }
    if(empty($this->Name)) ForwardTo($loginUrl);
  }else{
    $this->Name = $username;
    $query = "WHERE LOWER(`playerName`)='".mysql_san(strtolower($username))."'".
             " AND `password`='".mysql_san($password)."'";
  }
  // validate player
  $query="SELECT `id`,`money`,`itemsSold`,`itemsBought`,`earnt`,`spent`,`Permissions` ".
                   "FROM `".$config['table prefix']."Players` ".$query." LIMIT 1";
  $result=RunQuery($query, __file__, __line__);
  if($result){
    if(mysql_num_rows($result)==0){
      $_SESSION[$config['session name']] = '';
      $_GET['error']='bad login';
      return;
    }
    $row=mysql_fetch_assoc($result);
    $this->UserId      = ((int)    $row['id']         );
    $this->Money       = ((double) $row['money']      );
    $this->ItemsSold   = ((int)    $row['itemsSold']  );
    $this->ItemsBought = ((int)    $row['itemsBought']);
    $this->Earnt       = ((double) $row['earnt']      );
    $this->Spent       = ((double) $row['spent']      );
    foreach(explode(',',$row['Permissions']) as $perm){
      $this->permissions[$perm] = TRUE;
    }
    // get mail count
    $result=RunQuery("SELECT COUNT(*) AS `count` FROM `".$config['table prefix']."Items` WHERE ".
                     "`ItemTable`='Mail' AND LOWER(`playerName`)='".mysql_san(strtolower($this->Name))."'", __file__, __line__);
    $row=mysql_fetch_assoc($result);
    $this->numMail = ((int)$row['count']);
    $_SESSION[$config['session name']] = $this->Name;
  }else{
    $_SESSION[$config['session name']] = '';
    echo 'Error: '.mysql_error();
    exit();
  }
  // use iconomy table
  if ($config['iConomy']['use']===TRUE || $config['iConomy']['use']==='auto'){
    global $db;
    $result = mysql_query("SELECT `balance` FROM `".mysql_san($config['iConomy']['table'])."` WHERE ".
                          "LOWER(`username`)='".mysql_san(strtolower($this->Name))."' LIMIT 1", $db);
    if($result){
      $row = mysql_fetch_assoc($result);
      $this->Money = ((double)$row['balance']);
      $config['iConomy']['use'] = TRUE;
    }else{
      // table not found
      if(mysql_errno($db) == 1146){
        $config['iConomy']['use'] = FALSE;
      }else echo mysql_error($db);
    }
    unset($result, $row);
  }
}

// user id
public function getUserId(){
  return($this->UserId);
}

// player name
public function getName(){
  return($this->Name);
}
public function nameEquals($name){
  return(strtolower($name) == strtolower($this->getName()));
}

// permissions
public function hasPerms($perms){
  if(empty($perms) || count($this->permissions)==0) return(false);
  if(is_array($perms)){
    $hasPerms = true;
    foreach($perms as $perm){
      if(!(boolean)@$this->permissions[$perm]) $hasPerms = false;
    }
    return($hasPerms);
  }
  return((boolean)@$this->permissions[$perms]);
}

// money
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
//                         "`name`='".mysql_san($this->Name)."' LIMIT 1");
//  }
//  if ($useMySQLiConomy){
//    $query = mysql_query("UPDATE $iConTableName SET balance='$this->money' WHERE playername='$this->Name'");
//  }else{
//    $query = mysql_query("UPDATE WA_Players SET money='$this->money' WHERE name='$this->Name'");
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

public static function MakePayment($fromPlayer, $toPlayer, $amount, $desc=''){
  if(empty($fromPlayer) || empty($toPlayer) || $amount<=0){echo 'Invalid payment amount!'; exit();}
  self::PaymentQuery($toPlayer,     $amount);
  self::PaymentQuery($fromPlayer, 0-$amount);
  // TODO: log transaction
}

public static function PaymentQuery($playerName, $amount){global $config;
  if($config['iConomy']['use'] === TRUE){
    $query = "UPDATE `".mysql_san($config['iConomy']['table'])."` SET ".
             "`balance` = `balance` + ".((float)$amount)." ".
             "WHERE LOWER(`username`)='".mysql_san(strtolower($playerName))."' LIMIT 1";
  }else{
    $query = "UPDATE `".$config['table prefix']."Players` SET ".
             "`money` = `money` + ".((float)$amount)." ".
             "WHERE LOWER(`playerName`)='".mysql_san(strtolower($playerName))."' LIMIT 1";
  }
  $result = RunQuery($query, __file__, __line__);
}


}
?>