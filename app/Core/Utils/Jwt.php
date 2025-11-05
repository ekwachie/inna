<?php

namespace app\Core\Utils;

class Jwt
{
    protected string $secret;
    protected string $issuer;

    public function __construct(?string $secret = null, ?string $issuer = null)
    {
        $this->secret = $secret ?: ($_ENV['JWT_SECRET'] ?? 'change_this_secret');
        $this->issuer = $issuer ?: ($_ENV['JWT_ISSUER'] ?? ($_ENV['DOMAIN'] ?? 'inna'));
    }

    public function encode(array $claims, ?int $ttlSeconds = null): string
    {
        $now = time();
        $exp = $ttlSeconds ? $now + $ttlSeconds : ($claims['exp'] ?? ($now + (int)($_ENV['JWT_TTL'] ?? 3600)));

        $payload = array_merge([
            'iss' => $this->issuer,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $exp,
        ], $claims);

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload))
        ];

        $signature = $this->sign(implode('.', $segments));
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function decode(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Malformed token');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = json_decode($this->base64UrlDecode($encodedHeader), true) ?: [];
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true) ?: [];
        $signature = $this->base64UrlDecode($encodedSignature);

        if (($header['alg'] ?? '') !== 'HS256') {
            throw new \UnexpectedValueException('Unsupported algorithm');
        }

        $unsigned = $encodedHeader . '.' . $encodedPayload;
        $expected = $this->sign($unsigned);
        if (!hash_equals($expected, $signature)) {
            throw new \UnexpectedValueException('Signature verification failed');
        }

        $now = time();
        if (isset($payload['nbf']) && $now < (int)$payload['nbf']) {
            throw new \UnexpectedValueException('Token not yet valid');
        }
        if (isset($payload['exp']) && $now >= (int)$payload['exp']) {
            throw new \UnexpectedValueException('Token expired');
        }
        if (isset($payload['iss']) && $payload['iss'] !== $this->issuer) {
            throw new \UnexpectedValueException('Invalid issuer');
        }

        return $payload;
    }

    protected function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret, true);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}


