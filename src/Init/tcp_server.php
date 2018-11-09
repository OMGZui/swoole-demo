<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 21:15
 */

require __DIR__ . '/../bootstrap.php';

// new
$server = new swoole_server(HOST, "9501",SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

// connect 连接
$server->on("connect", function (swoole_server $server, $fd) {
    dump("{$fd}连接");
    $server->send($fd, "欢迎{$fd}大山驴\n");
});
// receive 回调
$server->on("receive", function (swoole_server $server, $fd, $from_id, $data) {
    $server->send($fd, "服务端回复:{$data}\n");
    foreach ($server->connections as $connection) {
        if ($connection != $fd){
            $server->send($connection, "{$fd}说{$data}\n");
        }
    }

});
// close
$server->on("close", function (swoole_server $server, $fd) {
    dump("{$fd}关闭");
    foreach ($server->connections as $connection) {
        if ($connection != $fd){
            $server->send($connection, "{$fd}断开连接\n");
        }
    }
});
// start
$server->start();