<?php

namespace Controllers;

use Httpeur\ControllerBase;

class RequestDemoController extends ControllerBase
{
    public function index()
    {
        $data = [
            'method' => $this->request->method(),
            'isGet' => $this->request->isGet(),
            'isPost' => $this->request->isPost(),
            'isAjax' => $this->request->isAjax(),
            'path' => $this->request->path(),
            'url' => $this->request->url(),
            'ip' => $this->request->ip(),
            'getParams' => $this->request->allGet(),
            'postParams' => $this->request->allPost(),
            'userAgent' => $this->request->header('User-Agent'),
            'accept' => $this->request->header('Accept'),
            'acceptLanguage' => $this->request->header('Accept-Language'),
        ];

        $this->render('request_demo', $data);
    }
}
