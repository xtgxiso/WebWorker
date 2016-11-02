<?php
namespace WebWorker;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Autoloader;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Protocols\Http;
use WebWorker\libs\db;

class App extends Worker
{
    private $conn = false;
    private $map = array();

    public  $autoload = array();
    public  $on404 ="";


    public function __construct($socket_name, $context_option = array())
    {
        parent::__construct($socket_name, $context_option);
    }

    public function HandleFunc($url,callable $callback){
        $url = strtolower(trim($url,"/"));
        $this->map[$url] = $callback;
    }

    private function show_404($connection){
        if ( $this->on404 ){
            call_user_func($this->on404, $connection);
        }else{
            Http::header("HTTP/1.1 404 Not Found");
            $html = '<html>
                <head><title>404 Not Found</title></head>
                <body bgcolor="white">
                <center><h1>404 Not Found</h1></center>
                <hr><center>Workerman</center>
                </body>
                </html>';
            $connection->send($html);
        }
    }

    private function exec_url($callback,$connection,$data){
        try {
            call_user_func($callback, $connection, $data);
        }catch (\Exception $e) {
            // Jump_exit?
            if ($e->getMessage() != 'jump_exit') {
                echo $e;
            }
        }
    }

    public function onClientMessage($connection,$data){
        $this->conn = $connection;
        $url= $_SERVER["REQUEST_URI"];
        $pos = stripos($url,"?");
        if ($pos != false) {
            $url = substr($url,0,$pos);
        }
        $url = strtolower(trim($url,"/"));
        $callback =  @$this->map[$url];
        if ( isset($callback) ){
            $this->exec_url($callback,$connection,$data);
        }else{
            $this->show_404($connection);
        }
    }

    public function  server_json($data){
        Http::header("Content-type: application/json");
        $this->conn->send(json_encode($data));
    }

    public function run()
    {
        autoload_dir($this->autoload);
        $this->onMessage = array($this, 'onClientMessage');
        parent::run();
    }

}

function autoload_dir($dir_arr){
    extract($GLOBALS);
    foreach($dir_arr as $dir ){
        foreach(glob($dir.'*.php') as $start_file)
        {
            require_once $start_file;
        }
    }
}
