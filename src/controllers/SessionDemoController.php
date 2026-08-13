<?php

namespace controllers;

use Acheteteper\ControllerBase;
use Acheteteper\Session;

class SessionDemoController extends ControllerBase
{
    public function index()
    {
        $counter = Session::get('counter', 0);
        $counter++;
        Session::set('counter', $counter);

        if ($this->request->isPost()) {
            $key = $this->request->post('key');
            $value = $this->request->post('value');
            if ($key) {
                Session::set($key, $value);
            }
        }

        $allSession = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $allSession = $_SESSION ?? [];
        }

        $data = [
            'counter' => $counter,
            'allSession' => $allSession,
        ];

        return $this->render('session_demo', $data);
    }

    public function clear()
    {
        Session::clear();
        return $this->redirect('/session');
    }
}
