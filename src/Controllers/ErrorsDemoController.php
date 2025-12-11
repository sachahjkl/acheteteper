<?php

namespace Controllers;

use Acheteteper\ControllerBase;

class ErrorsDemoController extends ControllerBase
{
    public function index()
    {
        $this->render('errors_demo');
    }

    public function notFound()
    {
        $this->setStatus(404);
        $this->render('errors_demo', ['error' => '404 Not Found']);
    }

    public function forbidden()
    {
        $this->setStatus(403);
        $this->render('errors_demo', ['error' => '403 Forbidden']);
    }

    public function serverError()
    {
        $this->setStatus(500);
        $this->render('errors_demo', ['error' => '500 Internal Server Error']);
    }
}
