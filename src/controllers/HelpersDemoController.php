<?php

namespace controllers;

use Acheteteper\ControllerBase;
class HelpersDemoController extends ControllerBase
{
    public function index()
    {
        $data = [
            'htmlString' => '<script>alert("XSS")</script>',
            'longText' => 'This is a very long text that needs to be truncated to demonstrate the truncate helper function.',
            'timestamp' => time(),
            'dateString' => '2024-01-15 14:30:00',
        ];

        return $this->render('helpers_demo', $data);
    }
}
