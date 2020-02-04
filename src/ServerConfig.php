<?php
namespace Bobby\Websocket;

class ServerConfig
{
    const POLL_MODE = 'poll';

    protected $mode = self::POLL_MODE;
    protected $address; // 监听地址
    protected $port; // 监听端口号

    public function setMode(string $mode)
    {
        $this->mode = $mode;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}