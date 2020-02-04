<?php
namespace Bobby\Websocket;

abstract class EventHandlerContract
{
    protected $server;

    public function bindServer(WebsocketServer $server)
    {
        $this->server = $server;
    }

    abstract public function onMessage($socket, Frame $frame, PushResponse $response);

    public function onPing($socket, Frame $frame, PushResponse $response)
    {
        $response->pong($socket);
    }

    public function onOutConnect($socket)
    {
        $this->outConnect($socket);
    }

    public function outConnect($socket)
    {
        fclose($socket);
        unset($this->server->connections[intval($socket)]);
        unset($this->server->readers[intval($socket)]);
    }
}