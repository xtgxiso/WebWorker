<?php
namespace WebWorker\Libs;

class Mredis extends \Redis{

    /**
     * 静态成品变量 保存全局实例
     */
    private static  $_instance = array();

    /**
     * 静态工厂方法，返还此类的唯一实例
     */
    public static function getInstance($config=array()) {
        $key = md5(implode(":",$config));
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self();
            $host = isset($config["host"]) ? $config["host"] : "127.0.0.1";
            $port = isset($config["port"]) ? $config["port"] : 6379;
            try {
                self::$_instance[$key]->connect($host,$port);
                $password = isset($config["password"]) ? $config["password"] : "";
                if ( $password ){
                    self::$_instance[$key]->auth($password);
                }
                $db = isset($config["db"]) ? $config["db"] : 0;
                if ( !self::$_instance[$key]->select($db) ){
                    echo "redis can't connect\r\n";
                }
            }catch (\Exception $e) {
                echo $e;
            } catch (\Error $e) {
                echo $e;
            }
        }
        return self::$_instance[$key];
    }

}
