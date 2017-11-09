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
        try {
            parent::__construct($host,$user,$password,$db,$port);
            if ( $this->connect_error )  {
                echo ("connect error " . $this->connect_errno ."\r\n");
                return false;
            }
            if ( !$this->set_charset($charset) ) {
                echo ("Error loading character set $charset".$this->error."\r\n");
                return false;
            }
        }catch (\Exception $e) {
            echo ($e);
        } catch (\Error $e) {
            echo ($e);
        }
        return true;

    }

    public function reconnect(){
        if ( !$this->ping() ){
            $this->close();
            return $this->connect_db();
        }
        return true;
    }

    public function query($query,$param=''){
        try {
            return new Mmysqli_stmt($this, $query,$param);
        } catch (\Exception  $e) {
            echo "query-exception-".$e->getCode()."--".$e->getMessage()."\r\n";
            if ( in_array($e->getCode(),array(2006,2014))) {
                $this->close();
                $this->connect_db();
                return new Mmysqli_stmt($this, $query,$param);
            }
        }
        return false;
    }

    public function get($table,$start='',$limit=''){
        if ( strlen($limit) && strlen($start) ){
            $sql = "select * from $table limit $start,$limit";
        }else{
            $sql = "select * from $table";
        }
        return $this->query($sql);
    }

    public function get_where($table,$where = array(),$start='',$limit=''){
        $sql = "select * from $table ";
        if ( $where ){
            $num = count($where)-1;
            $i = 0;
            foreach($where as $k=>$v){
                $type = gettype($v);
                if ( $type == "string" ){
                    $v = "'".$this->real_escape_string($v)."'";
                }else{
                    $v = $this->real_escape_string($v);
                }
                if ( $i == 0 && $num == 0 ) {
                    $sql .= " where $k = $v";
                }else if ( $i == 0 ){
                    $sql .= " where $k = $v and ";
                }else if ( $i == $num ){
                    $sql .= " where $k = $v ";
                }else{
                    $sql .= " $k = $v and";
                }
                $i++;
            }
        }
        if (strlen($limit) && strlen($start) ){
            $sql .= " limit $start,$limit";
        }
        echo $sql."<br/>";
        return $this->query($sql);
    }

    public function insert($table,$data){
        $keys = array_keys($data);
        $keys_str = implode(",",$keys);
        $sql = "insert into $table($keys_str) values(";
        $num = count($data)-1;
        $i = 0;
        foreach($data as $k=>$v){
            $type = gettype($v);
            if ( $type == "string" ){
                $v = "'".$this->real_escape_string($v)."'";
            }else{
                $v = $this->real_escape_string($v);
            }
            if ( $i == 0 && $num == 0 ) {
                $sql .= "$v";
            }else if ( $i == 0 ){
                $sql .= "$v,";
            }else if ( $i == $num ){
                $sql .= "$v";
            }else{
                $sql .= "$v,";
            }
            $i++;
        }
        $sql .= ")";
        $stmt = $this->query($sql);
        return $stmt->insert_id;
    }

    public function update($table,$data,$where){
        $keys = array_keys($data);
        $keys_str = implode(",",$keys);
        $sql = "update $table set ";
        $num = count($data)-1;
        $i = 0;
        foreach($data as $k=>$v){
            $type = gettype($v);
            if ( $type == "string" ){
                $v = "'".$this->real_escape_string($v)."'";
            }else{
                $v = $this->real_escape_string($v);
            }
            if ( $i == 0 && $num == 0 ) {
                $sql .= " $k = $v";
            }else if ( $i == 0 ){
                $sql .= "$k = $v,";
            }else if ( $i == $num ){
                $sql .= " $k = $v";
            }else{
                $sql .= "$k = $v,";
            }
            $i++;
        }
        $sql .= " where ";
        $num = count($where)-1;
        $i = 0;
        foreach($where as $k=>$v){
            $type = gettype($v);
            if ( $type == "string" ){
                $v = "'".$this->real_escape_string($v)."'";
            }else{
                $v = $this->real_escape_string($v);
            }
            if ( $i == 0 && $num == 0 ) {
                $sql .= " $k = $v";
            }else if ( $i == 0 ){
                $sql .= "$k = $v and ";
            }else if ( $i == $num ){
                $sql .= " $k = $v";
            }else{
                $sql .= "$k = $v and ";
            }
            $i++;
        }
        $stmt = $this->query($sql);
        return $stmt->affected_rows;
    }

    public function delete($table,$where){
        $sql = "delete from  $table ";
        $sql .= " where ";
        $num = count($where)-1;
        $i = 0;
        foreach($where as $k=>$v){
            $type = gettype($v);
            if ( $type == "string" ){
                $v = "'".$this->real_escape_string($v)."'";
            }else{
                $v = $this->real_escape_string($v);
            }
            if ( $i == 0 && $num == 0 ) {
                $sql .= " $k = $v";
            }else if ( $i == 0 ){
                $sql .= "$k = $v and ";
            }else if ( $i == $num ){
                $sql .= " $k = $v";
            }else{
                $sql .= "$k = $v and ";
            }
            $i++;
        }
        $stmt = $this->query($sql);
        return $stmt->affected_rows;
    }

}


class Mmysqli_stmt extends \MySQLi_STMT{

    private $mbind_types = array();
    private $mbind_params = array();

    public function __construct($link,$query,$param) {
        $this->mbind_reset();
        parent::__construct($link, $query);
        if ( $param ){
            if ( is_array($param)){
                foreach($param as $p){
                    $type = gettype($p);
                    if ( $type == "string" ){
                        $this->mbind_param('s',$p);
                    }else if ( $type == "integer" ) {
                        $this->mbind_param('i',$p);
                    }else if ( $type == "double" ){
                        $this->mbind_param('d',$p);
                    }else {
                        $this->mbind_param('s',$p);
                    }
                }
            }else{
                $type = gettype($param);
                if ( $type == "string" ){
                    $this->mbind_param('s',$param);
                }else if ( $type == "integer" ) {
                    $this->mbind_param('i',$param);
                }else if ( $type == "double" ){
                    $this->mbind_param('d',$param);
                }else {
                    $this->mbind_param('s',$param);
                }
            }
        }
        $this->execute();
    }

    private function mbind_reset() {
        unset($this->mbind_params);
        unset($this->mbind_types);
        $this->mbind_params = array();
        $this->mbind_types = array();
    }

    private function mbind_param($type, $param) {
        @$this->mbind_types[0].= $type;
        $this->mbind_params[] = $param;
    }

    private function mbind_value($type, $param) {
        $this->mbind_types[0].= $type;
        $this->mbind_params[] = $param;
    }

    private function mbind_param_do() {
        $params = array_merge($this->mbind_types, $this->mbind_params);
        return call_user_func_array(array($this, 'bind_param'), $this->makeValuesReferenced($params));
    }

    private function makeValuesReferenced($arr){
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;

    }

    public function execute() {
        if(count($this->mbind_params)){
            $this->mbind_param_do();
        }
        return parent::execute();
    }

    public function result(){
        $res = $this->get_result();
        $result = array();
        while ($obj = $res->fetch_object() ) {
            $result[]= $obj;
        }
        return $result;
    }

    public function result_array($resulttype = MYSQLI_ASSOC){
        $res = $this->get_result();
        return $res->fetch_all($resulttype) ;
    }

    public function row(){
        $res = $this->get_result();
        return $res->fetch_object();
    }

    public function row_array($resulttype = MYSQLI_ASSOC){
        $res = $this->get_result();
        return $res->fetch_array($resulttype);
    }


}
