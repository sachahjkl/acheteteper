<?php

namespace Acheteteper;

class ServerSentEvents
{
    public static function start(): void
    {
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    public static function send(mixed $data, ?string $event = null, ?string $id = null): void
    {
        if ($id !== null) {
            echo 'id: ' . self::line($id) . "\n";
        }
        if ($event !== null) {
            echo 'event: ' . self::line($event) . "\n";
        }

        $payload = is_string($data) ? $data : json_encode($data, JSON_THROW_ON_ERROR);
        foreach (preg_split('/\R/', $payload) ?: [] as $line) {
            echo 'data: ' . $line . "\n";
        }
        echo "\n";

        flush();
    }

    private static function line(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }
}
