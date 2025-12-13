<?php

namespace Services;

use Acheteteper\ServiceBase;
use Repositories\DbDemoRepository;

class DbDemoService extends ServiceBase
{
    public function dbPath(): string
    {
        return $this->config->dbPath();
    }

    public function listItems(): array
    {
        return $this->repo()->listAll();
    }

    public function addItem(string $name): void
    {
        $this->repo()->add($name);
    }

    public function deleteItem(int $id): void
    {
        $this->repo()->delete($id);
    }

    private function repo(): DbDemoRepository
    {
        /** @var DbDemoRepository */
        return $this->getRepository(DbDemoRepository::class);
    }
}
