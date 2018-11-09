<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 22:46
 */

require __DIR__ . '/../bootstrap.php';

// 每2秒执行一次
$time_id = swoole_timer_tick(2000, function ($time_id){
    dump("每2秒执行一次ID: {$time_id}");
});

// 3秒后执行
swoole_timer_after(3000, function (){
    dump("这是3s后");
});
