<?php

require __DIR__ . '/../vendor/autoload.php';

use Acheteteper\WebSocketServer;

(new WebSocketServer())->run(
    fn(string $message): string => '[' . gmdate('H:i:s') . '] ' . $message
);
