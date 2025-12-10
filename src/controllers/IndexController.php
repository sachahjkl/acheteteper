<?php

namespace Controllers;

use Httpeur\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        $this->render('index');
    }
}
