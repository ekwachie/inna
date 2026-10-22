<?php
namespace app\Core;

final class ApiResponse
{
    private static ?string $requestId = null;

    public static function requestId(): string
    {
        return self::$requestId ??= 'req_' . bin2hex(random_bytes(16));
    }

    public static function success(mixed $data, int $status = 200): never
    {
        self::send(['success' => true, 'data' => $data], $status);
    }

    public static function error(string $code, string $message, int $status, array $fields = []): never
    {
        self::send(['success' => false, 'error' => ['code' => $code, 'message' => $message, 'fields' => (object) $fields]], $status);
    }

    private static function send(array $payload, int $status): never
    {
        $payload['meta'] = ['request_id' => self::requestId()];
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Request-ID: ' . self::requestId());
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') echo $json;
        exit;
    }
}
