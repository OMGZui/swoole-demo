<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 11:21
 */

require __DIR__ . '/../bootstrap.php';

// 协程mysql客户端
go(function () {
    $db = new \Swoole\Coroutine\Mysql();
    $server = array(
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => 'root',
        'database' => 'mac',
        'port' => '3307',
    );

    $db->connect($server);

    $result = $db->query('select * from user');
    dump($result);
});
