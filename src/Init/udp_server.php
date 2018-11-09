<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 21:43
 */
require __DIR__ . '/../bootstrap.php';

// new
$server = new swoole_server(HOST, "9502", SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
// packet
$server->on("Packet", function (swoole_server $server, $data, $clientInfo) {
    $server->sendto($clientInfo['address'], $clientInfo['port'], "服务器回复: {$data}");
    dump($clientInfo);
});
// start
$server->start();
