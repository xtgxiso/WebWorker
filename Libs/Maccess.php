<?php
namespace WebWorker\Libs;

class Maccess{

    public static function verify_sign($data,$config) {
        if ( empty($data) ){
	    return true;
 	}
        if ( empty($config['appid']) || empty($config['appsecret']) ){
            return false;
        }
	//按照参数名排序
        ksort($data);
        //连接待加密的字符串
        $appid = $data['appid'];
        if ( $appid != $config['appid'] ){
            return false;
        }
        $codes = $appid;
        while (list($key, $val) = each($data))
        {
            if (!in_array($key,array('appid','sign')) ){//排除不签名的参数
                $codes .=$key.$val;
            }
        }
        $codes .= $config['appsecret'];
        $sign = strtoupper(sha1($codes));
        if ( $data['sign'] == $sign ){
            return true;
        }else{
	    return false;
	}
    }

}
