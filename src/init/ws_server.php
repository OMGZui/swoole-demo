<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 22:09
 */
require __DIR__ . '/../bootstrap.php';

// new
$ws = new swoole_websocket_server(HOST, "9504");
// open
$ws->on("open", function (Swoole\WebSocket\Server $ws, \Swoole\Http\Request $request) {
    dump($request->fd, $request->get, $request->server);
    $ws->push($request->fd, "你是大山驴\n");
});
// message
$ws->on("message", function (\Swoole\WebSocket\Server $ws, $frame) {
    dump("消息: {$frame->data}\n");
    $ws->push($frame->fd, "服务端回复: {$frame->data}\n");
});
// close
$ws->on("close", function (Swoole\WebSocket\Server $ws, $fd) {
    dump("{$fd}关闭");
});
// start
$ws->start();
