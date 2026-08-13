<?php

namespace Tests;

final class Fuzz
{
    public function __construct(int $seed)
    {
        mt_srand($seed);
    }

    public function repeat(int $count, callable $test): void
    {
        for ($case = 0; $case < $count; $case++) {
            try {
                $test($this, $case);
            } catch (\Throwable $error) {
                throw new \RuntimeException("Fuzz case $case failed: {$error->getMessage()}", 0, $error);
            }
        }
    }

    public function integer(int $minimum, int $maximum): int
    {
        return mt_rand($minimum, $maximum);
    }

    public function pick(array $values): mixed
    {
        return $values[$this->integer(0, count($values) - 1)];
    }

    public function string(int $minimumLength, int $maximumLength, string $alphabet): string
    {
        $length = $this->integer($minimumLength, $maximumLength);
        $lastIndex = strlen($alphabet) - 1;
        $result = '';
        for ($index = 0; $index < $length; $index++) {
            $result .= $alphabet[$this->integer(0, $lastIndex)];
        }
        return $result;
    }

    public function jsonValue(int $depth = 0): mixed
    {
        $scalar = fn() => $this->pick([
            null,
            (bool) $this->integer(0, 1),
            $this->integer(-1000000, 1000000),
            $this->string(0, 30, "abcdefghijklmnopqrstuvwxyz0123456789 <>/&\"'"),
        ]);

        if ($depth >= 3 || $this->integer(0, 2) === 0) {
            return $scalar();
        }

        $result = [];
        $count = $this->integer(0, 5);
        for ($index = 0; $index < $count; $index++) {
            $result[$this->string(1, 10, 'abcdefghijklmnopqrstuvwxyz')] = $this->jsonValue($depth + 1);
        }
        return $result;
    }
}
