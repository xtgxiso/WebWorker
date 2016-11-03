WebWorker
========

基于Workerman (http://www.workerman.net/) 实现的自带http server的web开发框架，用于开发高性能的api应用，例如app接口服务端等。

特性
========
* 天生继承workerman所拥有的特性
* 只实现了简单路由功能的小巧框架,便于开发者使用和扩展.demo1中只是目录示例，开发者可自行定义自己的应用目录结构
* 相比php-fpm或mod_php的方式性能有几十倍左右的提升

框架由来
========
大家经常说php性能差，其实主要是在php-fpm或mod_php方式下的差，而php语言本身是不错的，尤其在未来加入JIT之后，性能会越来越好的。面对新兴的语言和开发方式，个人认为php应该抛弃php-fpm或mod_php的开发方式了，以主流的守护进程的方式来开发，这样的方式性能会比php-fpm或mod_php有几十倍左右的提升.

测试对比
========
稍候给出

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
