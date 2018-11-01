<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 23:25
 */

require __DIR__ . '/../bootstrap.php';

// new 异步
$client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
// connect
$client->on("connect", function (\Swoole\Client $cli){
    $cli->send("你个山驴逼\n");
});
// receive
$client->on("receive", function (\Swoole\Client $cli, $data){
    dump("{$data}");
});
// error
$client->on("error", function (\Swoole\Client $cli){
    dump("连接失败");
});
// close
$client->on("close", function (\Swoole\Client $cli){
    dump("连接关闭");
});
$client->connect(HOST, 9501, 0.5);