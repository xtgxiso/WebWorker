<?php
use Workerman\Worker;
use Workerman\Protocols\Http;

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

//加载配置文件
define("WORKERMAN_RUN",getenv("WORKERMAN_RUN"));
if ( WORKERMAN_RUN == "production" ) {
    require_once __DIR__ . '/config/config_production.php';
}else if ( WORKERMAN_RUN == "testing" ) {
    require_once __DIR__ . '/config/config_testing.php';
}else if ( WORKERMAN_RUN == "development"  ) {
    require_once __DIR__ . '/config/config_development.php';
}else {
    require_once __DIR__ . '/config/config_production.php';
}

$app = new WebWorker\App("http://0.0.0.0:1215");

$app->name = "xtgxiso";

$app->count = 4;

$app->autoload = array(__DIR__."/controllers/",__DIR__."/libs/",__DIR__."/funcs/",__DIR__."/models/");

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
