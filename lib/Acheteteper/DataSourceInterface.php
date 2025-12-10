<?php

namespace Acheteteper;

interface DataSourceInterface
{
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): int;
    public function scalar(string $sql, array $params = []): mixed;
    public function lastInsertId(): string|int;
    public function transaction(callable $callback): mixed;
    public function raw(callable $callback): mixed;
}
