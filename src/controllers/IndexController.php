<?php

namespace controllers;

use Acheteteper\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        $this->render('index');
    }
}
