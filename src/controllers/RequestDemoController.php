<?php

namespace controllers;

use Acheteteper\ControllerBase;

class RequestDemoController extends ControllerBase
{
    public function options()
    {
        return $this->response
            ->setStatus(204)
            ->setHeader('Allow', 'GET, HEAD, POST, OPTIONS, QUERY');
    }

    public function index()
    {
        $data = [
            'method' => $this->request->method(),
            'isGet' => $this->request->isGet(),
            'isPost' => $this->request->isPost(),
            'isAjax' => $this->request->isAjax(),
            'isSafe' => $this->request->isSafe(),
            'path' => $this->request->path(),
            'url' => $this->request->url(),
            'ip' => $this->request->ip(),
            'getParams' => $this->request->allGet(),
            'postParams' => $this->request->allPost(),
            'userAgent' => $this->request->header('User-Agent'),
            'accept' => $this->request->header('Accept'),
            'acceptLanguage' => $this->request->header('Accept-Language'),
        ];

        return $this->render('request_demo', $data);
    }

    public function jsonInput()
    {
        $this->requirePost();
        $data = $this->request->json();
        return $this->json([
            'received' => $data,
            'bytes' => strlen($this->request->body()),
        ]);
    }
}
