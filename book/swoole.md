# 初始swoole

前言：都是为了生存

## 一、什么是swoole

Swoole：面向生产环境的 PHP 异步网络通信引擎

使 PHP 开发人员可以编写高性能的异步并发 TCP、UDP、Unix Socket、HTTP，WebSocket 服务。Swoole 可以广泛应用于互联网、移动通信、企业软件、云计算、网络游戏、物联网（IOT）、车联网、智能家居等领域。 使用 PHP + Swoole 作为网络通信框架，可以使企业 IT 研发团队的效率大大提升，更加专注于开发创新产品。
## 二、安装

### 1、pecl

```bash
pecl install swoole
```

### 2、源码安装

```bash
curl -O https://pecl.php.net/get/swoole-4.2.5.tgz
tar -zxvf swoole-4.2.5.tgz
cd swoole-4.2.5.tgz
phpize
./configure
make && make install
# 加入到php.ini中
php --ini
extension=swoole.so
```

## 三、基本入门

**注意：** 示例代码都引入了`"symfony/var-dumper"`包进行美化打印

`Swoole`的绝大部分功能只能用于`cli`命令行环境

### 1、TCP服务器

```php
<?php
// new
$server = new swoole_server("0.0.0.0", "9501",SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

// connect 连接
$server->on("connect", function (swoole_server $server, $fd) {
    dump("{$fd}连接");
    $server->send($fd, "欢迎{$fd}大山驴\n");
});
// receive 回调
$server->on("receive", function (swoole_server $server, $fd, $from_id, $data) {
    $server->send($fd, "服务端回复:{$data}\n");
    foreach ($server->connections as $connection) {
        if ($connection != $fd){
            $server->send($connection, "{$fd}说{$data}");
        }
    }

});
// close
$server->on("close", function (swoole_server $server, $fd) {
    dump("{$fd}关闭");
    foreach ($server->connections as $connection) {
        if ($connection != $fd){
            $server->send($connection, "{$fd}断开连接");
        }
    }
});
// start
$server->start();
```

```bash
php tcp_server.php

telnet 127.0.0.1 9501
```

### 2、UDP服务器

```php
// new
<?php
$server = new swoole_server("0.0.0.0", "9502", SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
// packet
$server->on("Packet", function (swoole_server $server, $data, $clientInfo) {
    $server->sendto($clientInfo['address'], $clientInfo['port'], "服务器回复: {$data}");
    dump($clientInfo);
});
// start
$server->start();
```

```bash
php udp_server.php

netcat -u 127.0.0.1 9502
```

### 3、Web服务器

```php
<?php
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

```

```bash
php web_server.php

curl -XGET "127.0.0.1:9503?id=1&name=aa&age=26"
curl -XPOST "127.0.0.1:9503?id=1&name=aa&age=26" -d "love=like"
```

### 4、WebSocket服务器

服务端

```php
<?php
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
```

客户端

```js
let ws = new WebSocket("ws://127.0.0.1:9504");
ws.onopen = function (ws) {
    console.log("连接服务器");
};

ws.onclose = function (ws) {
    console.log("断开连接");
};

ws.onmessage = function (ws) {
    console.log('接收来自服务器的消息：' + ws.data);
};

ws.onerror = function (ws, event) {
    console.log('错误了：' + ws.data);
};
```

### 5、定时器

```php
<?php
// 每2秒执行一次
swoole_timer_tick(2000, function ($time_id){
    dump($time_id);
});

// 3秒后执行
swoole_timer_after(3000, function (){
    dump("这是3s后");
});
```

### 6、异步任务

```php
<?php
// new
$server = new swoole_server(HOST, "9505",SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
//设置异步任务的工作进程数量
$server->set(['task_worker_num' => 4]);
// connect 连接
$server->on("connect", function (swoole_server $server, $fd) {
    dump("{$fd}连接");
    $server->send($fd, "欢迎{$fd}大山驴\n");
});
// receive 回调
$server->on("receive", function (swoole_server $server, $fd, $from_id, $data) {
    //投递异步任务
    $task_id = $server->task($data);
    dump("触发异步任务ID={$task_id}");
    $server->send($fd, "服务端回复:{$data}\n");
    foreach ($server->connections as $connection) {
        if ($connection != $fd){
            $server->send($connection, "{$fd}说{$data}");
        }
    }
});
// task 处理异步任务
$server->on("task", function (swoole_server $server, $task_id, $from_id, $data){
    dump("新的异步任务[ID={$task_id}]");
    //返回任务执行的结果
    $server->finish("{$data}完成了");
});
// finish 处理异步任务的结果
$server->on("finish", function (swoole_server $server, $task_id, $data){
    dump("异步任务[{$task_id}]已经完成[{$data}]");
});
// close
$server->on("close", function (swoole_server $server, $fd) {
    dump("{$fd}关闭");
    foreach ($server->connections as $connection) {
        if ($connection != $fd){
            $server->send($connection, "{$fd}断开连接");
        }
    }
});
// start
$server->start();
```

```bash
php async.php

telnet 127.0.0.1 9505
```

### 7、同步TCP客户端

```php
<?php
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
```

```bash
php tcp_server.php

php tcp_sync_client.php
```



### 8、异步TCP客户端

```php
<?php
// new 异步
$client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
// connect
$client->on("connect", function (\Swoole\Client $cli){
    $cli->send("你个山驴逼\n");
});
// receive
$client->on("receive", function (\Swoole\Client $cli, $data){
    dump("接收：{$data}");
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
```

```bash
php tcp_server.php

php tcp_async_client.php
```

### 