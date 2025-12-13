<?php

namespace controllers;

use Acheteteper\ControllerBase;

class FlashDemoController extends ControllerBase
{
    public function index()
    {
        $this->render('flash_demo');
    }

    public function submit()
    {
        $this->requirePost();

        $type = $this->request->post('type', 'success');
        $message = $this->request->post('message', 'Flash message sent!');

        $this->redirectWithFlash('/flash', $type, $message);
    }
}
