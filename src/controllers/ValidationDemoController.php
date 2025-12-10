<?php

namespace Controllers;

use Httpeur\ControllerBase;

class ValidationDemoController extends ControllerBase
{
    public function index()
    {
        $this->render('validation_demo');
    }

    public function submit()
    {
        $this->requirePost();

        $validator = $this->validate();
        $validator
            ->required('name', 'Name is required')
            ->minLength('name', 3, 'Name must be at least 3 characters')
            ->maxLength('name', 50, 'Name must be at most 50 characters')
            ->required('email', 'Email is required')
            ->email('email', 'Invalid email address')
            ->required('phone', 'Phone is required')
            ->pattern('phone', '/^[0-9+\-\s()]+$/', 'Phone must contain only numbers and phone characters')
            ->required('password', 'Password is required')
            ->minLength('password', 8, 'Password must be at least 8 characters')
            ->required('password_confirm', 'Password confirmation is required')
            ->equals('password', 'password_confirm', 'Passwords must match');

        $data = [
            'errors' => $validator->getErrors(),
            'values' => $this->request->allPost(),
        ];

        if ($validator->isValid()) {
            $data['success'] = true;
            $data['message'] = 'Registration successful!';
        }

        $this->render('validation_demo', $data);
    }
}
