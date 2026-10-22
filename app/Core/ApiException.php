<?php
namespace app\Core;

class ApiException extends \RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message, public readonly int $status = 400, public readonly array $fields = [])
    {
        parent::__construct($message);
    }
}
