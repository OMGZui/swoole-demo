<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/2
 * Time: 14:20
 */

require __DIR__ . '/../bootstrap.php';

$worker_num = 2;

for ($i = 0; $i < $worker_num; $i++) {
    $process = new swoole_process('callback_function');
    $pid = $process->start();
    $workers[$pid] = $process;
}

function callback_function(swoole_process $worker)
{
//    dump("子进程开始，PID: {$worker->pid}");
    //receive data from master
    $receive = $worker->read();
    dump("来自父进程: {$receive}");

    //send data to master
    $worker->write("hello master\n");

    sleep(2);
    $worker->exit(0);
}