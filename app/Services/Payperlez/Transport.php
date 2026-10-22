<?php
namespace app\Services\Payperlez;

interface Transport
{
    /** Return status, headers (lowercase names) and a raw body. Never log request credentials or payloads. */
    public function send(string $method, string $url, array $headers, ?string $body): array;
}
