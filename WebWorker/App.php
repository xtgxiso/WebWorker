<?php
namespace WebWorker;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Autoloader;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Protocols\Http;

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

    private function auto_close($conn){
        if ( strtolower($_SERVER["SERVER_PROTOCOL"]) == "http/1.1" ){
            if ( isset($_SERVER["HTTP_CONNECTION"]) ){
                if ( strtolower($_SERVER["HTTP_CONNECTION"]) == "close" ){
                        $conn->close();
                }
            }
        }else{
            if ( $_SERVER["HTTP_CONNECTION"] == "keep-alive" ){

            }else{
                $conn->close();
            }
        }
    }

    public function onClientMessage($connection,$data){
        if ( empty($this->map) ){
            $str = <<<'EOD'
<div style="margin: 200px auto;width:600px;height:800px;text-align:left;">基于<a href="http://www.workerman.net/" target="_blank">Workerman</a>实现的自带http server的web开发框架.没有添加路由，请添加路由!
<pre>$app->HandleFunc("/",function($conn,$data) use($app){
    $conn->send("默认页");
});</pre>
</div>
EOD;
            $connection->send($str);
            return;
        }
        require_once __DIR__ . '/../Applications/Statistics/Clients/StatisticClient.php';
        $statistic_address = 'udp://127.0.0.1:55656';
        $this->conn = $connection;
        $url= $_SERVER["REQUEST_URI"];
        $data = explode("/",$url);
        $class = $data[0];
        $method = isset($data[1]) ? $data[1] : "_default";
        \StatisticClient::tick($class, $method);
        $success = false;
        $pos = stripos($url,"?");
        if ($pos != false) {
            $url = substr($url,0,$pos);
        }
        $url = strtolower(trim($url,"/"));
        $callback =  @$this->map[$url];
        if ( isset($callback) ){
            try {
                call_user_func($callback, $connection, $data);
                \StatisticClient::report($class, $method, 1, 0, '', $statistic_address);
            }catch (\Exception $e) {
                // Jump_exit?
                if ($e->getMessage() != 'jump_exit') {
                    echo $e;
                }
                $code = $e->getCode() ? $e->getCode() : 500;
                StatisticClient::report($class, $method, $success, $code, $e, $statistic_address);
            }
        }else{
            $this->show_404($connection);
            $code = 404;
            $msg = "class $class not found";
            \StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
        }
        $this->auto_close($connection);
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
