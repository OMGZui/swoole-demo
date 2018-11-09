<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/9
 * Time: 11:46
 */

require __DIR__ . '/../bootstrap.php';

$cron = new \App\Advance\CronTab(2000);

$cron->run();