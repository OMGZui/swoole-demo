<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/2
 * Time: 14:36
 */

namespace App\Server;

class Server
{

    protected $server;

    public function __construct()
    {
        $this->server = new \swoole_server(HOST, 9530, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
    }

    public function run()
    {
        $this->setting();
        // 常规
        $this->server->on("connect", [$this, 'connect']);
        $this->server->on("receive", [$this, 'receive']);
        $this->server->on("close", [$this, 'close']);
        // 额外
        $this->server->on("start", [$this, 'start']);
        $this->server->on("managerStart", [$this, 'managerStart']);
        $this->server->on("workerStart", [$this, 'workerStart']);

        $this->server->start();
    }

    public function setting()
    {
        $this->server->set([
            'worker_num' => 4,
            'backlog' => 128,
            'max_request' => 50,
            'dispatch_mode' => 1,
        ]);
    }

    public function start(\swoole_server $server)
    {
        dump("start进程ID:{$server->worker_id}/{$server->manager_pid}/{$server->master_pid}");
    }

    public function managerStart(\swoole_server $server)
    {
        dump("managerStart进程ID:{$server->worker_id}/{$server->manager_pid}/{$server->master_pid}");
    }

    public function workerStart(\swoole_server $server, $worker_id)
    {
        dump("workerStart进程ID:{$server->worker_id}/{$server->manager_pid}/{$server->master_pid}");
    }

    public function connect(\swoole_server $server, $fd, $reactor_id)
    {
        dump("{$fd}连接");
        $server->send($fd, "欢迎{$fd}大山驴\n");
    }

    public function receive(\swoole_server $server, $fd, $reactor_id, $data)
    {
        $server->send($fd, "服务端回复:{$data}\n");
        foreach ($server->connections as $connection) {
            if ($connection != $fd) {
                $server->send($connection, "{$fd}说{$data}\n");
            }
        }
    }

    public function close(\swoole_server $server, $fd, $reactor_id)
    {
        dump("{$fd}关闭");
        foreach ($server->connections as $connection) {
            if ($connection != $fd) {
                $server->send($connection, "{$fd}断开连接\n");
            }
        }
    }
}

