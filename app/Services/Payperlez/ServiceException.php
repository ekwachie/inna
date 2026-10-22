<?php
namespace app\Services\Payperlez;

final class ServiceException extends \RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly int $httpStatus = 0,
        public readonly ?string $requestId = null,
        public readonly ?int $retryAfter = null,
        public readonly bool $uncertain = false,
        public readonly array $fields = []
    ) {
        parent::__construct('Payperlez request failed: ' . $errorCode);
    }
}
