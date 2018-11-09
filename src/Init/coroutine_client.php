<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/2
 * Time: 11:26
 */

require __DIR__ . '/../bootstrap.php';

// 协程mysql客户端
$http = new swoole_http_server(HOST, 9506);
$http->on('request', function ($request, $response) {
    $db = new \Swoole\Coroutine\Mysql();
    $db->connect([
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => 'root',
        'database' => 'mac',
        'port' => '3307',
    ]);
    $data = $db->query('select * from user');
    dump($data);
    $response->end(json_encode($data));
});
$http->start();
