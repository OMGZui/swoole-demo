<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/2
 * Time: 15:00
 */

require __DIR__ . '/../bootstrap.php';

$server = new \App\Server\Server();

$server->run();