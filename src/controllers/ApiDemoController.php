<?php

namespace Controllers;

use Acheteteper\ControllerBase;

class ApiDemoController extends ControllerBase
{
    public function index()
    {
        $this->render('api_demo');
    }

    public function users()
    {
        $users = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com'],
        ];

        $this->json(['users' => $users]);
    }

    public function user()
    {
        $id = $this->request->get('id', 1);
        $user = ['id' => (int)$id, 'name' => 'User ' . $id, 'email' => 'user' . $id . '@example.com'];

        $this->json(['user' => $user]);
    }

    public function error()
    {
        $this->response->json(['error' => 'Something went wrong'], 500);
    }
}
