<?php

namespace Controllers;

use Httpeur\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        $this->render('index', [
            'name' => 'Sacha',
            'items' => ['Item 1', 'Item 2', 'Item 3']
        ]);
    }
}
