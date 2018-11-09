<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/5
 * Time: 20:24
 */

namespace App\WebSocket;

/**
 * 简单的聊天室
 *
 * Class WebSocketServer
 * @package App\WebSocket
 */
class WebSocketServer
{
    private $config;
    private $table;
    private $server;

    public function __construct()
    {
        // 内存表 实现进程间共享数据，也可以使用redis替代
        $this->createTable();
        // 实例化配置
        $this->config = Config::getInstance();
    }

    public function run()
    {
        $this->server = new \swoole_websocket_server(
            $this->config['socket']['host'],
            $this->config['socket']['port']
        );

        $this->server->on('open', [$this, 'open']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);

        $this->server->start();
    }

    public function open(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        $user = [
            'fd' => $request->fd,
            'name' => $this->config['socket']['name'][array_rand($this->config['socket']['name'])] . $request->fd,
            'avatar' => $this->config['socket']['avatar'][array_rand($this->config['socket']['avatar'])]
        ];
        // 放入内存表
        $this->table->set($request->fd, $user);

        $server->push($request->fd, json_encode(
                array_merge(['user' => $user], ['all' => $this->allUser()], ['type' => 'openSuccess'])
            )
        );
    }

    private function allUser()
    {
        $users = [];
        foreach ($this->table as $row) {
            $users[] = $row;
        }
        return $users;
    }

    public function message(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        $this->pushMessage($server, $frame->data, 'message', $frame->fd);
    }

    /**
     * 推送消息
     *
     * @param \swoole_websocket_server $server
     * @param string $message
     * @param string $type
     * @param int $fd
     */
    private function pushMessage(\swoole_websocket_server $server, string $message, string $type, int $fd)
    {
        $message = htmlspecialchars($message);
        $datetime = date('Y-m-d H:i:s', time());
        $user = $this->table->get($fd);

        foreach ($this->table as $item) {
            // 自己不用发送
            if ($item['fd'] == $fd) {
                continue;
            }

            $server->push($item['fd'], json_encode([
                'type' => $type,
                'message' => $message,
                'datetime' => $datetime,
                'user' => $user
            ]));
        }
    }

    /**
     * 客户端关闭的时候
     *
     * @param \swoole_websocket_server $server
     * @param int $fd
     */
    public function close(\swoole_websocket_server $server, int $fd)
    {
        $user = $this->table->get($fd);
        $this->pushMessage($server, "{$user['name']}离开聊天室", 'close', $fd);
        $this->table->del($fd);
    }

    /**
     * 创建内存表
     */
    private function createTable()
    {
        $this->table = new \swoole_table(1024);
        $this->table->column('fd', \swoole_table::TYPE_INT);
        $this->table->column('name', \swoole_table::TYPE_STRING, 255);
        $this->table->column('avatar', \swoole_table::TYPE_STRING, 255);
        $this->table->create();
    }
}