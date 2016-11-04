<?php
namespace WebWorker\Libs;

class Mdb{

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
            self::$_instance[$key] = new Mmysqli($config);
        }
        return self::$_instance[$key];
    }

}
