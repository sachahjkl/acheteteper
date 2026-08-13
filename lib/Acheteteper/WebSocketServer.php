<?php

namespace Acheteteper;

class WebSocketServer
{
    public function __construct(private string $address = '127.0.0.1', private int $port = 9001) {}

    public function run(callable $onMessage): never
    {
        $server = stream_socket_server("tcp://{$this->address}:{$this->port}", $errorCode, $errorMessage);
        if ($server === false) {
            throw new \RuntimeException("WebSocket server failed: $errorMessage", $errorCode);
        }

        while ($client = @stream_socket_accept($server, -1)) {
            try {
                $this->serveClient($client, $onMessage);
            } finally {
                fclose($client);
            }
        }
        throw new \RuntimeException('WebSocket server stopped');
    }

    private function serveClient($client, callable $onMessage): void
    {
        $request = '';
        while (!str_contains($request, "\r\n\r\n")) {
            $chunk = fread($client, 4096);
            if ($chunk === false || $chunk === '') {
                return;
            }
            $request .= $chunk;
            if (strlen($request) > 16384) {
                return;
            }
        }

        if (!preg_match('/^Sec-WebSocket-Key:\s*(.+)$/mi', $request, $match)) {
            return;
        }
        $accept = base64_encode(sha1(trim($match[1]) . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        fwrite($client, "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: $accept\r\n\r\n");

        while (($message = $this->readFrame($client)) !== null) {
            $reply = $onMessage($message);
            fwrite($client, $this->frame((string) $reply));
        }
    }

    private function readFrame($client): ?string
    {
        $header = $this->readBytes($client, 2);
        if ($header === null) {
            return null;
        }
        $opcode = ord($header[0]) & 0x0f;
        if ($opcode === 8) {
            return null;
        }
        if ($opcode !== 1 || (ord($header[1]) & 0x80) === 0) {
            return null;
        }

        $length = ord($header[1]) & 0x7f;
        if ($length === 126) {
            $length = unpack('n', $this->readBytes($client, 2) ?? '')[1] ?? 0;
        } elseif ($length === 127) {
            $parts = unpack('Nhigh/Nlow', $this->readBytes($client, 8) ?? '');
            if (($parts['high'] ?? 1) !== 0) {
                return null;
            }
            $length = $parts['low'];
        }
        if ($length > 1048576) {
            return null;
        }

        $mask = $this->readBytes($client, 4);
        $payload = $this->readBytes($client, $length);
        if ($mask === null || $payload === null) {
            return null;
        }
        for ($index = 0; $index < $length; $index++) {
            $payload[$index] = $payload[$index] ^ $mask[$index % 4];
        }
        return $payload;
    }

    private function readBytes($client, int $length): ?string
    {
        $data = '';
        while (strlen($data) < $length) {
            $chunk = fread($client, $length - strlen($data));
            if ($chunk === false || $chunk === '') {
                return null;
            }
            $data .= $chunk;
        }
        return $data;
    }

    private function frame(string $payload): string
    {
        $length = strlen($payload);
        if ($length < 126) {
            return chr(0x81) . chr($length) . $payload;
        }
        if ($length <= 65535) {
            return chr(0x81) . chr(126) . pack('n', $length) . $payload;
        }
        return chr(0x81) . chr(127) . pack('NN', 0, $length) . $payload;
    }
}
