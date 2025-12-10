<?php

namespace Controllers;

use Acheteteper\ControllerBase;
use Acheteteper\ViewHelper;

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

        $this->render('helpers_demo', $data);
    }
}
