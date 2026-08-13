<?php

namespace controllers;

use Acheteteper\ControllerBase;
use services\DbDemoService;

class DBDemoController extends ControllerBase
{

    private function dbService(): DbDemoService
    {
        /** @var DbDemoService */
        return $this->getService(DbDemoService::class);
    }

    public function index()
    {
        $service = $this->dbService();

        if ($this->isPost()) {
            $action = $this->request->post('action');
            if ($action === 'add') {
                $name = trim((string)$this->request->post('name', ''));
                if ($name === '') {
                    $this->fail(400, 'Name is required');
                }
                $service->addItem($name);
                    return $this->redirect('/db');
            } elseif ($action === 'delete') {
                $id = (int)$this->request->post('id', 0);
                if ($id > 0) {
                    $service->deleteItem($id);
                }
                    return $this->redirect('/db');
            }
        }

        $items = $service->listItems();

        return $this->render('db_demo', [
            'items' => $items,
            'dbPath' => $service->dbPath(),
        ]);
    }
}
