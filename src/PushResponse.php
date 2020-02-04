<?php
namespace Bobby\Websocket;

class PushResponse
{
    public function ping($socket)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::PING, '')->content);
    }

    public function pong($socket)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::PONG, '')->content);
    }

    public function pushString($socket, $message)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::TEXT, $message)->content);
    }

    public function pushFile($socket, $message)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::BINARY, $message)->content);
    }
}