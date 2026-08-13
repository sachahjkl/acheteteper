<?php

namespace controllers;

use Acheteteper\ControllerBase;
use Acheteteper\Session;

class ConfigController extends ControllerBase
{
    public function index()
    {
        if ($this->request->isPost()) {
            $key = $this->request->post('key');
            $value = $this->request->post('value');
            if ($key === 'DEBUG') {
                Session::set($key, $value);
            }
            return $this->redirect('/config');
        }

        return $this->render('config');
    }
}
