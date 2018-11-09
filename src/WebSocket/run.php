<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/5
 * Time: 21:32
 */

require __DIR__ . '/../bootstrap.php';

$server = new \App\WebSocket\WebSocketServer();
//$server = new \App\WebSocket\WsRedisServer();

$server->run();