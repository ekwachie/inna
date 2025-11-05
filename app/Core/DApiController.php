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
use Exception;

class DApiController
{
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
        echo json_encode($value, $options, $dept);
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
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER['Authorization']);
        }
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if ($headers && isset($headers['Authorization'])) {
                return trim($headers['Authorization']);
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
            $this->json([
                'success' => false,
                'data' => 'Missing Authorization Bearer token',
                'error_code' => 'AUTH_MISSING_BEARER'
            ]);
        }

        try {
            $jwt = new Jwt();
            $payload = $jwt->decode($token);

            if (!empty($requiredRoles)) {
                $roles = isset($payload['roles']) && is_array($payload['roles']) ? $payload['roles'] : [];
                $hasRole = count(array_intersect($requiredRoles, $roles)) > 0;
                if (!$hasRole) {
                    $this->json([
                        'success' => false,
                        'data' => 'Forbidden: insufficient role',
                        'error_code' => 'AUTH_FORBIDDEN'
                    ]);
                }
            }

            return $payload;
        } catch (Exception $e) {
            $this->header('WWW-Authenticate: Bearer error="invalid_token"');
            $this->json([
                'success' => false,
                'data' => 'Unauthorized: ' . $e->getMessage(),
                'error_code' => 'AUTH_INVALID_TOKEN'
            ]);
        }

        return [];
    }
}