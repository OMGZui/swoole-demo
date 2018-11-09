<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/2
 * Time: 14:10
 */
require __DIR__ . '/../bootstrap.php';

for ($i = 0; $i < 3; $i++) {
    $p = new swoole_process(function () use ($i) {
        $port = 9510 + $i;
        $http = new swoole_http_server(HOST, $port);

        $http->on("start", function ($server) use ($port) {
            echo "Swoole http server is started at http://127.0.0.1:{$port}\n";
        });

        $http->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });

        $http->start();
    }, false, false);
    $p->start();
}