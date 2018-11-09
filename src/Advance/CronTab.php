<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/8
 * Time: 11:30
 */

namespace App\Advance;

// 拟linux定时任务
class CronTab
{
    public function __construct()
    {
    }

    public function run($time)
    {
        swoole_timer_tick($time, [$this, 'cron']);
    }

    private function cron($time_id)
    {

    }
}