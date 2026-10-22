<?php
/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019. Payperlez Inc.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */
namespace app\Core;

class Request
{
    private array $routeParams = [];
    private array $attributes = [];
    private ?string $rawBody = null;

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function getHeader(string $name, ?string $default = null): ?string
    {
        $key = strtoupper(str_replace('-', '_', $name));
        return $_SERVER['HTTP_' . $key] ?? $_SERVER[$key] ?? $default;
    }

    public function getQueryParams(): array
    {
        return $_GET;
    }

    public function getRawBody(int $maxBytes = 1048576): string
    {
        if ($maxBytes < 1) throw new \InvalidArgumentException('Body limit must be positive.');
        if ($this->rawBody === null) {
            $raw = file_get_contents('php://input', false, null, 0, $maxBytes + 1);
            if ($raw === false || strlen($raw) > $maxBytes) throw new ApiException('PAYLOAD_TOO_LARGE', 'Request body is too large.', 413);
            $this->rawBody = $raw;
        }
        if (strlen($this->rawBody) > $maxBytes) throw new ApiException('PAYLOAD_TOO_LARGE', 'Request body is too large.', 413);
        return $this->rawBody;
    }

    public function getJsonBody(int $maxBytes = 65536): array
    {
        $type = strtolower(trim(explode(';', $this->getHeader('Content-Type', ''))[0]));
        if ($type !== 'application/json') throw new ApiException('UNSUPPORTED_MEDIA_TYPE', 'Use Content-Type: application/json.', 415);
        try {
            $value = json_decode($this->getRawBody($maxBytes), false, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new ApiException('INVALID_JSON', 'Request body must be valid JSON.', 400);
        }
        if (!$value instanceof \stdClass) throw new ApiException('INVALID_JSON', 'Request body must be a JSON object.', 400);
        return (array) $value;
    }

    public function getMethod()
    {
        return strtolower((string)($_SERVER['REQUEST_METHOD'] ?? 'get'));
    }

    public function getUrl()
    {
        $path = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path;
    }

    public function isGet()
    {
        return $this->getMethod() === 'get';
    }

    public function isPost()
    {
        return $this->getMethod() === 'post';
    }

    public function getBody()
    {
        $data = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $data[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost()) {
            $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
            if (stripos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $decoded = json_decode($json ?: '', true);
                $data = is_array($decoded) ? $decoded : [];
            } else {
                foreach ($_POST as $key => $value) {
                    $data[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return $data;
    }

    /**
     * @param $params
     * @return self
     */
    public function setRouteParams($params)
    {
        $this->routeParams = $params;
        return $this;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function getRouteParam($param, $default = null)
    {
        return $this->routeParams[$param] ?? $default;
    }
}
