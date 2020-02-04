<?php
namespace Bobby\Websocket;

class Frame
{
    const MIN_FRAME_HEX_LENGTH = 12;

    public $finWith3Rsv;
    public $opcode;
    public $isMasked;
    public $payloadLen = 0;
    public $maskingKey = [];
    public $payloadData = '';

    protected $receiveSegment = '';
    public $content = '';

    public function decodeClientBuff(string $buff)
    {
        $this->receiveSegment = $buff;
        $this->content .= $buff;

        $this->content = $buff;
        // 把二进制字符串解压成16进制字符串
        $message = unpack('H*', $this->content)[1];

        if (($messageLen = strlen($message)) < static::MIN_FRAME_HEX_LENGTH) {
            return null;
        }

        $this->finWith3Rsv = substr($message, 0, 1);
        $this->opcode = substr($message, 1, 1);
        $this->isMasked = true; // 客户端请求服务端需要设置屏蔽码， buff mask必须位1个Bit的1,反之不需要设置

        $payloadLenDescBytes = 0;
        if (in_array($this->opcode, [OpcodeEnum::TEXT, OpcodeEnum::BINARY])) {
            if (($this->payloadLen = (hexdec(substr($message, 2, 2)) & 127)) < 126) {
                $maskingKeyStart = 1+ 1 + 2;
            } else {
                switch ($this->payloadLen) {
                    case 126:
                        $payloadLenDescBytes = 16 / 4;
                        break;
                    default:
                        $payloadLenDescBytes = 64 / 4;
                }

                if ($messageLen < (static::MIN_FRAME_HEX_LENGTH + $payloadLenDescBytes)) {
                    return null;
                }

                $this->payloadLen = hexdec(substr($message, 4, $payloadLenDescBytes)); // 如果payload len为126 则取后面16位表示payload data的长度
                $maskingKeyStart = 1 + 1 + 2 + $payloadLenDescBytes; // FIN,RSV1,RSV2,Rsv3 + OPCODE+ MASK,PAYLOAD LEN + EXTEND PAYLOAD LEN
            }

            if ($messageLen < ($completeFrameLen = static::MIN_FRAME_HEX_LENGTH + $payloadLenDescBytes + $this->payloadLen * 2)) {
                return null;
            }

            $message = substr($message, 0, $completeFrameLen);
            $this->content = substr($message, $completeFrameLen);

            for ($i = 0; $i < 4; $i++) {
                $this->maskingKey[] = hexdec(substr($message, $maskingKeyStart + $i * 2, 2)); // 截取32位屏蔽码
            }

            for($payloadDataStart = $maskingKeyStart + 32 / 4, $n = 0; $payloadDataStart < strlen($message); $payloadDataStart += 2, $n++) {
                $this->payloadData .= chr(hexdec(substr($message, $payloadDataStart, 2)) ^ $this->maskingKey[$n % 4]);
            }
        }

        return $this;
    }

    public function encodeServerMessage($opcode, string $payload)
    {
        $this->finWith3Rsv = FinWith3RsvEnum::FINISH;
        $this->opcode = $opcode;
        $this->payloadData = $payload;
        $this->isMasked = false; // 客户端请求服务端需要设置屏蔽码， buff mask必须位1个Bit的1,反之不需要设置
        $this->maskingKey = [];

        switch ($opcode) {
            case OpcodeEnum::PING:
            case OpcodeEnum::PONG:
            case OpcodeEnum::OUT_CONNECT:
                $this->content = pack("H*", sprintf("%x%x", FinWith3RsvEnum::FINISH, $opcode));
                break;
            case OpcodeEnum::BINARY:
            case OpcodeEnum::TEXT:
                if (($this->payloadLen = strlen($this->payloadData)) <= 125) {
                    $this->content = pack("H*", sprintf("%x%x", FinWith3RsvEnum::FINISH, $opcode)) . chr($this->payloadLen) . $payload;
                } else if ($this->payloadLen <= 65535) {
                    $this->content = pack("H*", sprintf("%x%x", FinWith3RsvEnum::FINISH, $opcode)) . chr(126) . pack("n", $this->payloadLen) . $payload;
                } else {
                    $this->content = pack("H*", sprintf("%x%x", FinWith3RsvEnum::FINISH, $opcode)) . chr(127) . pack("J", $this->payloadLen) . $payload;
                }
        }

        return $this;
    }
}