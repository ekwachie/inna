<?php
namespace app\Services\Payperlez;

final class Sms
{
    public function __construct(private readonly Client $client) {}
    public function send(string $senderId, string $recipient, string $message, string $key): array {
        return $this->client->request('POST', '/sms/messages', ['sender_id' => $senderId, 'recipient' => $recipient, 'message' => $message], $key);
    }
    public function bulk(string $senderId, array $recipients, string $message, string $key): array {
        return $this->client->request('POST', '/sms/batches', ['sender_id' => $senderId, 'recipients' => $recipients, 'message' => $message], $key);
    }
    public function otp(string $senderId, string $recipient, string $purpose, string $key): array {
        return $this->client->request('POST', '/sms/otp/challenges', ['sender_id' => $senderId, 'recipient' => $recipient, 'purpose' => $purpose], $key);
    }
    public function verifyOtp(string $reference, string $code): array {
        if (!preg_match('/^[0-9]{6}$/D', $code)) throw new \InvalidArgumentException('OTP must be a six-digit string.');
        return $this->client->request('POST', '/sms/otp/challenges/' . Client::reference($reference) . '/verify', ['code' => $code]);
    }
    public function message(string $reference): array { return $this->client->request('GET', '/sms/messages/' . Client::reference($reference)); }
    public function batch(string $reference): array { return $this->client->request('GET', '/sms/batches/' . Client::reference($reference)); }
    public function messages(array $query = []): array { return $this->client->request('GET', '/sms/messages', query: $query); }
    public function senders(array $query = []): array { return $this->client->request('GET', '/sms/sender-ids', query: $query); }
    public function balance(): array { return $this->client->request('GET', '/sms/balance'); }
}
