<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/6
 * Time: 10:58
 */

namespace App\WebSocket;

use Predis\Client;

/**
 * 使用redis代替table，并存储历史聊天记录
 *
 * Class WsRedisServer
 * @package App\WebSocket
 */
class WsRedisServer
{
    private $config;
    private $server;
    private $client;
    private $key = "socket:user";

    public function __construct()
    {
        // 实例化配置
        $this->config = Config::getInstance();
        // redis
        $this->initRedis();
        // 初始化，主要是服务端自己关闭不会清空redis
        foreach ($this->allUser() as $item) {
            $this->client->hdel("{$this->key}:{$item['fd']}", ['fd', 'name', 'avatar']);
        }
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
        // 放入redis
        $this->client->hmset("{$this->key}:{$user['fd']}", $user);

        // 给每个人推送，包括自己
        foreach ($this->allUser() as $item) {
            $server->push($item['fd'], json_encode([
                'user' => $user,
                'all' => $this->allUser(),
                'type' => 'openSuccess'
            ]));
        }
    }

    private function allUser()
    {
        $users = [];
        $keys = $this->client->keys("{$this->key}:*");
        // 所有的key
        foreach ($keys as $k => $item) {
            $users[$k]['fd'] = $this->client->hget($item, 'fd');
            $users[$k]['name'] = $this->client->hget($item, 'name');
            $users[$k]['avatar'] = $this->client->hget($item, 'avatar');
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
        $user['fd'] = $this->client->hget("{$this->key}:{$fd}", 'fd');
        $user['name'] = $this->client->hget("{$this->key}:{$fd}", 'name');
        $user['avatar'] = $this->client->hget("{$this->key}:{$fd}", 'avatar');

        foreach ($this->allUser() as $item) {
            // 自己不用发送
            if ($item['fd'] == $fd) {
                continue;
            }

            $is_push = $server->push($item['fd'], json_encode([
                'type' => $type,
                'message' => $message,
                'datetime' => $datetime,
                'user' => $user
            ]));
            // 删除失败的推送
            if (!$is_push) {
                $this->client->hdel("{$this->key}:{$item['fd']}", ['fd', 'name', 'avatar']);
            }
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
        $user['fd'] = $this->client->hget("{$this->key}:{$fd}", 'fd');
        $user['name'] = $this->client->hget("{$this->key}:{$fd}", 'name');
        $user['avatar'] = $this->client->hget("{$this->key}:{$fd}", 'avatar');
        $this->pushMessage($server, "{$user['name']}离开聊天室", 'close', $fd);
        $this->client->hdel("{$this->key}:{$fd}", ['fd', 'name', 'avatar']);
    }

    /**
     * 初始化redis
     */
    private function initRedis()
    {
        $this->client = new Client([
            'scheme' => $this->config['socket']['redis']['scheme'],
            'host' => $this->config['socket']['redis']['host'],
            'port' => $this->config['socket']['redis']['port'],
        ]);
    }
}