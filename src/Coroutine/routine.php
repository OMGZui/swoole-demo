<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/1
 * Time: 11:50
 */

require __DIR__ . '/../bootstrap.php';

go(function () {
    co::sleep(0.5);
    dump("这是协程");
});

go(function () {
    $chan = new chan(128);
    $chan->push(1234);
    $chan->push(1234.56);
    $chan->push("hello world");
    $chan->push(["hello world"]);
    $chan->push(new stdclass);
    while (true){
        dump($chan->pop());
    };
});

go(function () {
    dump(co::getHostByName('www.baidu.com'));
});
