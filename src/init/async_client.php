<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/2
 * Time: 10:17
 */
require __DIR__ . '/../bootstrap.php';

// 异步mysql客户端
$db = new \Swoole\Mysql();
$config = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'root',
    'database' => 'mac',
    'port' => '3307',
];

$db->connect($config, function (\Swoole\Mysql $db, $rs) {
    $db->query('SELECT * FROM user', function (\Swoole\Mysql $db, $rs) {
        dump($rs);
        $db->close();
    });
});

// 异步http客户端
$cli = new Swoole\Http\Client('127.0.0.1', 10000);
$cli->setHeaders(array('User-Agent' => 'swoole-http-client'));
$cli->setCookies(array('test' => 'value'));

$cli->get('/login', function (\Swoole\Http\Client $cli) {
    dump($cli->statusCode);
    dump($cli->cookies);
    dump($cli->headers);
});

