<?php

namespace app\Core\Utils;

use app\Core\Request;

class ActivityLogService
{
    public static function logRequest(Request $request, array $extra = []): void
    {
        $ip = DUtil::get_ip();
        $ip = '197.210.197.217';
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