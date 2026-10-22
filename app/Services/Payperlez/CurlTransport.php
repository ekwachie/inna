<?php
namespace app\Services\Payperlez;

final class CurlTransport implements Transport
{
    public function __construct(private readonly int $connectTimeout = 5, private readonly int $timeout = 30, private readonly int $maxResponseBytes = 2097152)
    {
        if ($connectTimeout < 1 || $timeout < $connectTimeout || $maxResponseBytes < 1) throw new \InvalidArgumentException('Invalid HTTP limits.');
    }

    public function send(string $method, string $url, array $headers, ?string $body): array
    {
        if (!extension_loaded('curl')) throw new \RuntimeException('Payperlez services require ext-curl.');
        $curl = curl_init($url);
        $responseHeaders = [];
        $response = '';
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$responseHeaders): int {
                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($name))] = trim($value);
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION => function ($handle, string $chunk) use (&$response): int {
                if (strlen($response) + strlen($chunk) > $this->maxResponseBytes) return 0;
                $response .= $chunk;
                return strlen($chunk);
            },
        ]);
        if ($body !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $ok = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if ($ok === false) throw new ServiceException('TRANSPORT_ERROR', $status, uncertain: $method !== 'GET');
        return ['status' => $status, 'headers' => $responseHeaders, 'body' => $response];
    }
}
