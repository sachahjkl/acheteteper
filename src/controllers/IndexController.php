<?php

namespace Controllers;

use Acheteteper\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        $this->render('index');
    }
}
