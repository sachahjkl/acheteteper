<?php

namespace Acheteteper;

class HttpException extends \Exception
{
    public function __construct(private int $status, string $message = '')
    {
        parent::__construct($message === '' ? 'HTTP ' . $status : $message, $status);
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
