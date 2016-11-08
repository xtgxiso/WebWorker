<?php
$app->HandleFunc("/welcome",function() use($app){
    $app->server_send('<div style="margin: 200px auto;width:500px;height:800px;text-align:center;">基于<a href="http://www.workerman.net/" target="_blank">Workerman</a>实现的自带http server的web开发框架</div>');
});

$app->HandleFunc("/",function() use($app){
    $app->server_send('<div style="margin: 200px auto;width:500px;height:800px;text-align:center;">基于<a href="http://www.workerman.net/" target="_blank">Workerman</a>实现的自带http server的web开发框架</div>');
});
