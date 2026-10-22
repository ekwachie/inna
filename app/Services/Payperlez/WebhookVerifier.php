<?php
namespace app\Services\Payperlez;

final class WebhookVerifier
{
    public function __construct(private readonly array $secrets, private readonly string $businessId, private readonly string $environment, private readonly int $tolerance = 300)
    {
        if (!$secrets || $businessId === '' || !in_array($environment, ['test', 'live'], true) || $tolerance < 1 || $tolerance > 300) throw new \InvalidArgumentException('Configure webhook secrets, business, environment and tolerance.');
        foreach ($secrets as $secret) if (!is_string($secret) || !str_starts_with($secret, 'whsec_') || strlen($secret) < 32) throw new \InvalidArgumentException('Use complete webhook signing secrets.');
    }

    public function verify(string $rawBody, string $signature, ?int $now = null): array
    {
        if (strlen($rawBody) > 1048576 || !preg_match('/^t=([0-9]{10}),v1=([a-f0-9]{64})$/D', $signature, $parts)
            || abs(($now ?? time()) - (int) $parts[1]) > $this->tolerance) throw new \UnexpectedValueException('Invalid webhook signature or timestamp.');
        $valid = false;
        foreach ($this->secrets as $secret) $valid = hash_equals(hash_hmac('sha256', $parts[1] . '.' . $rawBody, $secret), $parts[2]) || $valid;
        if (!$valid) throw new \UnexpectedValueException('Invalid webhook signature.');
        $event = json_decode($rawBody, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($event) || ($event['business_id'] ?? null) !== $this->businessId || ($event['environment'] ?? null) !== $this->environment
            || !is_string($event['id'] ?? null) || $event['id'] === '' || !is_string($event['type'] ?? null) || !is_array($event['data'] ?? null)) throw new \UnexpectedValueException('Unexpected webhook identity or payload.');
        return $event;
    }
}
