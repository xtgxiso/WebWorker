WebWorker
========

基于Workerman (http://www.workerman.net/) 实现的自带http server的web开发框架，用于开发高性能的api应用，例如app接口服务端等。

特性
========
* 天生继承workerman所拥有的特性
* 只实现了简单的路由功能
* 相比php-fpm或mod_php的方式性能有几十倍左右的提升


快速开始
======
demo.php
```php
<?php
use Workerman\Worker;
use Workerman\Protocols\Http;

//判断系统
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    require_once __DIR__.'/workerman-for-win/Autoloader.php';
}else {
    require_once __DIR__.'/workerman/Autoloader.php';
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
$app->count = 4;

//自动加载目录--会加载目录下的所有php文件
$app->autoload = array();

//注册路由hello
$app->HandleFunc("/hello",function($conn,$data) use($app){
    $conn->send("Hello World WorkerMan WebWorker!");
});

//注册路由json
$app->HandleFunc("/json",function($conn,$data) use($app){
     //以json格式响应
     $app->server_json(array("name"=>"WebWorker"));
});

//注册路由input
$app->HandleFunc("/input",function($conn,$data) use($app){
    //获取body
     $body = $GLOBALS['HTTP_RAW_POST_DATA'];
     $conn->send($body);
});


//自定义404
$app->on404  = function($conn){
    $conn->send("我的404");
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

// Run worker
Worker::runAll();
```
