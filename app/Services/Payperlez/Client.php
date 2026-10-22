<?php
namespace app\Services\Payperlez;

final class Client
{
    private Transport $transport;
    private string $baseUrl;

    public function __construct(private readonly string $apiKey, string $baseUrl = 'https://api.payperlez.org/api/v1', ?Transport $transport = null)
    {
        if ($apiKey === '' || preg_match('/[\x00-\x20\x7f]/', $apiKey)) throw new \InvalidArgumentException('Configure a Payperlez API key.');
        $parts = parse_url($baseUrl);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user'], $parts['pass'])
            || isset($parts['user']) || isset($parts['query']) || isset($parts['fragment'])) throw new \InvalidArgumentException('Use an HTTPS API base URL without credentials, query or fragment.');
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->transport = $transport ?? new CurlTransport();
    }

    public static function fromEnvironment(): self
    {
        return new self($_ENV['PAYPERLEZ_API_KEY'] ?? '', $_ENV['PAYPERLEZ_BASE_URL'] ?? 'https://api.payperlez.org/api/v1');
    }

    public function payments(): Payments { return new Payments($this); }
    public function sms(): Sms { return new Sms($this); }

    public function request(string $method, string $path, ?array $body = null, ?string $idempotencyKey = null, array $query = []): array
    {
        if (!in_array($method, ['GET', 'POST'], true) || !preg_match('~^/[a-zA-Z0-9/_%-]+$~D', $path) || str_contains($path, '..')) throw new \InvalidArgumentException('Invalid API request path or method.');
        $headers = ['X-API-Key: ' . $this->apiKey, 'Accept: application/json', 'Content-Type: application/json', 'User-Agent: Inna-Payperlez/2'];
        if ($idempotencyKey !== null) {
            if (!preg_match('/^[A-Za-z0-9._:-]{8,100}$/D', $idempotencyKey)) throw new \InvalidArgumentException('Idempotency key must contain 8-100 safe characters.');
            $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
        }
        $url = $this->baseUrl . $path . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
        $response = $this->transport->send($method, $url, $headers, $body !== null ? json_encode($body, JSON_THROW_ON_ERROR) : null);
        $status = (int) $response['status'];
        $headers = $response['headers'];
        $retryAfter = isset($headers['retry-after']) && ctype_digit($headers['retry-after']) ? min((int) $headers['retry-after'], 86400) : null;
        try { $payload = json_decode($response['body'], true, 64, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new ServiceException('INVALID_RESPONSE', $status, retryAfter: $retryAfter, uncertain: $method !== 'GET'); }
        if (!is_array($payload) || !is_bool($payload['success'] ?? null) || !is_array($payload['meta'] ?? null)) {
            throw new ServiceException('INVALID_RESPONSE', $status, uncertain: $method !== 'GET');
        }
        $requestId = is_string($payload['meta']['request_id'] ?? null) ? $payload['meta']['request_id'] : null;
        if ($status < 200 || $status >= 300 || !$payload['success']) {
            $code = $payload['error']['code'] ?? 'HTTP_ERROR';
            $code = is_string($code) && preg_match('/^[A-Z0-9_]{1,100}$/D', $code) ? $code : 'HTTP_ERROR';
            throw new ServiceException($code, $status, $requestId, $retryAfter, $method !== 'GET' && $status >= 500,
                is_array($payload['error']['fields'] ?? null) ? $payload['error']['fields'] : []);
        }
        if (!array_key_exists('data', $payload)) throw new ServiceException('INVALID_RESPONSE', $status, $requestId, uncertain: $method !== 'GET');
        return $payload;
    }

    public static function reference(string $value): string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{1,100}$/D', $value)) throw new \InvalidArgumentException('Invalid reference.');
        return rawurlencode($value);
    }
}
