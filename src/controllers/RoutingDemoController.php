<?php

namespace controllers;

use Acheteteper\ControllerBase;

class RoutingDemoController extends ControllerBase
{
    public function index()
    {
        $data = [
            'action' => 'index',
            'method' => $this->request->method(),
            'path' => $this->request->path(),
        ];

        return $this->render('routing_demo', $data);
    }

    public function show()
    {
        $id = $this->request->get('id', 'none');
        $data = [
            'action' => 'show',
            'id' => $id,
            'method' => $this->request->method(),
            'path' => $this->request->path(),
        ];

        return $this->render('routing_demo', $data);
    }

    public function edit()
    {
        $data = [
            'action' => 'edit',
            'method' => $this->request->method(),
            'path' => $this->request->path(),
        ];

        return $this->render('routing_demo', $data);
    }

    public function delete()
    {
        $this->requirePost();

        $data = [
            'action' => 'delete',
            'method' => $this->request->method(),
            'path' => $this->request->path(),
            'message' => 'Delete action executed (POST required)',
        ];

        return $this->render('routing_demo', $data);
    }
}
