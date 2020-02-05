<?php

class EventHandler extends \Bobby\Websocket\EventHandlerContract
{
    public function onMessage($socket, \Bobby\Websocket\Frame $frame, \Bobby\Websocket\PushResponse $response)
    {
        $data = json_decode($frame->payloadData);
        $data->time = date('Y-m-d H:i:s');
        $data = json_encode($data);
        foreach ($this->server->connections as $connection) {
            $response->pushString($connection, $data);
        }
    }
}