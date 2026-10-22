<?php
namespace app\Services\Payperlez;

final class Payments
{
    public function __construct(private readonly Client $client) {}

    public function checkout(array $input, string $key): array { return $this->create('/checkout/sessions', $input, $key); }
    public function checkoutStatus(string $id): array { return $this->client->request('GET', '/checkout/sessions/' . Client::reference($id)); }
    public function expireCheckout(string $id): array { return $this->client->request('POST', '/checkout/sessions/' . Client::reference($id) . '/expire', []); }
    public function collect(array $input, string $key): array { return $this->create('/collections', $input, $key); }
    public function disburse(array $input, string $key): array { return $this->create('/disbursements', $input, $key); }
    public function status(string $reference): array { return $this->client->request('GET', '/status/' . Client::reference($reference)); }
    public function statusByClientReference(string $reference): array {
        if ($reference === '' || strlen($reference) > 100) throw new \InvalidArgumentException('Invalid client reference.');
        return $this->client->request('GET', '/status', query: ['client_reference' => $reference]);
    }
    public function collections(array $query = []): array { return $this->client->request('GET', '/collections', query: $query); }
    public function disbursements(array $query = []): array { return $this->client->request('GET', '/disbursements', query: $query); }
    public function balance(): array { return $this->client->request('GET', '/account-balance'); }
    public function banks(): array { return $this->client->request('GET', '/banks'); }
    public function accountName(array $input): array { return $this->client->request('POST', '/account-name', $input); }

    private function create(string $path, array $input, string $key): array
    {
        // Decimal strings preserve GHS amounts without local floating-point arithmetic.
        $amount = $input['amount'] ?? null;
        if (is_int($amount)) $amount = (string) $amount;
        if (!is_string($amount) || !preg_match('/^[0-9]{1,12}(?:\.[0-9]{1,2})?$/D', $amount) || (int)str_replace('.', '', $amount) === 0) {
            throw new \InvalidArgumentException('Use a positive GHS decimal string with at most two decimal places.');
        }
        $input['amount'] = $amount;
        return $this->client->request('POST', $path, $input, $key);
    }
}
