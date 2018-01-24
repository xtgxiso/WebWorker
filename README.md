WebWorker
========

基于Workerman实现的自带http server的web开发框架，用于开发高性能的api应用，例如app接口服务端等。 

详细文档见 http://doc.webworker.xtgxiso.com/ 
问答互动见 http://ask.webworker.xtgxiso.com/ .

特性
========
* 仅只支持php7
* 天生继承workerman所拥有的特性
* 只实现了简单路由功能的小巧框架,便于开发者使用和扩展.demo1中只是目录示例，开发者可自行定义自己的应用目录结构
* 相比php-fpm或mod_php的方式性能有几十倍左右的提升
* 自带简单的单例redis操作类和单例mysqli操作类(支持自动重连)
* 可设置自动加载目录加载目录下的所有php文件(仅一级不支持递归)
* 自定义404响应
* 支持http协议1.1和1.0的长连接和短连接
* 集成了workerman-statistics项目，可以监控服务情况
* 支持中间件
* 支持一次性订阅和发布

框架由来
========
大家经常说php性能差，其实主要是在php-fpm或mod_php方式下的差，而php语言本身是不错的，尤其在未来加入JIT之后，性能会越来越好的。面对新兴的语言和开发方式，个人认为php应该抛弃php-fpm或mod_php的开发方式了，以主流的守护进程的方式来开发，这样的方式性能会比php-fpm或mod_php有几十倍左右的提升.

测试对比
========
https://github.com/xtgxiso/WebWorker-benchmark

项目示例
========
https://github.com/xtgxiso/WebWorker-example


安装
========

```
composer require xtgxiso/webworker
```

快速开始
======
demo.php
```php
<?php
use Workerman\Worker;
use Workerman\Protocols\Http;
use WebWorker\Libs\Mredis;
use WebWorker\Libs\Mdb;
use WebWorker\Libs\Mmysqli;
use WebWorker\Libs\Maccess;

require_once 'vendor/autoload.php';

$app = new WebWorker\App("http://0.0.0.0:1215");
$app->count = 1;

$config = array();
$config["redis"]["host"] = "127.0.0.1";
$config["redis"]["port"] = 6379;
$config["redis"]["password"] = "123456";
$config["redis"]["db"] = 1;
$config["db"]["host"] = "127.0.0.1";
$config["db"]["user"] = "root";
$config["db"]["password"] = "123456";
$config["db"]["db"] = "test";
$config["db"]["port"] = 3306;
$config["db"]["charset"] = "utf8";
$config["access"]["appid"] = "123456";
$config["access"]["appsecret"] = "abcdef";


$app->name = "demo";

//设置每个进程处理多少请求后重启(防止程序写的有问题导致内存泄露)，默认为10000
$app->max_request = 1000;

//进程数
$app->count = 4;

//自动加载目录--会加载目录下的所有php文件
$app->autoload = array();

//启动时执行的代码，这儿包含的文件支持reload
$app->onAppStart = function($app) use($config){
    WebWorker\autoload_dir($app->autoload);     
};

//应用级中间件--对/hello访问启用ip限制访问
$app->AddFunc("/hello",function() {
    if ( $_SERVER['REMOTE_ADDR'] != '127.0.0.1' ) {
        $this->ServerHtml("禁止访问");
        return true;//返回ture,中断执行后面的路由或中间件，直接返回给浏览器
    }   
});

//应用级中间件--对所有以api前缀开头的启用签名验证
$app->AddFunc("/api",function() use($config) {
    $data = $_GET ? $_GET : $_POST;
    if ( !Maccess::verify_sign($data,$config["access"]) ){
        $this->ServerHtml("禁止访问");
        return true;
    }
});


//注册路由api/test
$app->HandleFunc("/api/test",function() {
    $this->ServerHtml("api test hello");
});

//注册路由hello
$app->HandleFunc("/hello",function() {
    $this->ServerHtml("Hello World WorkerMan WebWorker!");
});

//注册路由json
$app->HandleFunc("/json",function() {
     //以json格式响应
     $this->ServerJson(array("name"=>"WebWorker"));
});

//注册路由/
$app->HandleFunc("/",function() {
     //自定义响应头
     $this->Header("server: xtgxiso");
     //设置cookie
     $this->Setcookie("xtgxiso",time()); 
     //以json格式响应
     $this->ServerJson(array("name"=>"WebWorker"));
});

//注册路由input
$app->HandleFunc("/input",function() {
    //获取body
     $body = $GLOBALS['HTTP_RAW_POST_DATA'];
     $this->ServerHtml($body);
});


//redis示例
$app->HandleFunc("/redis",function() use($app,$config){
     $redis = Mredis::getInstance($config["redis"]);
     $app->ServerHtml($redis->get("xtgxiso"));
});

//mysql示例
$app->HandleFunc("/mysql",function() use($app,$config){
     $this->db = Mdb::getInstance($config["db"]);
     $result = array();
     $sql = "select * from test limit 1";
     //取一行对象结果集
     $result['data1'] = $this->db->query($sql)->row();
     //取一行数组结果集
     $result['data2'] = $this->db->query($sql)->row_array();
     //取多行对象结果集
     $result['data3'] = $this->db->query($sql)->result();
     //取多行数组结果集
     $result['data4'] = $this->db->query($sql)->result_array();
     //取多行，自动转义参数
     $result['data5'] = $this->db->query("select * from test where id = ? or id =? ",array(1,2))->result_array();
     //取表test中的一行数据
     $result['data6'] = $this->db->get("test",0,1)->row_array();
     //取表中id=22的一行数据
     $result['data7'] = $this->db->get_where("test",array("id"=>22),0,1)->row_array();
     //向表test插入数据
     $result['data8'] = $this->db->insert("test",array("name"=>time()));
     //更新表test中的id=1的数据
     $result['data9'] = $this->db->update("test",array("name"=>time()),array("id"=>1));
     //删除表test中的id=2的数据
     $result['data10'] = $this->db->delete("test",array("id"=>2));
     $this->ServerJson($result);
    
});

//自定义404
$app->on404  = function() {
    $this->ServerHtml("我的404");
};

//测试类
class xtgxiso{
    static function test()
    {
        global $app;
        $app->ServerHtml("xtgxiso");
    }
}
//注册类
$app->HandleFunc("/xtgxiso",array("xtgxiso","test"));

//订阅，不考虑分布式，分布式的话连接不能存储在内存中
$app->HandleFunc("/subscription",function() {
    $id = $_GET["id"];
    $this->conn_list[$id] =  $this->conn;
    $this->conn_close = false;//通过设置属性控制连接不关闭
});

//发布，不考虑分布式，分布式的话连接不能存储在内存中
$app->HandleFunc("/publish",function() {
    $id = $_GET["id"];
    $conn = $this->conn_list[$id];
    if ( $conn ){
        $str = $_GET["content"];
        $conn->send($str);
    }
    $this->ServerHtml("");
});

//访问日志
Worker::$stdoutFile = './stdout.log';

// Run worker
Worker::runAll();
```


技术交流QQ群
========
517297682
