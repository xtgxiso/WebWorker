<?php
namespace WebWorker\Libs;

class Mredis extends \Redis{

    /**
     * 静态成品变量 保存全局实例
     */
    private static  $_instance = NULL;

    /**
     * 静态工厂方法，返还此类的唯一实例
     */
    public static function getInstance($config=array()) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
            $host = $config["host"] ? $config["host"] : "127.0.0.1";
            $port = $config["port"] ? $config["port"] : 6379;
            self::$_instance->connect($host,$port);
            $password = $config["password"] ? $config["password"] : "";
            if ( $password ){
                self::$_instance->auth($password);
            }
            $db = $config["db"] ? $config["db"] : 0;
            self::$_instance->select($db);
        }
        return self::$_instance;
    }

}
