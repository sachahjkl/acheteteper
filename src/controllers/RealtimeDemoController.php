<?php

namespace controllers;

use Acheteteper\ControllerBase;
use Acheteteper\ServerSentEvents;

class RealtimeDemoController extends ControllerBase
{
    private const ITEMS = [
        'Achetétéper', 'Controller', 'CSRF', 'Datasource', 'Engine', 'HTMX',
        'JSON', 'Nix', 'QUERY', 'Response', 'Router', 'Server-Sent Events',
        'Session', 'SQLite', 'Upload', 'WebSocket',
    ];

    public function index()
    {
        return $this->render('realtime_demo', ['items' => self::ITEMS]);
    }

    public function search()
    {
        $query = trim((string) $this->request->get('q', ''));
        $items = array_values(array_filter(
            self::ITEMS,
            fn(string $item): bool => $query === '' || stripos($item, $query) !== false
        ));
        return $this->renderPartial('realtime_search_results', ['items' => $items, 'query' => $query]);
    }

    public function events()
    {
        return $this->response->eventStream(function (): void {
            ServerSentEvents::start();
            for ($index = 1; $index <= 5; $index++) {
                ServerSentEvents::send([
                    'index' => $index,
                    'time' => gmdate('H:i:s'),
                ], 'tick', (string) $index);
                usleep(500000);
            }
        });
    }
}
