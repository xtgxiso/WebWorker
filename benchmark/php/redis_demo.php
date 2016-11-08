<?php
use Workerman\Worker;
use Workerman\Protocols\Http;
use WebWorker\Libs\Mredis;

//判断系统
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    require_once __DIR__.'/../../workerman-for-win/Autoloader.php';
}else {
    require_once __DIR__.'/../../workerman/Autoloader.php';
    // 检查扩展
    if(!extension_loaded('pcntl'))
    {
        exit("Please install pcntl extension. See http://doc3.workerman.net/install/install.html\n");
    }

    if(!extension_loaded('posix'))
    {
        exit("Please install posix extension. See http://doc3.workerman.net/install/install.html\n");
    }
}

$app = new WebWorker\App("http://0.0.0.0:1215");

//进程数
$app->count = 40;

//自动加载目录--会加载目录下的所有php文件
$app->autoload = array();


$config = array();
$config["host"] = "127.0.0.1";
$config["port"] = 6379;
$config["password"] = "123456";
$config["db"] = 1;

//注册路由
$app->HandleFunc("/",function() use($app,$config){
    $redis = Mredis::getInstance($config);
    $app->server_send($redis->get("xtgxiso"));
});


// Run worker
Worker::runAll();
