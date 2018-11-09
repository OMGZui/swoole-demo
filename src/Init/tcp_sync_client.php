<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 23:25
 */

require __DIR__ . '/../bootstrap.php';

// new 同步
$client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
// connect
if (! $client->connect(HOST, 9501, 0.5)){
    dump("连接失败");
}
// send
if (! $client->send("你个山驴逼\n")) {
    dump("发送失败");
}
// receive
if (! $data = $client->recv()) {
    dump("接收失败");
}
dump($data);
// close
$client->close();