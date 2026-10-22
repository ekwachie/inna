<?php

/**
 * @author      Obed Ademang <kizit2012@gmail.com>
 * @copyright   Copyright (C), 2015 Obed Ademang
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 */

namespace app\Core;

use app\Core\Utils\Jwt;

class DApiController extends Controller
{
    public function success(mixed $data, int $status = 200): never
    {
        ApiResponse::success($data, $status);
    }

    public function error(string $code, string $message, int $status = 400, array $fields = []): never
    {
        ApiResponse::error($code, $message, $status, $fields);
    }

    public function message($status, $message, $errorCode = null)
    {

        if ($status === false) {
            return $this->json([
                "success" => false,
                "data" => $message,
                "error_code" => (!empty($errorCode)) ? $errorCode : "DERRx000"
            ]);
        }
        else {
            return $this->json([
                "success" => true,
                "data" => $message
            ]);
        }
    }

    public function json($value, ?int $options = null, int $dept = 512): void
    {
        $this->header('Content-Type: application/json; charset=utf-8');
        echo json_encode($value, $options ?? 0, $dept);
        exit(0);
    }

    /**
     * Add header to response
     * @param string $value
     * @return static
     */
    public function header(string $value): self
    {
        header($value);

        return $this;
    }

    /**
     * Extracts the Authorization header value if present
     */
    protected function getAuthorizationHeader(): ?string
    {
        foreach (['Authorization', 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $serverKey) {
            if (!empty($_SERVER[$serverKey])) {
                return trim((string)$_SERVER[$serverKey]);
            }
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (is_array($headers)) {
                foreach ($headers as $name => $value) {
                    if (strcasecmp((string)$name, 'Authorization') === 0 && trim((string)$value) !== '') {
                        return trim((string)$value);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Returns Bearer token from Authorization header if available
     */
    protected function getBearerToken(): ?string
    {
        $header = $this->getAuthorizationHeader();
        if (!$header) {
            return null;
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return null;
    }

    /**
     * Issues a JWT token with provided claims and optional TTL (seconds)
     */
    protected function issueToken(array $claims, ?int $ttlSeconds = null): string
    {
        $jwt = new Jwt();
        return $jwt->encode($claims, $ttlSeconds);
    }

    /**
     * Validates the incoming Bearer token. On failure, responds with 401 JSON and exits.
     * Returns decoded payload on success.
     *
     * @param array $requiredRoles Optional role list to enforce (expects `roles` array claim)
     * @return array
     */
    protected function requireJwt(array $requiredRoles = []): array
    {
        $token = $this->getBearerToken();
        if (!$token) {
            $this->header('WWW-Authenticate: Bearer');
            throw new ApiException('AUTH_MISSING_BEARER', 'Provide an Authorization Bearer token.', 401);
        }
        $jwt = new Jwt();
        try { $payload = $jwt->decode($token); }
        catch (\Throwable) {
            $this->header('WWW-Authenticate: Bearer error="invalid_token"');
            throw new ApiException('AUTH_INVALID_TOKEN', 'Token is invalid or expired.', 401);
        }
        $roles = is_array($payload['roles'] ?? null) ? $payload['roles'] : [];
        if ($requiredRoles && !array_intersect($requiredRoles, $roles)) throw new ApiException('AUTH_FORBIDDEN', 'Required role is missing.', 403);
        return $payload;
    }
}
