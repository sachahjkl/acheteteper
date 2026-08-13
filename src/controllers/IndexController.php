<?php

namespace controllers;

use Acheteteper\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        return $this->render('index');
    }
}
