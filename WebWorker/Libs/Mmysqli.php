<?php
namespace WebWorker\Libs;

$driver = new \mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_STRICT|MYSQLI_REPORT_ERROR;

class Mmysqli extends \mysqli{

    private $config = array();

    public function __construct($config=array()){
        $this->config = $config;
        $this->connect_db();
    }


    private function connect_db(){
        $host = isset($this->config["host"]) ? $this->config["host"] : "127.0.0.1";
        $user = isset($this->config["user"]) ? $this->config["user"] : "root";
        $password = isset($this->config["password"]) ? $this->config["password"] : "123456";
        $db = isset($this->config["db"]) ? $this->config["db"] : "test";
        $port = isset($this->config["port"]) ? $this->config["port"] : 3306;
        $charset = isset($this->config["charset"]) ? $this->config["charset"] : "utf8";
        parent::__construct($host,$user,$password,$db,$port);
        if ( $this->connect_error )  {
            echo ("connect error " . $this->connect_errno ."\r\n");
            return false;
        }
        if ( !$this->set_charset($charset) ) {
            echo ("Error loading character set $charset".$this->error."\r\n");
            return false;
        }
        return true;
    }

    public function reconnect(){
        if ( !this->ping() ){
            $this->close();
            return $this->connect_db();
        }
        return true;
    }

}
