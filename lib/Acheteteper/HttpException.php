<?php

namespace Acheteteper;

class HttpException extends \Exception
{
    public function __construct(private int $status, string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message === '' ? 'HTTP ' . $status : $message, $status, $previous);
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
