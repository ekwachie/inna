<?php
namespace app\Core\Utils;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key;

class Jwt
{
    protected string $secret;
    protected string $issuer;

    public function __construct(?string $secret = null, ?string $issuer = null)
    {
        $this->secret = $secret ?? ($_ENV['JWT_SECRET'] ?? '');
        $this->issuer = $issuer ?? ($_ENV['JWT_ISSUER'] ?? ($_ENV['DOMAIN'] ?? 'inna'));
        if (strlen($this->secret) < 32 || in_array($this->secret, ['change_this_secret', 'your_long_random_secret'], true)) {
            throw new \RuntimeException('Configure JWT_SECRET with at least 32 random bytes.');
        }
    }

    public function encode(array $claims, ?int $ttlSeconds = null): string
    {
        $ttl = $ttlSeconds ?? (int) ($_ENV['JWT_TTL'] ?? 3600);
        if ($ttl < 1) throw new \InvalidArgumentException('JWT lifetime must be positive.');
        $now = time();
        $claims['iss'] = $this->issuer;
        $claims['iat'] = $now;
        $claims['nbf'] = $now;
        $claims['exp'] = $now + $ttl;
        return FirebaseJwt::encode($claims, $this->secret, 'HS256');
    }

    public function decode(string $jwt): array
    {
        $claims = (array) FirebaseJwt::decode($jwt, new Key($this->secret, 'HS256'));
        if (($claims['iss'] ?? null) !== $this->issuer || !is_int($claims['exp'] ?? null)
            || !is_int($claims['iat'] ?? null) || !is_int($claims['nbf'] ?? null)) {
            throw new \UnexpectedValueException('Invalid required token claims.');
        }
        return $claims;
    }
}


