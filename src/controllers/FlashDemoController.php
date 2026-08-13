<?php

namespace controllers;

use Acheteteper\ControllerBase;

class FlashDemoController extends ControllerBase
{
    public function index()
    {
        return $this->render('flash_demo');
    }

    public function submit()
    {
        $this->requirePost();
        $this->requireCsrf();

        $type = $this->request->post('type', 'success');
        $message = $this->request->post('message', 'Flash message sent!');

        return $this->redirectWithFlash('/flash', $type, $message);
    }
}
