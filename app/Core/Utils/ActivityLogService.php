<?php

namespace app\Core\Utils;

use app\Core\Request;

class ActivityLogService
{
    public static function logRequest(Request $request, array $extra = []): void
    {
        try {
            $ip = DUtil::get_ip();

            $countryName = null;
            $isp = null;
            try {
                if (defined('GEO_RDR') && GEO_RDR) {
                    $country = GEO_RDR->country($ip);
                    $countryName = $country->country->name ?? null;
                    $isp = $country->traits->isp ?? null;
                }
            } catch (\Throwable $e) {
                // Keep activity logging resilient even if GeoIP lookup fails.
            }

            $method = strtoupper((string)$request->getMethod());
            $url = (string)$request->getUrl();
            $isCli = PHP_SAPI === 'cli' || $method === 'CLI' || strpos($url, '/cli') === 0;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $referrer = $_SERVER['HTTP_REFERER'] ?? null;
            $isAjax = (strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest')
                || strpos(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json') !== false;
            $routeParams = $request->getRouteParams() ?? null;
            $serverPort = $_SERVER['SERVER_PORT'] ?? null;
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

            $sessionUser = Session::get('user');
            $user = is_array($sessionUser) ? ($sessionUser[0] ?? $sessionUser) : [];
            $hasUser = is_array($user) && !empty($user);
            $role = strtolower((string)($user['role'] ?? ''));
            $isAdmin = $hasUser && $role === 'admin';
            $actorType = $hasUser ? ($isAdmin ? 'admin' : 'user') : 'guest';

            $session = [
                'has_user' => $hasUser,
                'actor_type' => $actorType,
                'user_id' => $user['id'] ?? null,
                'username' => $user['username'] ?? null,
                'email' => $user['email'] ?? null,
                'role' => $role ?: null,
                'is_admin' => $isAdmin,
                'session_fingerprint' => session_id() ? hash('sha256', session_id()) : null,
            ];

            $payload = array_merge([
                'request_id' => \app\Core\ApiResponse::requestId(),
                'ip' => $ip,
                'country' => $countryName,
                'isp' => $isp,
                'method' => $method,
                'url' => $url,
                'is_cli' => $isCli,
                'ua' => $ua,
                'referrer' => $referrer,
                'is_ajax' => $isAjax,
                'accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
                'https' => $isHttps,
                'server_port' => $serverPort,
                'routeParams' => $routeParams,
                'session' => $session,
            ], $extra);

            Logger::write(Logger::CHANNEL_ACTIVITY, $payload);
        } catch (\Throwable $e) {
            // Never block the request lifecycle because logging failed.
        }
    }
}
