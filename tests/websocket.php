<?php

$socket = stream_socket_client('tcp://127.0.0.1:18082', $errorCode, $errorMessage, 5);
if ($socket === false) {
    throw new RuntimeException($errorMessage, $errorCode);
}

$key = base64_encode(random_bytes(16));
fwrite($socket, "GET /realtime/socket HTTP/1.1\r\nHost: localhost\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: $key\r\nSec-WebSocket-Version: 13\r\n\r\n");
$headers = '';
while (!str_contains($headers, "\r\n\r\n")) {
    $headers .= fread($socket, 4096);
}
if (!str_starts_with($headers, 'HTTP/1.1 101')) {
    throw new RuntimeException('WebSocket upgrade failed: ' . $headers);
}

$message = 'hello';
$mask = random_bytes(4);
$payload = '';
for ($index = 0; $index < strlen($message); $index++) {
    $payload .= $message[$index] ^ $mask[$index % 4];
}
fwrite($socket, chr(0x81) . chr(0x80 | strlen($message)) . $mask . $payload);

$header = fread($socket, 2);
$length = ord($header[1]) & 0x7f;
$reply = fread($socket, $length);
if (!str_ends_with($reply, 'hello')) {
    throw new RuntimeException('Unexpected WebSocket reply: ' . $reply);
}

fclose($socket);
fwrite(STDOUT, "WebSocket test passed\n");
