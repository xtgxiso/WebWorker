<?php
$app->HandleFunc("/",function($conn,$data) use($app){
    $conn->send("基于<a href='http://www.workerman.net/' target='_blank'>Workerman</a>实现的自带http server的web开发框架");
});
