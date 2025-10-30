<?php

namespace app\Core\Utils;
use app\Core\Request;
class AuditLogService
{
    public static function log(Request $request,
        string $action,
        string $resource,
        array $meta = [],
        ?int $userId = null,
        ?string $username = null,
        string $status = 'success'
    ): void {
        $ip = DUtil::get_ip();
        $ip = '197.210.197.217';
        $country = GEO_RDR->country($ip);
        $countryName = $country->country->name;
        $isp = $country->traits->isp;
        $method = $request->getMethod();
        $url = $request->getUrl();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        $routeParams = $request->getRouteParams() ?? null;

        $payload = array_merge([
            'action'    => $action,
            'resource'  => $resource,
            'status'    => $status,
            'user'      => [
                'id'       => $userId,
                'username' => $username,
                'present'  => Session::issert('user'),
            ],
            'ip'        => $ip,
            'country'   => $countryName,
            'isp'       => $isp,
            'method'    => $method,
            'url'       => $url,
            'ua'        => $ua,
            'referrer'  => $referrer,
            'routeParams' => $routeParams
        ], $meta);

        //hash the payload
        $payloadHash = self::encryptPayload($payload);
        
        // append the payload to the payload
        $payload['hashlog'] = $payloadHash;
        Logger::write(Logger::CHANNEL_AUDIT, $payload);
    }

    //encrypt the payload
    public static function encryptPayload(array $payload): string
    {
        return DUtil::encrypt(json_encode($payload), $_ENV['AUDIT_LOG_SECRET']);
    }

    //decrypt the payload
    public static function decryptPayload(string $payload)
    {
        return DUtil::decrypt($payload, $_ENV['AUDIT_LOG_SECRET']);
    }
}