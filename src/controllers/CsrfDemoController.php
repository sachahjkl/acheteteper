<?php

namespace Controllers;

use Httpeur\ControllerBase;
use Httpeur\Csrf;
use Httpeur\HttpException;

class CsrfDemoController extends ControllerBase
{
    public function index()
    {
        $data = [
            'token' => Csrf::token(),
        ];

        $this->render('csrf_demo', $data);
    }

    public function submit()
    {
        $this->requirePost();

        try {
            $this->requireCsrf();
            $data = [
                'success' => true,
                'message' => 'CSRF token validated successfully!',
                'token' => Csrf::token(),
            ];
        } catch (HttpException $e) {
            $data = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->render('csrf_demo', $data);
    }
}
