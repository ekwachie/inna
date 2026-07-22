<?php

namespace app\Core\Utils;

use app\Core\Request;

class AuditEvent
{
    public static function log(
        Request $request,
        string $event,
        array $meta = [],
        ?int $actorId = null,
        ?string $actorUsername = null,
        string $status = 'success',
        ?string $resource = null
    ): void {
        AuditLogService::log(
            $request,
            $event,
            $resource ?? 'system',
            array_merge(['event_key' => $event], $meta),
            $actorId,
            $actorUsername,
            $status
        );
    }
}
