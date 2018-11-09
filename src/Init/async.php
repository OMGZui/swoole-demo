<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 22:50
 */

require __DIR__ . '/../bootstrap.php';


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