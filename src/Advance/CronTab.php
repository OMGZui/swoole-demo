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
    private $time;

    public function __construct($time)
    {
        $this->time = $time;
    }

    public function run()
    {
        swoole_timer_tick($this->time, [$this, 'cron']);
    }

    private function cron($time_id)
    {
        dump("每{$this->time}毫秒执行一次，ID[{$time_id}]");
    }
}