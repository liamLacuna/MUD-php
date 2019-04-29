<?php
session_start();

class DB{
    protected $config = [
        'host' => '127.0.0.1',  //host
        'port' => 3306,     //database port
        'user' => 'root',   //database user
        'pass' => '123456', //database pass
        'name'=> 'MUD',    //database name
    ];  //mysql config

    protected static $db;

    public function __construct(){
        if(isset(self::$db)) return self::$db;
        $config = $this->config;
        self::$db = new mysqli($config['host'],$config['user'],$config['pass']);
        self::$db->query("set names 'utf8'");
        if(self::$db->connect_error){
            self::error($this->db->connect_error);
        }
        $this->loadDB();
    }

    public function loadDB(){
        $db = & self::$db;
        $dbname = $this->config['name'];
        $db_load = $db->select_db($dbname);
        if($db_load === false){
            $db->query("create database if not exists `$dbname`");
            $db->query("create table if not exists `$dbname`.`msg_tb`(`id` int unsigned auto_increment primary key,`type` int unsigned not null,`from` int unsigned not null,`to` int unsigned not null default 0,`content` text not null default '',`tm` timestamp default CURRENT_TIMESTAMP)");
            $db->query("create table if not exists `$dbname`.`usr_tb`(`id` int unsigned auto_increment primary key,`name` varchar(16) not null,`pass` char(32) not null,tm timestamp default CURRENT_TIMESTAMP,unique key(name))");
            $db->query("create table if not exists `$dbname`.`grp_tb`(`id` int unsigned auto_increment primary key,`name` varchar(32) not null,tm timestamp default CURRENT_TIMESTAMP,unique key(name))");
            $db->query("create table if not exists `$dbname`.`grp_uid_tb`(`id` int unsigned auto_increment primary key,`gid` int unsigned not null,`uid` int unsigned not null,tm timestamp default CURRENT_TIMESTAMP,unique key(`gid`,`uid`),foreign key(`gid`) references grp_tb(`id`),foreign key(`uid`) references usr_tb(`id`))");
            $db->query("create table if not exists `$dbname`.`recv_tb`(`id` int unsigned auto_increment primary key,`uid` int unsigned not null,`msg_id` int unsigned not null,tm timestamp default CURRENT_TIMESTAMP)");
            $db->query("create table if not exists `$dbname`.`msg_uid_tb`(`id` int unsigned auto_increment primary key,`mid` int unsigned not null,`uid` int unsigned not null,unique key(`mid`,`uid`),foreign key(`mid`) references msg_tb(`id`),foreign key(`uid`) references usr_tb(`id`))");
        }
    }

    public function setConfig($config){
        if(is_array($config)){
            foreach($config as $k=>$v){
                $this->config[$k] = $v;
            }
        }
        unset(self::$db);
        self::$db = new mysqli($config['host'],$config['user'],$config['pass']);
    }

    //error function
    public static function error($msg,$code = 1){
        die(json_encode(['code'=>$code,'res'=>$msg]));
    }

    //success function
    public static function success($msg,$code = 0){
        die(json_encode(['code'=>$code,'res'=>$msg]));
    }

    public static function query($sql){
        $db = self::$db;
        if(isset(self::$db))
            $res = self::$db->query($sql);
        if(!is_bool($res)){
            return $res->fetch_all(MYSQLI_ASSOC);
        }else{
            return $res;
        }
        return false;
    }

}

class ChatServer extends DB{
    const SAY   =   1;
    const TELL  =   2;
    const YELL  =   3;
    const NEW   =   1;
    const HOUR =   2;
    const DAY  =   3;
    const WEEK =   4;
    const MONTH=   5;
    const YEAR =   6;
    CONST ALL   =   7;
    public static $db;

    public function __construct(){
        self::$db = new DB;
    }

    //global send
    public function send($type,$from,$to,$msg){
        $dbname = $this->config['name'];
        $sql = "insert into `$dbname`.`msg_tb`(`type`,`from`,`to`,`content`) values('$type','$from','$to','$msg')";
        if(isset(self::$db))
            return self::$db->query($sql);
        return false;
    }

    public function createUser($user,$pass){
        $db = self::$db;
        $tm = date('Y-m-d H:i:s');
        $pass = md5($pass.$tm);
        $dbname = $this->config['name'];
        $sql = "insert into `$dbname`.`usr_tb`(`name`,`pass`,`tm`) value('$user','$pass','$tm')";
        return $db::query($sql);
    }

    public function modUser($fromUser,$pass,$toUser = null){
        $db = self::$db;
        $tm = date('Y-m-d H:i:s');
        $pass = md5($pass.$tm);
        $dbname = $this->config['name'];
        if(!isset($toUser)) $toUser = $fromUser;
        $sql = "update `$dbname`.`usr_tb` set `name` = '$toUser',`pass`='$pass',`tm`='$tm' where `name` = '$fromUser'";
        return $db::query($sql);
    }

    public function getUsers(){
        $sql = "select `id`,`name` from usr_tb";
        return self::$db::query($sql);
    }

    public function createGroup($name){
        $db = self::$db;
        $dbname = $this->config['name'];
        $sql = "insert into `$dbname`.`grp_tb`(`name`) value('$name')";
        return $db::query($sql);
    }

    public function modGroup($fromName,$toName){
        $db = self::$db;
        $dbname = $this->config['name'];
        $sql = "update `$dbname`.`grp_tb` set `name` = '$toName' where `name` = '$fromName'";
        return $db::query($sql);
    }

    public function getGid($name){
        $sql = "select id from grp_tb where `name`='$name'";
        return self::$db::query($sql)[0]['id'];
    }

    public function getGroup($gid){
        $sql = "select id,name,tm from grp_tb where `id`='$gid'";
        return self::$db::query($sql);
    }

    public function getUid($name){
        $sql = "select id from usr_tb where `name`='$name'";
        return self::$db::query($sql)[0]['id'];
    }

    public function getUser($uid){
        $sql = "select id,name,tm from usr_tb where `id`='$uid'";
        return self::$db::query($sql)[0];
    }

    public function isAuth($name,$pass){
        $sql = "select count(1) as cnt from `usr_tb` where `name`='$name' and `pass` = md5(concat('$pass',tm))";
        return !!self::$db::query($sql)[0]['cnt'];
    }

    public function getGroups($uid = null){
        if($uid === null)
            $sql = "select `id`,`name` from grp_tb";
        else{
            $uid = intval($uid);
            $sql = "select `gid`,usr_tb.name `user`,grp_tb.name `group` from grp_uid_tb left join usr_tb on usr_tb.id = uid left join grp_tb on grp_tb.id = gid where `uid` = '$uid' group by `gid`";
        }
        return self::$db::query($sql);
    }

    public function joinGroup($uid,$gid){
        $sql = "insert into grp_uid_tb(`gid`,`uid`) value('$gid','$uid')";
        return self::$db::query($sql);
    }

    public function readMessage($uid,$mid){
        $sql = "insert into `msg_uid_tb`(`mid`,`uid`) values('$mid','$uid')";
        return self::$db::query($sql);
    }

    public function getMessage($uid,$type = self::NEW,$arg = null){
        $groups = $this->getGroups($uid);
        if(!isset($groups)) $groups = [];
        $now = date('Y-m-d H:i:s');
        $groups = implode("','",$groups);
        $conds = "`type` <> '3' and `to` = '$uid' or `type`='3' and `to` = '0' or `type` = '2' and `to` in ('$groups')";
        switch($type){
            case self::NEW:
                $sql = "select * from (select id,content,tm from msg_tb where $conds) TEMP where id not in (select mid from msg_uid_tb where uid = '$uid') order by tm asc";
                $msgs = self::$db::query($sql);
                foreach($msgs as $msg){
                    $this->readMessage($uid,$msg['id']);
                }
                return $msgs;
                break;
            case self::HOUR:
                $arg = intval($arg);
                $tm_start = date('Y-m-d H:i:s',strtotime("-$arg hours"));
                break;
            case self::DAY:
                $arg = intval($arg);
                $tm_start = date('Y-m-d H:i:s',strtotime("-$arg days"));
                break;
            case self::WEEK:
                $arg = intval($arg);
                $tm_start = date('Y-m-d H:i:s',strtotime("-$arg weeks"));
                break;
            case self::MONTH:
                $arg = intval($arg);
                $tm_start = date('Y-m-d H:i:s',strtotime("-$arg months"));
                break;
            case self::YEAR:
                $arg = intval($arg);
                $tm_start = date('Y-m-d H:i:s',strtotime("-$arg years"));
                break;
            case self::ALL:
                $arg = intval($arg);
                $sql = "select id,content,tm from msg_tb where $conds";
                return self::$db::query($sql);
                break;
        }
        $sql = "select id,content,tm from msg_tb where ($conds) and tm between '$tm_start' and '$now'";
        return self::$db::query($sql);
    }
}

class ChatClient{
    private $server;
    private $from;
    private $to;
    private $msg;
    public function __construct($argv = []){
        $this->server = new ChatServer;
        if(isset($argv['uid'])){
            $this->from = $argv['uid'];
        }
        if(isset($argv['to'])){
            $this->to = $argv['to'];
        }
        if(isset($argv['msg']))
            $this->msg = $argv['msg'];
    }

    public function getUid($user){
        return $this->server->getUid($user);
    }

    public function setConfig($config){
        if(isset($config['uid']))
            $this->from = $config['uid'];
        if(isset($config['to']))
            $this->to = $config['to'];
        if(isset($config['msg']))
            $this->msg = $config['msg'];
        return $this;
    }

    public function say(){
        return $this->server->send(ChatServer::SAY,$this->from,$this->to,$this->msg);
    }

    public function tell(){
        return $this->server->send(ChatServer::TELL,$this->from,$this->to,$this->msg);
    }

    public function yell(){
        return $this->server->send(ChatServer::YELL,$this->from,0,$this->msg);
    }

    public function joinGroup($grp){
        return $this->server->joinGroup($this->from,$grp);
    }

    public function recv($type = ChatServer::NEW,$arg = null){
        if(!isset($this->from)){
            $this->server::error('no user login');
        }

        return $this->server->getMessage($this->from,$type,$arg);
    }
}

function param(&$v){
    if(!isset($v)) $v = '';
    if(get_magic_quotes_gpc()){
        $v = addslashes($v);
    }
    return $v;
}

$server = new ChatServer;
$client = new ChatClient;
$uid = $_SESSION['uid'];
$limit_action = [
    'say',
    'tell',
    'yell',
    'logout',
    'recv',
    'group',
    'groups',
    'create_group',
    'join',
    'users',
];

if(isset($_REQUEST)){
    $argc = count($_REQUEST);
    $action = param($_REQUEST['a']);    //action
    $to = param($_REQUEST['t']);        //to
    $msg = param($_REQUEST['m']);       //msg
    if(in_array($action,$limit_action)){
        if(!isset($uid)){
            $server::error('no login');
        }
    }else if(isset($uid)){
        $server::error('already logining');
    }

    if(isset($uid) and is_numeric($to) and !empty($msg)){
        $ret = ['uid'=>$uid,'to'=>$to,'msg'=>$msg];
        $client->setConfig($ret);
        $send = true;
    }

    switch($action){
        case 'login':
            $user = param($_REQUEST['u']);
            $pass = param($_REQUEST['p']);
            if($server->isAuth($user,$pass)){
                $uid = $server->getUid($user);
                $_SESSION['uid'] = $uid;
                $_SESSION['user'] = $user;
                $server::success('login success');
            }else{
                $server::error('login error');
            }
            break;
        case 'logout':
            unset($_SESSION['uid']);
            unset($_SESSION['user']);
            $server::success('logout');
            break;
        case 'say':
        case 'tell':
        case 'yell':
            if($send)
                $res = $client->$action();
            if(isset($res))
                $server::success('success');
            else
                $server::error('failure');
            break;
        case 'recv':
            $client->setConfig(['uid'=>$uid]);
            $type = param($_REQUEST['t']);
            $arg = param($_REQUEST['p']);
            if(!empty($type)){
                $type = intval($type);
                $arg = intval($arg);
                $server::success($client->recv($type,$arg));
            }else{
                $server::error('No recv type');
            }
            break;
        case 'group':
            $server::success($server->getGroups($uid));
            break;
        case 'groups':
            $server::success($server->getGroups());
            break;
        case 'users':
            $server::success($server->getUsers());
            break;
        case 'create_group':
            $name = param($_REQUEST['n']);
            $client->setConfig(['uid'=>$uid]);
            $ret = $server->createGroup($name);
            $gid = $server->getGid($name);
            $client->joinGroup($gid);
            $server::success($ret);
            break;
        case 'join'://join group
            $gid = param($_REQUEST['g']);
            if(!is_numeric($gid))
                $server::error('no gid');
            $server::success($server->joinGroup($uid,$gid));
            break;
        case 'reg':
            $user = param($_REQUEST['u']);
            $pass = param($_REQUEST['p']);
            $ret = $server->createUser($user,$pass);
            $ret?$server::success('Register Success!'):$server::error('Register Fail!');
            break;
    }
}else{
    $server::error('Hello,World!');
}
?>
