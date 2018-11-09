<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/11/5
 * Time: 20:33
 */

namespace App\WebSocket;

class Config implements \ArrayAccess
{
    private $path;
    private $config;
    private static $instance;

    public function __construct()
    {
        $this->path = __DIR__ . '/../../config/';
    }

    // 单例模式
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function offsetSet($offset, $value)
    {
        // 阉割
    }

    public function offsetGet($offset)
    {
        if (empty($this->config)) {
            $this->config[$offset] = require $this->path . $offset . ".php";
        }
        return $this->config[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetUnset($offset)
    {
        // 阉割
    }

    // 禁止克隆
    final private function __clone(){}
}