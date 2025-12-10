<?php

namespace Controllers;

use Httpeur\ControllerBase;

class ResponseDemoController extends ControllerBase
{
    public function index()
    {
        $status = $this->request->get('status', 200);
        $status = (int)$status;

        $this->response->setStatus($status);
        $this->response->setHeader('X-Demo-Header', 'Demo-Value');

        $data = [
            'status' => $this->response->getStatus(),
            'headers' => $this->response->getHeaders(),
        ];

        $this->render('response_demo', $data);
    }
}
