<?php

namespace repositories;

use Acheteteper\RepositoryBase;

class DbDemoRepository extends RepositoryBase
{
    private function ds()
    {
        return $this->datasource();
    }

    private function ensureSchema(): void
    {
        $this->ds()->execute('CREATE TABLE IF NOT EXISTS demo_items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
    }

    public function listAll(): array
    {
        $this->ensureSchema();
        return $this->ds()->query('SELECT id, name FROM demo_items ORDER BY id ASC');
    }

    public function add(string $name): void
    {
        $this->ensureSchema();
        $this->ds()->execute('INSERT INTO demo_items (name) VALUES (:name)', ['name' => $name]);
    }

    public function delete(int $id): void
    {
        $this->ensureSchema();
        $this->ds()->execute('DELETE FROM demo_items WHERE id = :id', ['id' => $id]);
    }
}
