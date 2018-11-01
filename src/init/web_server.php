<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 21:51
 */

require __DIR__ . '/../bootstrap.php';

// new
$http = new swoole_http_server(HOST, "9503");
// request
$http->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    dump($request->get, $request->post);
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #" . mt_rand(1000, 9999) . "</h1>\n");
});
// start
$http->start();
