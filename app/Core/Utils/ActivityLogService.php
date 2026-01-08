<?php

namespace app\Core\Utils;

use app\Core\Request;

class ActivityLogService
{
    public static function logRequest(Request $request, array $extra = []): void
    {
        $ip = DUtil::get_ip();
        
        // Use hardcoded IP only in development
        $appEnv = $_ENV['APP_ENV'] ?? null;
        $isDevelopment = ($appEnv === 'development' || $appEnv === 'dev') || 
                         ($appEnv !== 'production' && $appEnv !== 'prod' && 
                          (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'));
        
        if ($isDevelopment) {
            // Use hardcoded IP only in development Ghana (Ghana IP)
            $ip = '154.161.46.102';
        }
        
        // get country from ip address using GeoIP2
        $country = GEO_RDR->country($ip);
        $countryName = $country->country->name;
        $isp = $country->traits->isp;
        $method = $request->getMethod();
        $url = $request->getUrl();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        $routeParams = $request->getRouteParams() ?? null;
        $session = [
            'has_user' => Session::get('user') ? true : false,
        ];
        
        $payload = array_merge([
            'ip'        => $ip,
            'country'   => $countryName,
            'isp'       => $isp,
            'method'    => $method,
            'url'       => $url,
            'ua'        => $ua,
            'referrer'  => $referrer,
            'routeParams' => $routeParams,
            'session'   => $session,
        ], $extra);

        Logger::write(Logger::CHANNEL_ACTIVITY, $payload);
    }
}