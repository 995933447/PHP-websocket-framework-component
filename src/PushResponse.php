<?php
namespace Bobby\Websocket;

class PushResponse
{
    public function ping($socket)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::PING, '')->buff);
    }

    public function pong($socket)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::PONG, '')->buff);
    }

    public function pushString($socket, $message)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::TEXT, $message)->buff);
    }

    public function pushFile($socket, $message)
    {
        return fwrite($socket, (new Frame())->encodeServerMessage(OpcodeEnum::BINARY, $message)->buff);
    }
}