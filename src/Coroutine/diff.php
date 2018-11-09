<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/7
 * Time: 21:48
 */

require __DIR__ . '/../bootstrap.php';

// 协程
$time = microtime(true);
// 创建10个协程
for ($i = 0; $i < 10; ++$i) {
    // 创建协程
    go(function () use ($i) {
        co::sleep(1.0); // 模拟请求接口、读写文件等I/O
        dump($i);
    });
}
swoole_event_wait();
dump("协程用时: ", microtime(true) - $time);

// 同步
$time = microtime(true);
// 创建10个协程
for ($i = 0; $i < 10; ++$i) {
    sleep(1); // 模拟请求接口、读写文件等I/O
    dump($i);
}
dump("同步用时: ", microtime(true) - $time);